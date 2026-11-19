<?php

namespace Database\Seeders;

use App\Models\Complaint;
use App\Models\ComplaintPhoto;
use App\Models\ComplaintStatusHistory;
use App\Models\User;
use Illuminate\Database\Seeder;

class DummySubmittedComplaintsSeeder extends Seeder
{
    public function run(): void
    {
        $siti = User::firstOrCreate(
            ['email' => 'siti.rahmawati@warga.kolut.id'],
            ['name' => 'Siti Rahmawati', 'phone' => '081244556601', 'role' => 'WARGA', 'password' => bcrypt('Warga123!'), 'is_active' => true]
        );
        $hendrik = User::firstOrCreate(
            ['email' => 'hendrik.saputra@warga.kolut.id'],
            ['name' => 'Hendrik Saputra', 'phone' => '081244556602', 'role' => 'WARGA', 'password' => bcrypt('Warga123!'), 'is_active' => true]
        );
        $nur = User::firstOrCreate(
            ['email' => 'nurhayati@warga.kolut.id'],
            ['name' => 'Nurhayati', 'phone' => '081244556603', 'role' => 'WARGA', 'password' => bcrypt('Warga123!'), 'is_active' => true]
        );

        $photos = ComplaintPhoto::limit(5)->pluck('storage_key')->toArray();
        $nextNum = ((int) Complaint::max('id')) + 1;

        $dummies = [
            [
                'user_id' => 6, 'cat' => 1, 'reg' => 1, 'type' => 'minor',
                'title' => 'Jalan Berlubang di Simpang Pasar Lasusua',
                'desc'  => 'Lubang sedalam sekitar 20 cm di jalur utama menuju Pasar Lasusua yang membahayakan pengendara motor saat hujan.',
                'addr'  => 'Jalan Trans Sulawesi, Kel. Lasusua, Kec. Lasusua, Kab. Kolaka Utara',
                'lat'   => -3.5082, 'lng' => 120.8841,
            ],
            [
                'user_id' => 6, 'cat' => 2, 'reg' => 3, 'type' => 'minor',
                'title' => 'Lampu PJU Padam di Jalan Poros Lapai',
                'desc'  => 'Tiga titik lampu penerangan jalan umum padam total sejak seminggu terakhir sehingga jalan gelap di malam hari.',
                'addr'  => 'Jalan Poros Lapai, Kel. Lapai, Kec. Ngapa, Kab. Kolaka Utara',
                'lat'   => -3.3712, 'lng' => 120.9125,
            ],
            [
                'user_id' => 5, 'cat' => 1, 'reg' => 4, 'type' => 'major',
                'title' => 'Kerusakan Aspal Ruas Jalan Olo-Oloho',
                'desc'  => 'Permukaan aspal terkelupas dan bergelombang sepanjang sekitar 850 meter menuju pelabuhan rakyat Pakue.',
                'addr'  => 'Jalan Poros Olo-Oloho, Kel. Olo-Oloho, Kec. Pakue, Kab. Kolaka Utara',
                'lat'   => -3.1924, 'lng' => 120.9310, 'len' => '850 meter', 'cond' => 'Aspal Rusak Berat',
            ],
            [
                'user_id' => $siti->id, 'cat' => 3, 'reg' => 10, 'type' => 'minor',
                'title' => 'Drainase Tersumbat & Meluap di Mala-Mala',
                'desc'  => 'Saluran drainase di pinggir jalan desa pecah dan tersumbat material sehingga air meluap ke badan jalan saat hujan.',
                'addr'  => 'Jalan Poros Mala-Mala, Desa Mala-Mala, Kec. Kodeoha, Kab. Kolaka Utara',
                'lat'   => -3.4415, 'lng' => 120.9012,
            ],
            [
                'user_id' => $hendrik->id, 'cat' => 1, 'reg' => 14, 'type' => 'minor',
                'title' => 'Oprit Jembatan Rante Angin Retak',
                'desc'  => 'Sambungan aspal di ujung jembatan mengalami penurunan tanah dan retak cukup lebar.',
                'addr'  => 'Jalan Poros Rante Angin, Kel. Rante Angin, Kec. Rante Angin, Kab. Kolaka Utara',
                'lat'   => -3.6214, 'lng' => 120.9452,
            ],
            [
                'user_id' => $nur->id, 'cat' => 2, 'reg' => 13, 'type' => 'minor',
                'title' => 'Tiang Lampu Jalan Miring di Desa Lambai',
                'desc'  => 'Tiang PJU di tikungan desa miring akibat tanah tergerus hujan dan kabel mulai kendur.',
                'addr'  => 'Jalan Poros Lambai, Desa Lambai, Kec. Lambai, Kab. Kolaka Utara',
                'lat'   => -3.5742, 'lng' => 120.9188,
            ],
        ];

        foreach ($dummies as $i => $d) {
            $code = str_pad((string) ($nextNum + $i), 4, '0', STR_PAD_LEFT) . '/' . ($d['type'] === 'major' ? 'berat' : 'ringan') . '/2026';
            $c = Complaint::create([
                'public_code'    => $code,
                'user_id'        => $d['user_id'],
                'category_id'    => $d['cat'],
                'region_id'      => $d['reg'],
                'damage_type'    => $d['type'],
                'road_length'    => $d['len'] ?? null,
                'road_condition' => $d['cond'] ?? null,
                'title'          => $d['title'],
                'description'    => $d['desc'],
                'address'        => $d['addr'],
                'latitude'       => $d['lat'],
                'longitude'      => $d['lng'],
                'status'         => 'SUBMITTED',
            ]);

            ComplaintStatusHistory::create([
                'complaint_id' => $c->id,
                'from_status'  => null,
                'to_status'    => 'SUBMITTED',
                'changed_by'   => $d['user_id'],
                'note'         => 'Laporan pengaduan dikirim oleh warga dan menunggu verifikasi Operator.',
            ]);

            if (!empty($photos)) {
                ComplaintPhoto::create([
                    'complaint_id' => $c->id,
                    'storage_key'  => $photos[$i % count($photos)],
                    'type'         => 'evidence_before',
                ]);
            }
        }
    }
}
