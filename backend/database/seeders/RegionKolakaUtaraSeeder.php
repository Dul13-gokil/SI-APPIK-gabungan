<?php

namespace Database\Seeders;

use App\Models\Region;
use App\Models\User;
use App\Models\Complaint;
use Illuminate\Database\Seeder;

class RegionKolakaUtaraSeeder extends Seeder
{
    public function run(): void
    {
        Region::truncate();

        $districts = [
            ['district' => 'Kecamatan Lasusua',      'village' => 'Kel. Lasusua',      'latitude' => -3.5262, 'longitude' => 120.8407],
            ['district' => 'Kecamatan Batu Putih',   'village' => 'Kel. Batu Putih',   'latitude' => -3.0076, 'longitude' => 121.0267],
            ['district' => 'Kecamatan Katoi',        'village' => 'Desa Katoi',        'latitude' => -3.4682, 'longitude' => 120.8931],
            ['district' => 'Kecamatan Kodeoha',      'village' => 'Desa Mala-Mala',    'latitude' => -3.3986, 'longitude' => 120.9168],
            ['district' => 'Kecamatan Lambai',       'village' => 'Desa Lambai',       'latitude' => -3.5781, 'longitude' => 120.9189],
            ['district' => 'Kecamatan Ngapa',        'village' => 'Kel. Lapai',        'latitude' => -3.1783, 'longitude' => 120.9701],
            ['district' => 'Kecamatan Pakue',        'village' => 'Kel. Olo-Oloho',    'latitude' => -3.0903, 'longitude' => 121.0022],
            ['district' => 'Kecamatan Pakue Tengah', 'village' => 'Desa Latali',       'latitude' => -3.0822, 'longitude' => 121.0520],
            ['district' => 'Kecamatan Pakue Utara',  'village' => 'Desa Pakue',        'latitude' => -3.0135, 'longitude' => 121.0182],
            ['district' => 'Kecamatan Porehu',       'village' => 'Desa Porehu',       'latitude' => -2.9682, 'longitude' => 121.2189],
            ['district' => 'Kecamatan Rante Angin',  'village' => 'Kel. Rante Angin',  'latitude' => -3.6425, 'longitude' => 121.0112],
            ['district' => 'Kecamatan Tiwu',         'village' => 'Desa Tiwu',         'latitude' => -3.6163, 'longitude' => 120.9765],
            ['district' => 'Kecamatan Tolala',       'village' => 'Desa Tolala',       'latitude' => -2.5732, 'longitude' => 121.1485],
            ['district' => 'Kecamatan Watunohu',     'village' => 'Desa Watunohu',     'latitude' => -3.2796, 'longitude' => 120.9328],
            ['district' => 'Kecamatan Wawo',         'village' => 'Desa Wawo',         'latitude' => -3.7381, 'longitude' => 121.0371],
        ];

        foreach ($districts as $d) {
            Region::create([
                'province'  => 'Sulawesi Tenggara',
                'regency'   => 'Kabupaten Kolaka Utara',
                'district'  => $d['district'],
                'village'   => $d['village'],
                'latitude'  => $d['latitude'],
                'longitude' => $d['longitude'],
            ]);
        }

        $lasusua = Region::first();
        User::query()->update(['region_id' => $lasusua->id]);

        $warga = User::where('role', 'WARGA')->first();
        $cat = \App\Models\Category::first();
        $petugas = User::where('role', 'PETUGAS')->first();
        $dept = \App\Models\Department::first();

        if ($warga && $cat) {
            $cmp = Complaint::create([
                'public_code' => 'ADU-0001',
                'user_id'     => $warga->id,
                'region_id'   => $lasusua->id,
                'category_id' => $cat->id,
                'title'       => 'Laporan Jalan Rusak / Berlubang Dalam',
                'description' => 'Terdapat lubang jalan cukup lebar dan dalam di ruas jalan protokol Lasusua, membahayakan pengendara motor saat malam hari.',
                'address'     => 'Jl. Trans Sulawesi, Kel. Lasusua, Kec. Lasusua, Kab. Kolaka Utara',
                'latitude'    => -3.2428,
                'longitude'   => 120.9028,
                'status'      => 'COMPLETED',
            ]);

            if ($dept && $petugas) {
                \App\Models\Disposition::create([
                    'complaint_id'   => $cmp->id,
                    'department_id'  => $dept->id,
                    'assigned_to'    => $petugas->id,
                    'assigned_by'    => 1,
                    'instruction'    => 'Segera lakukan penambalan hotmix aspal di badan jalan protokol Lasusua.',
                    'status'         => 'COMPLETED',
                ]);
            }
        }
    }
}
