<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Department;
use App\Models\Category;
use App\Models\Region;
use App\Models\SystemSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Initial Regions: 15 Kecamatan di Kabupaten Kolaka Utara (Sulawesi Tenggara)
        $districtsKolakaUtara = [
            ['district' => 'Kecamatan Lasusua', 'village' => 'Kelurahan Lasusua', 'latitude' => -3.2428, 'longitude' => 120.9028],
            ['district' => 'Kecamatan Kodeoha', 'village' => 'Desa Lametuna', 'latitude' => -3.3250, 'longitude' => 120.9350],
            ['district' => 'Kecamatan Tiwu', 'village' => 'Desa Tiwu', 'latitude' => -3.3600, 'longitude' => 120.9400],
            ['district' => 'Kecamatan Ngapa', 'village' => 'Desa Lawolatu', 'latitude' => -3.1500, 'longitude' => 120.9100],
            ['district' => 'Kecamatan Watunohu', 'village' => 'Desa Watunohu', 'latitude' => -3.1900, 'longitude' => 120.9000],
            ['district' => 'Kecamatan Pakue', 'village' => 'Desa Olo-Oloho', 'latitude' => -3.0500, 'longitude' => 120.9800],
            ['district' => 'Kecamatan Pakue Tengah', 'village' => 'Desa Pasampang', 'latitude' => -3.0100, 'longitude' => 121.0100],
            ['district' => 'Kecamatan Pakue Utara', 'village' => 'Desa Pakue', 'latitude' => -2.9600, 'longitude' => 121.0500],
            ['district' => 'Kecamatan Batu Putih', 'village' => 'Desa Batu Putih', 'latitude' => -2.8900, 'longitude' => 121.0800],
            ['district' => 'Kecamatan Porehu', 'village' => 'Desa Porehu', 'latitude' => -3.0200, 'longitude' => 121.1600],
            ['district' => 'Kecamatan Tolala', 'village' => 'Desa Tolala', 'latitude' => -2.8500, 'longitude' => 121.1200],
            ['district' => 'Kecamatan Ranteangin', 'village' => 'Desa Ranteangin', 'latitude' => -3.4500, 'longitude' => 120.9700],
            ['district' => 'Kecamatan Wawo', 'village' => 'Desa Wawo', 'latitude' => -3.5200, 'longitude' => 121.0200],
            ['district' => 'Kecamatan Lambai', 'village' => 'Desa Lambai', 'latitude' => -3.4100, 'longitude' => 120.9600],
            ['district' => 'Kecamatan Katoi', 'village' => 'Desa Katoi', 'latitude' => -3.3900, 'longitude' => 120.9500],
        ];

        $firstRegion = null;
        foreach ($districtsKolakaUtara as $d) {
            $r = Region::create([
                'province'  => 'Sulawesi Tenggara',
                'regency'   => 'Kabupaten Kolaka Utara',
                'district'  => $d['district'],
                'village'   => $d['village'],
                'latitude'  => $d['latitude'],
                'longitude' => $d['longitude'],
            ]);
            if (!$firstRegion) $firstRegion = $r;
        }
        $region = $firstRegion;

        // 2. Initial Users per Role (SAP Section 5)
        User::create([
            'name' => 'Super Administrator',
            'email' => 'admin@kabupaten.go.id',
            'phone' => '081100000001',
            'password' => Hash::make('Admin123!'),
            'role' => 'ADMIN',
            'region_id' => $region->id,
        ]);

        User::create([
            'name' => 'Kepala Daerah Kabupaten',
            'email' => 'bupati@kabupaten.go.id',
            'phone' => '081100000002',
            'password' => Hash::make('Bupati123!'),
            'role' => 'KEPALA_DAERAH',
            'region_id' => $region->id,
        ]);

        User::create([
            'name' => 'Operator Verifikasi',
            'email' => 'operator@kabupaten.go.id',
            'phone' => '081100000003',
            'password' => Hash::make('Operator123!'),
            'role' => 'OPERATOR',
            'region_id' => $region->id,
        ]);

        User::create([
            'name' => 'Petugas Lapangan PUPR',
            'email' => 'petugas@kabupaten.go.id',
            'phone' => '081100000004',
            'password' => Hash::make('Petugas123!'),
            'role' => 'PETUGAS',
            'region_id' => $region->id,
        ]);

        User::create([
            'name' => 'Warga Masyarakat (Budi)',
            'email' => 'warga@example.com',
            'phone' => '081234567890',
            'password' => Hash::make('Warga123!'),
            'role' => 'WARGA',
            'region_id' => $region->id,
        ]);

        // 3. Master Department (Hanya Dinas PUPR)
        $deptPupr = Department::create([
            'name' => 'Dinas Pekerjaan Umum dan Penataan Ruang (PUPR)',
            'code' => 'PUPR',
            'description' => 'Penanganan jalan, jembatan, penerangan jalan umum, dan fasilitas umum.',
        ]);

        // 4. Master Categories with Default Severity & AI Label (Seluruhnya di bawah naungan Dinas PUPR)
        Category::create([
            'id' => 1,
            'name' => 'Jalan & Jembatan',
            'department_id' => $deptPupr->id,
            'severity_default' => 4,
            'ai_label' => 'pothole',
            'sla_hours' => 48,
        ]);

        Category::create([
            'id' => 2,
            'name' => 'Penerangan Jalan Umum (PJU)',
            'department_id' => $deptPupr->id,
            'severity_default' => 2,
            'ai_label' => 'street_light_damaged',
            'sla_hours' => 72,
        ]);

        Category::create([
            'id' => 3,
            'name' => 'Fasilitas Umum',
            'department_id' => $deptPupr->id,
            'severity_default' => 3,
            'ai_label' => 'sidewalk_damaged',
            'sla_hours' => 72,
        ]);

        // 5. System Settings (SAP Section 32)
        SystemSetting::create([
            'key' => 'duplicate_radius_m',
            'value' => '100',
            'type' => 'integer',
            'description' => 'Radius pencarian duplikasi laporan dalam meter',
        ]);

        SystemSetting::create([
            'key' => 'upload_max_mb',
            'value' => '10',
            'type' => 'integer',
            'description' => 'Batas maksimal ukuran upload foto dalam MB',
        ]);

        SystemSetting::create([
            'key' => 'priority_weights',
            'value' => json_encode([
                'severity_weight' => 0.35,
                'support_weight' => 0.20,
                'location_weight' => 0.20,
                'age_weight' => 0.15,
                'duplicate_weight' => 0.10,
            ]),
            'type' => 'json',
            'description' => 'Bobot kalkulasi skor prioritas',
        ]);
    }
}
