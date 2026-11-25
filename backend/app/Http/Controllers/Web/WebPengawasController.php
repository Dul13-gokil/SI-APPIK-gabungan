<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Pengawas lapangan (berasal dari pengawas lapangan) mengelola anggota regunya sendiri.
 * Batas wewenang: hanya regu tempat ia terdaftar. Regu lain tetap 403 (Aturan 3.2).
 */
class WebPengawasController extends Controller
{
    private const MAX_MEMBERS = 15;

    public function index(): View
    {
        $user = auth()->user();
        $team = $this->ownTeamOrFail();
        $this->assertMayManage($team);

        $members = $team
            ? $team->users()->orderBy('name')->get()
            : collect();

        // Kandidat: pengawas lapangan yang belum tergabung di regu mana pun.
        $candidates = User::whereIn('role', ['PETUGAS', 'PENGAWAS'])
            ->whereNotIn('id', TeamMember::query()->select('user_id'))
            ->orderBy('name')
            ->get();

        return view('pengawas.team', compact('team', 'members', 'candidates'));
    }

    public function addMember(Request $request): RedirectResponse
    {
        $team = $this->ownTeamOrFail();
        $this->assertMayManage($team);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ], [
            'user_id.required' => 'Pilih pengawas lapangan yang akan ditambahkan.',
            'user_id.exists'   => 'Akun pengawas lapangan tersebut tidak ditemukan.',
        ]);

        $user = User::findOrFail($validated['user_id']);

        if (! in_array(strtoupper((string) $user->role), ['PETUGAS', 'PENGAWAS'], true)) {
            return back()->with('error', 'Hanya akun pengawas lapangan yang dapat ditambahkan ke regu.');
        }

        if ($team->members()->count() >= self::MAX_MEMBERS) {
            return back()->with('error', 'Regu sudah mencapai batas maksimum personil (' . self::MAX_MEMBERS . ' orang).');
        }

        $existing = TeamMember::with('team')->where('user_id', $user->id)->first();
        if ($existing) {
            $reguName = $existing->team?->name ?? 'lain';

            return back()->with('error', "Petugas ini sudah terdaftar dalam regu '{$reguName}'. Satu petugas hanya dapat bergabung dalam 1 regu.");
        }

        TeamMember::create([
            'team_id' => $team->id,
            'user_id' => $user->id,
            'role'    => 'MEMBER',
        ]);

        AuditLog::create([
            'actor_id'    => auth()->id(),
            'action'      => 'TEAM_MEMBER_ADDED_BY_PENGAWAS',
            'entity_type' => Team::class,
            'entity_id'   => $team->id,
            'before_json' => null,
            'after_json'  => ['user_id' => $user->id, 'team_id' => $team->id],
            'ip'          => $request->ip(),
        ]);

        return back()->with('success', "{$user->name} berhasil ditambahkan ke {$team->name}.");
    }

    public function removeMember(Request $request, int $user): RedirectResponse
    {
        $team = $this->ownTeamOrFail();
        $this->assertMayManage($team);
        $member = User::findOrFail($user);

        if ((int) $member->id === (int) auth()->id()) {
            return back()->with('error', 'Anda tidak dapat mengeluarkan diri sendiri dari regu.');
        }

        $deleted = TeamMember::where('team_id', $team->id)->where('user_id', $member->id)->delete();

        if (! $deleted) {
            return back()->with('error', "{$member->name} bukan anggota regu Anda.");
        }

        if ((int) $team->leader_id === (int) $member->id) {
            $team->update(['leader_id' => null]);
        }

        AuditLog::create([
            'actor_id'    => auth()->id(),
            'action'      => 'TEAM_MEMBER_REMOVED_BY_PENGAWAS',
            'entity_type' => Team::class,
            'entity_id'   => $team->id,
            'before_json' => ['user_id' => $member->id, 'team_id' => $team->id],
            'after_json'  => null,
            'ip'          => $request->ip(),
        ]);

        return back()->with('success', "{$member->name} dikeluarkan dari {$team->name}.");
    }

    /**
     * Pengawas tanpa regu tidak boleh mengelola apa pun.
     */
    /**
     * Hanya ketua regu (team.leader_id) atau akun berperan PENGAWAS yang boleh
     * mengubah susunan anggota. Anggota biasa tetap 403.
     */
    private function assertMayManage(Team $team): void
    {
        $user = auth()->user();

        abort_unless(
            $user->isPengawas() || (int) $team->leader_id === (int) $user->id,
            403,
            'Hanya ketua regu yang dapat mengelola anggota regu.'
        );
    }

    private function ownTeamOrFail(): Team
    {
        $team = auth()->user()->currentTeam();

        abort_if(! $team, 403, 'Akun Anda belum terdaftar pada regu mana pun. Hubungi Operator untuk penempatan regu.');

        return $team;
    }
}
