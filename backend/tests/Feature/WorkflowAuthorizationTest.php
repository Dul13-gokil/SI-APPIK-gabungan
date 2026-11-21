<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Complaint;
use App\Models\Department;
use App\Models\Disposition;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Mengunci aturan Aturan 3.2 (team shield) dan 3.3 (kuota 3 tugas aktif)
 * serta memastikan transisi status ilegal tidak lagi menghasilkan HTTP 500.
 */
class WorkflowAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected Department $department;
    protected Category $category;
    private int $sequence = 0;

    protected function setUp(): void
    {
        parent::setUp();

        $this->department = Department::create(['name' => 'Dinas PU', 'code' => 'PU']);
        $this->category = Category::create([
            'name' => 'Jalan Rusak',
            'department_id' => $this->department->id,
            'severity_default' => 4,
        ]);
    }

    private function makeUser(string $role): User
    {
        $this->sequence++;

        return User::create([
            'name' => "Petugas {$this->sequence}",
            'email' => "petugas{$this->sequence}@example.test",
            'phone' => '08123456' . str_pad((string) $this->sequence, 4, '0', STR_PAD_LEFT),
            'password' => 'Password123',
            'role' => $role,
        ]);
    }

    private function makeTeam(string $code, User $leader, array $members = []): Team
    {
        $team = Team::create([
            'name' => "Regu {$code}",
            'code' => $code,
            'department_id' => $this->department->id,
            'leader_id' => $leader->id,
            'is_active' => true,
        ]);

        $team->users()->attach($leader->id, ['role' => 'LEADER']);
        foreach ($members as $member) {
            $team->users()->attach($member->id, ['role' => 'MEMBER']);
        }

        return $team;
    }

    private function makeTask(Team $team, ?User $assignee, string $status): Complaint
    {
        $this->sequence++;

        $complaint = Complaint::create([
            'public_code' => 'TUGAS-' . $this->sequence,
            'user_id' => $this->makeUser('WARGA')->id,
            'category_id' => $this->category->id,
            'title' => 'Kerusakan jalan ' . $this->sequence,
            'description' => 'Deskripsi kerusakan untuk pengujian.',
            'address' => 'Jl. Poros Lasusua',
            'latitude' => -3.506,
            'longitude' => 120.887,
            'status' => $status,
            'priority_score' => 20,
            'priority_level' => 'LOW',
        ]);

        Disposition::create([
            'complaint_id' => $complaint->id,
            'department_id' => $this->department->id,
            'team_id' => $team->id,
            'assigned_to' => $assignee?->id,
            'instruction' => 'Perbaiki kerusakan sesuai standar teknis.',
            'deadline' => now()->addDays(3),
            'status' => $status === 'IN_PROGRESS' ? 'IN_PROGRESS' : 'PENDING',
        ]);

        return $complaint;
    }

    public function test_petugas_from_another_team_cannot_submit_completion_proof(): void
    {
        $leaderA = $this->makeUser('PETUGAS');
        $teamA = $this->makeTeam('A', $leaderA);

        $petugasB = $this->makeUser('PETUGAS');
        $this->makeTeam('B', $petugasB);

        $complaint = $this->makeTask($teamA, $leaderA, 'IN_PROGRESS');

        $response = $this->actingAs($petugasB, 'sanctum')
            ->postJson("/api/v1/complaints/{$complaint->id}/finish-work", [
                'note' => 'Selesai dikerjakan.',
            ]);

        $response->assertStatus(403);
        $this->assertSame('IN_PROGRESS', $complaint->fresh()->status);
    }

    public function test_team_with_three_active_tasks_cannot_start_a_fourth(): void
    {
        $leader = $this->makeUser('PETUGAS');
        $team = $this->makeTeam('A', $leader);

        $this->makeTask($team, $leader, 'IN_PROGRESS');
        $this->makeTask($team, $leader, 'IN_PROGRESS');
        $this->makeTask($team, $leader, 'REVISION_REQUIRED');

        $fourth = $this->makeTask($team, $leader, 'DISPATCHED');

        $response = $this->actingAs($leader, 'sanctum')
            ->postJson("/api/v1/complaints/{$fourth->id}/start-work");

        $response->assertStatus(422);
        $response->assertJsonPath('message', fn ($message) => str_contains($message, 'Kuota'));
        $this->assertSame('DISPATCHED', $fourth->fresh()->status);
    }

    public function test_team_can_start_task_when_quota_has_room(): void
    {
        $leader = $this->makeUser('PETUGAS');
        $team = $this->makeTeam('A', $leader);

        $task = $this->makeTask($team, $leader, 'DISPATCHED');

        $this->actingAs($leader, 'sanctum')
            ->postJson("/api/v1/complaints/{$task->id}/start-work")
            ->assertStatus(200);

        $this->assertSame('IN_PROGRESS', $task->fresh()->status);
    }

    public function test_invalid_status_transition_returns_422_not_500(): void
    {
        $leader = $this->makeUser('PETUGAS');
        $team = $this->makeTeam('A', $leader);

        // Sudah WAITING_VERIFICATION; finish-work lagi adalah transisi ilegal.
        $complaint = $this->makeTask($team, $leader, 'WAITING_VERIFICATION');

        $response = $this->actingAs($leader, 'sanctum')
            ->postJson("/api/v1/complaints/{$complaint->id}/finish-work", [
                'note' => 'Kirim ulang bukti.',
            ]);

        $response->assertStatus(422);
        $this->assertSame('WAITING_VERIFICATION', $complaint->fresh()->status);
    }

    public function test_admin_may_submit_completion_proof_for_any_team(): void
    {
        $leader = $this->makeUser('PETUGAS');
        $team = $this->makeTeam('A', $leader);
        $admin = $this->makeUser('ADMIN');

        $complaint = $this->makeTask($team, $leader, 'IN_PROGRESS');

        $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/complaints/{$complaint->id}/finish-work", [
                'note' => 'Diselesaikan oleh admin untuk pengujian.',
            ])
            ->assertStatus(200);

        $this->assertSame('WAITING_VERIFICATION', $complaint->fresh()->status);
    }
}
