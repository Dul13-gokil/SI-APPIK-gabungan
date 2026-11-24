# Deploy SI-APPIK dengan Docker

Panduan ini memakai susunan yang sudah diuji di mesin ini:
aplikasi Laravel + Apache + Python (AI) dalam satu image, PostgreSQL 17 sebagai layanan terpisah,
data dan foto disimpan di volume Docker.

```
┌─────────────────────────────┐      ┌──────────────────────────┐
│ app  (php:8.3-apache)       │      │ db (postgres:17-alpine)  │
│ - Laravel + Vite build      │─────▶│ - database "sik"         │
│ - python3 + opencv + numpy  │      │ - volume db-data         │
│ - volume app-storage (foto) │      │ - port host 5433         │
│ - port host 8080 -> 80      │      └──────────────────────────┘
└─────────────────────────────┘
```

---

## 1. Prasyarat

- Docker Engine 24+ dan Docker Compose v2 (`docker compose version`)
- RAM minimal 4 GB untuk build (opencv + composer)
- Port host 8080 dan 5433 kosong
- File yang dipakai: `docker-compose.yml`, `backend/Dockerfile`, `backend/docker-entrypoint.sh`,
  `backend/requirements.txt`, `.env` (root, tidak di-commit), `si-appik.sql` (opsional, data demo)

---

## 2. Deploy lokal (langkah lengkap)

### 2.1 Siapkan `.env` root

```bash
cd "D:/Servers AIS/SI-APPIK"
php -r "echo 'base64:'.base64_encode(random_bytes(32));"
```

Buat file `.env` di root:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=http://localhost:8080
APP_PORT=8080
APP_KEY=base64:HASIL_GENERATE_DI_ATAS

DB_DATABASE=sik
DB_USERNAME=postgres
DB_PASSWORD=ganti-dengan-sandi-kuat

MAIL_MAILER=log
```

`.env` sudah masuk `.gitignore`, jadi tidak akan ter-commit.

### 2.2 Build image

```bash
docker compose build
```

Yang terjadi: npm build aset Vite, install 81 paket Composer tanpa dev, install
opencv-python-headless + numpy, set batas upload PHP (12 MB file / 64 MB post).

### 2.3 Jalankan

```bash
docker compose up -d
docker compose ps
```

Tunggu sampai `db` dan `app` berstatus **healthy** (sekitar 30-60 detik).

### 2.4 Verifikasi

```bash
curl http://localhost:8080/up
curl -o /dev/null -w "%{http_code}\n" http://localhost:8080/
curl -o /dev/null -w "%{http_code}\n" http://localhost:8080/login
```

Semua harus 200. Login staf memakai akun hasil dump: `admin@kabupaten.go.id` / `password`.

### 2.5 Isi database

Pada volume database yang masih kosong, entrypoint otomatis:
1. menunggu PostgreSQL siap,
2. mengimpor `si-appik.sql` (di-mount dari root repo),
3. menjalankan `php artisan migrate --force`,
4. membuat symlink storage dan cache produksi.

Pada start berikutnya, penanda `app_db_sync_marker` mencegah impor ulang sehingga data Anda aman.

Ingin database kosong tanpa data demo? Hapus baris mount `./si-appik.sql` dari `docker-compose.yml`
sebelum `up`, lalu isi lewat aplikasi atau `php artisan db:seed`:

```bash
docker compose exec app php artisan db:seed --force
```

---

## 3. Deploy ke VPS (domain + HTTPS)

Contoh VPS Ubuntu 24.04, domain `aduan.kolakautara.go.id`.

### 3.1 Pasang Docker

```bash
curl -fsSL https://get.docker.com | sh
sudo usermod -aG docker $USER   # logout/login sekali
docker compose version
```

### 3.2 Ambil kode dan konfigurasi

```bash
git clone https://github.com/Dul13-gokil/SI-APPIK.git
cd SI-APPIK
cp docker-compose.yml docker-compose.yml.bak   # opsional
nano .env
```

Isi `.env` produksi:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://aduan.kolakautara.go.id
APP_PORT=8080
APP_KEY=base64:...            # generate baru, jangan pakai kunci lokal
DB_DATABASE=sik
DB_USERNAME=postgres
DB_PASSWORD=...               # kuat, simpan di password manager
MAIL_MAILER=smtp              # isi MAIL_HOST/USERNAME/PASSWORD bila email aktif
```

### 3.3 Jalankan

```bash
docker compose build
docker compose up -d
docker compose ps
```

### 3.4 HTTPS dengan Caddy (paling singkat, sertifikat otomatis)

`/etc/caddy/Caddyfile`:

```
aduan.kolakautara.go.id {
    reverse_proxy 127.0.0.1:8080
}
```

```bash
sudo apt install -y caddy
sudo systemctl reload caddy
```

Caddy mengurus sertifikat Let's Encrypt selama DNS A record domain sudah menunjuk ke IP VPS
dan port 80/443 terbuka.

Alternatif Nginx:

```nginx
server {
    listen 80;
    server_name aduan.kolakautara.go.id;
    client_max_body_size 64m;

    location / {
        proxy_pass http://127.0.0.1:8080;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

Catatan: `client_max_body_size` wajib dinaikkan, kalau tidak unggahan foto 10 MB ditolak proxy.

### 3.5 Firewall

```bash
sudo ufw allow 22,80,443/tcp
sudo ufw enable
```

Port 8080 dan 5433 cukup diakses lokal VPS; jangan dibuka ke publik.

---

## 4. Deploy ke Railway

1. Buat project baru, hubungkan repo `Dul13-gokil/SI-APPIK`.
2. Set **Root Directory** ke `backend`. Railway akan memakai `backend/Dockerfile`.
3. Tambahkan plugin **PostgreSQL**; Railway memberi `DATABASE_URL`.
4. Isi Variables: `APP_ENV=production`, `APP_DEBUG=false`, `APP_KEY=...`,
   `APP_URL=https://<domain-railway>`, `MAIL_MAILER=log`, `AI_PYTHON_BINARY=/usr/bin/python3`.
