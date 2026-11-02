# Aturan 04: Keamanan, Hak Akses RBAC & Integritas Data (Security, RBAC & Data Integrity)

## 1. Standar Otentikasi & Otorisasi API
- **Penggunaan Resmi Laravel Sanctum:**
  - Seluruh rute API yang membutuhkan autentikasi wajib dilindungi middleware `auth:sanctum`.
  - Model `App\Models\User` WAJIB mengikutsertakan trait `Laravel\Sanctum\HasApiTokens`.
  - **Larangan Keras Fallback Manual:** Dilarang mengimplementasikan bypass autentikasi menggunakan header mentah `X-User-Id`, token base64 custom, atau fallback user otomatis ke `User::first()` / `User::where('role', 'WARGA')->first()`.
- **Manajemen Token:** Token API dibuat saat login berhasil dan dicabut (*revoked*) saat user menekan logout.

## 2. Kontrol Akses Berbasis Peran (RBAC - Role-Based Access Control)
- **Middleware Peran Spesifik:**
  Rute web panel dan endpoint API sensitif TIDAK BOLEH hanya dilindungi `auth` biasa, melainkan wajib mencantumkan middleware otorisasi peran (`EnsureUserHasRole`):
  - Area Super Admin: `middleware(['auth', 'role:SUPERADMIN,ADMIN'])`
  - Area Operator PUPR: `middleware(['auth', 'role:OPERATOR,DINAS'])`
  - Area Eksekutif Bupati: `middleware(['auth', 'role:BUPATI,KEPALA_DAERAH'])`
  - Area Petugas Lapangan: `middleware(['auth', 'role:PETUGAS'])`
  - Area Warga: `middleware(['auth', 'role:WARGA'])`
- **Pencegahan Akses BOLA / IDOR (Broken Object Level Authorization):**
  Setiap controller yang memanipulasi objek berbasis ID (misal: `/complaints/{id}`, `/chat/{session_id}`, `/tasks/{id}`) wajib memverifikasi bahwa user yang sedang login memiliki hak atas objek tersebut atau memiliki izin administratif yang sah.

## 3. Pencegahan Brute-Force, DoS & Pengamanan Kredensial
- **Rate Limiting (Throttle Middleware):**
  - Endpoint otentikasi (`/login`, `/api/v1/auth/login`, `/register`) wajib dilindungi rate limiter (maksimal 5 percobaan gagal per menit).
  - Endpoint komputasi berat AI (`/api/v1/ai/classify-image`) wajib dilindungi rate limiting ketat untuk mencegah server overload atau Denial of Service (DoS).
- **Prosedur Lupa Sandi (Forgot Password):**
  - Dilarang melakukan update password instan hanya dengan parameter email dan kata sandi baru.
  - Alur reset sandi wajib memvalidasi token berbatas waktu kedaluwarsa (15–30 menit) atau verifikasi OTP terenkripsi sebelum password baru dapat disimpan.
- **Kredensial Demo di Halaman Publik:**
  - Fitur atau tombol cepat akun demo pada halaman login publik HANYA boleh aktif pada environment lokal:
    `@if(app()->environment('local')) ... @endif`
  - Dilarang membocorkan password akun pimpinan di markup HTML produksi.

## 4. Integritas Data & Jejak Audit (Audit Trails)
- Setiap perubahan status aduan, penetapan disposisi, atau penghapusan data wajib dicatat ke tabel `audit_logs` dan `complaint_status_histories`.
- Rekam metadata audit: `actor_id`, `role`, alamat IP, user-agent, timestamp, serta rincian data sebelum dan sesudah perubahan (`before_json` dan `after_json`).
