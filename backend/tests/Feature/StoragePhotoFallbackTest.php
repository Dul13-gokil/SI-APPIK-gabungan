<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Complaint;
use App\Models\ComplaintPhoto;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Foto yang hilang dari disk harus tetap tersaji dari kolom file_data,
 * dan rute bawaan Laravel (storage.local) tidak boleh menimpanya.
 */
class StoragePhotoFallbackTest extends TestCase
{
    use RefreshDatabase;

    private function makeComplaint(): Complaint
    {
        $department = Department::create(['name' => 'Dinas PU', 'code' => 'PU']);
        $category = Category::create([
            'name' => 'Jalan Rusak',
            'department_id' => $department->id,
            'severity_default' => 3,
        ]);
        $user = User::create([
            'name' => 'Warga Uji',
            'email' => 'warga.storage@example.test',
            'phone' => '081200000001',
            'password' => 'Password123',
            'role' => 'WARGA',
        ]);

        return Complaint::create([
            'public_code' => 'UJI-STORAGE-1',
            'user_id' => $user->id,
            'category_id' => $category->id,
            'title' => 'Foto uji',
            'description' => 'Pengujian penyajian foto.',
            'address' => 'Jl. Poros Lasusua',
            'latitude' => -3.506,
            'longitude' => 120.887,
            'status' => 'SUBMITTED',
            'priority_score' => 10,
            'priority_level' => 'LOW',
        ]);
    }

    public function test_missing_file_is_served_from_database_column(): void
    {
        $complaint = $this->makeComplaint();
        $bytes = "\xFF\xD8\xFF\xE0contoh-biner-jpeg\xFF\xD9";

        ComplaintPhoto::create([
            'complaint_id' => $complaint->id,
            'storage_key' => 'complaints/uji-dari-database.jpg',
            'type' => 'evidence_before',
            'mime' => 'image/jpeg',
            'size' => strlen($bytes),
            'file_data' => base64_encode($bytes),
            'captured_at' => now(),
        ]);

        $response = $this->get('/storage/complaints/uji-dari-database.jpg');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'image/jpeg');
        $this->assertSame($bytes, $response->getContent());
    }

    public function test_unknown_photo_returns_404(): void
    {
        $this->get('/storage/complaints/tidak-ada.jpg')->assertStatus(404);
    }

    public function test_path_traversal_is_rejected(): void
    {
        $status = $this->get('/storage/../.env')->status();

        $this->assertContains($status, [403, 404]);
    }
}
