<?php

namespace Tests\Feature;

use App\Domain\AI\Services\AICVService;
use App\Models\AiAssessment;
use App\Models\Category;
use App\Models\Complaint;
use App\Models\ComplaintPhoto;
use App\Models\Department;
use App\Models\Disposition;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Mengunci keputusan rapat: AI memverifikasi laporan (>=80%), AI memvalidasi hasil kerja,
 * radius 20 m + tawaran dukung, peran baru, dan operator boleh menambah pekerja lapangan.
 */
class MeetingChangesTest extends TestCase
{
    use RefreshDatabase;

    private Department $department;
    private Category $category;
    private int $seq = 0;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->department = Department::create(['name' => 'Dinas PU', 'code' => 'PU']);
        $this->category = Category::create([
            'name' => 'Jalan & Jembatan',
            'department_id' => $this->department->id,
            'severity_default' => 4,
        ]);
    }

    private function user(string $role, array $extra = []): User
    {
        $this->seq++;

        return User::create(array_merge([
            'name' => "Pengguna {$role} {$this->seq}",
            'email' => strtolower($role) . "{$this->seq}@example.test",
            'phone' => '08121111' . str_pad((string) $this->seq, 4, '0', STR_PAD_LEFT),
            'password' => 'Password123',
            'role' => $role,
        ], $extra));
    }

    private function complaint(User $reporter, array $extra = []): Complaint
    {
        $this->seq++;

        return Complaint::create(array_merge([
            'public_code' => 'UJI-RAPAT-' . $this->seq,
            'user_id' => $reporter->id,
            'category_id' => $this->category->id,
            'title' => 'Jalan berlubang',
            'description' => 'Lubang di badan jalan.',
            'address' => 'Jl. Poros Lasusua',
            'latitude' => -3.5060,
            'longitude' => 120.8878,
            'status' => 'SUBMITTED',
            'priority_score' => 10,
            'priority_level' => 'LOW',
        ], $extra));
    }

    private function mockAi(array $result): void
    {
        $this->mock(AICVService::class, function ($mock) use ($result) {
            $mock->shouldReceive('classifyMultipleImages')->andReturn($result);
            $mock->shouldReceive('classifyInfrastructureDamage')->andReturn($result);
        });
    }

    private function aiResult(float $confidence, int $severity = 4): array
    {
        return [
            'is_valid_infrastructure' => true,
            'is_damaged' => true,
            'category_id' => 1,
            'category_label' => 'Jalan & Jembatan',
            'confidence' => $confidence,
            'severity_level' => $severity,
            'severity_text' => 'BERAT',
            'damage_type' => 'Lubang besar',
        ];
    }

    public function test_report_is_auto_verified_when_ai_confidence_reaches_threshold(): void
    {
        $this->mockAi($this->aiResult(0.92));
        $reporter = $this->user('WARGA');

        $response = $this->actingAs($reporter, 'sanctum')->postJson('/api/v1/complaints', [
            'title' => 'Jalan amblas',
            'description' => 'Amblas sedalam 30 cm.',
            'category_id' => $this->category->id,
            'address' => 'Jl. Poros KM 2',
            'latitude' => -3.5061,
            'longitude' => 120.8879,
            'photos' => [UploadedFile::fake()->image('bukti.jpg', 800, 600)],
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('data.status', 'VERIFIED');
        $response->assertJsonPath('data.verified_by_ai', true);

        $complaint = Complaint::latest('id')->first();
        $this->assertTrue((bool) $complaint->verified_by_ai);
        $this->assertSame('major', $complaint->damage_type);
        $this->assertSame(20, (int) $complaint->radius_m);
        $this->assertDatabaseHas('ai_assessments', [
            'complaint_id' => $complaint->id,
            'task' => 'DAMAGE_CLASSIFICATION',
        ]);
    }

    public function test_report_stays_submitted_when_ai_confidence_is_low(): void
    {
        $this->mockAi($this->aiResult(0.45, 2));
        $reporter = $this->user('WARGA');

        $response = $this->actingAs($reporter, 'sanctum')->postJson('/api/v1/complaints', [
            'title' => 'Retak halus',
            'description' => 'Retak rambut di aspal.',
            'category_id' => $this->category->id,
            'address' => 'Jl. Poros KM 3',
            'latitude' => -3.5062,
            'longitude' => 120.8880,
            'photos' => [UploadedFile::fake()->image('bukti.jpg', 800, 600)],
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('data.status', 'SUBMITTED');
        $response->assertJsonPath('data.verified_by_ai', false);
    }

    public function test_nearby_report_offers_support_instead_of_duplicate(): void
    {
        $this->mockAi($this->aiResult(0.90));
        $reporter = $this->user('WARGA');

        // Laporan lama berjarak sekitar 11 meter.
        $this->complaint($reporter, [
            'latitude' => -3.5060,
            'longitude' => 120.8879,
        ]);

        $response = $this->actingAs($reporter, 'sanctum')->postJson('/api/v1/complaints', [
            'title' => 'Lubang yang sama',
            'description' => 'Sepertinya sudah pernah dilaporkan.',
            'category_id' => $this->category->id,
            'address' => 'Jl. Poros KM 2',
            'latitude' => -3.5061,
            'longitude' => 120.8880,
            'photos' => [UploadedFile::fake()->image('bukti.jpg', 800, 600)],
        ]);

        $response->assertStatus(201);
        $this->assertNotEmpty($response->json('data.similar_complaints'));
        $this->assertStringContainsString('Dukung', (string) $response->json('data.similar_message'));
    }

    public function test_ai_completes_work_when_damage_no_longer_detected(): void
    {
        $this->mockAi([
            'is_valid_infrastructure' => true,
            'is_damaged' => false,
            'category_id' => 1,
            'category_label' => 'Jalan & Jembatan',
            'confidence' => 0.95,
            'severity_level' => 1,
        ]);

        $leader = $this->user('PETUGAS');
        $team = Team::create([
            'name' => 'Regu A',
            'code' => 'REG-A',
            'department_id' => $this->department->id,
            'leader_id' => $leader->id,
            'is_active' => true,
        ]);
        $team->users()->attach($leader->id, ['role' => 'LEADER']);

        $complaint = $this->complaint($this->user('WARGA'), ['status' => 'IN_PROGRESS']);
        Disposition::create([
            'complaint_id' => $complaint->id,
            'department_id' => $this->department->id,
            'team_id' => $team->id,
            'assigned_to' => $leader->id,
            'instruction' => 'Perbaiki jalan.',
            'deadline' => now()->addDays(2),
            'status' => 'IN_PROGRESS',
        ]);

        $response = $this->actingAs($leader, 'sanctum')->postJson(
            "/api/v1/complaints/{$complaint->id}/finish-work",
            [
                'note' => 'Sudah diaspal ulang.',
                'photos' => [UploadedFile::fake()->image('sesudah.jpg', 800, 600)],
            ]
        );

        $response->assertStatus(200);
        $complaint->refresh();
        $this->assertSame('COMPLETED', $complaint->status);
        $this->assertTrue((bool) $complaint->completion_verified_by_ai);
        $this->assertDatabaseHas('ai_assessments', [
            'complaint_id' => $complaint->id,
            'task' => 'COMPLETION_VALIDATION',
        ]);
    }

    public function test_operator_can_reject_a_dispatched_report(): void
    {
        $operator = $this->user('OPERATOR');
        $complaint = $this->complaint($this->user('WARGA'), ['status' => 'DISPATCHED']);

        $this->actingAs($operator)
            ->post("/operator/complaints/{$complaint->id}/reject", ['reason' => 'Lokasi di luar wewenang dinas.'])
            ->assertRedirect();

        $this->assertSame('REJECTED', $complaint->fresh()->status);
    }

    public function test_new_roles_get_the_right_portals_and_operator_can_create_field_worker(): void
    {
        $monitoring = $this->user('MONITORING');
        $this->actingAs($monitoring)->get('/monitoring')->assertStatus(200);
        $this->actingAs($monitoring)->get('/admin')->assertStatus(403);

        $pengawas = $this->user('PENGAWAS');
        $this->actingAs($pengawas)->get('/petugas')->assertStatus(200);

        $operator = $this->user('OPERATOR');
        $this->actingAs($operator)->post('/operator/petugas', [
            'name' => 'Petugas Baru',
            'email' => 'petugas.baru@example.test',
            'phone' => '081299990001',
        ])->assertRedirect();

        $this->assertDatabaseHas('users', [
            'email' => 'petugas.baru@example.test',
            'role' => 'PETUGAS',
        ]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'USER_CREATED_BY_OPERATOR']);
    }
}
