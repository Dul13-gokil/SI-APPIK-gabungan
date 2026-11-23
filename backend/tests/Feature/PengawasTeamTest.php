<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Department;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Keputusan rapat: PENGAWAS boleh menambah/mengeluarkan anggota regunya sendiri.
 * Batas wewenang: hanya regunya (Aturan 3.2), tidak boleh menyentuh regu lain.
 */
class PengawasTeamTest extends TestCase
{
    use RefreshDatabase;

    private Department $department;
    private Team $teamA;
    private Team $teamB;
    private int $seq = 0;

    protected function setUp(): void
    {
        parent::setUp();

        $this->department = Department::create(['name' => 'Dinas PU', 'code' => 'PU']);
        Category::create([
            'name' => 'Jalan & Jembatan',
            'department_id' => $this->department->id,
            'severity_default' => 3,
        ]);

        $this->teamA = Team::create(['name' => 'Regu A', 'code' => 'A', 'department_id' => $this->department->id, 'is_active' => true]);
        $this->teamB = Team::create(['name' => 'Regu B', 'code' => 'B', 'department_id' => $this->department->id, 'is_active' => true]);
    }

    private function user(string $role, ?Team $team = null): User
    {
        $this->seq++;

        $user = User::create([
            'name' => "Pengguna {$role} {$this->seq}",
            'email' => strtolower($role) . "{$this->seq}@example.test",
            'phone' => '08123333' . str_pad((string) $this->seq, 4, '0', STR_PAD_LEFT),
            'password' => 'Password123',
            'role' => $role,
        ]);

        if ($team) {
            TeamMember::create(['team_id' => $team->id, 'user_id' => $user->id, 'role' => 'MEMBER']);
        }

        return $user;
    }

    public function test_pengawas_can_add_member_to_own_team(): void
    {
        $pengawas = $this->user('PENGAWAS', $this->teamA);
        $worker = $this->user('PETUGAS');

        $response = $this->actingAs($pengawas)
            ->post(route('pengawas.team.members.add'), ['user_id' => $worker->id]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('team_members', [
            'team_id' => $this->teamA->id,
            'user_id' => $worker->id,
        ]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'TEAM_MEMBER_ADDED_BY_PENGAWAS']);
    }

    public function test_pengawas_cannot_add_worker_who_already_has_a_team(): void
    {
        $pengawas = $this->user('PENGAWAS', $this->teamA);
        $worker = $this->user('PETUGAS', $this->teamB);

        $response = $this->actingAs($pengawas)
            ->post(route('pengawas.team.members.add'), ['user_id' => $worker->id]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('team_members', [
            'team_id' => $this->teamA->id,
            'user_id' => $worker->id,
        ]);
    }

    public function test_pengawas_cannot_add_non_field_worker(): void
    {
        $pengawas = $this->user('PENGAWAS', $this->teamA);
        $operator = $this->user('OPERATOR');

        $response = $this->actingAs($pengawas)
            ->post(route('pengawas.team.members.add'), ['user_id' => $operator->id]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('team_members', ['user_id' => $operator->id]);
    }

    public function test_pengawas_can_remove_member_only_from_own_team(): void
    {
        $pengawas = $this->user('PENGAWAS', $this->teamA);
        $ownMember = $this->user('PETUGAS', $this->teamA);
        $otherMember = $this->user('PETUGAS', $this->teamB);

        $this->actingAs($pengawas)
            ->post(route('pengawas.team.members.remove', $ownMember->id))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('team_members', [
            'team_id' => $this->teamA->id,
            'user_id' => $ownMember->id,
        ]);

        $this->actingAs($pengawas)
            ->post(route('pengawas.team.members.remove', $otherMember->id))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('team_members', [
            'team_id' => $this->teamB->id,
            'user_id' => $otherMember->id,
        ]);
    }

    public function test_petugas_without_team_cannot_manage_members(): void
    {
        $petugas = $this->user('PETUGAS', $this->teamA);
        $this->assertFalse($petugas->isPengawas());

        // Petugas biasa ditolak middleware role:PENGAWAS.
        $this->actingAs($petugas)->get(route('pengawas.team'))->assertForbidden();
    }

    public function test_pengawas_page_lists_own_team_members(): void
    {
        $pengawas = $this->user('PENGAWAS', $this->teamA);
        $member = $this->user('PETUGAS', $this->teamA);

        $response = $this->actingAs($pengawas)->get(route('pengawas.team'));

        $response->assertOk();
        $response->assertSee('Regu A');
        $response->assertSee($member->name);
    }
}