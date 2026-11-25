<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Mengunci perbaikan UX yang diminta: menu tambah pekerja pindah ke dalam
 * accordion Operator PUPR, dan akun baru tidak lagi memakai sandi 'password'.
 */
class OperatorUxTest extends TestCase
{
    use RefreshDatabase;

    private function operator(): User
    {
        $department = Department::create(['name' => 'Dinas PU', 'code' => 'PU']);
        Team::create([
            'name' => 'Regu Uji',
            'code' => 'UJI',
            'department_id' => $department->id,
            'is_active' => true,
        ]);

        return User::create([
            'name' => 'Operator Uji',
            'email' => 'operator.ux@example.test',
            'phone' => '081299970001',
            'password' => 'Password123',
            'role' => 'OPERATOR',
            'is_active' => true,
        ]);
    }

    public function test_menu_regu_dan_anggota_berada_di_dalam_accordion_operator(): void
    {
        $html = $this->actingAs($this->operator())
            ->get('/operator')
            ->assertOk()
            ->getContent();

        $posisiAccordion = strpos($html, 'accordionBody-operator');
        $posisiMenu = strpos($html, 'Regu &amp; Anggota');
        $posisiMenu = $posisiMenu !== false ? $posisiMenu : strpos($html, 'Regu & Anggota');

        $this->assertNotFalse($posisiAccordion);
        $this->assertNotFalse($posisiMenu);
        $this->assertGreaterThan(
            $posisiAccordion,
            $posisiMenu,
            'Menu Regu & Anggota harus dirender di dalam accordion Operator PUPR.'
        );
    }

    public function test_operator_membuat_identitas_anggota_regu_dengan_sandi_acak(): void
    {
        $operator = $this->operator();
        $team = \App\Models\Team::firstOrFail();

        $this->actingAs($operator)->post("/operator/regu/{$team->id}/anggota", [
            'name' => 'Anggota Baru',
            'email' => 'anggota.baru@example.test',
            'phone' => '081299970002',
        ])->assertRedirect();

        $anggota = User::where('email', 'anggota.baru@example.test')->firstOrFail();

        $this->assertSame('PETUGAS', $anggota->role);
        $this->assertFalse(
            Hash::check('password', $anggota->password),
            'Sandi bawaan "password" tidak boleh dipakai lagi.'
        );
        $this->assertDatabaseHas('team_members', [
            'team_id' => $team->id,
            'user_id' => $anggota->id,
        ]);

        $pesan = (string) session('success');
        $this->assertStringContainsString('Sandi sementara:', $pesan);
        preg_match('/Sandi sementara: (\S+)/', $pesan, $cocok);
        $this->assertTrue(Hash::check($cocok[1] ?? '', $anggota->password));
    }
}
