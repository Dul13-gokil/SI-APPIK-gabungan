# Rencana Perubahan dari Rapat (SI-APPIK)

Sumber: keputusan rapat yang disampaikan pemilik proyek. Dokumen ini memetakan setiap
keputusan ke perubahan teknis, menandai mana yang bisa langsung dikerjakan dan mana yang
masih menunggu keputusan atau data.

Status kode saat ini: Docker + tunnel https://aduan.aispektra.com jalan, 27 test lulus.

## Status pelaksanaan (diperbarui)

Sudah dikerjakan dan diuji:

1. Kategori menjadi 2 (Jalan & Jembatan, Fasilitas Umum Lainnya); data PJU dipindahkan, APK lama yang masih mengirim `category_id=2` dipetakan ke 3.
2. Radius laporan 20 m tersimpan di `complaints.radius_m`; cek duplikat dan respons API memakai radius itu.
3. Respons laporan memuat `similar_complaints` + pesan "dukung aduan yang sudah ada".
4. AI menentukan berat/ringan dan kategori; keyakinan >= 80% -> status `VERIFIED` (`verified_by_ai`), di bawahnya tetap `SUBMITTED`.
5. Hasil klasifikasi disimpan di `ai_assessments` (dulu tidak pernah disimpan).
6. AI memvalidasi hasil kerja: kerusakan tidak terdeteksi -> `COMPLETED` (`completion_verified_by_ai`), selain itu `WAITING_VERIFICATION`.
7. Operator boleh menolak laporan berstatus `DISPATCHED`.
8. Operator mendapat halaman tambah akun pekerja lapangan (`/operator/petugas/baru`) dengan jejak audit.
9. Peran baru `PENGAWAS` dan `MONITORING`; role `DINAS` (Kepala Dinas) berakses penuh. `/monitoring` hanya baca.
10. SUPERADMIN mendapat halaman log aktivitas (`/admin/activity`) dengan filter aksi, aktor, dan tanggal.

11. Portal operator mendapat tab "Siap Ditugaskan (AI)" untuk laporan berstatus `VERIFIED` (tombol Tugaskan/Tolak). Sebelumnya laporan lolos AI tidak muncul di tab mana pun.
12. Landing page hanya menampilkan 2 kartu kategori; statistik tidak lagi memakai kategori PJU yang sudah dihapus.
13. Aplikasi mobile: daftar kategori 2 (PJU dihapus), label "Kerusakan Titik"/"Ruas Panjang" (bukan Ringan/Berat), dan keterangan bahwa berat/ringan ditentukan AI.
14. Sidebar: menu Log Aktivitas (Super Admin), Tambah Pekerja Lapangan (Operator/Dinas), Dashboard Monitoring, dan portal Petugas kini tampil untuk PENGAWAS.
15. Bug layout: `needsFirstLoginSetup()` memanggil `Hash::check` pada hash non-bcrypt dan membuat seluruh halaman 500; kini dijaga `Hash::isHashed`.

16. PENGAWAS dapat mengelola anggota regunya sendiri di `/petugas/regu` (tambah dari pekerja lapangan tanpa regu, keluarkan anggota, maksimal 15 orang). Regu lain ditolak; petugas biasa mendapat 403.
17. Laporan kinerja pekerja lapangan dicabut: tab di portal petugas, menu sidebar, tab "Laporan" di aplikasi mobile, dan endpoint `/api/v1/field/performance` dihapus. Panel monitoring Admin/Operator/Kepala Daerah tetap ada.
18. Super Admin kini bisa membuat akun `PENGAWAS`, `DINAS`, dan `MONITORING` dari form tambah pengguna (sebelumnya validasinya menolak ketiga role itu). Ketiganya juga mendapat email undangan staf dan wajib ganti sandi saat login pertama.

19. Foto bukti pengerjaan dijaga radius: `PhotoRadiusGuard` menolak foto di luar `radius_m` (toleransi akurasi GPS 25 m) di portal web maupun API. Petugas web mengirim lokasi perangkat lewat geolocation, mobile mengirim koordinat saat kirim bukti (termasuk antrean offline). Lokasi wajib hanya bila `photo_radius_require_location=true`; default longgar agar APK lama yang belum mengirim lokasi tidak berhenti bekerja.
20. Aplikasi mobile menampilkan lingkaran radius 20 m pada peta pemilihan titik laporan.

