<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\SystemSetting;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class WebTeamController extends Controller
{
    /**
     * Tambahkan anggota regu sekaligus membuat identitas akunnya.
     *
     * Operator PUPR dan Pengawas Lapangan tidak punya akses ke menu Kelola Akun,
     * jadi identitas personil regu (nama, email, nomor HP) diisi di sini. Sandi
     * sementara dibuat acak dan ditampilkan sekali agar tidak ada akun bersandi
     * bawaan yang bisa dipakai orang lain.
     */
    public function storeMemberIdentity(Request $request, Team $team): RedirectResponse
    {
        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'phone' => ['nullable', 'string', 'max:20', 'unique:users,phone'],
            'role'  => 'nullable|in:LEADER,MEMBER',
        ], [
            'email.unique' => 'Email tersebut sudah dipakai akun lain.',
            'phone.unique' => 'Nomor HP tersebut sudah terdaftar.',
        ]);

        $this->authorizeTeam($team);

        if ($team->members()->count() >= 15) {
            return back()->with('error', 'Regu sudah mencapai batas maksimum personil (15 orang).');
        }

        $sandi = Str::password(10, symbols: false);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'phone'    => $validated['phone'] ?? null,
            'password' => Hash::make($sandi),
            'role'     => 'PETUGAS',
            // Pesan ke operator menjanjikan sandi wajib diganti saat login pertama;
            // tandai di sini supaya janji itu benar-benar dijalankan sistem.
            'must_change_password' => true,
        ]);

        $peran = $validated['role'] ?? 'MEMBER';
        TeamMember::create([
            'team_id' => $team->id,
            'user_id' => $user->id,
            'role'    => $peran,
        ]);

        if ($peran === 'LEADER') {
            $team->update(['leader_id' => $user->id]);
        }

        AuditLog::create([
            'actor_id'    => auth()->id(),
            'action'      => 'TEAM_MEMBER_CREATED',
            'entity_type' => User::class,
            'entity_id'   => $user->id,
            'before_json' => null,
            'after_json'  => [
                'email'   => $user->email,
                'team_id' => $team->id,
                'role'    => $peran,
            ],
            'ip'          => $request->ip(),
        ]);

        return back()->with(
            'success',
            "Anggota {$user->name} ditambahkan ke {$team->name}. Sandi sementara: {$sandi} — catat dan berikan ke yang bersangkutan, wajib diganti saat login pertama."
        );
    }

    /**
     * Pengawas hanya boleh mengubah regunya sendiri; operator dan admin semua regu.
     */
    private function authorizeTeam(Team $team): void
    {
        $user = auth()->user();

        if ($user->isOperator() || $user->isAdmin() || $user->isKepalaDaerah()) {
            return;
        }

        // Ketua regu ditunjuk lewat team.leader_id; anggota biasa tidak boleh menambah personil.
        $teamId = $user->currentTeam()?->id;
        abort_unless(
            $teamId === $team->id && ($user->isPengawas() || (int) $team->leader_id === (int) $user->id),
            403,
            'Hanya ketua regu yang dapat mengelola anggota regu ini.'
        );
    }

    /**
     * Simpan regu / tim baru
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:100',
            'code'          => 'nullable|string|max:20|unique:teams,code',
            'department_id' => 'nullable|exists:departments,id',
            'leader_id'     => 'nullable|exists:users,id',
            'district'      => 'nullable|string|max:255',
            'coverage_area' => 'nullable|string|max:255',
            'description'   => 'nullable|string',
            'notes'         => 'nullable|string',
        ]);

        if (empty($validated['code'])) {
            $count = Team::count() + 1;
            $validated['code'] = 'REGU-' . str_pad($count, 2, '0', STR_PAD_LEFT);
        } else {
            $validated['code'] = strtoupper(trim($validated['code']));
        }

        $district = $validated['district'] ?? $validated['coverage_area'] ?? 'Semua Wilayah';
        $description = $validated['description'] ?? $validated['notes'] ?? null;

        $team = Team::create([
            'name'          => $validated['name'],
            'code'          => $validated['code'],
            'department_id' => $validated['department_id'] ?? null,
            'leader_id'     => $validated['leader_id'] ?? null,
            'district'      => $district,
            'description'   => $description,
            'is_active'     => true,
        ]);

        // Jika leader di-set, otomatis masukkan ke team_members sebagai LEADER
        if (!empty($validated['leader_id'])) {
            TeamMember::updateOrCreate(
                ['team_id' => $team->id, 'user_id' => $validated['leader_id']],
                ['role' => 'LEADER']
            );
        }

        return redirect()->back()->with('success', "Regu '{$team->name}' ({$team->code}) berhasil dibentuk.");
    }

    /**
     * Perbarui data regu
     */
    public function update(Request $request, Team $team): RedirectResponse
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:100',
            'code'          => 'required|string|max:20|unique:teams,code,' . $team->id,
            'department_id' => 'nullable|exists:departments,id',
            'leader_id'     => 'nullable|exists:users,id',
            'district'      => 'nullable|string|max:255',
            'coverage_area' => 'nullable|string|max:255',
            'is_active'     => 'nullable|boolean',
            'description'   => 'nullable|string',
            'notes'         => 'nullable|string',
        ]);

        $district = $validated['district'] ?? $validated['coverage_area'] ?? $team->district;
        $description = $validated['description'] ?? $validated['notes'] ?? $team->description;
        $isActive = $request->has('is_active') ? (bool) $request->is_active : true;

        $oldLeaderId = $team->leader_id;

        $team->update([
            'name'          => $validated['name'],
            'code'          => strtoupper(trim($validated['code'])),
            'department_id' => $validated['department_id'] ?? $team->department_id,
            'leader_id'     => $validated['leader_id'] ?? null,
            'district'      => $district,
            'description'   => $description,
            'is_active'     => $isActive,
        ]);

        // Jika leader berubah
        if (!empty($validated['leader_id']) && $validated['leader_id'] != $oldLeaderId) {
            if ($oldLeaderId) {
                TeamMember::where('team_id', $team->id)
                    ->where('user_id', $oldLeaderId)
                    ->update(['role' => 'MEMBER']);
            }
            TeamMember::updateOrCreate(
                ['team_id' => $team->id, 'user_id' => $validated['leader_id']],
                ['role' => 'LEADER']
            );
        }

        return redirect()->back()->with('success', "Data Regu '{$team->name}' berhasil diperbarui.");
    }

    /**
     * Hapus regu
     */
    public function destroy(Team $team): RedirectResponse
    {
        $name = $team->name;
        $team->delete();

        return redirect()->back()->with('success', "Regu '{$name}' telah dihapus.");
    }

    /**
     * Tambah anggota ke dalam regu
     */
    public function addMember(Request $request, Team $team): RedirectResponse
    {
        $validated = $request->validate([
            'user_id'      => 'required|exists:users,id',
            'role'         => 'nullable|in:LEADER,MEMBER',
            'role_in_team' => 'nullable|in:LEADER,MEMBER',
        ]);

        $userId = $validated['user_id'];
        $role = $validated['role'] ?? $validated['role_in_team'] ?? 'MEMBER';

        // Cek jumlah anggota saat ini
        $memberCount = $team->members()->count();
        if ($memberCount >= 15) {
            return redirect()->back()->with('error', "Regu sudah mencapai batas maksimum personil (15 orang).");
        }

        // Cek apakah sudah terdaftar di regu mana pun
        $existingInAny = TeamMember::with('team')->where('user_id', $userId)->first();
        if ($existingInAny) {
            $reguName = $existingInAny->team?->name ?? 'lain';
            return redirect()->back()->with('error', "Petugas ini sudah terdaftar dalam regu '{$reguName}'. Satu petugas hanya dapat bergabung dalam 1 regu.");
        }

        TeamMember::create([
            'team_id' => $team->id,
            'user_id' => $userId,
            'role'    => $role,
        ]);

        if ($role === 'LEADER') {
            $team->update(['leader_id' => $userId]);
        }

        $user = User::find($userId);
        return redirect()->back()->with('success', "Petugas {$user->name} berhasil ditambahkan ke regu {$team->name}.");
    }

    /**
     * Hapus anggota dari regu
     */
    public function removeMember(Team $team, User $user): RedirectResponse
    {
        TeamMember::where('team_id', $team->id)->where('user_id', $user->id)->delete();

        if ($team->leader_id == $user->id) {
            $team->update(['leader_id' => null]);
        }

        return redirect()->back()->with('success', "Petugas {$user->name} dikeluarkan dari regu {$team->name}.");
    }

    /**
     * Angkat anggota menjadi Ketua Regu (Koordinator Lapangan)
     */
    public function setLeader(Team $team, User $user): RedirectResponse
    {
        $member = TeamMember::where('team_id', $team->id)->where('user_id', $user->id)->first();
        if (!$member) {
            return redirect()->back()->with('error', "Petugas belum terdaftar sebagai anggota regu.");
        }

        TeamMember::where('team_id', $team->id)->where('role', 'LEADER')->update(['role' => 'MEMBER']);

        $member->update(['role' => 'LEADER']);
        $team->update(['leader_id' => $user->id]);

        return redirect()->back()->with('success', "Petugas {$user->name} ditetapkan sebagai Ketua Regu {$team->name}.");
    }

    /**
     * Update Mode Penugasan Sistem (HYBRID, OPERATOR_ONLY, SELF_CLAIM_ONLY)
     */
    public function updateDispatchMode(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'dispatch_mode' => 'required|in:HYBRID,OPERATOR_ONLY,SELF_CLAIM_ONLY',
        ]);

        SystemSetting::set('dispatch_mode', $validated['dispatch_mode'], 'string', 'Mode Penugasan Regu Lapangan');

        $labels = [
            'HYBRID'          => 'Hybrid (Disposisi Operator ke Regu Tertentu ATAU Terbuka untuk Klaim Regu)',
            'OPERATOR_ONLY'   => 'Disposisi Terarah Operator Saja (Setiap Laporan Wajib Ditunjuk Regu oleh Operator)',
            'SELF_CLAIM_ONLY' => 'Klaim Bebas Mandiri Regu Saja (Regu Mengambil Tugas Sesuai Wilayah/Kesiapan)',
        ];

        $modeLabel = $labels[$validated['dispatch_mode']] ?? $validated['dispatch_mode'];

        return redirect()->back()->with('success', "Mode penugasan berhasil diubah menjadi: {$modeLabel}.");
    }
}
