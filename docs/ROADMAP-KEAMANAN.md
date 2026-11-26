# Laporan Audit Keamanan Sistem & Panduan Implementasi (Security Audit & Roadmap)
## Sistem Informasi Pengaduan & Penanganan Kerusakan Infrastruktur Kabupaten Kolaka Utara (SI-APPIK)
*(Backend Laravel 11/12 + Frontend Mobile Flutter + Computer Vision YOLOv8)*

Dokumen ini berisi hasil analisis mendalam terkait postur keamanan siber website dan aplikasi mobile, evaluasi terhadap potensi celah ilegal, ketahanan terhadap injeksi dan DDoS, serta panduan implementasi teknis untuk pengembangan ke depan.

---

## 📊 1. RINGKASAN EVALUASI POSTUR KEAMANAN

| Area Keamanan | Status Saat Ini | Tingkat Risiko | Evaluasi Singkat |
| :--- | :---: | :---: | :--- |
| **Proteksi Injeksi (SQLi & XSS)** | **Sangat Baik** | 🟢 Rendah | Menggunakan PDO parameterized query & Blade escaping bawaan. |
| **Otentikasi & OTP (Anti-Brute Force)** | **Sangat Baik** | 🟢 Rendah | Rate limiter 5 req/min, kuota 3x salah langsung dibatalkan otomatis. |
| **Otorisasi Objek (IDOR / BOLA)** | **Sangat Baik** | 🟢 Rendah | *Team Authorization Shield* aktif (HTTP 403 Forbidden). |
| **Penyimpanan Token Mobile** | **Sangat Baik** | 🟢 Rendah | Menggunakan `FlutterSecureStorage` (Android Keystore / iOS Keychain). |
| **Perlindungan Privasi (UU PDP)** | **Baik** | 🟢 Rendah | Nomor kontak disamarkan di tampilan publik web & mobile. |
| **Ketahanan DDoS & Resource Exhaustion** | **Cukup** | 🟡 Sedang | Inferensi AI YOLO masih berjalan secara *synchronous* di worker web. |
| **Anti-Spam Laporan Publik** | **Perlu Peningkatan** | 🟡 Sedang | Pengaduan publik/anonim belum dilengkapi captcha/anti-bot. |
| **MitM Protection (SSL Pinning)** | **Perlu Peningkatan** | 🟡 Sedang | Mobile belum menerapkan *SSL Certificate Pinning*. |

---

## 🔍 2. ANALISIS POTENSI CELAH ILEGAL & PENYALAHGUNAAN

### A. Potensi Pembuatan Laporan Fiktif / Spam Massal (Laporan Anonim)
* **Kondisi Saat Ini:** Endpoint `POST /api/v1/complaints` terbuka untuk publik/tamu agar memudahkan warga yang belum mendaftar.
* **Risiko Potensial:** Penyerang dapat membuat skrip otomatis untuk membombardir laporan palsu dengan titik koordinat acak di wilayah Kolaka Utara guna membanjiri antrean verifikasi operator.
* **Mitigasi Saat Ini:** Validasi Geofencing 15 kecamatan dan filter AI YOLOv8. Namun, jika bot mengunggah foto jalan rusak dari internet secara berulang, laporan bisa lolos.
* **Rekomendasi Implementasi:** Pasang **Cloudflare Turnstile** (tanpa puzzle rumit) atau Invisible reCAPTCHA v3 pada form publik.

### B. Isolasi Hak Regu (*Team Shield*) & Manipulasi Tugas SPK
* **Kondisi Saat Ini:** Evaluasi apakah petugas dari Regu B dapat mengklaim atau menyelesaikan tugas milik Regu A secara ilegal.
* **Hasil Uji Kode (`WorkflowController.php`):**
  * `startWork()` dan `finishWork()` memvalidasi relasi `team_id` petugas yang sedang login.
  * Jika ID regu tidak cocok, sistem mengembalikan **HTTP 403 Forbidden**.
  * Maksimal kuota 3 tugas aktif per regu membatasi monopoli tugas.
* **Status:** **AMAN & TERISOLASI SECARA KETAT.**

### C. Keamanan Kode OTP & Pembajakan Akun (*Account Takeover*)
* **Kondisi Saat Ini:** Evaluasi apakah kode OTP 6-digit dapat ditebak melalui serangan *brute force*.
* **Hasil Uji Kode (`OtpService.php`):**
  1. Dibuat menggunakan fungsi kriptografis acak `random_int(100000, 999999)`.
  2. Masa kedaluwarsa 10 menit.
  3. **Penghancuran Otomatis:** Jika salah dimasukkan sebanyak 3 kali berturut-turut, kode langsung hangus permanen.
  4. Respon JSON publik mengunci `'dev_otp' => null`.
  5. Rute OTP dilindungi `throttle:5,1` (maksimal 5 permintaan per menit per IP).
