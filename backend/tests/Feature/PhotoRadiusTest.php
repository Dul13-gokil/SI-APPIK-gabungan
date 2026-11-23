<?php

namespace Tests\Feature;

use App\Domain\Complaints\Services\PhotoRadiusGuard;
use App\Models\Category;
use App\Models\Complaint;
use App\Models\Department;
use App\Models\Disposition;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Keputusan rapat: foto bukti pengerjaan wajib diambil dalam radius lokasi aduan.
 */
class PhotoRadiusTest extends TestCase
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
            'name' => 'Regu Uji',
            'code' => 'UJI',
            'department_id' => $this->department->id,
            'is_active' => true,
        ]);
    }

    private function worker(): User
    {
        $this->seq++;
        $worker = User::create([
            'name' => "Petugas {$this->seq}",
            'email' => "petugas{$this->seq}@example.test",
            'phone' => '08124444' . str_pad((string) $this->seq, 4, '0', STR_PAD_LEFT),
            'password' => 'Password123',
            'role' => 'PETUGAS',
        ]);
        TeamMember::create(['team_id' => $this->team->id, 'user_id' => $worker->id, 'role' => 'MEMBER']);

        return $worker;
    }

    private function inProgressComplaint(User $reporter, int $radius = 20): Complaint
    {
        $complaint = Complaint::create([
            'public_code' => 'UJI-RADIUS-' . ++$this->seq,
            'user_id' => $reporter->id,
            'category_id' => $this->category->id,
            'title' => 'Jalan berlubang',
            'description' => 'Lubang di badan jalan.',
            'address' => 'Jl. Poros Lasusua',
            'latitude' => self::LAT,
            'longitude' => self::LON,
            'radius_m' => $radius,
            'status' => 'IN_PROGRESS',
            'priority_score' => 10,
            'priority_level' => 'LOW',
        ]);

        Disposition::create([
            'complaint_id' => $complaint->id,
            'department_id' => $this->department->id,
            'team_id' => $this->team->id,
            'assigned_to' => null,
            'instruction' => 'Perbaiki lubang jalan.',
            'status' => 'IN_PROGRESS',
            'deadline' => now()->addDays(3),
        ]);

        return $complaint;
    }

    private function submit(User $worker, Complaint $complaint, ?float $lat, ?float $lon)
    {
        $payload = ['note' => 'Penambalan selesai.'];
        if ($lat !== null && $lon !== null) {
            $payload['latitude'] = $lat;
            $payload['longitude'] = $lon;
        }
        $payload['photos'] = [UploadedFile::fake()->image('sesudah.jpg', 800, 600)];

        return $this->actingAs($worker, 'sanctum')
            ->postJson("/api/v1/complaints/{$complaint->id}/finish-work", $payload);
    }

    public function test_photo_inside_radius_is_accepted(): void
    {
        $worker = $this->worker();
        $complaint = $this->inProgressComplaint($worker);

        // ~11 m dari titik aduan.
        $this->submit($worker, $complaint, self::LAT + 0.0001, self::LON)
            ->assertStatus(200);

        $this->assertContains($complaint->fresh()->status, ['WAITING_VERIFICATION', 'COMPLETED']);
    }

    public function test_photo_outside_radius_is_rejected(): void
    {
        $worker = $this->worker();
        $complaint = $this->inProgressComplaint($worker);

        // ~1,1 km dari titik aduan.
        $response = $this->submit($worker, $complaint, self::LAT + 0.01, self::LON);

        $response->assertStatus(422);
        $this->assertStringContainsString('di luar radius', $response->json('message'));
        $this->assertSame('IN_PROGRESS', $complaint->fresh()->status);
    }

    public function test_photo_without_location_is_allowed_until_apk_rollout(): void
    {
        $worker = $this->worker();
        $complaint = $this->inProgressComplaint($worker);

        // APK lama belum mengirim lokasi: jangan hentikan pekerjaan lapangan.
        $this->submit($worker, $complaint, null, null)->assertStatus(200);
    }

    public function test_photo_without_location_is_rejected_when_strict_mode_enabled(): void
    {
        \App\Models\SystemSetting::set('photo_radius_require_location', true, 'boolean');

        $worker = $this->worker();
        $complaint = $this->inProgressComplaint($worker);

        $response = $this->submit($worker, $complaint, null, null);

        $response->assertStatus(422);
        $this->assertStringContainsString('Lokasi pengambilan foto tidak terdeteksi', $response->json('message'));
        $this->assertSame('IN_PROGRESS', $complaint->fresh()->status);
    }

    public function test_gps_tolerance_allows_slight_drift(): void
    {
        $worker = $this->worker();
        $complaint = $this->inProgressComplaint($worker);

        // ~33 m: di luar radius 20 m, tetapi masih dalam toleransi akurasi GPS (25 m).
        $this->submit($worker, $complaint, self::LAT + 0.0003, self::LON)
            ->assertStatus(200);
    }

    public function test_radius_guard_computes_distance_in_meters(): void
    {
        $guard = new PhotoRadiusGuard();

        $distance = $guard->distanceMeters(self::LAT, self::LON, self::LAT + 0.0001, self::LON);

        $this->assertEqualsWithDelta(11.1, $distance, 1.0);
    }
}
