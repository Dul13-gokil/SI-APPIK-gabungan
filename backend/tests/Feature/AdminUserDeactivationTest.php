<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Penonaktifan akun pimpinan oleh Super Admin.
 *
 * Alasan wajib ≥ 10 karakter; sebelumnya kegagalan ini tidak terlihat karena
 * halaman admin tidak menampilkan pesan validasi, sehingga tombol seperti tidak
 * berfungsi.
 */
class AdminUserDeactivationTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create([
            'name'      => 'Super Admin',
            'email'     => 'sa.deact@example.test',
            'phone'     => '081200000101',
            'password'  => Hash::make('Rahasia123'),
            'role'      => 'SUPERADMIN',
            'is_active' => true,
        ]);
    }

    private function kepalaDaerah(): User
    {
        return User::create([
            'name'      => 'Kepala Daerah',
            'email'     => 'kd.deact@example.test',
            'phone'     => '081200000102',
            'password'  => Hash::make('Rahasia123'),
            'role'      => 'KEPALA_DAERAH',
            'is_active' => true,
        ]);
    }

    public function test_alasan_kurang_dari_sepuluh_karakter_ditolak_dengan_pesan_jelas(): void
    {
        $kd = $this->kepalaDaerah();

        $this->assertTrue($kd->fresh()->is_active);

        // Pesan harus muncul di halaman agar tombol tidak terasa "tidak berfungsi".
        $this->actingAs($this->admin())
            ->followingRedirects()
            ->post("/admin/users/{$kd->id}/deactivate", [
                'reason'                 => 'dewe',
                'admin_password'         => 'Rahasia123',
                'confirmation_statement' => 'NONAKTIFKAN ' . $kd->email,
            ])
            ->assertOk()
            ->assertSee('Data belum bisa disimpan')
            ->assertSee('minimal 10 karakter');
    }

    public function test_alasan_valid_menonaktifkan_akun_pimpinan(): void
    {
        $kd = $this->kepalaDaerah();

        $this->actingAs($this->admin())->post("/admin/users/{$kd->id}/deactivate", [
            'reason'                 => 'Mutasi jabatan ke pemerintah provinsi',
            'admin_password'         => 'Rahasia123',
            'confirmation_statement' => 'NONAKTIFKAN ' . $kd->email,
        ])->assertRedirect();

        $this->assertFalse($kd->fresh()->is_active);
        $this->assertDatabaseHas('audit_logs', ['action' => 'user.deactivated', 'entity_id' => $kd->id]);
    }
}
