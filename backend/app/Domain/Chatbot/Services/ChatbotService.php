<?php

namespace App\Domain\Chatbot\Services;

use App\Models\Complaint;

class ChatbotService
{
    public const STATE_START = 'START';
    public const STATE_IDENTIFY_TYPE = 'IDENTIFY_TYPE';
    public const STATE_IDENTIFY_CATEGORY = 'IDENTIFY_CATEGORY';
    public const STATE_COLLECT_DESCRIPTION = 'COLLECT_DESCRIPTION';
    public const STATE_COLLECT_LOCATION = 'COLLECT_LOCATION';
    public const STATE_CONFIRM = 'CONFIRM';
    public const STATE_DONE = 'DONE';

    /**
     * Proses pesan chat cerdas: panduan aplikasi, FAQ alur/SLA, cek kode tiket, dan asisten pembuatan draf laporan.
     */
    public function handleMessage(string $currentState, string $userMessage, array $draftData = [], ?string $role = null): array
    {
        $msg = trim($userMessage);
        $lower = strtolower($msg);

        // 1. Cek apakah user menanyakan status kode aduan (contoh: 0005/ringan/2026 atau ADU-0001)
        if (preg_match('/(?:[A-Z0-9\/_-]*(?:\d{4}\/(?:ringan|berat)\/\d{4})[A-Z0-9\/_-]*|ADU[-\/A-Z0-9]+)/i', $msg, $matches)) {
            $rawCode = trim($matches[0], " #\t\n\r\0\x0B");
            $cleanCode = str_replace(['/', '-'], '', $rawCode);
            $found = Complaint::where('public_code', 'ILIKE', $rawCode)
                ->orWhere('public_code', 'LIKE', "%{$rawCode}%")
                ->orWhereRaw("REPLACE(REPLACE(public_code, '/', ''), '-', '') LIKE ?", ["%{$cleanCode}%"])
                ->first();

            if ($found) {
                $statusMap = [
                    'SUBMITTED' => '⏳ Menunggu Verifikasi Operator Dinas',
                    'VERIFIED' => '✅ Terverifikasi, Menunggu Disposisi Pekerja Lapangan',
                    'DISPATCHED' => '🚚 Ditugaskan ke Pekerja Lapangan PUPR/Dishub',
                    'IN_PROGRESS' => '🛠️ Sedang Dikerjakan Secara Fisik di Lokasi',
                    'WAITING_VERIFICATION' => '🔍 Pekerjaan Selesai, Menunggu Validasi Operator',
                    'COMPLETED' => '🎉 SELESAI TUNTAS (Pekerjaan Telah Tervalidasi)',
                    'REJECTED' => '❌ Laporan Ditolak (Tidak Memenuhi Kriteria / Duplikat)',
                ];
                $statusText = $statusMap[$found->status] ?? $found->status;
                $categoryName = $found->category?->name ?? 'Fasilitas Umum';

                return [
                    'next_state' => self::STATE_START,
                    'reply' => "📌 **Status Tiket Aduan #{$found->public_code}**\n\n"
                        . "• **Kategori:** {$categoryName}\n"
                        . "• **Status Saat Ini:** {$statusText}\n"
                        . "• **Lokasi:** {$found->address}\n"
                        . "• **Tanggal Lapor:** {$found->created_at->format('d M Y H:i')} WITA\n"
                        . ($found->damage_type ? "• **Tipe Kerusakan:** {$found->damage_type}\n" : "")
                        . ($found->estimated_length_meters ? "• **Estimasi Panjang:** " . number_format($found->estimated_length_meters, 0, ',', '.') . " meter\n" : "")
                        . "\n💡 *Notifikasi perkembangan tiket ini otomatis dikirim ke akun Warga Terdaftar Anda.* Ada hal lain yang ingin Anda tanyakan?",
                    'draft' => [],
                    'is_complete' => false,
                ];
            } else {
                return [
                    'next_state' => self::STATE_START,
                    'reply' => "Maaf, nomor tiket aduan **{$rawCode}** tidak ditemukan di database. Pastikan format nomor tiket sudah tepat (contoh: ADU-0001 atau ADU-202609-0001).",
                    'draft' => [],
                    'is_complete' => false,
                ];
            }
        }

        // 2. Tanya Apa Itu Pekerja Lapangan (Peran, Tugas, & Wewenang)
        if (str_contains($lower, 'apa itu petugas') || str_contains($lower, 'pekerja lapangan') || str_contains($lower, 'peran petugas') || str_contains($lower, 'tugas petugas') || str_contains($lower, 'siapa petugas')) {
            return [
                'next_state' => self::STATE_START,
                'reply' => "👷‍♂️ **Mengenal Peran Pekerja Lapangan (Dinas PUPR & PRKP):**\n\n"
                    . "Pekerja Lapangan adalah **tim teknis operasional resmi** Pemerintah Kabupaten Kolaka Utara yang bertugas langsung di lokasi kejadian untuk menangani perbaikan fisik infrastruktur publik (jalan, jembatan, lampu penerangan jalan, drainase) berdasarkan Surat Perintah Kerja (SPK) dari Operator Dinas.\n\n"
                    . "🔑 **Wewenang & Fitur Khusus di Aplikasi Mobile:**\n"
                    . "1. **Menerima Disposisi Tugas:** Memantau SPK penugasan pada tab *Tugas Aktif* lengkap dengan data koordinat GPS satelit dan foto laporan warga.\n"
                    . "2. **Navigasi Rute Nyata GIS (OSRM):** Menghubungkan langsung ke rute jalan nyata menuju titik kerusakan.\n"
                    . "3. **Tombol 'Mulai Kerja Lapangan':** Menandai mulainya pengerjaan fisik di lokasi sekaligus memulai pencatatan waktu SLA secara transparan.\n"
                    . "4. **Akses Kontak Pelapor:** Petugas berwenang melihat nomor telepon pelapor untuk keperluan konfirmasi lokasi darurat di lapangan.\n"
                    . "5. **Kirim Bukti Selesai:** Mengambil foto fisik hasil perbaikan (*After-Work*) dan melampirkan catatan teknis material kepada Operator Dinas.\n\n"
                    . "⚠️ **Aturan Disiplin Petugas:**\n"
                    . "• **Batas Kuota:** Maksimal mengerjakan **3 tugas aktif** secara bersamaan agar mutu pekerjaan terjaga.\n"
                    . "• **Isolasi Hak Akses (Team Shield):** Hanya petugas/regu yang ditugaskan resmi yang berhak membuka modul kerja dan mengunggah bukti pengerjaan.\n"
                    . "• **Validasi Mutu:** Pekerjaan tidak langsung selesai tuntas, melainkan diverifikasi terlebih dahulu oleh Operator Dinas.",
                'draft' => [],
                'is_complete' => false,
            ];
        }

        // 3. Tanya Cara Verifikasi OTP (One-Time Password)
        if (str_contains($lower, 'otp') || str_contains($lower, 'kode otp') || str_contains($lower, 'verifikasi otp') || str_contains($lower, 'cara otp')) {
            return [
                'next_state' => self::STATE_START,
                'reply' => "✉️ **Panduan Lengkap Verifikasi Kode OTP:**\n\n"
                    . "Kode OTP (*One-Time Password*) adalah 6 digit angka rahasia yang dikirimkan ke email terdaftar untuk memastikan keamanan akun Anda.\n\n"
                    . "1. **Untuk Pendaftaran Akun Warga Baru:**\n"
                    . "   • Isi formulir pendaftaran (Nama, Email, Nomor Telepon, Kata Sandi).\n"
                    . "   • Sistem akan otomatis mengirimkan kode OTP 6 digit ke email Anda.\n"
                    . "   • Masukkan kode OTP pada halaman verifikasi di aplikasi mobile sebelum waktu kedaluwarsa (5 menit).\n"
                    . "   • Akun Anda langsung aktif dan resmi menjadi **Warga Terdaftar**.\n\n"
                    . "2. **Untuk Lupa / Reset Kata Sandi (Warga & Pekerja Lapangan):**\n"
                    . "   • Pada halaman login aplikasi, tekan **'Lupa Kata Sandi?'**.\n"
                    . "   • Masukkan alamat email akun terdaftar Anda.\n"
                    . "   • Cek kotak masuk (Inbox) atau folder Spam pada email Anda untuk melihat 6 digit kode OTP.\n"
                    . "   • Masukkan kode OTP di aplikasi mobile, lalu tentukan kata sandi baru Anda.\n\n"
                    . "💡 **Tips Keamanan OTP:**\n"
                    . "• Jangan pernah membagikan kode OTP kepada siapa pun, termasuk pihak yang mengatasnamakan dinas/petugas.\n"
                    . "• Jika kode tidak muncul dalam 1 menit, tekan tombol **'Kirim Ulang OTP'** di aplikasi.",
                'draft' => [],
                'is_complete' => false,
            ];
        }

        // 4. Tanya Ganti Password & Lupa Kata Sandi
        if (str_contains($lower, 'password') || str_contains($lower, 'sandi') || str_contains($lower, 'ganti password') || str_contains($lower, 'lupa sandi') || str_contains($lower, 'ubah sandi') || str_contains($lower, 'reset password')) {
            return [
                'next_state' => self::STATE_START,
                'reply' => "🔑 **Panduan Mengubah & Mengatur Ulang Kata Sandi:**\n\n"
                    . "1. **Jika Anda Sudah Login ke Aplikasi:**\n"
                    . "   • Buka tab **Profil** di pojok kanan bawah aplikasi.\n"
                    . "   • Pilih menu **'Ubah Profil & Kata Sandi'**.\n"
                    . "   • Masukkan kata sandi lama Anda, kemudian masukkan kata sandi baru (minimal 8 karakter) dan konfirmasi kata sandi baru.\n"
                    . "   • Tekan tombol **'Simpan Perubahan'**.\n\n"
                    . "2. **Jika Anda Lupa Sandi (Belum Login):**\n"
                    . "   • Pada layar Login aplikasi mobile, tekan tautan **'Lupa Kata Sandi?'** di bawah kolom input sandi.\n"
                    . "   • Masukkan alamat email akun Anda yang terdaftar.\n"
                    . "   • Buka email Anda dan salin 6 digit **Kode OTP** yang dikirimkan sistem.\n"
                    . "   • Masukkan kode OTP tersebut di aplikasi mobile dan masukkan kata sandi baru Anda.\n\n"
                    . "👷‍♂️ **Catatan Khusus Pekerja Lapangan:**\n"
                    . "Meskipun akun Petugas dibuatkan oleh Superadmin Dinas PUPR, Anda dapat mengubah atau mereset kata sandi Anda sendiri kapan saja melalui fitur di atas!",
                'draft' => [],
                'is_complete' => false,
            ];
        }

        // 5. Tanya Tentang Aplikasi & Gambaran Keseluruhan Sistem
        if (str_contains($lower, 'tentang aplikasi') || str_contains($lower, 'apa itu aplikasi') || str_contains($lower, 'seputar aplikasi') || str_contains($lower, 'info aplikasi') || (str_contains($lower, 'aplikasi') && !str_contains($lower, 'pakai') && $role !== 'PETUGAS')) {
            return [
                'next_state' => self::STATE_START,
                'reply' => "📱 **Tentang Aplikasi Pengaduan Infrastruktur Kolaka Utara:**\n\n"
                    . "Aplikasi ini merupakan platform digital terpadu milik Pemerintah Kabupaten Kolaka Utara (Dinas PUPR & PRKP) yang dirancang untuk mempercepat pelaporan dan penanganan kerusakan fasilitas publik secara transparan dan akuntabel.\n\n"
                    . "🌟 **Fitur Unggulan Aplikasi:**\n"
                    . "• 🤖 **AI Computer Vision (YOLOv8):** Mendeteksi lubang jalan, retakan aspal, lampu PJU rusak, jalan mulus, serta memfilter foto yang tidak relevan secara otomatis.\n"
                    . "• 🛣️ **2 Tipe Pelaporan:** Kerusakan Ringan (< 500m, 1 titik GPS) dan Kerusakan Berat (≥ 500m, polyline rute jalan nyata 2 titik GPS).\n"
                    . "• 📍 **Geofencing Kabupaten:** Menjamin seluruh aduan berada di dalam batas administratif 15 kecamatan Kolaka Utara.\n"
                    . "• ⏱️ **Transparansi SLA:** Batas waktu penanganan terbuka (Jembatan darurat 24 jam, Jalan berlubang 48 jam, Lampu PJU 72 jam).\n"
                    . "• 🔒 **Perlindungan Privasi:** Nomor HP pelapor disamarkan untuk sesama warga demi menjaga keamanan data pribadi.\n"
                    . "• 👷‍♂️ **Integrasi Pekerja Lapangan:** Petugas URC langsung menerima SPK disposisi dan menuju lokasi via navigasi GIS terintegrasi.",
                'draft' => [],
                'is_complete' => false,
            ];
        }

        // 6. Tanya Privasi Nomor Pelapor
        if (str_contains($lower, 'nomor') && (str_contains($lower, 'privasi') || str_contains($lower, 'pelapor') || str_contains($lower, 'telepon') || str_contains($lower, 'sembunyi') || str_contains($lower, 'tampil'))) {
            return [
                'next_state' => self::STATE_START,
                'reply' => "🔒 **Kebijakan Privasi Nomor Telepon Pelapor:**\n\n"
                    . "Demi menjaga kenyamanan dan keamanan data pribadi masyarakat Kolaka Utara:\n"
                    . "• **Untuk Warga Lain:** Nomor telepon pelapor disamarkan (contoh: *******890) dan dikunci dengan ikon gembok privasi.\n"
                    . "• **Untuk Pelapor Asli:** Anda dapat melihat nomor telepon Anda sendiri pada rincian laporan yang Anda buat.\n"
                    . "• **Untuk Pekerja Lapangan & Operator:** Nomor telepon pelapor dapat dilihat secara utuh agar petugas dapat menghubungi pelapor jika membutuhkan panduan titik lokasi kerusakan di lapangan.",
                'draft' => [],
                'is_complete' => false,
            ];
        }

        // 7. Tanya Perbedaan Tipe Pelaporan (Kerusakan Ringan vs Kerusakan Berat)
        if (str_contains($lower, 'beda') || str_contains($lower, 'ruas panjang') || str_contains($lower, 'titik') || str_contains($lower, '500') || str_contains($lower, 'tipe pelaporan') || str_contains($lower, 'tipe laporan') || str_contains($lower, 'parah') || str_contains($lower, 'kerusakan ringan') || str_contains($lower, 'kerusakan berat')) {
            return [
                'next_state' => self::STATE_START,
                'reply' => "🛣️ **Perbedaan 2 Tipe Pelaporan Kerusakan:**\n\n"
                    . "1. **Kerusakan Ringan (< 500 Meter):**\n"
                    . "   • **Peruntukan:** Kerusakan skala lokal (lubang jalan tunggal/spot, retak lokal, amblas kecil, lampu jalan PJU padam, fasilitas umum).\n"
                    . "   • **Kategori:** Jalan & Jembatan, Penerangan Jalan Umum (PJU), Fasilitas Umum.\n"
                    . "   • **Titik GPS:** Cukup menentukan **1 titik koordinat** lokasi kejadian di peta.\n"
                    . "   • **Dukungan AI:** Multi-model YOLOv8 ONNX memeriksa foto bukti (deteksi lubang, retak, lampu, fasilitas umum, atau jalan mulus).\n\n"
                    . "2. **Kerusakan Berat (≥ 500 Meter):**\n"
                    . "   • **Peruntukan:** Kerusakan jalan berkelanjutan/panjang (minimal 500 meter hingga berkilo-kilometer).\n"
                    . "   • **Kategori:** **Terkunci khusus Jalan & Jembatan** (tidak dapat untuk lampu jalan/fasilitas lain).\n"
                    . "   • **Titik GPS:** Wajib menentukan **2 titik di peta**: **Titik Awal Ruas** dan **Titik Akhir Ruas**.\n"
                    . "   • **Estimasi Panjang:** Dihitung otomatis dari rute jalan nyata (road routing service), bukan diketik manual.\n"
                    . "   • **Kondisi Permukaan:** Dilengkapi penilaian kondisi (Aspal Rusak Berat, Berlubang & Amblas, Jalan Tanah/Kerikil).",
                'draft' => [],
                'is_complete' => false,
            ];
        }

        // 8. Tanya Wilayah Layanan Kabupaten Kolaka Utara & Geofencing
        if (str_contains($lower, 'wilayah') || str_contains($lower, 'kolaka utara') || str_contains($lower, 'kecamatan') || str_contains($lower, 'geofence') || str_contains($lower, 'cakupan') || str_contains($lower, 'luar daerah')) {
            return [
                'next_state' => self::STATE_START,
                'reply' => "📍 **Cakupan Wilayah Kabupaten Kolaka Utara & Geofencing:**\n\n"
                    . "Sistem pengaduan ini beroperasi secara terpadu khusus untuk wilayah administratif **Kabupaten Kolaka Utara**, Sulawesi Tenggara, yang mencakup 15 kecamatan:\n"
                    . "1. Lasusua (Ibu Kota Kabupaten)\n"
                    . "2. Wawo\n"
                    . "3. Ngapa\n"
                    . "4. Kodeoha\n"
                    . "5. Pakue\n"
                    . "6. Pakue Tengah\n"
                    . "7. Pakue Utara\n"
                    . "8. Tiwu\n"
                    . "9. Tolala\n"
                    . "10. Ranteangin\n"
                    . "11. Lambai\n"
                    . "12. Katoi\n"
                    . "13. Porehu\n"
                    . "14. Watunohu\n"
                    . "15. Batu Putih\n\n"
                    . "🛡️ **Sistem Geofencing:** Koordinat GPS di luar batas wilayah Kabupaten Kolaka Utara akan otomatis diberi peringatan/ditolak sistem agar tindak lanjut dinas teknis tepat sasaran.",
                'draft' => [],
                'is_complete' => false,
            ];
        }

        // 9. Tanya Teknologi AI Computer Vision & Tips Mengambil Foto
        if (str_contains($lower, 'ai') || str_contains($lower, 'computer vision') || str_contains($lower, 'kamera') || str_contains($lower, 'foto') || str_contains($lower, 'deteksi') || str_contains($lower, 'yolo') || str_contains($lower, 'pothole')) {
            return [
                'next_state' => self::STATE_START,
                'reply' => "🤖 **Teknologi AI Computer Vision & Tips Foto Bukti:**\n\n"
                    . "Aplikasi menggunakan multi-model AI Computer Vision (YOLOv8 ONNX) yang berjalan otomatis saat Anda mengunggah foto:\n"
                    . "• **Deteksi Kerusakan:** Mendeteksi lubang jalan (*potholes*), retakan jalan (*cracks, alligator cracking* RDD2022), tiang lampu PJU rusak, serta saluran drainase.\n"
                    . "• **Deteksi Jalan Mulus:** Mampu memverifikasi jika jalan sudah dalam kondisi baik (*Smooth Road*).\n"
                    . "• **Penyaring Cerdas:** Otomatis menolak foto non-infrastruktur seperti wajah orang/selfie, hewan, makanan, kendaraan, atau ruangan dalam rumah.\n\n"
                    . "📸 **Tips Mengambil Foto yang Baik:**\n"
                    . "1. Ambil foto di tempat terang (siang hari atau dengan pencahayaan cukup).\n"
                    . "2. Arahkan kamera langsung ke kerusakan jalan/fasilitas.\n"
                    . "3. Hindari mengambil foto wajah orang di depan kamera.\n"
                    . "4. Pastikan gambar tidak blur agar AI dapat mendeteksi dengan akurasi tinggi.",
                'draft' => [],
                'is_complete' => false,
            ];
        }

        // 10. Tanya Alur & Standar Waktu Penanganan (SLA)
        if (str_contains($lower, 'alur') || str_contains($lower, 'proses') || str_contains($lower, 'berapa lama') || str_contains($lower, 'sla') || str_contains($lower, 'tahap') || str_contains($lower, 'waktu penanganan')) {
            return [
                'next_state' => self::STATE_START,
                'reply' => "⏱️ **Alur Kerja & Standar Waktu Penanganan (SLA Dinas):**\n\n"
                    . "1. **Pengajuan Laporan (`SUBMITTED`)** → Warga mengirim laporan disertai foto & GPS.\n"
                    . "2. **Verifikasi Operator (`VERIFIED`)** → Operator Dinas PUPR/Dishub memeriksa keabsahan data (< 2 jam kerja).\n"
                    . "3. **Disposisi Petugas (`DISPATCHED`)** → Diteruskan ke tim pekerja lapangan terkait.\n"
                    . "4. **Penanganan Fisik di Lapangan (`IN_PROGRESS`)**:\n"
                    . "   • **Lubang / Titik Ringan:** Maksimal 48 Jam (2 Hari Kerja)\n"
                    . "   • **Jembatan Darurat / Kritis:** Maksimal 24 Jam (Prioritas Darurat)\n"
                    . "   • **Lampu Jalan PJU Padam:** Maksimal 72 Jam (3 Hari Kerja)\n"
                    . "   • **Ruas Jalan Panjang / Parah:** Penanganan darurat dan diagendakan pada program pemeliharaan berkala\n"
                    . "5. **Validasi Mutu (`WAITING_VERIFICATION`)** → Petugas upload foto pengerjaan, operator memverifikasi mutu.\n"
                    . "6. **Selesai Tuntas (`COMPLETED`)** → Laporan ditutup tuntas, notifikasi dikirimkan ke HP Warga Terdaftar.",
                'draft' => [],
                'is_complete' => false,
            ];
        }

        // 11. Tanya Peran Pengguna (Warga Terdaftar vs Tamu, Petugas, Operator, Pimpinan)
        if (str_contains($lower, 'role') || str_contains($lower, 'peran') || str_contains($lower, 'warga terdaftar') || str_contains($lower, 'pelapor') || str_contains($lower, 'tamu') || str_contains($lower, 'operator') || str_contains($lower, 'bupati')) {
            return [
                'next_state' => self::STATE_START,
                'reply' => "👥 **Peran dalam Sistem Pengaduan Kolaka Utara:**\n\n"
                    . "• **Warga Terdaftar:** Warga yang login dengan akun terdaftar resmi. Memiliki akses penuh ke riwayat laporan terpusat, notifikasi real-time, preview identitas akun, foto profil, dan pemantauan tiket aduan.\n"
                    . "• **Mode Tamu / Anonim:** Pengguna yang melapor tanpa login. Identitas nama dan kontak tidak ditampilkan pada publik demi privasi.\n"
                    . "• **Pekerja Lapangan:** Tim teknis lapangan Dinas PUPR/Dishub yang menerima surat perintah tugas, datang ke lokasi via GPS, melakukan pengerjaan fisik, dan mengunggah foto bukti penyelesaian.\n"
                    . "• **Operator Dinas:** Memverifikasi laporan masuk, menentukan prioritas, meneruskan disposisi, dan memvalidasi hasil kerja lapangan.\n"
                    . "• **Bupati / Pimpinan Daerah:** Memantau peta persebaran laporan GIS dan statistik kinerja penanganan se-Kabupaten Kolaka Utara secara real-time.",
                'draft' => [],
                'is_complete' => false,
            ];
        }

        // 12. Tanya Fitur Akun & Halaman Profil
        if (str_contains($lower, 'profil') || str_contains($lower, 'akun') || str_contains($lower, 'username') || str_contains($lower, 'ganti nama') || str_contains($lower, 'notifikasi') || str_contains($lower, 'preview')) {
            return [
                'next_state' => self::STATE_START,
                'reply' => "👤 **Fitur Akun & Profil Pengguna:**\n\n"
                    . "Pada menu **Profil**, Anda dapat menikmati berbagai kemudahan:\n"
                    . "1. **Foto Profil:** Tekan ikon kamera pada avatar profil Anda untuk mengambil foto baru dari Kamera atau Galeri ponsel.\n"
                    . "2. **Ubah Profil & Kata Sandi:** Perbarui nama lengkap, email, nomor telepon, dan kata sandi baru Anda secara mandiri.\n"
                    . "3. **Preview Akun:** Tekan menu *Preview Akun* untuk melihat rincian kartu identitas akun Anda (Status akun, email, tanggal bergabung, dan domisili).\n"
                    . "4. **Panduan Penggunaan Aplikasi:** Buka menu panduan untuk mempelajari tips lapor titik ringan vs ruas panjang dan tips foto AI.\n"
                    . "5. **Pengaturan Notifikasi:** Atur pemberitahuan pembaruan status laporan, info penugasan petugas, pengumuman dinas, suara, dan getar sesuai kenyamanan Anda.",
                'draft' => [],
                'is_complete' => false,
            ];
        }

        // 13. Tanya Kontak Darurat (Hanya Call Center dan Dinas PUPR / Operator)
        if (str_contains($lower, 'kontak') || str_contains($lower, 'darurat') || str_contains($lower, 'telepon') || str_contains($lower, 'call center') || str_contains($lower, '112')) {
            return [
                'next_state' => self::STATE_START,
                'reply' => "🚨 **Kontak Darurat Kabupaten Kolaka Utara:**\n\n"
                    . "• **Call Center Darurat:** **112** (Siaga 24 Jam Bebas Pulsa)\n"
                    . "• **Dinas PUPR (Operator):** (0405) 2321100\n\n"
                    . "Untuk laporan kerusakan infrastruktur jalan, jembatan, dan lampu jalan, Anda dapat langsung membuat laporan melalui aplikasi ini atau menghubungi Call Center & Dinas PUPR di atas.",
                'draft' => [],
                'is_complete' => false,
            ];
        }

        // 14a. Petugas: Cara Mengambil & Memulai Tugas
        if (str_contains($lower, 'ambil tugas') || str_contains($lower, 'mengambil tugas') || str_contains($lower, 'cara ambil') || str_contains($lower, 'mulai tugas') || str_contains($lower, 'mulai kerja')) {
            return [
                'next_state' => self::STATE_START,
                'reply' => "📋 **Panduan Mengambil & Memulai Tugas Lapangan:**\n\n"
                    . "1. **Buka Tab Tugas Aktif:** Masuk ke menu Tugas Aktif untuk melihat Daftar Perintah Kerja (SPK).\n"
                    . "2. **Pilih Penugasan:** Ketuk kartu tugas yang ingin Anda kerjakan atau pilih tab *Tugas Saya*.\n"
                    . "3. **Tekan 'Mulai Kerja Lapangan':** Tombol ini langsung mencatat waktu resmi mulai kerja dan membuka peta navigasi GIS ke titik lokasi kerusakan.\n"
                    . "4. **Batas Kuota:** Anda dibatasi maksimal mengerjakan **3 tugas aktif** secara serentak agar penyelesaian tetap fokus dan memenuhi target SLA.",
                'draft' => [],
                'is_complete' => false,
            ];
        }

        // 14b. Petugas: Cara Menggunakan Aplikasi Lapangan
        if (str_contains($lower, 'pakai aplikasi') || str_contains($lower, 'menggunakan aplikasi') || (str_contains($lower, 'aplikasi') && $role === 'PETUGAS')) {
            return [
                'next_state' => self::STATE_START,
                'reply' => "📱 **Panduan Penggunaan Aplikasi untuk Pekerja Lapangan:**\n\n"
                    . "1. **Kotak Ringkasan (KPI):** Memantau Total Aduan, Menunggu Verifikasi, Ditugaskan, Dikerjakan, Menunggu Validasi, dan Tuntas se-Kabupaten.\n"
                    . "2. **Filter Tugas:** Beralih antara *Semua Tugas* (tugas tim) dan *Tugas Saya* (tugas yang ditugaskan khusus ke regu Anda).\n"
                    . "3. **Mulai Kerja & Peta GIS:** Tekan kartu tugas untuk rute jalan nyata (OSRM) menuju koordinat GPS.\n"
                    . "4. **Kirim Bukti Selesai:** Ambil foto fisik tuntas dan isi catatan teknis melalui tombol *Selesai + Kirim Bukti*.\n"
                    . "5. **Validasi Operator:** Foto Anda akan diperiksa oleh Operator Dinas hingga tiket berstatus Tuntas.",
                'draft' => [],
                'is_complete' => false,
            ];
        }

        // 14c. Petugas: Tips Foto Bukti Validasi Operator
        if (str_contains($lower, 'foto validasi') || str_contains($lower, 'tips foto') || str_contains($lower, 'foto yang baik') || str_contains($lower, 'bukti validasi')) {
            return [
                'next_state' => self::STATE_START,
                'reply' => "📸 **Tips Foto yang Baik untuk Validasi Operator Dinas:**\n\n"
                    . "1. **Sudut Permukaan Rata:** Ambil foto dari sudut yang jelas memperlihatkan hasil tambalan/perbaikan telah rata dengan permukaan jalan sekitar.\n"
                    . "2. **Cahaya Cukup & Fokus:** Hindari foto yang buram, goyang, atau terlalu gelap.\n"
                    . "3. **Fokus Infrastruktur:** Jangan ada objek manusia/selfie yang menghalangi kamera.\n"
                    . "4. **Sertakan Catatan Teknis:** Contoh: *Hotmix AC-WC tebal 5cm, luas 2x3m padat tuntas*.\n"
                    . "5. Foto yang jelas akan langsung disetujui tanpa perlu revisi teknis.",
                'draft' => [],
                'is_complete' => false,
            ];
        }

        // 14d. Petugas: Batas Maksimal 3 Tugas
        if (str_contains($lower, 'maksimal') || str_contains($lower, '3 tugas') || str_contains($lower, 'batas tugas') || str_contains($lower, 'kuota')) {
            return [
                'next_state' => self::STATE_START,
                'reply' => "⚠️ **Aturan Batas Maksimal 3 Tugas Aktif:**\n\n"
                    . "• Setiap pekerja lapangan dibatasi maksimal mengerjakan **3 tugas berstatus 'Sedang Dikerjakan'** secara bersamaan.\n"
                    . "• Jika kuota 3 tugas terpenuhi, sistem akan memblokir tombol *Mulai Kerja Lapangan* untuk tugas ke-4.\n"
                    . "• Selesaikan dan kirimkan bukti foto minimal salah satu tugas terlebih dahulu agar kuota aktif kembali tersedia.",
                'draft' => [],
                'is_complete' => false,
            ];
        }

        // 15. Tanya Panduan Umum Aplikasi
        if (str_contains($lower, 'panduan') || str_contains($lower, 'cara lapor') || str_contains($lower, 'bantuan') || str_contains($lower, 'menu')) {
            if ($role === 'PETUGAS') {
                return [
                    'next_state' => self::STATE_START,
                    'reply' => "📱 **Panduan Penggunaan Aplikasi untuk Pekerja Lapangan:**\n\n"
                        . "1. **Kotak Ringkasan (KPI):** Memantau Total Aduan, Menunggu Verifikasi, Ditugaskan, Dikerjakan, Menunggu Validasi, dan Tuntas se-Kabupaten.\n"
                        . "2. **Filter Tugas:** Beralih antara *Semua Tugas* (tugas tim) dan *Tugas Saya* (tugas yang sedang Anda kerjakan).\n"
                        . "3. **Mulai Kerja & Peta GIS:** Tekan kartu tugas untuk rute jalan nyata (OSRM) menuju koordinat GPS.\n"
                        . "4. **Kirim Bukti Selesai:** Ambil foto fisik tuntas dan isi catatan teknis melalui tombol *Selesai + Kirim Bukti*.\n"
                        . "5. **Validasi Operator:** Foto Anda akan diperiksa oleh Operator Dinas hingga tiket berstatus Tuntas.",
                    'draft' => [],
                    'is_complete' => false,
                ];
            }
            return [
                'next_state' => self::STATE_START,
                'reply' => "📖 **Panduan Penggunaan Aplikasi Pengaduan Kolaka Utara:**\n\n"
                    . "1. **Tombol (+) Lapor:** Tekan tombol di tengah bawah untuk membuat laporan baru.\n"
                    . "2. **Pilih Tipe Laporan:**\n"
                    . "   • *Kerusakan Ringan (< 500m)* untuk lubang/retak titik, lampu PJU, atau saluran drainase (1 titik GPS).\n"
                    . "   • *Kerusakan Berat (≥ 500m)* khusus jalan rusak kontinu (wajib 2 titik GPS: awal & akhir).\n"
                    . "3. **Ambil Foto Kerusakan:** AI Computer Vision otomatis memverifikasi kerusakan fasilitas.\n"
                    . "4. **Kirim & Pantau:** Pantau perkembangan tiket Anda di menu *Laporan Saya* atau tanyakan kepada saya dengan menyebut nomor tiket.\n\n"
                    . "Ketik **'Lapor'** jika ingin saya pandu membuat draf laporan sekarang!",
                'draft' => [],
                'is_complete' => false,
            ];
        }

        // 16. Tanya SOP Pekerja Lapangan & Teknis Pengerjaan
        if (str_contains($lower, 'sop') || str_contains($lower, 'spk') || str_contains($lower, 'pengerjaan') || str_contains($lower, 'tugas lapangan')) {
            return [
                'next_state' => self::STATE_START,
                'reply' => "🛠️ **Panduan Teknis & SOP Pekerja Lapangan PUPR:**\n\n"
                    . "1. **Penerimaan SPK Tugas:** Operator mendisposisikan tiket aduan ke akun Anda. Buka menu *Tugas Aktif* untuk melihat rincian lokasi GPS, foto kerusakan warga, dan batas waktu SLA.\n"
                    . "2. **Mulai Bekerja (`Mulai Kerjakan`):** Tekan tombol *Mulai Kerjakan* sesampainya di lokasi agar operator & warga mengetahui pengerjaan fisik sedang berjalan (`IN_PROGRESS`).\n"
                    . "3. **SOP Perbaikan Standar PUPR & Dishub:**\n"
                    . "   • **Lubang Jalan:** Bersihkan area lubang, potong tepi rapi (tack coat), padatkan dengan material hotmix/coldmix hingga rata permukaan jalan (Maksimal 48 jam).\n"
                    . "   • **Jembatan Darurat/Kritis:** Pasang rambu pengaman segera, lakukan perbaikan darurat lantai/gelagar (Prioritas Utama < 24 jam).\n"
                    . "   • **Lampu Jalan PJU:** Periksa grounding, kabel tiang, dan ganti armature LED/konektor rusak (Maksimal 72 jam).\n"
                    . "4. **Upload Bukti Selesai:** Ambil foto hasil perbaikan di lokasi yang sama dari sudut yang jelas dan sertakan catatan teknis penyelesaian.\n"
                    . "5. **Validasi Operator:** Status berubah menjadi `WAITING_VERIFICATION`. Setelah operator memverifikasi mutu fisik, tiket otomatis berpindah ke tab *Riwayat Selesai* (`COMPLETED`).",
                'draft' => [],
                'is_complete' => false,
            ];
        }

        // 17. Tanya Perhitungan Aspal & Material Tambal Sulam
        if (str_contains($lower, 'aspal') || str_contains($lower, 'hotmix') || str_contains($lower, 'coldmix') || str_contains($lower, 'tack coat') || str_contains($lower, 'tambal') || str_contains($lower, 'material') || str_contains($lower, 'hitung')) {
            return [
                'next_state' => self::STATE_START,
                'reply' => "🧮 **Perhitungan Kebutuhan Material Aspal (Patching):**\n\n"
                    . "• **Rumus Berat Hotmix:** `Panjang (m) × Lebar (m) × Tebal Padat (0,04 - 0,05 m) × Berat Jenis (2,2 - 2,3 ton/m³)`\n"
                    . "• **Simulasi Contoh:** Lubang ukuran 2m × 1m dengan tebal galian 5cm (0,05m):\n"
                    . "  -> Volume = 2 × 1 × 0,05 = **0,1 m³**\n"
                    . "  -> Kebutuhan Hotmix = 0,1 × 2,25 = **~0,225 ton (225 kg)**\n"
                    . "  -> Kebutuhan Lapis Ikat (Tack Coat): **0,3 s/d 0,5 Liter/m²**\n\n"
                    . "💡 *Pastikan suhu penghamparan hotmix minimal 120°C - 140°C dan padatkan dengan stamper/baby roller sampai tidak meninggalkan jejak roda!*",
                'draft' => [],
                'is_complete' => false,
            ];
        }

        // 18. Tanya Foto Bukti Penyelesaian Petugas
        if (str_contains($lower, 'foto bukti') || str_contains($lower, 'after') || str_contains($lower, 'bukti selesai')) {
            return [
                'next_state' => self::STATE_START,
                'reply' => "📸 **Ketentuan Foto Bukti Penyelesaian Lapangan (After-Work):**\n\n"
                    . "1. **Sudut Pengambilan Sama:** Arahkan kamera dari posisi dan sudut yang identik dengan foto kerusakan awal pelapor.\n"
                    . "2. **Pencahayaan Jelas:** Pastikan foto diambil dengan pencahayaan terang sehingga permukaan aspal rata, tiang PJU berdiri tegak, atau jembatan terlihat kokoh.\n"
                    . "3. **Kerapian Lokasi:** Singkirkan puing, sisa adukan, dan alat kerja sebelum memotret.\n"
                    . "4. **Catatan Teknis:** Sertakan ringkasan pekerjaan (misal: 'Penambalan lubang aspal volume 2x1m tebal 5cm, pemadatan rata, lalu lintas normal kembali').",
                'draft' => [],
                'is_complete' => false,
            ];
        }

        // 19. Tanya K3 & Keselamatan Kerja Petugas
        if (str_contains($lower, 'k3') || str_contains($lower, 'helm') || str_contains($lower, 'rompi') || str_contains($lower, 'safety') || str_contains($lower, 'rambu') || str_contains($lower, 'selamat')) {
            return [
                'next_state' => self::STATE_START,
                'reply' => "🛡️ **Standar K3 & Keselamatan Pekerja Lapangan PUPR:**\n\n"
                    . "1. **APD Wajib:** Rompi keselamatan reflektif (high-visibility), helm proyek (hard hat), sarung tangan kerja, dan sepatu safety.\n"
                    . "2. **Rambu Pengaman Jalan:** Pasang traffic cone dan rambu peringatan pekerjaan jalan minimal 50 meter sebelum titik penanganan.\n"
                    . "3. **Petugas Pemandu (Flagman):** Tugaskan 1 orang rekan untuk mengatur buka-tutup lalu lintas jika jalan sempit.\n"
                    . "4. **Kotak P3K & Hidrasi:** Selalu sediakan kotak P3K dan air minum cukup di kendaraan operasional tim teknis.",
                'draft' => [],
                'is_complete' => false,
            ];
        }

        // 20. Alur State Machine: Asistensi Interaktif Pembuatan Draf Laporan
        switch ($currentState) {
            case self::STATE_START:
                $isPetugas = strtoupper($role ?? '') === 'PETUGAS';

                if ($isPetugas) {
                    return [
                        'next_state' => self::STATE_START,
                        'reply' => "Halo Rekan Pekerja Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten Teknis SI-APPIK** Kabupaten Kolaka Utara. Siap memandu operasional aplikasi Anda:\n\n"
                            . "• 👷‍♂️ Ketik **'Apa Itu Pekerja Lapangan'** (peran, tugas, & wewenang)\n"
                            . "• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n"
                            . "• 📱 Ketik **'Tentang Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n"
                            . "• ✉️ Ketik **'Verifikasi OTP'** (panduan kode OTP & keamanan)\n"
                            . "• 🔑 Ketik **'Ganti Password'** (ubah & reset kata sandi akun)\n"
                            . "• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n"
                            . "• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota pengerjaan)\n"
                            . "• ⏱️ Ketik **'Batas Waktu (SLA)'** (standar tenggat waktu dinas)\n"
                            . "• 🔍 Masukkan nomor tiket (misal: **0005/ringan/2026**) untuk lacak status\n\n"
                            . "Ada yang perlu saya bantu untuk kelancaran tugas lapangan Anda hari ini?",
                        'draft' => [],
                        'is_complete' => false,
                    ];
                }

                if (str_contains($lower, 'lapor') || str_contains($lower, 'buat') || str_contains($lower, 'rusak') || str_contains($lower, 'aduan')) {
                    return [
                        'next_state' => self::STATE_IDENTIFY_TYPE,
                        'reply' => "Baik, saya siap membantu menyusun draf aduan Anda! 📋\n\n"
                            . "Pertama, pilih **Tipe Pelaporan** Anda:\n"
                            . "1️⃣ Ketik **'Ringan'** untuk Kerusakan Ringan (< 500 meter, misal: lubang jalan, lampu PJU, trotoar)\n"
                            . "2️⃣ Ketik **'Berat'** untuk Kerusakan Berat (≥ 500 meter, khusus jalan & jembatan)",
                        'draft' => $draftData,
                        'is_complete' => false,
                    ];
                }
                return [
                    'next_state' => self::STATE_START,
                    'reply' => "Halo! Saya **Asisten SI-APPIK** Kabupaten Kolaka Utara. Saya siap membantu Anda sebagai **Warga Terdaftar**:\n\n"
                        . "• 📱 Ketik **'Tentang Aplikasi'** untuk info menyeluruh & fitur unggulan sistem\n"
                        . "• ✉️ Ketik **'Cara Verifikasi OTP'** untuk panduan kode OTP pendaftaran & akun\n"
                        . "• 🔑 Ketik **'Ganti Password'** untuk panduan ubah & lupa kata sandi\n"
                        . "• 🛣️ Ketik **'Beda Tipe'** untuk memahami Kerusakan Ringan vs Kerusakan Berat\n"
                        . "• 📝 Ketik **'Panduan Lapor'** untuk tata cara pengaduan masyarakat\n"
                        . "• 🤖 Ketik **'AI'** untuk info Computer Vision & tips foto bukti\n"
                        . "• ⏱️ Ketik **'SLA'** untuk standar waktu penanganan dinas teknis\n"
                        . "• 📍 Ketik **'Wilayah'** untuk daftar 15 kecamatan Kolaka Utara\n"
                        . "• 🔍 Ketik nomor tiket (misal: **0005/ringan/2026**) untuk mengecek status terkini\n\n"
                        . "Ada yang ingin Anda tanyakan seputar aplikasi atau infrastruktur Kolaka Utara?",
                    'draft' => [],
                    'is_complete' => false,
                ];

            case self::STATE_IDENTIFY_TYPE:
                if (str_contains($lower, 'berat') || str_contains($lower, 'ruas') || str_contains($lower, 'panjang') || str_contains($lower, 'parah') || str_contains($lower, '2')) {
                    $draftData['damage_type'] = 'Kerusakan Berat (≥ 500m)';
                    $draftData['category'] = 'Jalan & Jembatan';
                    return [
                        'next_state' => self::STATE_COLLECT_DESCRIPTION,
                        'reply' => "Tipe terpilih: **Kerusakan Berat (≥ 500m)**.\nKategori otomatis dikunci ke: **Jalan & Jembatan** 🔒.\n\nBisa jelaskan detail kondisi kerusakan jalan tersebut? (Misal: aspal hancur, amblas di tanjakan, membahayakan truk/motor)",
                        'draft' => $draftData,
                        'is_complete' => false,
                    ];
                } else {
                    $draftData['damage_type'] = 'Kerusakan Ringan (< 500m)';
                    return [
                        'next_state' => self::STATE_IDENTIFY_CATEGORY,
                        'reply' => "Tipe terpilih: **Kerusakan Ringan (< 500m)**.\n\nFasilitas apa yang mengalami kerusakan? (Pilihan: **Jalan Berlubang**, **Jembatan**, **Lampu PJU**, atau **Trotoar & Saluran**)",
                        'draft' => $draftData,
                        'is_complete' => false,
                    ];
                }

            case self::STATE_IDENTIFY_CATEGORY:
                $draftData['category'] = $msg;
                return [
                    'next_state' => self::STATE_COLLECT_DESCRIPTION,
                    'reply' => "Kategori terpilih: **{$msg}**.\n\nBisa jelaskan detail kerusakannya? (Misal: kedalaman lubang, lampu padam total, membahayakan pengendara)",
                    'draft' => $draftData,
                    'is_complete' => false,
                ];

            case self::STATE_COLLECT_DESCRIPTION:
                $draftData['description'] = $msg;
                return [
                    'next_state' => self::STATE_COLLECT_LOCATION,
                    'reply' => "Detail kerusakan dicatat! 📍 Di mana lokasi persisnya? Sebutkan nama jalan, desa/kelurahan, dan kecamatan di Kolaka Utara:",
                    'draft' => $draftData,
                    'is_complete' => false,
                ];

            case self::STATE_COLLECT_LOCATION:
                $draftData['address'] = $msg;
                return [
                    'next_state' => self::STATE_CONFIRM,
                    'reply' => "Berikut rangkuman draf aduan Anda:\n\n"
                        . "🛣️ **Tipe Pelaporan:** {$draftData['damage_type']}\n"
                        . "📋 **Kategori:** {$draftData['category']}\n"
                        . "📝 **Deskripsi:** {$draftData['description']}\n"
                        . "📍 **Lokasi:** {$draftData['address']}\n\n"
                        . "Apakah Anda ingin membuka form pembuatan laporan dengan data ini? (Ketik **'Buka Form'** atau **'Batal'**)",
                    'draft' => $draftData,
                    'is_complete' => false,
                ];

            case self::STATE_CONFIRM:
                if (str_contains($lower, 'buka') || str_contains($lower, 'ya') || str_contains($lower, 'kirim') || str_contains($lower, 'lanjut')) {
                    return [
                        'next_state' => self::STATE_DONE,
                        'reply' => "Draf siap! Silakan tekan tombol **(+) Lapor** di menu tengah bawah untuk melengkapi foto bukti dan mengirim.",
                        'draft' => $draftData,
                        'is_complete' => true,
                    ];
                }
                return [
                    'next_state' => self::STATE_START,
                    'reply' => "Draf pembuatan aduan dibatalkan. Ada hal lain yang bisa saya bantu terkait infrastruktur Kabupaten Kolaka Utara?",
                    'draft' => [],
                    'is_complete' => false,
                ];

            default:
                return [
                    'next_state' => self::STATE_START,
                    'reply' => "Ada yang bisa saya bantu terkait fasilitas infrastruktur Kabupaten Kolaka Utara?",
                    'draft' => [],
                    'is_complete' => false,
                ];
        }
    }
}
