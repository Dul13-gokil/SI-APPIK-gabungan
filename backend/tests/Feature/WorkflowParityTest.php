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
}
