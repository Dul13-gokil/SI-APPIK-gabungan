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

    public function test_sandi_awal_acak_dan_wajib_diganti_saat_login_pertama(): void
    {
        $this->actingAs($this->admin())->post('/admin/users', [
            'name'  => 'Operator Baru',
            'email' => 'operator.baru@example.test',
            'phone' => '081200000203',
            'role'  => 'OPERATOR',
        ])->assertRedirect();

        $user = User::where('email', 'operator.baru@example.test')->firstOrFail();

        $this->assertFalse(Hash::check('password', $user->password), 'Sandi awal tidak boleh "password".');
        $this->assertTrue($user->must_change_password);
        $this->assertTrue($user->needsFirstLoginSetup());

        // Sandi sementara ditampilkan sekali pada pesan sukses.
        $pesan = (string) session('success');
        $this->assertStringContainsString('Sandi sementara:', $pesan);
        preg_match('/Sandi sementara: (\S+)/', $pesan, $cocok);
        $this->assertTrue(Hash::check($cocok[1] ?? '', $user->password));
    }

    public function test_peran_akun_dapat_diperbarui_dari_daftar_pengguna(): void
    {
        $user = User::create([
            'name'      => 'Calon Operator',
            'email'     => 'calon.operator@example.test',
            'phone'     => '081200000204',
            'password'  => Hash::make('Rahasia123'),
            'role'      => 'PETUGAS',
            'is_active' => true,
        ]);

        $this->actingAs($this->admin())->put("/admin/users/{$user->id}", [
            'name'  => 'Calon Operator',
            'email' => 'calon.operator@example.test',
            'phone' => '081200000204',
            'role'  => 'OPERATOR',
        ])->assertRedirect();

        $this->assertSame('OPERATOR', $user->fresh()->role);
    }

    public function test_perubahan_peran_dibatasi_untuk_akun_tunggal_dan_akun_sendiri(): void
    {
        $admin = $this->admin();
        $lain = User::create([
            'name'      => 'Operator Lain',
            'email'     => 'operator.lain@example.test',
            'phone'     => '081200000205',
            'password'  => Hash::make('Rahasia123'),
            'role'      => 'OPERATOR',
            'is_active' => true,
        ]);

        // Super Admin sudah ada (akun yang sedang login).
        $this->actingAs($admin)->put("/admin/users/{$lain->id}", [
            'name'  => 'Operator Lain',
            'email' => 'operator.lain@example.test',
            'phone' => '081200000205',
            'role'  => 'SUPERADMIN',
        ])->assertSessionHas('error');

        // Peran akun sendiri tidak boleh diubah.
        $this->actingAs($admin)->put("/admin/users/{$admin->id}", [
            'name'  => $admin->name,
            'email' => $admin->email,
            'phone' => '081200000201',
            'role'  => 'KEPALA_DAERAH',
        ])->assertSessionHas('error');

        $this->assertSame('OPERATOR', $lain->fresh()->role);
        $this->assertSame('SUPERADMIN', $admin->fresh()->role);
    }

    public function test_reset_sandi_menghasilkan_sandi_sementara_bukan_password(): void
    {
        $user = User::create([
            'name'      => 'Pengawas Uji',
            'email'     => 'pengawas.uji@example.test',
            'phone'     => '081200000206',
            'password'  => Hash::make('Rahasia123'),
            'role'      => 'PENGAWAS',
            'is_active' => true,
        ]);

        $this->actingAs($this->admin())->post("/admin/users/{$user->id}/reset")->assertRedirect();

        $user->refresh();
        $this->assertFalse(Hash::check('password', $user->password));
        $this->assertTrue($user->must_change_password);
        $this->assertStringContainsString('Sandi sementara:', (string) session('success'));
    }

    public function test_reset_sandi_hanya_lewat_tombol_kunci_dan_sandinya_tetap_terlihat(): void
    {
        $user = User::create([
            'name'      => 'Operator Uji Reset',
            'email'     => 'operator.reset@example.test',
            'phone'     => '081200000207',
            'password'  => Hash::make('Rahasia123'),
            'role'      => 'OPERATOR',
            'is_active' => true,
        ]);

        $admin = $this->admin();

        // Modal edit tidak lagi memuat opsi reset (satu jalur saja).
        $html = $this->actingAs($admin)->get('/admin?tab=users-management')->assertOk()->getContent();
        $this->assertStringNotContainsString('reset_default', $html);

        $response = $this->actingAs($admin)
            ->followingRedirects()
            ->post("/admin/users/{$user->id}/reset")
            ->assertOk();

        // Sandi sementara tampil di toast yang tidak hilang sendiri.
        $response->assertSee('Sandi sementara:');
        $response->assertSee('data-persist="1"', false);
    }

    public function test_super_admin_boleh_menetapkan_sandi_akun_baru(): void
    {
        $this->actingAs($this->admin())->post('/admin/users', [
            'name'     => 'Operator Bersandi',
            'email'    => 'operator.bersandi@example.test',
            'phone'    => '081200000208',
            'role'     => 'OPERATOR',
            'password' => 'Rahasia2026',
        ])->assertRedirect();

        $user = User::where('email', 'operator.bersandi@example.test')->firstOrFail();

        $this->assertTrue(Hash::check('Rahasia2026', $user->password));
        $this->assertFalse($user->must_change_password, 'Sandi yang ditetapkan admin tidak perlu dipaksa ganti.');
        $this->assertStringContainsString('sandi yang Anda tetapkan', (string) session('success'));
    }

    public function test_sandi_dapat_diubah_dari_modal_edit(): void
    {
        $user = User::create([
            'name'      => 'Operator Ganti Sandi',
            'email'     => 'operator.ganti@example.test',
            'phone'     => '081200000209',
            'password'  => Hash::make('Rahasia123'),
            'role'      => 'OPERATOR',
            'is_active' => true,
        ]);

        $this->actingAs($this->admin())->put("/admin/users/{$user->id}", [
            'name'     => 'Operator Ganti Sandi',
            'email'    => 'operator.ganti@example.test',
            'phone'    => '081200000209',
            'role'     => 'OPERATOR',
            'password' => 'SandiBaru2026',
        ])->assertRedirect();

        $this->assertTrue(Hash::check('SandiBaru2026', $user->fresh()->password));
    }

    public function test_akun_baru_dengan_sandi_ditetapkan_bisa_login(): void
    {
        $this->actingAs($this->admin())->post('/admin/users', [
            'name'     => 'Operator Login',
            'email'    => 'operator.login@example.test',
            'phone'    => '081200000210',
            'role'     => 'OPERATOR',
            'password' => 'Rahasia2026',
        ])->assertRedirect();

        $this->post('/login', [
            'email'    => 'operator.login@example.test',
            'password' => 'Rahasia2026',
        ])->assertRedirect('/operator');
    }

    /**
     * Pengguna sering mengetik email dengan huruf kapital atau mengisi nomor HP
     * dengan spasi. PostgreSQL peka huruf, jadi dulu login selalu gagal.
     */
    public function test_login_tidak_peka_huruf_besar_kecil_dan_format_nomor(): void
    {
        $user = User::create([
            'name'      => 'Operator Kapital',
            'email'     => 'Operator.Kapital@Example.Test',
            'phone'     => '0812-0000-0211',
            'password'  => Hash::make('Rahasia2026'),
            'role'      => 'OPERATOR',
            'is_active' => true,
        ]);

        // Email tersimpan huruf kecil.
        $this->assertSame('operator.kapital@example.test', $user->fresh()->email);

        foreach (['operator.kapital@example.test', 'OPERATOR.KAPITAL@EXAMPLE.TEST', '  Operator.Kapital@Example.Test  '] as $email) {
            $this->post('/login', ['email' => $email, 'password' => 'Rahasia2026'])
                ->assertRedirect('/operator');
            $this->post('/logout');
        }

        foreach (['081200000211', '0812-0000-0211'] as $phone) {
            $this->post('/login', ['email' => $phone, 'password' => 'Rahasia2026'])
                ->assertRedirect('/operator');
            $this->post('/logout');
        }
    }
}
