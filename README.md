# SI-APPIK — Sistem Pengaduan Infrastruktur Kabupaten Kolaka Utara

Warga melaporkan kerusakan jalan, jembatan, dan fasilitas umum lewat aplikasi mobile.
AI memeriksa foto laporan, Operator PUPR menugaskan regu, pengawas lapangan mengerjakan,
dan hasil kerja diperiksa otomatis oleh AI. Kepala Daerah dan masyarakat memantau lewat
peta serta layar pantau.

| Bagian | Teknologi |
| --- | --- |
| Backend & panel web | Laravel 13 (PHP 8.3), Blade + Tailwind, PostgreSQL |
| Aplikasi warga & petugas | Flutter (BLoC), OpenStreetMap |
| AI | YOLOv8 (ONNX) untuk deteksi kerusakan, Gemini untuk chatbot |
| Penjalanan | Docker Compose (`app`, `db`, `scheduler`, `tunnel`) |

## Mulai cepat

```bash
cp .env.example .env          # isi APP_KEY, DB_PASSWORD
docker compose up -d
docker compose exec app php artisan migrate --seed
```

Panel web: `http://localhost:8080`. Akun pertama dibuat manual lewat
`php artisan user:password email@anda --password=SandiAnda` (lihat bagian Pemulihan).

Panduan lengkap: [docs/DOCKER-DEPLOY.md](docs/DOCKER-DEPLOY.md).

## Peta folder

```
.
├── backend/            Laravel: API v1, panel web per peran, layanan domain
│   ├── app/
│   │   ├── Domain/     Logika inti, dipisah per masalah (bukan per tipe file)
│   │   │   ├── Complaints/   aduan, state machine, workflow, otorisasi tugas
│   │   │   ├── AI/           skrip + model YOLOv8, klasifikasi, chatbot
│   │   │   ├── Chatbot/
│   │   │   └── ...
│   │   ├── Http/       Controller Api/v1 untuk mobile, Web/ untuk Blade
│   │   ├── Models/     Eloquent
│   │   ├── Services/   layanan lintas domain (watermark, OTP, email keamanan)
│   │   └── Console/    perintah artisan (backup, restore, impor jalan, reset sandi)
│   ├── database/       migrasi + seeder data acuan
│   ├── resources/      Blade + CSS
│   └── routes/         web.php (portal), api.php (mobile), console.php
├── mobile/             Flutter: core/, features/<nama>/{data,domain,presentation}, shared/
├── docs/               semua dokumentasi (indeks: docs/README.md)
├── scripts/            skrip Windows: jalankan Laravel + tunnel, sambungkan HP via ADB
├── .agents/            aturan wajib proyek + skill untuk agen AI
├── AGENTS.md           ringkasan aturan (dibaca agen & wajib dipatuhi)
└── docker-compose.yml
```

## Aturan yang paling sering dilanggar

Baca [AGENTS.md](AGENTS.md) untuk lengkapnya. Ringkasnya:

1. Semua mutasi status aduan lewat `App\Domain\Complaints\Services\WorkflowService` —
   jangan mengubah kolom `status` langsung.
2. Endpoint mobile di `App\Http\Controllers\Api\v1`, portal Blade di `...\Web`.
3. Regu hanya boleh mengerjakan tugas miliknya; batas 3 tugas aktif per regu.
4. Tidak ada validasi/verifikasi manual: foto laporan dan foto hasil kerja dinilai AI.
5. Warna dan tipografi mengikuti Aturan 5 (Outfit untuk judul, Inter untuk isi).

## Di mana menambah fitur

| Kebutuhan | Lokasi |
| --- | --- |
| Endpoint baru untuk mobile | `backend/routes/api.php` + `app/Http/Controllers/Api/v1` |
| Halaman/panel baru | `backend/routes/web.php` + `app/Http/Controllers/Web` + `resources/views` |
| Aturan bisnis baru | `backend/app/Domain/<masalah>/Services` |
| Tabel/kolom baru | `backend/database/migrations` |
| Layar mobile baru | `mobile/lib/features/<nama>/{data,domain,presentation}` |
| Perintah operasional | `backend/app/Console/Commands` |

## Operasional

```bash
docker compose ps                      # status layanan
docker compose logs -f app             # log aplikasi
docker compose exec app php artisan db:backup     # cadangan DB + foto
docker compose exec app php artisan db:restore    # pulihkan
```

## Pemulihan akun tanpa email

Email OTP hanya berfungsi bila `MAIL_MAILER=smtp` diisi. Kalau tidak, reset sandi dari server:

```bash
docker compose exec app php artisan user:password superadmin@gmail.com --password=SandiBaru2026
```

Perintah ini mengaktifkan kembali akun, mencabut sesi lama, dan tidak memerlukan email.

## Dokumentasi

- [docs/README.md](docs/README.md) — indeks seluruh dokumen
- [docs/MATRIKS-PERAN.md](docs/MATRIKS-PERAN.md) — hak akses tiap peran
- [docs/ALUR-WORKFLOW.md](docs/ALUR-WORKFLOW.md) — alur yang benar-benar berjalan di kode
- [docs/DOCKER-DEPLOY.md](docs/DOCKER-DEPLOY.md) — deploy lokal dan VPS
- [docs/FLUTTER-SETUP.md](docs/FLUTTER-SETUP.md) — menyiapkan toolchain mobile
