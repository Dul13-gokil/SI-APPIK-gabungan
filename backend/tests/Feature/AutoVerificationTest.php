<?php

namespace Tests\Feature;

use App\Domain\AI\Services\AICVService;
use App\Models\Category;
use App\Models\Complaint;
use App\Models\Department;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Keputusan rapat: verifikasi dan validasi manual dihapus, sepenuhnya ditangani AI.
 * Foto laporan wajib dari kamera dan diberi watermark waktu + koordinat.
 */
class AutoVerificationTest extends TestCase
{
    use RefreshDatabase;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        $department = Department::create(['name' => 'Dinas PU', 'code' => 'PU']);
        $this->category = Category::create([
            'name' => 'Jalan & Jembatan',
            'department_id' => $department->id,
            'severity_default' => 4,
        ]);
    }

    private function warga(): User
    {
        return User::create([
            'name' => 'Warga Uji',
            'email' => 'warga.auto@example.test',
            'phone' => '081277770001',
            'password' => 'Password123',
            'role' => 'WARGA',
            'is_active' => true,
        ]);
    }

    private function mockAi(array $hasil): void
    {
        $this->mock(AICVService::class, function ($mock) use ($hasil) {
            $mock->shouldReceive('classifyMultipleImages')->andReturn($hasil);
            $mock->shouldReceive('classifyInfrastructureDamage')->andReturn($hasil);
        });
    }

    private function kirim(): \Illuminate\Testing\TestResponse
    {
        return $this->actingAs($this->warga(), 'sanctum')->postJson('/api/v1/complaints', [
            'title' => 'Jalan amblas',
            'description' => 'Amblas sedalam 30 cm.',
            'category_id' => $this->category->id,
            'address' => 'Jl. Poros KM 2',
            'latitude' => -3.5061,
            'longitude' => 120.8879,
            'photos' => [UploadedFile::fake()->image('bukti.jpg', 800, 600)],
        ]);
    }

    public function test_foto_wajib_ada_karena_ai_yang_menilai(): void
    {
        $this->actingAs($this->warga(), 'sanctum')->postJson('/api/v1/complaints', [
            'title' => 'Tanpa foto',
            'description' => 'Tidak melampirkan foto.',
            'category_id' => $this->category->id,
            'address' => 'Jl. Poros KM 2',
            'latitude' => -3.5061,
            'longitude' => 120.8879,
        ])->assertStatus(422)->assertJsonValidationErrors('photos');
    }

    public function test_ai_menilai_kerusakan_laporan_langsung_terverifikasi(): void
    {
        $this->mockAi([
            'is_valid_infrastructure' => true,
            'is_damaged' => true,
            'category_id' => 1,
            'category_label' => 'Jalan & Jembatan',
            'confidence' => 0.93,
            'severity_level' => 4,
        ]);

        $this->kirim()->assertStatus(201)->assertJsonPath('data.status', 'VERIFIED');
        $this->assertTrue((bool) Complaint::latest('id')->first()->verified_by_ai);
    }

    public function test_ai_menilai_bukan_kerusakan_laporan_ditolak_otomatis(): void
    {
        $this->mockAi([
            // AI benar-benar berjalan dan memutuskan foto bukan kerusakan infrastruktur.
            'is_valid_infrastructure' => false,
            'is_damaged' => false,
            'category_id' => 1,
            'category_label' => 'Bukan Infrastruktur',
            'confidence' => 0.85,
            'severity_level' => 0,
        ]);

        $this->kirim()->assertStatus(201);

        $complaint = Complaint::latest('id')->first();
        $this->assertSame('REJECTED', $complaint->status);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $complaint->user_id,
            'type' => 'COMPLAINT_REJECTED',
        ]);
    }

    public function test_operator_tidak_lagi_punya_tab_verifikasi_dan_validasi_manual(): void
    {
        $operator = User::create([
            'name' => 'Operator Uji',
            'email' => 'operator.auto@example.test',
            'phone' => '081277770002',
            'password' => 'Password123',
            'role' => 'OPERATOR',
            'is_active' => true,
        ]);

        $html = $this->actingAs($operator)->get('/operator')->assertOk()->getContent();

        // Ambil hanya isi accordion Operator PUPR di sidebar.
        $awal = strpos($html, 'accordionBody-operator');
        $akhir = strpos($html, 'accordionBody-bupati') ?: strpos($html, '</nav>');
        $sidebar = substr($html, $awal, max(0, $akhir - $awal));

        $this->assertNotFalse($awal);
        $this->assertStringNotContainsString('Verifikasi Aduan', $sidebar);
        $this->assertStringNotContainsString('Validasi Hasil', $sidebar);
        $this->assertStringContainsString('Siap Ditugaskan', $sidebar);
    }

    public function test_layar_monitoring_wajib_login(): void
    {
        // Diperiksa lebih dulu, sebelum ada sesi login lain di test ini.
        $this->get('/monitoring')->assertRedirect('/login');

        $monitoring = User::create([
            'name' => 'Petugas Monitoring',
            'email' => 'monitoring.auto@example.test',
            'phone' => '081277770004',
            'password' => 'Password123',
            'role' => 'MONITORING',
            'is_active' => true,
        ]);

        $this->actingAs($monitoring)->get('/monitoring')->assertOk()->assertSee('Pusat Pantau Aduan');
        $this->actingAs($monitoring)->getJson('/monitoring/data')->assertOk()->assertJsonStructure([
            'total', 'selesai', 'dikerjakan', 'rasio', 'tren', 'kecamatan', 'peta', 'terbaru',
        ]);
    }

    public function test_kepala_dinas_melihat_menu_admin(): void
    {
        $dinas = User::create([
            'name' => 'Kepala Dinas',
            'email' => 'dinas.auto@example.test',
            'phone' => '081277770003',
            'password' => 'Password123',
            'role' => 'DINAS',
            'is_active' => true,
        ]);

        $this->actingAs($dinas)->get('/admin')->assertOk();

        // Log aktivitas & pengaturan sistem khusus Super Admin.
        $this->actingAs($dinas)->get('/admin/activity')->assertStatus(403);
        $this->actingAs($dinas)->get('/admin/settings')->assertStatus(403);
    }
}
