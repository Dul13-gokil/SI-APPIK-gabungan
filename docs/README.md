# Dokumentasi SI-APPIK

Semua dokumen proyek ada di sini. Urutan baca yang disarankan untuk orang baru:
mulai dari nomor 1, lalu dokumen sesuai pekerjaan.

| Dokumen | Isi | Kapan dibaca |
| --- | --- | --- |
| [../README.md](../README.md) | Peta proyek, cara menjalankan, di mana menambah fitur | Hari pertama |
| [ALUR-WORKFLOW.md](ALUR-WORKFLOW.md) | Alur status aduan yang benar-benar berjalan di kode | Sebelum menyentuh alur aduan |
| [MATRIKS-PERAN.md](MATRIKS-PERAN.md) | Hak akses tiap peran, aturan ketua regu, kebijakan sandi | Sebelum mengubah rute atau menu |
| [DOCKER-DEPLOY.md](DOCKER-DEPLOY.md) | Menjalankan stack di lokal, deploy ke VPS, cadangan | Saat menyiapkan server |
| [FLUTTER-SETUP.md](FLUTTER-SETUP.md) | Toolchain Flutter, JDK, Android SDK, build APK | Saat mengerjakan aplikasi mobile |
| [ROADMAP-KEAMANAN.md](ROADMAP-KEAMANAN.md) | Catatan keamanan dan rencana lanjutan | Saat audit keamanan |
| [RENCANA-PERUBAHAN-RAPAT.md](RENCANA-PERUBAHAN-RAPAT.md) | Keputusan rapat yang menjadi dasar perubahan | Saat mempertanyakan "kenapa begini" |
| [api/openapi.yaml](api/openapi.yaml) | Kontrak REST API v1 untuk mobile | Saat menambah endpoint |
| [workflow/](workflow/) | Flowchart (PNG) dan PDF alur sistem | Saat presentasi |

## Aturan proyek

Aturan yang mengikat ada di [../AGENTS.md](../AGENTS.md) dan [../.agents/rules/](../.agents/rules/).
Dokumen di folder ini menjelaskan, aturan di sana yang mengikat.

## Kebiasaan menulis dokumen

- Tulis apa yang benar-benar dilakukan kode, bukan yang seharusnya.
- Sertakan perintah yang bisa dijalankan ulang, bukan tangkapan layar saja.
- Kalau sebuah dokumen usang, perbarui atau hapus — jangan biarkan dua dokumen
  menjelaskan hal yang sama dengan isi berbeda.