5. Volume: tambahkan volume di `/var/www/html/storage/app` agar foto tidak hilang saat redeploy.
6. Ingin data demo ikut ter-deploy? Salin dump ke dalam konteks build sebelum push:
   `cp si-appik.sql backend/database/si-appik.sql`. Entrypoint akan mengimpornya sekali.
7. Deploy. Port mengikuti variabel `PORT` yang diberikan Railway (entrypoint sudah menyesuaikan Apache).

---

## 5. Operasional

| Kebutuhan | Perintah |
| :--- | :--- |
| Lihat log | `docker compose logs -f app` |
| Masuk container | `docker compose exec app bash` |
| Artisan | `docker compose exec app php artisan about` |
| Restart | `docker compose restart app` |
| Hentikan | `docker compose down` |
| Hentikan + hapus data | `docker compose down -v` (DB dan foto hilang) |
| Update kode | `git pull && docker compose build app && docker compose up -d` |

### Penjadwal tugas (scheduler)

Service `scheduler` menjalankan `php artisan schedule:work`, sehingga tugas terjadwal
berjalan tanpa cron host:

- Impor jaringan jalan OSM (`roads:import`) setiap tanggal 1 pukul 02.00 WITA.
- Hasil impor ditulis ke volume `geojson-data` yang dipakai bersama oleh `app`,
  jadi peta langsung memakai data terbaru tanpa rebuild image.

Cek status dan jalankan manual:

```bash
docker compose ps scheduler
docker compose exec scheduler php artisan schedule:list
docker compose exec app php artisan roads:import   # impor manual kapan saja
```

Bila memakai VPS tanpa service scheduler (mis. hanya cron host), tambahkan satu baris cron:

```
* * * * * cd /path/ke/proyek && docker compose exec -T app php artisan schedule:run >> /dev/null 2>&1
```

### Backup database

```bash
docker compose exec -T db pg_dump -U postgres -d sik -Fc > backup-$(date +%Y%m%d-%H%M).dump
```

### Restore

```bash
cat backup-20261121-1200.dump | docker compose exec -T db pg_restore -U postgres -d sik --clean --if-exists
```

### Backup foto

```bash
docker run --rm -v si-appik_app-storage:/data -v "$PWD":/backup alpine \
  tar czf /backup/storage-$(date +%Y%m%d).tar.gz -C /data .
```

---

## 6. Menghubungkan aplikasi mobile

Aplikasi tidak perlu di-build ulang. Di aplikasi: **Profil -> Alamat Server API**,
isi alamat server (aplikasi menambahkan `/api/v1` sendiri). Alamat bawaan saat ini
`https://aduan.aispektra.com`; untuk build baru ubah `mobile/lib/core/config/app_config.dart`.

---

## 7. Log dan penyimpanan bukti foto

- Log ditulis ke `storage/logs/laravel.log` **dan** ke stderr container
  (`LOG_STACK=single,stderr`), sehingga error terlihat lewat `docker compose logs -f app`
  tanpa perlu masuk ke dalam container.
- Entrypoint mengembalikan kepemilikan `storage/logs` ke `www-data` setiap start.
  Tanpa itu, `php artisan migrate` yang berjalan sebagai root meninggalkan
  `laravel.log` milik root dan Apache gagal menulis log (error produksi tidak tercatat).
- Bukti foto hanya disimpan sebagai berkas di disk (`storage/app/public/complaints`).
  Kolom `complaint_photos.file_data` tidak lagi diisi karena duplikat base64 membuat
  tabel dan backup membengkak. Kolomnya tetap ada agar foto lama masih bisa disajikan
  bila berkasnya hilang. Dijaga oleh `tests/Feature/PhotoStorageTest.php`.

---

## 8. Troubleshooting

| Gejala | Penyebab dan solusi |
| :--- | :--- |
| `APP_KEY wajib diisi` saat `up` | File `.env` root belum ada atau `APP_KEY` kosong |
| Port 5432 bentrok | Compose sengaja memakai 5433 untuk host; ubah di `docker-compose.yml` bila perlu |
| `app` unhealthy | `docker compose logs app`; biasanya DB belum siap atau import dump gagal |
| Halaman 500 "Vite manifest not found" | Build aset gagal; jalankan `docker compose build --no-cache app` |
| Foto gagal diunggah | Pastikan reverse proxy mengizinkan 64 MB; image sudah set 12 MB per file |
| AI selalu "belum terverifikasi" | `AI_PYTHON_BINARY` harus `/usr/bin/python3` di container; cek `docker compose exec app python3 -c "import cv2"` |
| Data hilang setelah redeploy | Volume `app-storage` dan `db-data` belum dipasang |
| Email tidak terkirim | `MAIL_MAILER=log` hanya menulis ke log; isi SMTP untuk produksi |

---

## 9. Hasil uji di mesin ini

```
docker compose ps          app healthy (8080), db healthy (5433)
GET /                      200
GET /login                 200
GET /up                    200
GET /api/v1/complaints     200
POST /api/v1/auth/login    200 (admin@kabupaten.go.id / password)
POST /api/v1/ai/classify-image 200 "Kerusakan Trotoar" conf 0.94
Database                  19 user, 12 aduan, 15 region (impor dump otomatis)
PHP                        upload_max_filesize 12M, post_max_size 64M, max_file_uploads 15
```
