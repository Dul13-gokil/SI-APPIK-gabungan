<?php

namespace Tests\Feature;

use App\Domain\AI\Services\AICVService;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Complaint;
use App\Models\Department;
use App\Models\Disposition;
use App\Models\SystemSetting;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Ambang AI dapat dikalibrasi tanpa deploy, dan setiap aksi penting meninggalkan
 * jejak audit agar halaman "Log Aktivitas" Super Admin tidak bolong.
 */
class AiGovernanceTest extends TestCase
{
    use RefreshDatabase;

    private Department $department;
    private Category $category;
    private Team $team;
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
        $this->team = Team::create([
            'name' => 'Regu Uji',
            'code' => 'UJI',
            'department_id' => $this->department->id,
            'is_active' => true,
        ]);
    }

    private function user(string $role): User
    {
        $this->seq++;
        $user = User::create([
            'name' => "Pengguna {$role} {$this->seq}",
            'email' => strtolower($role) . "{$this->seq}@example.test",
            'phone' => '08125555' . str_pad((string) $this->seq, 4, '0', STR_PAD_LEFT),
            'password' => 'Password123',
            'role' => $role,
        ]);

        if ($role === 'PETUGAS') {
            TeamMember::create(['team_id' => $this->team->id, 'user_id' => $user->id, 'role' => 'MEMBER']);
        }

        return $user;
    }

    private function mockAi(array $result): void
    {
        $this->mock(AICVService::class, function ($mock) use ($result) {
            $mock->shouldReceive('classifyMultipleImages')->andReturn($result);
            $mock->shouldReceive('classifyInfrastructureDamage')->andReturn($result);
        });
    }

    private function aiResult(float $confidence, bool $damaged = true, int $severity = 4): array
    {
        return [
            'is_valid_infrastructure' => true,
            'is_damaged' => $damaged,
            'category_id' => 1,
            'category_label' => 'Jalan & Jembatan',
            'confidence' => $confidence,
            'severity_level' => $severity,
            'severity_text' => $damaged ? 'BERAT' : 'RINGAN',
            'damage_type' => $damaged ? 'Lubang besar' : 'Tidak ada kerusakan',
        ];
    }

    private function submitReport(User $reporter)
    {
        return $this->actingAs($reporter, 'sanctum')->postJson('/api/v1/complaints', [
            'title' => 'Jalan amblas',
            'description' => 'Amblas sedalam 30 cm.',
            'category_id' => $this->category->id,
            'address' => 'Jl. Poros KM 2',
            'latitude' => -3.5061,
            'longitude' => 120.8879,
            'photos' => [UploadedFile::fake()->image('bukti.jpg', 800, 600)],
        ]);
    }

    public function test_ai_threshold_only_marks_confidence_not_status(): void
    {
        // Verifikasi manual sudah dihapus: status tetap ditentukan AI. Ambang hanya
        // menandai apakah keyakinan AI melewati batas (untuk penanda "AI ragu").
        SystemSetting::set('ai_verify_threshold', 0.95, 'float');

        $this->mockAi($this->aiResult(0.90));
        $reporter = $this->user('WARGA');

        $this->submitReport($reporter)
            ->assertStatus(201)
            ->assertJsonPath('data.status', 'VERIFIED')
            ->assertJsonPath('data.verified_by_ai', false);
    }

    public function test_default_threshold_still_auto_verifies(): void
    {
        $this->mockAi($this->aiResult(0.90));
        $reporter = $this->user('WARGA');

        $this->submitReport($reporter)->assertStatus(201)->assertJsonPath('data.status', 'VERIFIED');
    }

    public function test_report_creation_is_audited(): void
    {
        $this->mockAi($this->aiResult(0.90));
        $reporter = $this->user('WARGA');

        $this->submitReport($reporter)->assertStatus(201);

        $log = AuditLog::where('action', 'COMPLAINT_CREATED')->latest('id')->first();
        $this->assertNotNull($log, 'Laporan baru tidak tercatat di audit_logs');
        $this->assertTrue((bool) $log->after_json['verified_by_ai']);
        $this->assertSame(0.90, (float) $log->after_json['ai_confidence']);
    }

    public function test_ai_failure_is_audited_and_marked(): void
    {
        $this->mock(AICVService::class, function ($mock) {
            $mock->shouldReceive('classifyMultipleImages')->andThrow(new \RuntimeException('model tidak tersedia'));
        });

        $reporter = $this->user('WARGA');
        // AI gagal dijalankan: laporan tetap diterima (tidak ada verifikasi manual lagi),
        // ditandai belum tervalidasi dan kegagalannya diaudit.
        $this->submitReport($reporter)
            ->assertStatus(201)
            ->assertJsonPath('data.status', 'VERIFIED')
            ->assertJsonPath('data.verified_by_ai', false);

        $this->assertDatabaseHas('audit_logs', ['action' => 'AI_CLASSIFICATION_FAILED']);
        $this->assertNotNull(SystemSetting::get('ai_last_failure_at'), 'Waktu kegagalan AI tidak dicatat');
    }

    public function test_field_work_actions_are_audited_and_completion_validation_recorded(): void
    {
        $worker = $this->user('PETUGAS');
        $reporter = $this->user('WARGA');

        $complaint = Complaint::create([
            'public_code' => 'UJI-AUDIT-' . ++$this->seq,
            'user_id' => $reporter->id,
            'category_id' => $this->category->id,
            'title' => 'Jalan berlubang',
            'description' => 'Lubang di badan jalan.',
            'address' => 'Jl. Poros Lasusua',
            'latitude' => -3.5060,
            'longitude' => 120.8878,
            'radius_m' => 20,
            'status' => 'DISPATCHED',
            'priority_score' => 10,
            'priority_level' => 'LOW',
        ]);
        Disposition::create([
            'complaint_id' => $complaint->id,
            'department_id' => $this->department->id,
            'team_id' => $this->team->id,
            'instruction' => 'Perbaiki lubang.',
            'status' => 'DISPATCHED',
            'deadline' => now()->addDays(3),
        ]);

        $this->actingAs($worker, 'sanctum')
            ->postJson("/api/v1/complaints/{$complaint->id}/start-work")
            ->assertStatus(200);

        $this->assertDatabaseHas('audit_logs', ['action' => 'WORK_STARTED', 'entity_id' => $complaint->id]);

        $this->mockAi($this->aiResult(0.95, damaged: false, severity: 1));

        $this->actingAs($worker, 'sanctum')
            ->postJson("/api/v1/complaints/{$complaint->id}/finish-work", [
                'note' => 'Penambalan selesai.',
                'latitude' => -3.50605,
                'longitude' => 120.88780,
                'photos' => [UploadedFile::fake()->image('sesudah.jpg', 800, 600)],
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('audit_logs', ['action' => 'WORK_FINISHED', 'entity_id' => $complaint->id]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'COMPLETION_VALIDATED_BY_AI', 'entity_id' => $complaint->id]);
        $this->assertSame('COMPLETED', $complaint->fresh()->status);
    }
}
