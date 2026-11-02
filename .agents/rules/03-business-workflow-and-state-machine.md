# Aturan 03: Alur Bisnis Inti & Siklus State Machine (Business Workflow & State Machine)

## 1. Siklus Hidup Status Resmi (Complaint Status Lifecycle)
Seluruh transisi status wajib mengikuti diagram alur resmi tanpa toleransi loncatan status:

```
[DRAFT]
   │
   ▼
[SUBMITTED] ────────────► [REJECTED] (Ditolak Operator: data fiktif / bukan wewenang)
   │
   ▼
[VERIFIED] / [VERIFICATION]
   │
   ▼
[DISPATCHED] (Ditugaskan ke Regu Lapangan / Terbuka untuk Klaim Mandiri)
   │
   ▼
[IN_PROGRESS] (Petugas Lapangan mulai pengerjaan fisik di lokasi)
   │
   ▼
[WAITING_VERIFICATION] (Petugas unggah bukti foto pengerjaan fisik selesai)
   │
   ├───► [REVISION_REQUIRED] (Hasil kerja ditolak Operator karena belum rapi/sesuai SOP)
   │           │
   │           └───► (Petugas perbaiki di lapangan & kirim ulang bukti) ──┐
   │                                                                     │
   ▼                                                                     ▼
[COMPLETED] ◄────────────────────────────────────────────────────────────┘
   │
   ▼
[CLOSED]
```

## 2. Aturan Ketat Petugas & Regu Lapangan (Field Team Authorization Shield)
- **Isolasi Kepemilikan Tugas Regu (CRITICAL RULE):**
  - Regu lapangan HANYA berhak mengerjakan, membuka tombol kamera kerja, dan mengunggah foto bukti penyelesaian pada tugas yang secara resmi ditugaskan kepada regunya (`team_id` / `assigned_to`).
  - **Larangan Intervensi Regu Lain:** Petugas dari regu lain DILARANG KERAS mengambil alih, membuka modul pengerjaan, atau mengunggah foto bukti yang bukan merupakan tanggung jawab tugas regunya.
  - **Proteksi Berlapis (Backend + Mobile):**
    1. **Backend:** Endpoint `finishWork`, `startWork`, dan pengunggahan bukti di `WorkflowController` / `ComplaintController` wajib memvalidasi kecocokan ID regu user dengan `assigned_team_id`. Jika tidak cocok, tolak dengan **HTTP 403 Forbidden**.
    2. **Mobile UI:** Aplikasi mobile wajib memeriksa status kepemilikan regu sebelum menampilkan form upload. Jika aduan sedang dikerjakan oleh regu lain, tombol aksi/kamera wajib dinonaktifkan (*locked*) dengan menampilkan banner informasi: *"Tugas ini sedang dikerjakan oleh Regu [Nama Regu]"*.
- **Batas Kuota Beban Kerja Maksimal (Task Quota Limit):**
  - Satu regu/petugas maksimal hanya diperbolehkan memegang **3 tugas aktif** secara bersamaan (`DISPATCHED` atau `IN_PROGRESS`).
  - Tugas ke-4 atau pengklaiman baru wajib diblokir oleh sistem hingga minimal 1 tugas sebelumnya diselesaikan (`WAITING_VERIFICATION` / `COMPLETED`) guna mencegah penumpukan pekerjaan terbengkalai.
- **Navigasi & Pelaporan Bukti Fisik:**
  - Aplikasi mobile wajib menyediakan tautan langsung ke *Google Maps Navigation* menuju koordinat kerusakan.
  - Foto bukti selesai (*after repair*) wajib diambil langsung melalui kamera fisik di lokasi kerja, disertai catatan teknis pengerjaan.

## 3. Aturan Pengawasan Mutu & Validasi Operator
- Petugas lapangan tidak pernah memiliki hak mengubah status laporan langsung menjadi `COMPLETED`.
- Setiap pekerjaan fisik yang dilaporkan tuntas oleh petugas akan berstatus `WAITING_VERIFICATION`.
- Operator Dinas PUPR membandingkan foto sebelum (*before*) dan foto sesudah (*after*).
  - Jika memenuhi standar teknis: Operator mengesahkan status menjadi `COMPLETED`.
  - Jika belum memenuhi standar teknis: Operator mengembalikan status ke `REVISION_REQUIRED` disertai catatan instruksi perbaikan fisik.
