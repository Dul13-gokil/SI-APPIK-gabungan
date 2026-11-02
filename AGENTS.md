# Standard Operating Rules & Architecture Guidelines
## Sistem Informasi Pengaduan & Penanganan Kerusakan Infrastruktur Kabupaten Kolaka Utara
*(Backend Laravel 11/12 + Frontend Mobile Flutter BLoC + Computer Vision YOLOv8)*

Dokumen ini merupakan panduan aturan wajib (*Golden Rules*) yang diturunkan dari evaluasi arsitektur dan analisis menyeluruh proyek (Poin 1 hingga Poin 5). Seluruh agen AI dan developer yang mengembangkan proyek ini WAJIB mematuhi aturan berikut demi menjaga konsistensi, stabilitas, dan keamanan sistem.

---

## DAFTAR ATURAN UTAMA (RULES 1 - 5)

### 📌 ATURAN 1: Identitas Proyek & Konsistensi Ekosistem (Ecosystem Consistency)
1. **Domain & Konteks Wilayah:** Proyek ini melayani wilayah Kabupaten Kolaka Utara, Sulawesi Tenggara. Semua penamaan administratif, koordinat GIS, nama instansi (Dinas PUPR / PRKP), serta batasan wilayah wajib merujuk ke data resmi daerah ini.
2. **Sinkronisasi Tiga Ekosistem:** Setiap perubahan fitur wajib mempertahankan keselarasan kontrak data antara:
   - **Web Portal & Admin Panel** (Laravel Blade + Leaflet.js + Tailwind CSS)
   - **Mobile Client** (Flutter BLoC + OpenStreetMap GIS untuk Warga & Petugas)
   - **Layanan AI** (YOLOv8 ONNX/PT untuk Computer Vision + Gemini Chatbot)
3. **Dukungan Kerusakan:** Wajib selalu mendukung dua bentuk laporan:
   - Kerusakan Titik (*Minor*): 1 titik GPS (Lat, Long).
   - Kerusakan Ruas Panjang (*Major*): Titik awal GPS, titik akhir GPS, estimasi panjang (meter/km), dan rute polyline koordinat.

