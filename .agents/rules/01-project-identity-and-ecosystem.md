# Aturan 01: Identitas Proyek & Konsistensi Ekosistem (Project Identity & Ecosystem)

## 1. Identitas & Domain Proyek
- **Nama Proyek:** Sistem Informasi Pengaduan & Penanganan Kerusakan Infrastruktur (Smart City PUPR).
- **Wilayah Cakupan Resmi:** Kabupaten Kolaka Utara, Provinsi Sulawesi Tenggara (beserta seluruh kecamatan dan desa/kelurahan di dalamnya).
- **Tujuan Utama:** Mengelola siklus pelaporan publik, verifikasi dinas, disposisi tim teknis lapangan, pengerjaan fisik, hingga validasi mutu infrastruktur jalan, jembatan, drainase, dan penerangan jalan umum (PJU).

## 2. Prinsip Tripartit Ekosistem
Proyek ini terdiri dari 3 pilar yang wajib selalu selaras:
1. **Portal Publik & Web Panel Administrasi:** Laravel Blade + Tailwind CSS + Leaflet.js GIS (untuk Publik, Operator PUPR, Eksekutif Bupati, dan Super Admin).
2. **Aplikasi Mobile Terintegrasi:** Flutter Clean Architecture + BLoC + OpenStreetMap GIS (untuk Warga Pelapor dan Petugas Regu Lapangan).
3. **Layanan AI & Otomasi:** Model Computer Vision YOLOv8 (deteksi visual pothole/keretakan/tingkat keparahan) + AI Chatbot Asisten Warga.

## 3. Aturan Sinkronisasi Lintas Platform
- **Kontrak Data Bersama:** Setiap penambahan atau perubahan kolom tabel database / properti model HARUS diselaraskan pada API Resource Laravel, Payload JSON, dan Data Model di Flutter (`mobile/lib/features/**/models/`). Dilarang mengubah skema data secara sepihak.
- **Konsistensi Terminologi:** Istilah birokrasi, penamaan peran (`SUPERADMIN`, `BUPATI`, `OPERATOR`, `PETUGAS`, `WARGA`), serta kategori pengaduan harus seragam di Web dan Mobile.
- **Dukungan Dua Jenis Kerusakan Fisik:**
  - **Kerusakan Titik (*Minor*):** Menggunakan 1 titik koordinat GPS presisi (Latitude, Longitude).
  - **Kerusakan Ruas Panjang (*Major*):** Wajib mendukung titik awal GPS, titik akhir GPS, estimasi panjang jalan (meter/kilometer), dan rute polyline jalan.
