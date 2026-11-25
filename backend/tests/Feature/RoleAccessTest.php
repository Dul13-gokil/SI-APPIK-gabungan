<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Batas akses antar peran.
 *
 * Super Admin adalah peran tertinggi. Kepala Daerah mendapat seluruh fitur
 * administrasi kecuali Log Aktivitas dan Pengaturan Sistem. Operator PUPR dan
 * Pengawas Lapangan mengelola regu beserta identitas personilnya sendiri.
 */
class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    private function buat(string $role, string $email): User
    {
        return User::create([
            'name'      => "Uji {$role}",
            'email'     => $email,
            'phone'     => '08' . random_int(1000000000, 9999999999),
            'password'  => Hash::make('Password123'),
            'role'      => $role,
            'is_active' => true,
        ]);
    }

    public function test_kepala_daerah_mendapat_seluruh_fitur_admin_kecuali_log_dan_pengaturan(): void
    {
        $kd = $this->buat('KEPALA_DAERAH', 'kd@example.test');

        $this->actingAs($kd)->get('/admin')->assertOk();
        $this->actingAs($kd)->get('/operator')->assertOk();
        $this->actingAs($kd)->get('/bupati')->assertOk();

        $this->actingAs($kd)->get('/admin/activity')->assertStatus(403);
        $this->actingAs($kd)->get('/admin/settings')->assertStatus(403);
    }

    public function test_super_admin_tetap_memegang_log_aktivitas_dan_pengaturan(): void
    {
        $sa = $this->buat('SUPERADMIN', 'sa@example.test');

        $this->actingAs($sa)->get('/admin')->assertOk();
        $this->actingAs($sa)->get('/admin/activity')->assertOk();
        $this->actingAs($sa)->get('/admin/settings')->assertOk();
    }

    public function test_monitoring_menolak_tamu_dan_peran_lain(): void
    {
        $this->get('/monitoring')->assertRedirect('/login');

        $operator = $this->buat('OPERATOR', 'op@example.test');
        $this->actingAs($operator)->get('/monitoring')->assertStatus(403);

        $monitoring = $this->buat('MONITORING', 'mon@example.test');
        $this->actingAs($monitoring)->get('/monitoring')->assertOk();
    }

    public function test_operator_mengelola_regu_dan_menambah_identitas_personil(): void
    {
        $operator = $this->buat('OPERATOR', 'op2@example.test');

        $this->actingAs($operator)->get('/operator/regu')->assertOk();

        $this->actingAs($operator)->post('/operator/regu', [
            'name'     => 'Regu Uji Lapangan',
            'district' => 'Lasusua',
        ])->assertRedirect();

        $team = \App\Models\Team::where('name', 'Regu Uji Lapangan')->firstOrFail();

        $this->actingAs($operator)->post("/operator/regu/{$team->id}/anggota", [
            'name'  => 'Personil Uji',
            'email' => 'personil.uji@example.test',
            'phone' => '081234567890',
            'role'  => 'MEMBER',
        ])->assertRedirect();

        $personil = User::where('email', 'personil.uji@example.test')->firstOrFail();
        $this->assertSame('PETUGAS', $personil->role);
        $this->assertDatabaseHas('team_members', [
            'team_id' => $team->id,
            'user_id' => $personil->id,
            'role'    => 'MEMBER',
        ]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'TEAM_MEMBER_CREATED']);
    }

    public function test_pengawas_hanya_boleh_menambah_anggota_ke_regunya_sendiri(): void
    {
        $reguA = \App\Models\Team::create(['name' => 'Regu A', 'code' => 'RA']);
        $reguB = \App\Models\Team::create(['name' => 'Regu B', 'code' => 'RB']);

        $pengawas = $this->buat('PENGAWAS', 'pw@example.test');
        \App\Models\TeamMember::create([
            'team_id' => $reguA->id,
            'user_id' => $pengawas->id,
            'role'    => 'LEADER',
        ]);
        $reguA->update(['leader_id' => $pengawas->id]);

        $this->actingAs($pengawas)->post("/petugas/regu/{$reguA->id}/anggota/baru", [
            'name'  => 'Anggota A',
            'email' => 'anggota.a@example.test',
        ])->assertRedirect();

        $this->actingAs($pengawas)->post("/petugas/regu/{$reguB->id}/anggota/baru", [
            'name'  => 'Anggota B',
            'email' => 'anggota.b@example.test',
        ])->assertStatus(403);

        $this->assertDatabaseMissing('users', ['email' => 'anggota.b@example.test']);
    }

    public function test_urutan_accordion_sidebar_mengikuti_hierarki_peran(): void
    {
        $sa = $this->buat('SUPERADMIN', 'sa2@example.test');
        $html = $this->actingAs($sa)->get('/admin')->assertOk()->getContent();

        $posisi = [];
        foreach (['admin', 'bupati', 'operator', 'petugas'] as $kunci) {
            $posisi[$kunci] = strpos($html, "accordionBody-{$kunci}");
            $this->assertNotFalse($posisi[$kunci], "Accordion {$kunci} tidak ditemukan.");
        }

        $this->assertLessThan($posisi['bupati'], $posisi['admin'], 'Super Admin harus di atas Kepala Daerah.');
        $this->assertLessThan($posisi['operator'], $posisi['bupati'], 'Kepala Daerah harus di atas Operator PUPR.');
        $this->assertLessThan($posisi['petugas'], $posisi['operator'], 'Operator PUPR harus di atas Pengawas Lapangan.');
    }

    public function test_admin_mengisi_identitas_personil_di_regu_dan_tugas(): void
    {
        $sa = $this->buat('SUPERADMIN', 'sa3@example.test');
        $team = \App\Models\Team::create(['name' => 'Regu Admin', 'code' => 'RADM']);

        $this->actingAs($sa)->post("/admin/teams/{$team->id}/anggota-baru", [
            'name'  => 'Personil Admin',
            'email' => 'personil.admin@example.test',
            'phone' => '081234567891',
        ])->assertRedirect();

        $personil = User::where('email', 'personil.admin@example.test')->firstOrFail();
        $this->assertSame('PETUGAS', $personil->role);
        $this->assertDatabaseHas('team_members', ['team_id' => $team->id, 'user_id' => $personil->id]);
    }

    public function test_layar_pantau_menyediakan_keluar_sesi(): void
    {
        $monitoring = $this->buat('MONITORING', 'mon2@example.test');
        $html = $this->actingAs($monitoring)->get('/monitoring')->assertOk()->getContent();

        $this->assertStringContainsString('action="' . route('logout') . '"', $html);
    }
}
