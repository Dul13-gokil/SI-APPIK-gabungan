<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Kelola Akun & Pengguna: satu form (tambah akun) + edit lewat daftar pengguna.
 * Dulu ada dua form terpisah (Perbarui Akun Kepala Daerah dan Tambah Akun),
 * sehingga data akun bisa diperbarui dari dua tempat berbeda.
 */
class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create([
            'name'      => 'Super Admin',
            'email'     => 'sa.kelola@example.test',
            'phone'     => '081200000201',
            'password'  => Hash::make('Rahasia123'),
            'role'      => 'SUPERADMIN',
            'is_active' => true,
        ]);
    }

    public function test_hanya_ada_satu_form_dan_edit_lewat_daftar_pengguna(): void
    {
        $html = $this->actingAs($this->admin())
            ->get('/admin?tab=users-management')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Tambah Akun Baru', $html);
        $this->assertStringNotContainsString('Perbarui Akun Kepala Daerah', $html);

        // Tombol edit pada baris daftar pengguna tetap tersedia.
        $this->assertStringContainsString('openEditUserModalFromBtn', $html);
    }

    public function test_akun_kepala_daerah_masih_bisa_dibuat_dari_form_tambah_akun(): void
    {
        $response = $this->actingAs($this->admin())->post('/admin/users', [
            'name'  => 'Kepala Daerah Baru',
            'email' => 'kd.baru@example.test',
            'phone' => '081200000202',
            'role'  => 'KEPALA_DAERAH',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'email' => 'kd.baru@example.test',
            'role'  => 'KEPALA_DAERAH',
        ]);
    }
}