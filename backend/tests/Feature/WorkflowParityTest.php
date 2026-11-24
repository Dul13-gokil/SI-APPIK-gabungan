<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Complaint;
use App\Models\Department;
use App\Models\Disposition;
use App\Models\Notification;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Web dan API harus berperilaku sama karena keduanya memakai WorkflowService.
 *
 * Sebelum penyatuan, jalur web tidak mengirim notifikasi ke pemilik laporan, tidak
 * mencatat jumlah foto di audit, dan tidak menimpa `assigned_to` seperti API.
 */
class WorkflowParityTest extends TestCase
{
    use RefreshDatabase;

    private const LAT = -3.5060;
    private const LON = 120.8878;

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
            'severity_default' => 3,
        ]);
        $this->team = Team::create([
            'name' => 'Regu Paritas',
            'code' => 'PAR',
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

    private function complaint(User $reporter, string $status, ?int $assignedTo = null): Complaint
    {
        $complaint = Complaint::create([
            'public_code' => 'UJI-PARITAS-' . ++$this->seq,
            'user_id' => $reporter->id,
            'category_id' => $this->category->id,
            'title' => 'Jalan berlubang',
            'description' => 'Lubang di badan jalan.',
            'address' => 'Jl. Poros Lasusua',
            'latitude' => self::LAT,
            'longitude' => self::LON,
            'radius_m' => 20,
            'status' => $status,
            'priority_score' => 10,
            'priority_level' => 'LOW',
        ]);

        Disposition::create([
            'complaint_id' => $complaint->id,
            'department_id' => $this->department->id,
            'team_id' => $this->team->id,
            'assigned_to' => $assignedTo,
            'instruction' => 'Perbaiki lubang jalan.',
            'status' => $status === 'IN_PROGRESS' ? 'IN_PROGRESS' : 'DISPATCHED',
            'deadline' => now()->addDays(3),
        ]);

        return $complaint;
    }

    public function test_jalur_web_menyelesaikan_kerja_mengirim_notifikasi_dan_mencatat_jumlah_foto(): void
    {
        $reporter = $this->user('WARGA');
        $worker = $this->user('PETUGAS');
        $complaint = $this->complaint($reporter, 'IN_PROGRESS');

        $this->actingAs($worker)
            ->post(route('petugas.complaints.finish', $complaint->id), [
                'note' => 'Penambalan selesai.',
                'latitude' => self::LAT + 0.0001,
                'longitude' => self::LON,
                'photos' => [UploadedFile::fake()->image('sesudah.jpg', 800, 600)],
            ])
            ->assertRedirect();

        $this->assertContains($complaint->fresh()->status, ['WAITING_VERIFICATION', 'COMPLETED']);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $reporter->id,
            'type' => 'COMPLAINT_WAITING_VERIFICATION',
        ]);

        $audit = AuditLog::where('action', 'WORK_FINISHED')
            ->where('entity_id', $complaint->id)
            ->latest('id')
            ->firstOrFail();

        $this->assertSame(1, $audit->after_json['photo_count'] ?? null);
    }

    public function test_jalur_web_mencatat_petugas_yang_benar_benar_memulai_kerja(): void
    {
        $reporter = $this->user('WARGA');
        $lain = $this->user('PETUGAS');
        $worker = $this->user('PETUGAS');
        $complaint = $this->complaint($reporter, 'DISPATCHED', $lain->id);

        $this->actingAs($worker)
            ->post(route('petugas.complaints.start', $complaint->id))
            ->assertRedirect();

        $this->assertSame('IN_PROGRESS', $complaint->fresh()->status);
        $this->assertSame(
            $worker->id,
            $complaint->latestDisposition()->first()->assigned_to,
            'Disposisi harus mencatat petugas yang benar-benar memulai pekerjaan.'
        );
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'WORK_STARTED',
            'entity_id' => $complaint->id,
            'actor_id' => $worker->id,
        ]);
    }

    public function test_revisi_lewat_api_memberi_tahu_petugas_dan_pelapor(): void
    {
        $reporter = $this->user('WARGA');
        $worker = $this->user('PETUGAS');
        $operator = $this->user('OPERATOR');
        $complaint = $this->complaint($reporter, 'WAITING_VERIFICATION', $worker->id);

        $this->actingAs($operator, 'sanctum')
            ->postJson("/api/v1/complaints/{$complaint->id}/validate-resolution", [
                'decision' => 'REVISION_REQUIRED',
                'notes' => 'Tambalan belum rata.',
            ])
            ->assertStatus(200);

        $this->assertSame('REVISION_REQUIRED', $complaint->fresh()->status);

        // Petugas pemegang tugas hanya bisa memperbaiki bila diberi tahu.
        $this->assertDatabaseHas('notifications', [
            'user_id' => $worker->id,
            'type' => 'TASK_REVISION_REQUIRED',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $reporter->id,
            'type' => 'COMPLAINT_REVISION',
        ]);
    }

    public function test_penugasan_terbuka_lewat_api_menyiarkan_tugas_ke_semua_petugas(): void
    {
        $reporter = $this->user('WARGA');
        $operator = $this->user('OPERATOR');
        $petugasA = $this->user('PETUGAS');
        $petugasB = $this->user('PETUGAS');
        $complaint = $this->complaint($reporter, 'VERIFIED');

        $this->actingAs($operator, 'sanctum')
            ->postJson("/api/v1/complaints/{$complaint->id}/dispositions", [
                'department_id' => $this->department->id,
                'instruction' => 'Tambal lubang.',
            ])
            ->assertStatus(200);

        $this->assertSame('DISPATCHED', $complaint->fresh()->status);

        // Mode klaim: tanpa regu dan tanpa petugas tunggal, semua petugas harus tahu.
        foreach ([$petugasA, $petugasB] as $petugas) {
            $this->assertDatabaseHas('notifications', [
                'user_id' => $petugas->id,
                'type' => 'FIELD_TASK_ASSIGNED',
            ]);
        }
    }

    public function test_penolakan_lewat_web_meninggalkan_jejak_audit(): void
    {
        $reporter = $this->user('WARGA');
        $operator = $this->user('OPERATOR');
        $complaint = $this->complaint($reporter, 'SUBMITTED');

        $this->actingAs($operator)
            ->post(route('operator.complaints.reject', $complaint->id), [
                'reason' => 'Foto bukan kerusakan jalan.',
            ])
            ->assertRedirect();

        $this->assertSame('REJECTED', $complaint->fresh()->status);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'REJECTED',
            'entity_id' => $complaint->id,
            'actor_id' => $operator->id,
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $reporter->id,
            'type' => 'COMPLAINT_REJECTED',
        ]);
    }

    public function test_ubah_status_lewat_api_meninggalkan_jejak_audit(): void
    {
        $reporter = $this->user('WARGA');
        $admin = $this->user('SUPERADMIN');
        $complaint = $this->complaint($reporter, 'SUBMITTED');

        $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/complaints/{$complaint->id}/status", [
                'status' => 'VERIFIED',
                'note' => 'Diperiksa langsung di kantor.',
            ])
            ->assertStatus(200);

        $this->assertSame('VERIFIED', $complaint->fresh()->status);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'STATUS_UPDATED',
            'entity_id' => $complaint->id,
            'actor_id' => $admin->id,
        ]);
    }

    public function test_ubah_regu_lewat_web_meninggalkan_jejak_audit(): void
    {
        $reporter = $this->user('WARGA');
        $operator = $this->user('OPERATOR');
        $complaint = $this->complaint($reporter, 'DISPATCHED');

        $this->actingAs($operator)
            ->post(route('operator.complaints.assign-team', $complaint->id), [
                'team_id' => $this->team->id,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'TEAM_ASSIGNED',
            'entity_id' => $complaint->id,
            'actor_id' => $operator->id,
        ]);
    }
}
