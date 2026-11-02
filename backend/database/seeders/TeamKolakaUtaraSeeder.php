<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Disposition;
use App\Models\SystemSetting;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TeamKolakaUtaraSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Inisialisasi Mode Penugasan Global (Default: HYBRID / Opsi C)
        SystemSetting::set(
            'dispatch_mode',
            'HYBRID',
            'string',
            'Mode Penugasan Regu Lapangan: HYBRID (Operator Dispatch + Self-Claim), OPERATOR_ONLY (Hanya Operator), SELF_CLAIM_ONLY (Mandiri Regu)'
        );

        $pupr = Department::firstOrCreate(
            ['name' => 'Dinas Pekerjaan Umum dan Penataan Ruang (PUPR)'],
            ['code' => 'D-PUPR', 'description' => 'Dinas Teknis Penyelenggaraan Jalan & Jembatan Kab. Kolaka Utara']
        );

        // 2. Pastikan personil petugas lapangan mencukupi (5-10 personil per regu)
        $petugasData = [
            ['name' => 'Petugas Lapangan', 'email' => 'petugas@kabupaten.go.id', 'phone' => '081234567804'],
            ['name' => 'Budi Santoso', 'email' => 'budi.petugas@kabupaten.go.id', 'phone' => '081234567811'],
            ['name' => 'Agus Prasetyo', 'email' => 'agus.petugas@kabupaten.go.id', 'phone' => '081234567812'],
            ['name' => 'Dedi Kurniawan', 'email' => 'dedi.petugas@kabupaten.go.id', 'phone' => '081234567813'],
            ['name' => 'Eko Wahyudi', 'email' => 'eko.petugas@kabupaten.go.id', 'phone' => '081234567814'],
            ['name' => 'Fajar Hidayat', 'email' => 'fajar.petugas@kabupaten.go.id', 'phone' => '081234567815'],
            ['name' => 'Hendra Saputra', 'email' => 'hendra.petugas@kabupaten.go.id', 'phone' => '081234567816'],
            ['name' => 'Irfan Maulana', 'email' => 'irfan.petugas@kabupaten.go.id', 'phone' => '081234567817'],
            ['name' => 'Joko Susilo', 'email' => 'joko.petugas@kabupaten.go.id', 'phone' => '081234567818'],
            ['name' => 'Kurniawan Pratama', 'email' => 'kurniawan.petugas@kabupaten.go.id', 'phone' => '081234567819'],
        ];

        $users = [];
        foreach ($petugasData as $p) {
            $users[] = User::firstOrCreate(
                ['email' => $p['email']],
                [
                    'name'     => $p['name'],
                    'phone'    => $p['phone'],
                    'password' => Hash::make('password'),
                    'role'     => 'PETUGAS',
                ]
            );
        }

        // 3. Buat Regu 1: Regu Aspal 01 - Lasusua (Fokus Jalan & Jembatan)
        $regu1 = Team::firstOrCreate(
            ['code' => 'REGU-01'],
            [
                'name'          => 'Regu Aspal 01 - Lasusua',
                'district'      => 'Kecamatan Lasusua',
                'department_id' => $pupr->id,
                'leader_id'     => $users[0]->id, // Petugas Lapangan akun utama sebagai Ketua Regu
                'description'   => 'Regu spesialis tambal aspal (coldmix/hotmix), perataan badan jalan, dan armada dump truck wilayah Lasusua dan sekitarnya.',
                'is_active'     => true,
            ]
        );

        // Anggota Regu 1 (6 personil: 1 Ketua + 5 Anggota)
        TeamMember::firstOrCreate(
            ['team_id' => $regu1->id, 'user_id' => $users[0]->id],
            ['role' => 'LEADER']
        );
        for ($i = 1; $i <= 5; $i++) {
            TeamMember::firstOrCreate(
                ['team_id' => $regu1->id, 'user_id' => $users[$i]->id],
                ['role' => 'MEMBER']
            );
        }

        // 4. Buat Regu 2: Regu Reaksi Cepat PJU & Darurat (Fokus Seluruh Wilayah)
        $regu2 = Team::firstOrCreate(
            ['code' => 'REGU-02'],
            [
                'name'          => 'Regu Reaksi Cepat PJU & Darurat',
                'district'      => 'Semua Wilayah',
                'department_id' => $pupr->id,
                'leader_id'     => $users[6]->id,
                'description'   => 'Regu mobil tangga hidrolik penerangan jalan umum, pembersihan longsoran bahu jalan darurat, dan jembatan.',
                'is_active'     => true,
            ]
        );

        TeamMember::firstOrCreate(
            ['team_id' => $regu2->id, 'user_id' => $users[6]->id],
            ['role' => 'LEADER']
        );
        for ($i = 7; $i <= 9; $i++) {
            TeamMember::firstOrCreate(
                ['team_id' => $regu2->id, 'user_id' => $users[$i]->id],
                ['role' => 'MEMBER']
            );
        }

        // 5. Hubungkan seluruh disposisi yang ada saat ini ke Regu 1 agar sinkron
        Disposition::whereNull('team_id')->update(['team_id' => $regu1->id]);
    }
}