21. Peta jaringan jalan: `php artisan roads:import` mengunduh jalan dari Overpass OSM ke `public/geojson/roads_kolaka_utara.geojson`, mengklasifikasikan kewenangan lewat `RoadAuthorityClassifier`, dan portal Operator serta Petugas menampilkannya sebagai layer yang bisa dinyalakan/dimatikan (Jalan Nasional, Jalan Provinsi, Jalan Kabupaten, Kewenangan belum ditetapkan).
    Catatan penting: data OSM Kolaka Utara belum memuat tag kewenangan resmi (0 `ref`, 0 `network`; hanya 15 dari 146 ruas yang bernama), sehingga status nasional/provinsi/kabupaten adalah perkiraan dari kelas jalan. Koreksi manual per `osm_id` disediakan di `database/data/road_authority_overrides.json`.

22. Log aktivitas kini mencatat aksi yang sebelumnya bolong: `COMPLAINT_CREATED` (beserta keyakinan AI dan ambang yang dipakai), `AI_CLASSIFICATION_FAILED`, `WORK_STARTED`, `WORK_FINISHED`, dan `COMPLETION_VALIDATED_BY_AI` beserta skor AI.
23. Ambang AI dapat dikalibrasi tanpa deploy lewat `system_settings`: `ai_verify_threshold` (bawaan 0.80) dan `ai_completion_threshold` (bawaan 0.80). Kegagalan AI juga menulis `ai_last_failure_at` agar operator tahu model sedang bermasalah, bukan laporannya yang buruk.

24. Panel `admin/settings` (Super Admin) untuk mengubah ambang verifikasi, ambang validasi hasil kerja, dan penegakan lokasi GPS foto; setiap perubahan dicatat sebagai `SETTINGS_UPDATED`.
25. Portal operator menampilkan peringatan bila klasifikasi AI gagal dalam 24 jam terakhir.
26. Impor jalan OSM dijadwalkan otomatis tiap tanggal 1 pukul 02.00 WITA lewat service `scheduler` di docker-compose; hasilnya ditulis ke volume `geojson-data` yang dipakai bersama `app`.

Belum dikerjakan:

1. Rebuild APK mobile (kode sudah diubah; SDK Flutter tidak ada di mesin ini, jadi belum dikompilasi/diuji).
2. Lingkaran radius pada peta web: pada zoom operasional (13-15) lingkaran 20 m hanya beberapa piksel sehingga belum digambar.
3. Anti-spam laporan anonim (mis. Cloudflare Turnstile) — butuh kredensial Cloudflare.
4. Perbandingan foto sebelum-sesudah yang mengukur luas kerusakan (sekarang masih biner).
5. Sampling review operator untuk laporan yang lolos AI otomatis.
6. Penyeragaman nama aksi audit lama (`VERIFY`, `DISPOSITION`, `MERGE`).

---

## 1. Ringkasan keputusan vs kondisi kode sebelum perubahan (riwayat perencanaan)

| Keputusan rapat | Kondisi sekarang | Perlu |
| :--- | :--- | :--- |
| Klasifikasi berat/ringan otomatis oleh AI | Warga memilih `damage_type` manual; AI hanya mengisi kategori | AI menentukan `damage_type`, warga tetap boleh menulis deskripsi |
| Kategori laporan jadi 2 | Sudah ada 3: Jalan & Jembatan, PJU, Fasilitas Umum | Gabungkan PJU ke "Fasilitas Umum Lainnya" + migrasi data |
| Titik radius 20 m saat input | Belum ada; duplikat dicek 100 m | Kolom `radius_m` default 20, dipakai untuk duplikat + peta |
| Verifikasi operator diganti AI | Operator verifikasi manual (SUBMITTED -> DISPATCHED) | AI menetapkan VERIFIED otomatis, operator hanya menugaskan |
| Validasi hasil kerja diganti AI | Operator memvalidasi WAITING_VERIFICATION | AI membandingkan foto sebelum/sesudah -> COMPLETED |
| Pemetaan jalan provinsi & kabupaten | Hanya batas kabupaten (`public/geojson/kolaka_utara.geojson`) | Butuh data jaringan jalan (lihat pertanyaan 3) |
| Foto pengerjaan memakai radius | Bebas, tanpa cek lokasi | Validasi jarak foto ke titik laporan |
| Notifikasi "aduan sudah ada, tolong dukung" | Ada cek duplikat, belum ada notifikasi/dukungan otomatis | Tampilkan tawaran dukung saat lapor di radius sama |
| Tombol tolak untuk operator pada status ditugaskan | State machine: DISPATCHED hanya ke IN_PROGRESS/WAITING_VERIFICATION | Tambah transisi DISPATCHED -> REJECTED |
| Operator boleh menambah akun pekerja lapangan | Hanya Super Admin yang bisa (`/admin/users`) | Form khusus operator, role dibatasi PETUGAS |
| Peran PENGAWAS | Tidak ada | Peran baru, boleh menambah anggota regu |
| Hapus laporan kinerja | Ada `/petugas/performance`, `/field/performance`, cetak kinerja | Cabut rute, menu, dan endpoint |
| KEPALA DINAS & ADMIN full akses | Role `DINAS` ada di kode tetapi tanpa user | Petakan DINAS = Kepala Dinas, akses penuh |
| SUPERADMIN full akses + log aktivitas | `audit_logs` ada, tetapi belum semua aksi dicatat dan belum ada halaman | Halaman log + lengkapi pencatatan |
| Peran khusus DASHBOARD MONITORING | Tidak ada | Peran baru, hanya baca dashboard |

