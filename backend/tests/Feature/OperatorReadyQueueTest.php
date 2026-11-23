<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Complaint;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Mengunci keputusan rapat: verifikasi manual operator diganti AI, sehingga laporan
 * berstatus VERIFIED harus tetap muncul di portal operator (tab "Siap Ditugaskan"),
 * dan landing page hanya menampilkan 2 kategori resmi.
 */
class OperatorReadyQueueTest extends TestCase
{
    use RefreshDatabase;

    private Department $department;
    private Category $category;
    private int $seq = 0;

    protected function setUp(): void
    {
        parent::setUp();

        $this->department = Department::create(['name' => 'Dinas PU', 'code' => 'PU']);
        $this->category = Category::create([
            'name' => 'Jalan & Jembatan',
            'department_id' => $this->department->id,
            'severity_default' => 3,
        ]);
    }

    private function user(string $role): User
    {
        $this->seq++;

        return User::create([
            'name' => "Pengguna {$role} {$this->seq}",
            'email' => strtolower($role) . "{$this->seq}@example.test",
            'phone' => '08122222' . str_pad((string) $this->seq, 4, '0', STR_PAD_LEFT),
            'password' => 'Password123',
            'role' => $role,
        ]);
    }

    private function complaint(User $reporter, string $status, string $title): Complaint
    {
        return Complaint::create([
            'public_code' => 'UJI-SIAP-' . ++$this->seq,
            'user_id' => $reporter->id,
            'category_id' => $this->category->id,
            'title' => $title,
            'description' => 'Deskripsi pengaduan.',
            'address' => 'Jl. Poros Lasusua',
            'latitude' => -3.5060,
            'longitude' => 120.8878,
            'status' => $status,
            'verified_by_ai' => $status === 'VERIFIED',
            'priority_score' => 10,
            'priority_level' => 'LOW',
        ]);
    }

    public function test_operator_ready_tab_lists_ai_verified_complaints(): void
    {
        $reporter = $this->user('WARGA');
        $operator = $this->user('OPERATOR');

        $this->complaint($reporter, 'VERIFIED', 'Aduan lolos verifikasi AI');
        $this->complaint($reporter, 'SUBMITTED', 'Aduan belum diverifikasi');

        $response = $this->actingAs($operator)->get('/operator?tab=ready');

        $response->assertOk();
        $response->assertViewHas('readyToDispatch', function ($ready) {
            return $ready->count() === 1
                && $ready->first()->title === 'Aduan lolos verifikasi AI'
                && ($ready->first()->status?->value ?? $ready->first()->status) === 'VERIFIED';
        });
        $response->assertSee('Siap Ditugaskan (Verifikasi AI)');
        $response->assertSee('operator-ready-row', false);
    }

    public function test_landing_page_only_shows_two_official_categories(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Jalan &amp; Jembatan', false);
        $response->assertSee('Fasilitas Umum Lainnya');
        $response->assertDontSee('Penerangan Jalan Umum');
    }
}
