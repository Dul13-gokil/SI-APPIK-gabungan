<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Validasi hasil kerja dilakukan AI, bukan operator. Tidak boleh ada lagi
 * tampilan yang menawarkan validasi manual.
 */
class NoManualValidationTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role, string $email): User
    {
        return User::create([
            'name'      => "Uji {$role}",
            'email'     => $email,
            'phone'     => '08' . random_int(1000000000, 9999999999),
            'password'  => Hash::make('Rahasia123'),
            'role'      => $role,
            'is_active' => true,
        ]);
    }

    public function test_dasbor_tidak_lagi_menampilkan_kartu_validasi(): void
    {
        $sa = $this->user('SUPERADMIN', 'sa.novalid@example.test');

        foreach (['/admin', '/bupati', '/operator'] as $url) {
            $html = $this->actingAs($sa)->get($url)->assertOk()->getContent();

            $this->assertStringNotContainsString('Uji hasil kerja', $html, "{$url} masih memuat kartu validasi.");
            $this->assertStringNotContainsString('>Validasi<', $html, "{$url} masih memuat label validasi.");
        }

        // Dasbor lapangan juga tidak boleh menawarkan validasi manual.
        $petugas = $this->user('PETUGAS', 'pt.novalid@example.test');
        $html = $this->actingAs($petugas)->get('/petugas')->assertOk()->getContent();
        $this->assertStringNotContainsString('>Validasi<', $html, '/petugas masih memuat label validasi.');
        $this->assertStringNotContainsString('petugasTabContent-validation', $html);
    }

    public function test_tab_validasi_operator_dialihkan(): void
    {
        $operator = $this->user('OPERATOR', 'op.novalid@example.test');

        $this->actingAs($operator)->get('/operator?tab=validation')->assertOk();

        // Panel validasi manual tidak boleh dirender sebagai tab aktif.
        $html = $this->actingAs($operator)->get('/operator?tab=validation')->getContent();
        $this->assertStringNotContainsString('Lakukan Validasi', $html);
    }

    public function test_filter_status_tidak_menawarkan_status_validasi(): void
    {
        $sa = $this->user('SUPERADMIN', 'sa.filter@example.test');

        foreach (['/admin', '/bupati'] as $url) {
            $html = $this->actingAs($sa)->get($url)->assertOk()->getContent();
            $this->assertStringNotContainsString('value="WAITING_VERIFICATION"', $html);
        }
    }
}