*(Rujukan lengkap: [01-project-identity-and-ecosystem.md](file:///d:/Laragon/laragon/www/aduan/.agents/rules/01-project-identity-and-ecosystem.md))*

---

### 🏗️ ATURAN 2: Arsitektur Teknologi & Standar Rekayasa (Architecture Standards)
1. **Backend Separation:**
   - Endpoint REST API untuk mobile wajib berada di `App\Http\Controllers\Api\v1\...`.
   - Controller Dashboard Web Blade wajib berada di `App\Http\Controllers\Web\...`.
   - Respon API v1 wajib seragam mengikuti amplop (*envelope*): `{ "status": ..., "message": ..., "data": ..., "meta": ... }`.
2. **State Machine Single Authority:** Seluruh mutasi status pengaduan wajib melalui `App\Services\ComplaintStateMachine`. Dilarang mengupdate field status secara langsung tanpa melalui state machine.
3. **Dispatch Mode Obedience:** Alur penugasan wajib mematuhi mode sistem aktif (`OPERATOR_ONLY`, `HYBRID`, atau `CLAIM`).
4. **Mobile Clean Architecture:** Struktur mobile wajib memisahkan `core/`, `features/<name>/` (`data/`, `domain/`, `presentation/`), dan `shared/`.

*(Rujukan lengkap: [02-technology-architecture-and-standards.md](file:///d:/Laragon/laragon/www/aduan/.agents/rules/02-technology-architecture-and-standards.md))*

---

### 🔄 ATURAN 3: Alur Bisnis Inti & Siklus State Machine (Workflow Integrity)
1. **Transisi Status Baku:**
   `DRAFT` ➔ `SUBMITTED` ➔ (`REJECTED` atau `VERIFIED`) ➔ `DISPATCHED` ➔ `IN_PROGRESS` ➔ `WAITING_VERIFICATION` ➔ (`REVISION_REQUIRED` ➔ perbaikan ➔ `WAITING_VERIFICATION`) ➔ `COMPLETED` ➔ `CLOSED`.
2. **Isolasi Hak Regu Lapangan (Team Authorization Shield):**
   - **CRITICAL:** Regu lapangan HANYA boleh mengambil tindakan, membuka modul kamera pengerjaan, dan mengunggah foto bukti penyelesaian pada tugas yang secara resmi ditugaskan kepada regunya (`team_id` / `assigned_to`).
   - Petugas dari regu lain DILARANG KERAS membuka modul pengerjaan atau mengunggah bukti pengerjaan tugas regu lain.
   - Proteksi wajib aktif ganda: **Backend** (HTTP 403 Forbidden pada `finishWork`/`startWork`) dan **Mobile UI** (tombol terkunci + banner penjelas).
3. **Batas Beban Kerja (Quota Limit):** Maksimal 3 tugas aktif per regu secara bersamaan. Tugas ke-4 diblokir sampai minimal 1 tugas tuntas dilaporkan.
4. **Validasi Mutu:** Pekerjaan fisik lapangan tidak pernah langsung tuntas (`COMPLETED`), melainkan masuk ke tahap `WAITING_VERIFICATION` untuk diuji kesesuaiannya oleh Operator PUPR.

*(Rujukan lengkap: [03-business-workflow-and-state-machine.md](file:///d:/Laragon/laragon/www/aduan/.agents/rules/03-business-workflow-and-state-machine.md))*

---

### 🛡️ ATURAN 4: Keamanan, Hak Akses RBAC & Integritas Data (Security & RBAC)
1. **Otentikasi Baku Sanctum:**
   - Semua rute API terlindungi wajib menggunakan middleware `auth:sanctum`.
   - Model `User` wajib menggunakan trait `Laravel\Sanctum\HasApiTokens`.
   - Dilarang keras menggunakan header bypass mentah `X-User-Id`, token base64 custom, atau fallback user otomatis.
2. **Otorisasi Peran Ketat (RBAC):** Rute web dan API administratif wajib dibungkus middleware `EnsureUserHasRole` (`role:SUPERADMIN`, `role:OPERATOR`, `role:BUPATI`, `role:PETUGAS`). Dilarang hanya memakai middleware `auth` umum.
3. **Pencegahan BOLA / IDOR:** Validasi kepemilikan objek pada seluruh request berbasis ID (`$complaint_id`, `$session_id`).
4. **Rate Limiting & Anti-Takeover:**
   - Pasang rate limiter pada login (maks. 5 percobaan/menit) dan endpoint komputasi AI YOLO.
   - Reset sandi wajib melalui alur token kedaluwarsa atau OTP; dilarang reset instan tanpa verifikasi.
   - Kredensial demo di form login hanya boleh aktif pada environment lokal (`app()->environment('local')`).
5. **Jejak Audit (Audit Trails):** Seluruh mutasi status wajib tercatat di tabel `audit_logs` dan `complaint_status_histories` lengkap dengan aktor, IP, timestamp, dan context data.

*(Rujukan lengkap: [04-security-rbac-and-data-integrity.md](file:///d:/Laragon/laragon/www/aduan/.agents/rules/04-security-rbac-and-data-integrity.md))*

---

### 💎 ATURAN 5: Desain Sistem, Tipografi, Warna & Standar UI/UX (Design System)
1. **Tipografi Google Fonts:**
   - **Heading / Judul:** `Outfit` (sans-serif, bold 600–800) seragam di Web dan Mobile (`GoogleFonts.outfit`).
   - **Body / Metrik:** `Inter` atau `Plus Jakarta Sans` (sans-serif, 400–600) untuk keterbacaan tinggi.
   - Mobile dilarang menggunakan `TextStyle(...)` polos tanpa font family.
2. **Palet Warna Resmi (Brand Colors):**
   - Primary: `#1E3A8A` (Deep Slate Blue)
   - Primary Light: `#3B82F6` (Royal Blue)
   - Primary Dark: `#172554` (Navy Dark)
   - Background: `#F8FAFC` (Slate 50)
   - Border: `#E2E8F0` (Slate 200)
3. **Warna Standar Status Workflow (Web & Mobile Wajib Seragam):**
   - `SUBMITTED` (Verifikasi): Merah `#EF4444`
   - `DISPATCHED` (Ditugaskan): Biru `#1E40AF`
   - `IN_PROGRESS` (Sedang Dikerjakan): Kuning `#F59E0B`
   - `WAITING_VERIFICATION` (Validasi): Kuning `#F59E0B`
   - `REVISION_REQUIRED` (Revisi): Kuning `#F59E0B` *(Dilarang menggunakan merah agar tidak dikira Ditolak)*
   - `COMPLETED` / `CLOSED` (Selesai): Hijau `#10B981`
   - `REJECTED` (Ditolak): Merah `#DC2626`
4. **Sentralisasi Token Mobile:** Dilarang meletakkan kode hex hardcoded `Color(0xFF...)` di file widget. Wajib menggunakan token terpusat di `AppColors`.
5. **Filter Publik "Diproses":** Menggabungkan status teknis (`VERIFIED` s.d. `REVISION_REQUIRED`) ke dalam satu kategori ramah publik: **"Diproses"**.

*(Rujukan lengkap: [05-ui-ux-design-system-and-branding.md](file:///d:/Laragon/laragon/www/aduan/.agents/rules/05-ui-ux-design-system-and-branding.md))*

<!-- antislop:start -->
## antislop
For UI, copy, people, mobile layout, or code comments work, read `.agents/skills/antislop/SKILL.md` (core) and then the skill for the task:
- UI / visual: `.agents/skills/antislop-ui/SKILL.md`
- Copy & text: `.agents/skills/antislop-copywriting/SKILL.md`
- People: `.agents/skills/antislop-human/SKILL.md`
- Mobile / responsive: `.agents/skills/antislop-layoutmobile/SKILL.md`
- Code comments: `.agents/skills/antislop-code/SKILL.md`
Before starting, follow the core's "Two Usage Modes" section in strict order: explicit session instruction first, then global preference, then ask. A session instruction always wins. For a resolved mode, say `antislop active: <mode> (session override).` or `antislop active: <mode> (global preference).` once before presenting findings or making edits, using the actual mode and source. Acknowledging the user's request without naming the source does not replace this notice.
Only an explicit choice of antislop during or after selects a session mode. A request to review, audit, or avoid file edits does not select a mode; read the global preference in that case. Another skill's mode does not select antislop's mode.
If the mode is unresolved, ask during/after and end the response; wait for the answer before any UI review, planning, or concept. For read-only tasks, put the active-mode notice only at the start of the final answer, never in progress messages. For editing tasks, announce before the first edit and omit it from the final answer.
To update antislop later: run `npx skills update` or `npx antislop-ai --update`.
<!-- antislop:end -->