* **Status:** **SANGAT AMAN DARI BRUTE FORCE.**

---

## 💉 3. ANALISIS INJEKSI (SQLi, XSS, & COMMAND INJECTION)

### A. SQL Injection (SQLi)
* **Status:** **KEBAL / SANGAT AMAN.**
* **Analisis:** Seluruh controller memakai Eloquent ORM berbasis PDO Parameterized Query.
* Pada query spasial GIS kompleks ([DuplicateDetectionService.php](file:///d:/Laragon/laragon/www/aduan/backend/app/Domain/Complaints/Services/DuplicateDetectionService.php)), klausa `whereRaw` dan `havingRaw` menggunakan binding `?`:
  ```php
  ->whereRaw("ST_DWithin(geom::geography, ST_GeomFromText(?, 4326)::geography, ?)", [$pointWkt, $radiusMeters])
  ```
  Tidak ada perangkaian string langsung (*string concatenation*) ke dalam query SQL.

### B. Cross-Site Scripting (XSS)
* **Status:** **AMAN DENGAN 1 CATATAN MINOR.**
* **Analisis:** Blade engine otomatis meng-escape data dengan `{{ $var }}`.
* **Catatan Perbaikan:** Pada [executive_monitoring_reporting_js.blade.php](file:///d:/Laragon/laragon/www/aduan/backend/resources/views/partials/executive_monitoring_reporting_js.blade.php):
  ```javascript
  const catLabels = {!! json_encode($categories->pluck('name')) !!};
  ```
  Disarankan diganti menggunakan direktif `@json($categories->pluck('name'))` yang otomatis mengaktifkan sanitasi tag `JSON_HEX_TAG`.

### C. Command Injection (Remote Code Execution)
* **Status:** **TERLINDUNGI SECARA KODE.**
* **Analisis:** Pada [AICVService.php](file:///d:/Laragon/laragon/www/aduan/backend/app/Domain/AI/Services/AICVService.php), eksekusi skrip Python via `shell_exec()` telah membungkus seluruh argumen dengan `escapeshellarg()`. Tidak ada input mentah pengguna yang disisipkan ke shell.

---

## ⚡ 4. ANALISIS KETAHANAN TERHADAP SERANGAN DDoS

### Titik Rawan: Inferensi AI Berjalan Secara Synchronous
1. **Mekanisme Saat Ini:** Pemanggilan endpoint `/api/v1/ai/classify-image` langsung menjalankan komputasi Python OpenCV/YOLO dan menunggu hingga selesai sebelum merespons HTTP.
2. **Potensi Risiko:** 
   - Komputasi Computer Vision menyedot daya CPU/GPU tinggi.
   - Jika terjadi lonjakan ratusan pengunggahan foto resolusi besar bersamaan, worker PHP-FPM / Apache akan terkunci penuh (*resource exhaustion*).
   - Pengunjung web portal biasa dapat mengalami *Gateway Timeout (HTTP 504)*.
3. **Solusi:** Pindahkan inferensi AI ke sistem antrean latar belakang (*Asynchronous Queue*).

---

## 📱 5. ANALISIS KEAMANAN APLIKASI MOBILE FLUTTER

1. **Penyimpanan Kredensial:** Menggunakan `FlutterSecureStorage` dengan `encryptedSharedPreferences: true` di Android dan `KeychainAccessibility.first_unlock` di iOS. Token aman dari pembacaan berkas XML biasa pada perangkat yang di-root.
2. **Intersepsi Lalu Lintas Jaringan (MitM):** Komunikasi menggunakan HTTPS standar via Dio. Belum ada *SSL Certificate Pinning*. Jika penyerang memasang root certificate proxy (seperti Burp Suite) di perangkat pengguna, data API dapat diintip.
3. **Integritas Lokasi GPS:** Diperlukan pengecekan mock GPS agar petugas lapangan tidak dapat memalsukan koordinat kehadiran perbaikan (*Fake GPS*).

---

## 🛠️ 6. PANDUAN IMPLEMENTASI PERBAIKAN BERTAHAP (ROADMAP)

Berikut panduan teknis yang siap Anda implementasikan:

### 🚀 TAHAP 1: IMPLEMENTASI JANGKA PENDEK (QUICK WINS)

#### 1. Hardening Folder Upload Web Server (Blokir Eksekusi PHP di Storage)
Buat file `public/storage/.htaccess` dengan isi:
```apache
# Blokir eksekusi skrip apapun di dalam folder file publik
<FilesMatch "(?i)\.(php|phtml|phar|pl|py|cgi|sh|bash)$">
    Order allow,deny
    Deny from all
</FilesMatch>

Options -Indexes -ExecCGI
```

#### 2. Perbaiki Output JSON di View Blade
Ubah format:
```php
// SEBELUM:
const catLabels = {!! json_encode($categories->pluck('name')) !!};

// SESUDAH:
const catLabels = @json($categories->pluck('name'));
```

#### 3. Pasang Cloudflare Turnstile pada Form Laporan Publik
- Daftar di Cloudflare (gratis, menu *Turnstile*).
- Tambahkan script Turnstile pada halaman pelaporan publik:
```html
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
<div class="cf-turnstile" data-sitekey="YOUR_SITE_KEY"></div>
```
- Validasi secret token di `ComplaintController::store()` sebelum menyimpan laporan tamu.

---

### 🛡️ TAHAP 2: IMPLEMENTASI JANGKA MENENGAH (ARSITEKTUR & KETAHANAN BEBAN)

#### 1. Migrasi AI Computer Vision ke Asynchronous Queue
Ubah alur pemrosesan foto kerusakan:
1. Endpoint `POST /api/v1/complaints` langsung menyimpan data aduan dengan status `SUBMITTED` dan foto fisik.
2. Dispatch Laravel Job ke antrean:
   ```php
   ProcessAiVerificationJob::dispatch($complaint->id);
   ```
3. Worker terpisah menjalankan script Python tanpa menahan request HTTP pengunjung.
4. Hasil klasifikasi di-update ke tabel `complaints`, lalu sistem mengirim notifikasi real-time ke aplikasi.

#### 2. Perlindungan Anti-Fake GPS pada Aplikasi Mobile
Pada modul `mobile/lib/features/field_officer/` saat petugas menekan tombol *Mulai Kerja Lapangan* atau *Selesai*:
```dart
Position position = await Geolocator.getCurrentPosition(
  desiredAccuracy: LocationAccuracy.high,
);

// Validasi apakah koordinat berasal dari Mock Provider (Fake GPS)
if (position.isMocked) {
  ScaffoldMessenger.of(context).showSnackBar(
    const SnackBar(
      content: Text('Peringatan: Terdeteksi penggunaan Fake GPS / Lokasi Tiruan. Matikan aplikasi lokasi tiruan untuk melanjutkan tugas!'),
      backgroundColor: Colors.red,
    ),
  );
  return;
}
```

#### 3. Pasang Cloudflare WAF di Depan Domain Web Dinas
- Aktifkan fitur *Under Attack Mode* atau *DDoS Layer 7 Protection*.
- Pasang rule pembatasan request per IP (misal: maks 100 request/menit untuk rute `/api/*`).

---

### 🏛️ TAHAP 3: IMPLEMENTASI JANGKA PANJANG (SKALABILITAS & TATA KELOLA)

#### 1. Implementasi SSL Certificate Pinning di Flutter
Gunakan package `dio_certificate_pinning`:
```dart
// Mencegah sniffing data menggunakan Burp Suite / MitM proxy
dio.interceptors.add(
  CertificatePinningInterceptor(
    allowedSHAFingerprints: [
      'SHA256_FINGERPRINT_SERTIFIKAT_SSL_PEMKAB_KOLAKA_UTARA',
    ],
  ),
);
```

#### 2. Backup Otomatis Database & Media Terenkripsi
Pasang package `spatie/laravel-backup`:
```bash
composer require spatie/laravel-backup
php artisan vendor:publish --provider="Spatie\Backup\BackupServiceProvider"
```
Konfigurasikan backup harian pada `routes/console.php`:
```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('backup:run --only-db')->dailyAt('02:00');
Schedule::command('backup:clean')->dailyAt('03:00');
```

#### 3. Penyelarasan Kepatuhan Regulasi UU PDP (UU No. 27/2022)
Tambahkan halaman Kebijakan Privasi resmi di web dan mobile yang menyatakan:
- Data koordinat GPS dan foto kerusakan hanya digunakan untuk penanganan infrastruktur daerah.
- Nomor telepon warga pelapor dirahasiakan dari publik dan hanya dapat diakses oleh petugas resmi yang bertugas menangani laporan darurat tersebut.

---
*Catatan ini dibuat pada 09 Oktober 2026 sebagai dokumen rujukan teknis implementasi keamanan SI-APPIK Kabupaten Kolaka Utara.*
