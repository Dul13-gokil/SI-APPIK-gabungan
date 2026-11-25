# Matriks Peran & Hak Akses

Berlaku untuk portal web (Blade) dan API v1. Sumber kebenaran pembatasan adalah
`routes/web.php`, `routes/api.php`, `App\Http\Middleware\EnsureUserHasRole`, dan
`App\Models\User`.

## Ringkasan peran

| Peran (kolom `users.role`) | Sebutan di antarmuka | Portal utama |
| --- | --- | --- |
| `SUPERADMIN` / `ADMIN` | Super Administrator | `/admin` |
| `KEPALA_DAERAH` | Kepala Daerah | `/admin` |
| `DINAS` | Kepala Dinas | `/admin` |
| `OPERATOR` | Operator PUPR | `/operator` |
| `PENGAWAS` | Pengawas Lapangan (Ketua Regu) | `/petugas` |
| `PETUGAS` | Pengawas Lapangan (Anggota Regu) | `/petugas` |
| `MONITORING` | Monitoring | `/monitoring` |
| `WARGA` | Warga | aplikasi mobile |

## Batas akses

| Kemampuan | SUPERADMIN/ADMIN | KEPALA_DAERAH | DINAS | OPERATOR | PENGAWAS | PETUGAS | MONITORING |
| --- | --- | --- | --- | --- | --- | --- | --- |
| Dashboard admin, kelola akun, kategori, wilayah | ya | ya | ya | – | – | – | – |
| Log Aktivitas & Pengaturan Sistem | ya | tidak | tidak | – | – | – | – |
| Verifikasi, penugasan, penolakan aduan | ya | ya | ya | ya | – | – | – |
| Regu & Anggota (buat regu, isi identitas personil) | ya | ya | ya | ya | regu sendiri | – | – |
| Pengerjaan tugas lapangan | – | – | – | – | regu sendiri | regu sendiri | – |
| Layar pantau `/monitoring` | ya | ya | ya | – | – | – | ya |
| Buat akun peran apa pun | ya | ya | ya | – | – | – | – |

Catatan:

1. **Super Admin adalah peran tertinggi.** Hanya Super Admin (dan `ADMIN`) yang dapat
   membuka Log Aktivitas dan Pengaturan Sistem. Middleware `EnsureUserHasRole` tidak lagi
   memberi jalan pintas kepada peran lain; setiap rute harus menyebut peran yang diizinkan.
2. **Kepala Daerah** memakai seluruh fitur administrasi seperti Super Admin, kecuali dua
   menu di atas.
3. **Kepala Dinas** setara Kepala Daerah untuk urusan teknis; pembatasan Log Aktivitas dan
   Pengaturan Sistem juga berlaku.
4. **Akun anggota regu (`PETUGAS`) tidak dibuat dari menu Kelola Akun.** Identitas personil
   (nama, email, nomor HP) diisi pada menu **Regu & Anggota** oleh Operator PUPR atau oleh
   Ketua Regu untuk regunya sendiri. Sistem membuat sandi sementara acak yang ditampilkan
   sekali dan wajib diganti saat login pertama.
5. **Monitoring wajib login** dengan email dan sandi; setelah masuk, layar pantau tampil
   tanpa sidebar agar cocok untuk layar besar di ruang kendali.
6. **Batas personil 15 orang per regu** dan **maksimal 3 tugas aktif per regu** dijaga di
   `WorkflowService` serta `FieldTaskAuthorizer`, berlaku untuk web maupun API.

## Urutan menu sidebar

1. Super Admin
2. Kepala Daerah
3. Operator PUPR
4. Pengawas Lapangan

Urutan ini dikunci oleh `tests/Feature/RoleAccessTest.php`
(`test_urutan_accordion_sidebar_mengikuti_hierarki_peran`).

## Daftar Aduan

Tab **Daftar Aduan** memuat seluruh status, termasuk yang sudah selesai dan yang ditolak,
sehingga tab *Arsip Aduan* dan *Aduan Ditolak* dihapus dari sidebar admin.
