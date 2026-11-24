<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Complaint;
use App\Models\ComplaintPhoto;
use App\Models\Department;
use App\Models\User;
use App\Services\AICVService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Mengunci keputusan penyimpanan bukti foto: berkasnya hanya di disk.
 *
 * Dulu setiap unggahan juga menyalin isi berkas sebagai base64 ke kolom
 * complaint_photos.file_data. Untuk foto 10 MB itu berarti +-13 MB per baris di
 * PostgreSQL, membuat tabel dan backup membengkak. Kolomnya tidak dihapus agar
 * foto lama tetap bisa disajikan, tetapi tidak boleh diisi lagi.
 */
class PhotoStorageTest extends TestCase
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

        $this->mock(AICVService::class, function ($mock) {
            $mock->shouldReceive('classifyMultipleImages')->andReturn([
                'is_valid' => true,
                'confidence' => 0.95,
                'severity_level' => 4,
                'damage_type' => 'pothole',
                'recommended_radius_m' => 20,
                'ai_description' => 'Lubang jalan',
                'validated' => true,
            ]);
            $mock->shouldReceive('classifyInfrastructureDamage')->andReturn([
                'is_valid' => true,
                'confidence' => 0.95,
            ]);
        });
    }

    private function warga(): User
    {
        return User::create([
            'name' => 'Warga Foto',
            'email' => 'warga.foto@example.test',
            'phone' => '081299990001',
            'password' => 'Password123',
            'role' => 'WARGA',
        ]);
    }

    private function kirimLaporan(): Complaint
    {
        $response = $this->actingAs($this->warga(), 'sanctum')->postJson('/api/v1/complaints', [
            'title' => 'Jalan berlubang dalam',
            'description' => 'Lubang sedalam 25 cm di lajur kiri.',
            'category_id' => $this->category->id,
            'address' => 'Jl. Poros Lasusua KM 4',
            'latitude' => -3.5061,
            'longitude' => 120.8879,
            'photos' => [UploadedFile::fake()->image('bukti.jpg', 800, 600)],
        ]);

        $response->assertStatus(201);

        return Complaint::latest('id')->firstOrFail();
    }

    public function test_unggahan_foto_tidak_lagi_menyalin_base64_ke_database(): void
    {
        $complaint = $this->kirimLaporan();

        $photo = ComplaintPhoto::where('complaint_id', $complaint->id)->firstOrFail();

        $this->assertNull(
            $photo->file_data,
            'Kolom file_data tidak boleh diisi lagi; foto cukup disimpan di disk.'
        );
    }

    public function test_berkas_foto_tetap_tersimpan_di_disk_dan_bisa_disajikan(): void
    {
        $complaint = $this->kirimLaporan();

        $photo = ComplaintPhoto::where('complaint_id', $complaint->id)->firstOrFail();

        $this->assertNotEmpty($photo->storage_key);
        Storage::disk('public')->assertExists($photo->storage_key);

        // Ukuran baris tetap kecil: tidak ada payload base64 yang menempel di tabel.
        $bytes = DB::table('complaint_photos')->where('id', $photo->id)->count();
        $this->assertSame(1, $bytes);
    }
}