---

## 2. Fase 1 — bisa dikerjakan tanpa keputusan tambahan

1. Peran baru: `PENGAWAS`, `MONITORING` (migrasi data + middleware role + menu).
   - `PENGAWAS`: melihat tugas regu, menambah/melepas anggota regu, tidak boleh menugaskan.
   - `MONITORING`: hanya halaman baca (landing/dashboard/peta/statistik), tanpa aksi.
2. `DINAS` diperlakukan sebagai Kepala Dinas: akses penuh seperti ADMIN pada portal web.
3. Operator menolak laporan pada status `DISPATCHED` (tambah transisi + tombol).
4. Operator menambah akun `PETUGAS` (form terbatas: nama, email, telepon; sandi awal `password`).
5. Kategori 2: gabungkan PJU ke "Fasilitas Umum Lainnya".
   - Migrasi: `complaints.category_id = 2 -> 3`, lalu nonaktifkan kategori 2 (jangan dihapus agar histori aman).
   - Dampak: statistik landing (jalan/pju/fasilitas), peta, form mobile, skrip AI (kategori 1/2/3).
6. Radius 20 m pada laporan: kolom `complaints.radius_m` default 20, dipakai cek duplikat
   dan gambar lingkaran di peta.
7. Notifikasi "aduan serupa sudah ada": saat lapor di dalam radius laporan aktif,
   respons API mengembalikan daftar kandidat + tombol dukung.
8. Hapus laporan kinerja: cabut rute, endpoint `/field/performance`, menu, dan cetak kinerja.
9. Log aktivitas untuk SUPERADMIN: halaman daftar `audit_logs` (filter aktor/aksi/tanggal),
   plus lengkapi pencatatan pada aksi yang belum menulis audit (start/finish/validate, user, regu, wilayah).

---

## 3. Fase 2 — menunggu keputusan (lihat pertanyaan di sesi)

1. AI menggantikan verifikasi operator.
   Rancangan: hasil AI disimpan di `ai_assessments` (sekarang tidak disimpan), status otomatis
   `VERIFIED` bila keyakinan >= ambang; di bawah ambang tetap `SUBMITTED` agar operator menangani.
   Perlu: ambang keyakinan dan perilaku saat AI gagal (mis. Python mati).
2. AI menggantikan validasi hasil kerja.
   Rancangan: deteksi kerusakan pada foto sesudah; bila tidak terdeteksi kerusakan berat ->
   `COMPLETED`, selain itu `REVISION_REQUIRED`. Perlu: ambang dan siapa yang menangani keraguan.
3. Pemetaan jalan provinsi dan kabupaten.
   Repo hanya punya batas kabupaten. Butuh GeoJSON/Shapefile jaringan jalan + atribut status
   (provinsi/kabupaten). Tanpa itu, fitur hanya bisa menampilkan peta dasar.
4. Foto pengerjaan berbasis radius: perlu jarak maksimum (20 m sama dengan radius laporan?)
   dan apa yang terjadi bila petugas memotret di luar radius (tolak atau tandai).

---

## 4. Perubahan skema database (ringkas)

```
users.role                      : + PENGAWAS, MONITORING (string, tidak perlu alter tipe)
complaints.radius_m             : integer default 20
complaints.verified_by_ai       : boolean default false
complaints.ai_assessment_id     : nullable FK ke ai_assessments
ai_assessments                  : dipakai kembali (kategori, keyakinan, berat/ringan, foto acuan)
categories                      : kategori 2 dinonaktifkan, label 3 -> "Fasilitas Umum Lainnya"
team_members.role               : + PENGAWAS
```

