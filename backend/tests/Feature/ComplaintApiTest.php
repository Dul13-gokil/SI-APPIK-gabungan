<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Complaint;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComplaintApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_complaints_via_api_v1(): void
    {
        $dept = Department::create(['name' => 'Dinas PU', 'code' => 'PU']);
        $cat = Category::create(['name' => 'Jalan Rusak', 'department_id' => $dept->id, 'severity_default' => 4]);
        $user = User::create(['name' => 'Budi', 'email' => 'budi@example.com', 'password' => 'secret', 'role' => 'WARGA']);

        Complaint::create([
            'public_code' => 'ADU-TEST-001',
            'user_id' => $user->id,
            'category_id' => $cat->id,
            'title' => 'Jalan Rusak Parah',
            'description' => 'Ada lubang besar di jalan utama.',
            'address' => 'Jl. Merdeka No. 1',
            'latitude' => -3.5060,
            'longitude' => 120.8878,
            'status' => 'SUBMITTED',
            'priority_score' => 60.0,
            'priority_level' => 'HIGH',
        ]);

        $response = $this->getJson('/api/v1/complaints');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'items' => [
                        '*' => ['id', 'public_code', 'title', 'status', 'category', 'reporter']
                    ],
                    'pagination' => ['current_page', 'total_items']
                ],
                'meta' => ['request_id', 'timestamp']
            ]);
    }

    public function test_can_create_complaint_via_api_v1(): void
    {
        $dept = Department::create(['name' => 'Dinas PU', 'code' => 'PU']);
        $cat = Category::create(['name' => 'Jalan Rusak', 'department_id' => $dept->id, 'severity_default' => 4]);
        $user = User::create(['name' => 'Budi', 'email' => 'budi@example.com', 'password' => 'secret', 'role' => 'WARGA']);

        $payload = [
            'title' => 'Jembatan Retak',
            'description' => 'Pondasi jembatan retak setelah hujan deras.',
            'category_id' => $cat->id,
            'address' => 'Desa Sukamaju RT 02',
            'latitude' => -3.5061,
            'longitude' => 120.8879,
            'is_anonymous' => false,
            // Foto wajib: laporan tanpa foto tidak bisa dinilai AI.
            'photos' => [\Illuminate\Http\UploadedFile::fake()->image('bukti.jpg', 800, 600)],
        ];

        $response = $this->actingAs($user)->postJson('/api/v1/complaints', $payload);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['id', 'public_code', 'title', 'priority_score', 'status'],
                'meta' => ['request_id']
            ]);

        // Status awal ditentukan AI: VERIFIED bila foto dinilai kerusakan infrastruktur,
        // REJECTED bila tidak. Tidak ada lagi status yang menunggu verifikasi manual.
        $tersimpan = \App\Models\Complaint::where('title', 'Jembatan Retak')->firstOrFail();
        $this->assertContains($tersimpan->status, ['VERIFIED', 'REJECTED']);
    }

    public function test_dashboard_summary_api(): void
    {
        $response = $this->getJson('/api/v1/dashboard/summary');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['total_complaints', 'status_breakdown', 'priority_breakdown'],
                'meta' => ['request_id']
            ]);
    }
}
