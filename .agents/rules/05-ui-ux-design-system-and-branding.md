# Aturan 05: Desain Sistem, Tipografi, Warna & Standar UI/UX (Design System & Branding)

## 1. Standar Tipografi (Google Fonts)
Kombinasi font resmi proyek dirancang untuk keterbacaan data tinggi dan estetika modern:
- **Heading / Judul Antarmuka:** Google Fonts `Outfit` (sans-serif, font-weight 600 - 800) digunakan konsisten pada Web Dashboard Blade dan Mobile Flutter (`GoogleFonts.outfit`).
- **Body Text / Angka & Tabel Data:** Google Fonts `Inter` atau `Plus Jakarta Sans` (sans-serif, font-weight 400 - 600) digunakan untuk keterbacaan teks panjang dan angka metrik.
- **Larangan Mobile:**
  - Dilarang mendeklarasikan `TextStyle(...)` polos di widget Flutter tanpa menentukan `fontFamily` atau tanpa mewarisi `Theme.of(context).textTheme`. Semua teks wajib mewarisi tema aplikasi atau menggunakan builder `GoogleFonts`.

## 2. Palet Warna Resmi (Design Tokens)
Tema visual proyek adalah **Deep Slate Blue & Clean Administrative Theme**:

### A. Warna Identitas Utama (Brand Colors)
- `primary`: `#1E3A8A` (Deep Slate Blue) — Warna utama navigasi, header, dan kartu profil.
- `primaryLight`: `#3B82F6` (Royal Blue) — Aksen aktif, tombol aksi utama, dan highlight interaktif.
- `primaryDark`: `#172554` (Navy Dark) — Latar belakang hero landing page dan header kontras tinggi.
- `background`: `#F8FAFC` (Slate 50) — Latar kanvas utama aplikasi yang bersih dan nyaman di mata.
- `surface`: `#FFFFFF` (White) — Kartu konten, modal dialog, dan dropdown panel.
- `border`: `#E2E8F0` (Slate 200) — Garis pembatas kartu yang halus dan konsisten.

### B. Warna Status Penanganan Alur Kerja (Workflow Status Colors)
Setiap status penanganan memiliki asosiasi warna standar yang **identik** antara Web dan Mobile:
- **`SUBMITTED` (Verifikasi):** Merah `#EF4444` (badge darurat laporan baru warga yang menunggu verifikasi awal).
- **`DISPATCHED` (Ditugaskan):** Biru `#1E40AF` (indikasi surat tugas aktif kepada regu lapangan).
- **`IN_PROGRESS` (Sedang Dikerjakan):** Kuning `#F59E0B` (regu aktif menangani fisik di lapangan).
- **`WAITING_VERIFICATION` (Validasi):** Kuning `#F59E0B` (pekerjaan fisik selesai dilaporkan, menunggu validasi mutu oleh Operator Dinas PUPR).
- **`REVISION_REQUIRED` (Perlu Revisi Fisik):** Kuning `#F59E0B` (Waspada: pengerjaan ulang fisik di lapangan).  
  *(PERHATIAN: Dilarang memberi warna merah pada revisi agar tidak disalahartikan warga/petugas sebagai laporan ditolak permanen).*
- **`COMPLETED` / `CLOSED` (Selesai):** Hijau `#10B981` (pengerjaan berhasil dan disetujui tuntas).
- **`REJECTED` (Ditolak):** Merah `#DC2626` (laporan dibatalkan/tidak valid).

## 3. Konvensi Kode Tampilan Mobile (Clean UI Code)
- **Sentralisasi Token Warna:**
  - Hindari penulisan kode heksadesimal langsung seperti `Color(0xFFEFF6FF)` atau `Color(0xFFE2E8F0)` di dalam berkas screen/widget.
  - Semua warna harus merujuk ke token terpusat di `AppColors` (misal: `AppColors.primaryContainer`, `AppColors.neutralBorder`).
- **Penyederhanaan Kategori Filter Publik:**
  - Untuk kenyamanan warga dan pimpinan daerah, status penanganan teknis (`VERIFIED`, `DISPATCHED`, `IN_PROGRESS`, `WAITING_VERIFICATION`, dan `REVISION_REQUIRED`) dipayungi dalam 1 filter besar ramah publik: **"Diproses"**.
- **Ikonografi:**
  - Gunakan ikon modern yang konsisten (seperti `Lucide Icons` pada Web dan Material/Cupertino symbols yang selaras pada Flutter).