Migrasi kategori bersifat mengubah data 12 aduan yang ada; wajib backup sebelum dijalankan.

---

## 5. Berkas utama yang akan disentuh

| Area | Berkas |
| :--- | :--- |
| Peran & akses | `app/Http/Middleware/EnsureUserHasRole.php`, `routes/web.php`, `routes/api.php`, `resources/views/layouts/app.blade.php` |
| State machine | `app/Domain/Complaints/Services/ComplaintStateMachine.php` (DISPATCHED -> REJECTED) |
| Laporan warga | `app/Http/Controllers/Api/v1/ComplaintController.php` (radius, duplikat, AI berat/ringan) |
| AI | `app/Domain/AI/Services/AICVService.php`, `Scripts/ai_analyzer.py`, `app/Models/AiAssessment.php` |
| Operator | `app/Http/Controllers/Web/WebOperatorController.php`, `resources/views/operator/dashboard.blade.php` |
| Pengawas | controller + view baru, `app/Http/Controllers/Web/WebTeamController.php` |
| Log aktivitas | controller + view baru, `app/Models/AuditLog.php` |
| Kinerja (dihapus) | `WebReportController`, rute `petugas/performance/*`, `field/performance` |
| Mobile | `features/complaints/presentation/create_complaint_screen.dart`, `features/field/*` |

---

## 6. Risiko

- Migrasi kategori mengubah data aduan yang sudah ada; salah map membuat statistik dan AI meleset.
- AI menggantikan verifikasi berarti laporan bisa masuk antrean penugasan tanpa manusia.
  Tanpa ambang dan fallback yang jelas, laporan fiktif bisa lolos.
- Menghapus laporan kinerja juga menghapus bukti evaluasi petugas; pastikan bukan kebutuhan audit.
- Data jaringan jalan tidak ada di repo; fitur pemetaan tidak bisa "ditebak".

---

## 7. Keputusan lanjutan: verifikasi & validasi sepenuhnya oleh AI

Rapat menetapkan verifikasi dan validasi manual operator dihapus, bukan sekadar
dijadikan cadangan. Alasannya: foto laporan wajib diambil dari kamera (album ditutup)
sehingga foto lama atau tangkapan layar tidak bisa dipakai, dan setiap foto dicap
watermark berisi tanggal, jam, serta koordinat.

Alur yang berlaku sekarang:

| Tahap | Yang menentukan | Hasil |
| :--- | :--- | :--- |
| Laporan masuk | AI menilai foto | kerusakan infrastruktur -> `VERIFIED`; bukan kerusakan -> `REJECTED` otomatis |
| AI tidak dapat dijalankan | — | laporan tetap `VERIFIED` dengan penanda `verified_by_ai = false`, kegagalan dicatat di audit dan `ai_last_failure_at` |
| Pekerjaan selesai | AI + radius foto | lolos -> `COMPLETED`; gagal -> `REVISION_REQUIRED` otomatis |

Konsekuensi yang disengaja:
- Tab "Verifikasi Aduan" dan "Validasi Hasil" dihapus dari sidebar operator; tab
  validasi di portal pekerja lapangan juga dihapus.
- Status `WAITING_VERIFICATION` tidak lagi berhenti menunggu manusia; hanya menjadi
  titik singgah singkat sebelum AI memutuskan.
- `ai_verify_threshold` kini hanya menandai keyakinan AI (penanda "AI ragu"),
  bukan penentu status.

### Akses peran

- **Kepala Dinas (role `DINAS`)**: akses penuh setara admin, termasuk menu admin,
  log aktivitas, dan pengaturan sistem.
- **Super Admin**: menu "Log Aktivitas" dan "Pengaturan Sistem" dipindah ke dalam
  dropdown Super Admin, bukan lagi menu terpisah di sidebar.
- **Monitoring**: layar pantau dibuka di `/monitoring` **tanpa login dan tanpa
  sidebar**, dirancang untuk TV ruang kendali. Isinya agregat: aduan masuk, sedang
  dikerjakan, selesai, tingkat tuntas, sebaran titik di peta, status penanganan,
  tren tujuh hari, kecamatan terbanyak, dan aduan terbaru. Tidak ada nama, nomor HP,
  atau surel pelapor. Data diperbarui otomatis tiap 30 detik.

Catatan risiko: karena `/monitoring` tanpa login, siapa pun yang tahu alamatnya dapat
melihat agregat tersebut. Isinya sengaja dibatasi pada data non-pribadi. Bila kelak
perlu ditutup, tambahkan token akses di route group `monitoring`.
