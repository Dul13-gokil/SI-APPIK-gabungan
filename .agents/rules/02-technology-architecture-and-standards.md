# Aturan 02: Arsitektur Teknologi & Standar Rekayasa (Technology Architecture & Standards)

## 1. Arsitektur Backend (Laravel)
- **Versi Platform:** Laravel 11/12 dengan runtime PHP ^8.3.
- **Pemisahan Lapisan Controller:**
  - Controller Mobile/Client REST API wajib diletakkan di namespace `App\Http\Controllers\Api\v1\...`.
  - Controller Web Blade/Dashboard wajib diletakkan di namespace `App\Http\Controllers\Web\...`.
  - Dilarang mencampurkan logika rendering Blade ke dalam API Controller atau sebaliknya.
- **Standar Respon API (Envelope JSON Baku):**
  Semua respon API v1 harus konsisten menggunakan struktur:
  ```json
  {
    "status": "success" | "error",
    "message": "Pesan deskriptif berbahasa Indonesia",
    "data": { ... } | [ ... ] | null,
    "meta": {
      "timestamp": "2026-09-23T...",
      "pagination": { ... } // opsional jika berupa daftar berhalaman
    }
  }
  ```
- **State Machine Engine:**
  Seluruh mutasi status aduan WAJIB dieksekusi melalui `App\Services\ComplaintStateMachine`. Dilarang melakukan update status langsung (`$complaint->update(['status' => ...])`) tanpa melalui transisi yang divalidasi oleh state machine.
- **Mode Disposisi Sistem (Dispatch Modes):**
  Sistem mendukung 3 mode penugasan: `OPERATOR_ONLY`, `HYBRID`, dan `CLAIM`. Logika controller penugasan wajib mematuhi mode aktif dari pengaturan dinas.

## 2. Arsitektur Mobile (Flutter)
- **Versi Platform:** Flutter ^3.24 dengan runtime Dart ^3.5.
- **Pola Arsitektur:** Clean Architecture dengan BLoC pattern (`flutter_bloc`):
  - `core/`: Konfigurasi jaringan (`dio` / `http`), router, token storage, interceptor, utils.
  - `features/<feature_name>/`:
    - `data/`: DTO/Models, remote/local data sources, repository implementations.
    - `domain/`: Entities dan contract repositories.
    - `presentation/`: BLoC/Cubit, screens, and reusable feature widgets.
  - `shared/`: Tema visual (`app_theme.dart`), palet warna (`app_colors.dart`), reusable UI components.
- **Standar Geospatial & Peta:**
  - Gunakan `flutter_map` dengan tile OpenStreetMap resmi / CartoDB.
  - Sediakan mekanisme caching atau penanganan kegagalan koneksi (*offline-tolerant*) saat perangkat berada di area blank spot Kolaka Utara.

## 3. Standar Integrasi AI & Otomasi
- Model deteksi kerusakan visual YOLOv8 (`yolov8n.onnx` / `.pt`) berfungsi sebagai validator awal untuk memberikan rekomendasi klasifikasi jenis kerusakan, tingkat keparahan, dan estimasi skor prioritas.
- Hasil inferensi AI adalah alat bantu pertimbangan (*decision support*), keputusan validasi dan disposisi akhir tetap di bawah wewenang Operator/Dinas teknis.
