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

    public function test_menu_tambah_pekerja_berada_di_dalam_accordion_operator(): void
    {
        $html = $this->actingAs($this->operator())
            ->get('/operator')
            ->assertOk()
            ->getContent();

        $posisiAccordion = strpos($html, 'accordionBody-operator');
        $posisiMenu = strpos($html, 'Tambah Pekerja Lapangan');

        $this->assertNotFalse($posisiAccordion);
        $this->assertNotFalse($posisiMenu);
        $this->assertGreaterThan(
            $posisiAccordion,
            $posisiMenu,
            'Menu tambah pekerja harus dirender di dalam accordion Operator PUPR, bukan menu terpisah.'
        );
    }

    public function test_akun_pekerja_baru_memakai_sandi_acak_bukan_password(): void
    {
        $operator = $this->operator();

        $response = $this->actingAs($operator)->post('/operator/petugas', [
            'name' => 'Petugas Baru',
            'email' => 'petugas.baru@example.test',
            'phone' => '081299970002',
        ]);

        $response->assertRedirect();

        $petugas = User::where('email', 'petugas.baru@example.test')->firstOrFail();

        $this->assertFalse(
            Hash::check('password', $petugas->password),
            'Sandi bawaan "password" tidak boleh dipakai lagi untuk akun pekerja baru.'
        );

        // Sandi sementara harus bisa dibaca operator sekali dari pesan sukses.
        $pesan = session('success');
        $this->assertStringContainsString('Sandi sementara:', (string) $pesan);

        preg_match('/Sandi sementara: (\S+)/', (string) $pesan, $cocok);
        $this->assertNotEmpty($cocok[1] ?? null);
        $this->assertTrue(Hash::check($cocok[1], $petugas->password));
    }
}
