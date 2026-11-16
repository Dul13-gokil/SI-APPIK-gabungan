import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:lucide_icons_flutter/lucide_icons.dart';
import '../../../core/auth/auth_service.dart';
import '../../../core/network/api_client.dart';
import '../../../shared/theme/app_colors.dart';
import '../../../shared/widgets/formatted_markdown_text.dart';

class ChatMessageModel {
  final String text;
  final bool isUser;
  final DateTime time;

  ChatMessageModel({
    required this.text,
    required this.isUser,
    required this.time,
  });
}

class AIAssistantScreen extends StatefulWidget {
  const AIAssistantScreen({super.key});

  @override
  State<AIAssistantScreen> createState() => _AIAssistantScreenState();
}

class _AIAssistantScreenState extends State<AIAssistantScreen> {
  final ApiClient _apiClient = ApiClient();
  final AuthService _authService = AuthService();
  final TextEditingController _messageController = TextEditingController();
  final ScrollController _scrollController = ScrollController();

  final List<ChatMessageModel> _messages = [];
  String? _sessionId;
  bool _isLoading = false;
  bool _isPetugas = false;

  @override
  void initState() {
    super.initState();
    _checkRoleAndInit();
  }

  Future<void> _checkRoleAndInit() async {
    final user = await _authService.getCurrentUser();
    if (mounted) {
      setState(() {
        _isPetugas = user?.role == 'PETUGAS';
      });
    }
    await _initChatSession();
  }

  @override
  void dispose() {
    _messageController.dispose();
    _scrollController.dispose();
    super.dispose();
  }

  Future<void> _initChatSession() async {
    setState(() => _isLoading = true);
    try {
      final res = await _apiClient.dio.post('/chat/sessions', data: {
        'role': _isPetugas ? 'PETUGAS' : 'WARGA',
      });
      if (res.statusCode == 201 && res.data['data'] != null) {
        final data = res.data['data'];
        final backendMsg = data['initial_message'] as String?;
        // Deteksi jika role Petugas tapi pesan backend masih template lama Warga
        final isWrongRoleMsg = _isPetugas && (backendMsg == null || backendMsg.contains('Warga Terdaftar') || backendMsg.contains('Beda Tipe'));

        final initialText = isWrongRoleMsg
            ? "Halo Rekan Petugas Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten Teknis SI-APPIK** Kabupaten Kolaka Utara. Siap memandu operasional aplikasi Anda:\n\n• 👷‍♂️ Ketik **'Apa Itu Petugas Lapangan'** (peran, tugas, & wewenang)\n• 📋 Ketik **'Cara Mengambil Tugas'** (panduan ambil & mulai tugas SPK)\n• 📱 Ketik **'Tentang Aplikasi'** (cara kerja aplikasi & navigasi GIS)\n• ✉️ Ketik **'Verifikasi OTP'** (panduan kode OTP & keamanan)\n• 🔑 Ketik **'Ganti Password'** (ubah & reset kata sandi akun)\n• 📸 Ketik **'Tips Foto Bukti Validasi'** (ketentuan foto hasil perbaikan)\n• ⚠️ Ketik **'Batas Maksimal 3 Tugas'** (aturan kuota pengerjaan)\n• ⏱️ Ketik **'Batas Waktu Penanganan (SLA)'** (tenggat waktu standar dinas)\n• 🔍 Masukkan nomor tiket (misal: **0005/ringan/2026**) untuk lacak status\n\nAda yang perlu saya bantu untuk kelancaran tugas Anda hari ini?"
            : (backendMsg ??
                (_isPetugas
                    ? "Halo Rekan Petugas Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten Teknis SI-APPIK** Kabupaten Kolaka Utara. Siap memandu Anda seputar peran petugas lapangan, ambil tugas, panduan aplikasi, verifikasi OTP, ganti password, foto validasi, kuota 3 tugas, hingga SLA."
                    : "Halo! Saya **Asisten SI-APPIK** Kabupaten Kolaka Utara. Siap membantu Anda sebagai **Warga Terdaftar** terkait informasi aplikasi, verifikasi OTP pendaftaran, ganti password, panduan lapor Kerusakan Ringan vs Berat, verifikasi AI foto, pelacakan tiket aduan, hingga standar SLA dinas."));

        setState(() {
          _sessionId = data['session_id'].toString();
          _messages.add(
            ChatMessageModel(
              text: initialText,
              isUser: false,
              time: DateTime.now(),
            ),
          );
        });
      }
    } catch (_) {
      setState(() {
        _messages.add(
          ChatMessageModel(
            text: _isPetugas
                ? "Halo Rekan Petugas Lapangan Dinas PUPR! 👷‍♂️\nSaya **Asisten Teknis SI-APPIK** Kabupaten Kolaka Utara. Siap memandu Anda seputar peran petugas lapangan, ambil tugas, panduan aplikasi, verifikasi OTP, ganti password, foto validasi operator, batas 3 tugas aktif, atau cek tiket (misal: 0005/ringan/2026)."
                : "Halo! Saya **Asisten SI-APPIK** Kabupaten Kolaka Utara. Siap membantu Anda sebagai **Warga Terdaftar** terkait informasi aplikasi, verifikasi OTP pendaftaran, ganti password, panduan lapor Kerusakan Ringan vs Berat, tips foto AI, wilayah 15 kecamatan, atau masukkan nomor tiket (misal: 0005/ringan/2026).",
            isUser: false,
            time: DateTime.now(),
          ),
        );
      });
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  Future<void> _sendMessage([String? presetText]) async {
    final text = presetText ?? _messageController.text.trim();
    if (text.isEmpty) return;

    if (presetText == null) {
      _messageController.clear();
    }

    setState(() {
      _messages.add(
        ChatMessageModel(text: text, isUser: true, time: DateTime.now()),
      );
      _isLoading = true;
    });

    _scrollToBottom();

    try {
      if (_sessionId != null) {
        final res = await _apiClient.dio.post('/chat/sessions/$_sessionId/messages', data: {
          'message': text,
          'role': _isPetugas ? 'PETUGAS' : 'WARGA',
        });

        if (res.statusCode == 200 && res.data['data'] != null) {
          final reply = res.data['data']['reply'] ?? 'Terima kasih atas informasi Anda.';
          setState(() {
            _messages.add(
              ChatMessageModel(text: reply, isUser: false, time: DateTime.now()),
            );
          });
          return;
        }
      }

      // Offline / Direct Smart Responder Fallback
      await Future.delayed(const Duration(milliseconds: 600));
      final reply = _getSmartOfflineResponse(text);
      setState(() {
        _messages.add(
          ChatMessageModel(text: reply, isUser: false, time: DateTime.now()),
        );
      });
    } catch (e) {
      final reply = _getSmartOfflineResponse(text);
      setState(() {
        _messages.add(
          ChatMessageModel(
            text: reply,
            isUser: false,
            time: DateTime.now(),
          ),
        );
      });
    } finally {
      if (mounted) {
        setState(() => _isLoading = false);
        _scrollToBottom();
      }
    }
  }

  String _getSmartOfflineResponse(String input) {
    final lower = input.toLowerCase();

    // ─── Respon Khusus Petugas Lapangan ──────────────────────────────
    if (_isPetugas) {
      if (lower.contains('apa itu petugas') || lower.contains('petugas lapangan') || lower.contains('peran petugas') || lower.contains('tugas petugas') || lower.contains('siapa petugas')) {
        return '👷‍♂️ **Mengenal Peran Petugas Lapangan (Dinas PUPR & PRKP):**\n\n'
            'Petugas Lapangan adalah **tim teknis operasional resmi** Pemerintah Kabupaten Kolaka Utara yang bertugas langsung di lokasi kejadian untuk menangani perbaikan fisik infrastruktur publik berdasarkan Surat Perintah Kerja (SPK) dari Operator Dinas.\n\n'
            '🔑 **Wewenang & Fitur Khusus di Aplikasi Mobile:**\n'
            '1. **Menerima Disposisi Tugas:** Memantau SPK penugasan pada tab *Tugas Aktif* lengkap dengan data koordinat GPS satelit dan foto laporan warga.\n'
            '2. **Navigasi Rute Nyata GIS (OSRM):** Menghubungkan langsung ke rute jalan nyata menuju titik kerusakan.\n'
            '3. **Tombol "Mulai Kerja Lapangan":** Menandai mulainya pengerjaan fisik di lokasi sekaligus memulai pencatatan waktu SLA secara transparan.\n'
            '4. **Akses Kontak Pelapor:** Petugas berwenang melihat nomor telepon pelapor untuk keperluan konfirmasi lokasi darurat di lapangan.\n'
            '5. **Kirim Bukti Selesai:** Mengambil foto fisik hasil perbaikan (*After-Work*) dan melampirkan catatan teknis material kepada Operator Dinas.\n\n'
            '⚠️ **Aturan Disiplin Petugas:**\n'
            '• **Batas Kuota:** Maksimal mengerjakan **3 tugas aktif** secara bersamaan agar mutu pekerjaan terjaga.\n'
            '• **Isolasi Hak Akses (Team Shield):** Hanya petugas/regu yang ditugaskan resmi yang berhak membuka modul kerja dan mengunggah bukti pengerjaan.\n'
            '• **Validasi Mutu:** Pekerjaan tidak langsung selesai tuntas, melainkan diverifikasi terlebih dahulu oleh Operator Dinas.';
      }

      if (lower.contains('otp') || lower.contains('kode otp') || lower.contains('verifikasi otp') || lower.contains('cara otp')) {
        return '✉️ **Panduan Kode OTP untuk Petugas Lapangan:**\n\n'
            'Kode OTP (*One-Time Password*) adalah 6 digit kode keamanan rahasia yang dikirimkan ke email resmi akun petugas Anda.\n\n'
            '1. **Fungsi OTP Petugas:** Digunakan untuk verifikasi identitas saat melakukan **Reset / Lupa Kata Sandi** secara mandiri di aplikasi mobile.\n'
            '2. **Cara Menggunakan:**\n'
            '   • Pada halaman login aplikasi, tekan **"Lupa Kata Sandi?"**.\n'
            '   • Masukkan alamat email akun petugas Anda.\n'
            '   • Buka kotak masuk email (atau folder Spam) dan salin 6 digit kode OTP.\n'
            '   • Masukkan kode OTP di aplikasi mobile, lalu tentukan kata sandi baru Anda.\n'
            '3. **Keamanan:** Jangan berikan kode OTP kepada siapa pun demi integritas data laporan daerah.';
      }

      if (lower.contains('password') || lower.contains('sandi') || lower.contains('ganti password') || lower.contains('lupa sandi') || lower.contains('reset')) {
        return '🔑 **Panduan Mengubah & Mengatur Ulang Kata Sandi Akun Petugas:**\n\n'
            '1. **Jika Sudah Login ke Aplikasi:**\n'
            '   • Masuk ke tab **Profil** di pojok kanan bawah.\n'
            '   • Pilih menu **"Ubah Profil & Kata Sandi"**.\n'
            '   • Masukkan kata sandi lama, lalu ketik kata sandi baru (minimal 8 karakter) dan konfirmasi kata sandi baru.\n'
            '   • Tekan **"Simpan Perubahan"**.\n\n'
            '2. **Jika Lupa Kata Sandi di Halaman Login:**\n'
            '   • Tekan tautan **"Lupa Kata Sandi?"** pada layar login.\n'
            '   • Masukkan email akun Anda, tunggu 6 digit kode OTP masuk ke email.\n'
            '   • Masukkan kode OTP di aplikasi mobile dan masukkan kata sandi baru Anda.\n\n'
            '💡 Meskipun akun Petugas dibuat oleh Superadmin Dinas, Anda memiliki kebebasan penuh mengelola kata sandi sendiri!';
      }

      if (lower.contains('tentang aplikasi') || lower.contains('apa itu aplikasi') || lower.contains('info aplikasi')) {
        return '📱 **Tentang Aplikasi Petugas Lapangan Kolaka Utara:**\n\n'
            'Aplikasi ini adalah modul kerja terpadu untuk tim teknis lapangan Dinas PUPR & PRKP Kabupaten Kolaka Utara.\n\n'
            '🌟 **Kemampuan Sistem:**\n'
            '• **Penerimaan SPK Real-Time:** Notifikasi penugasan langsung dari Operator Dinas.\n'
            '• **Navigasi GIS:** Peta digital interaktif dengan rute jalan nyata OSRM ke titik koordinat kerusakan.\n'
            '• **Mulai Kerja Transparan:** Pencatatan jam mulai kerja aktual untuk perhitungan SLA dinas.\n'
            '• **Modul Kamera Bukti:** Unggah foto fisik perbaikan *after-work* beserta catatan spesifikasi material.\n'
            '• **Akses Kontak Pelapor:** Mendukung panggilan darurat langsung ke pelapor warga demi kepastian lokasi.';
      }

      if (lower.contains('ambil') || lower.contains('ambil tugas') || lower.contains('tugas baru') || lower.contains('mulai kerja')) {
        return '📋 **Panduan Mengambil & Memulai Tugas Lapangan:**\n\n'
            '1. **Buka Tab Tugas Aktif:** Pilih menu Tugas Aktif untuk melihat daftar SPK penugasan dari Operator.\n'
            '2. **Pilih Penugasan:** Ketuk kartu tugas yang ingin Anda kerjakan atau pilih tab "Tugas Saya".\n'
            '3. **Tekan "Mulai Kerja Lapangan":** Tombol ini langsung mencatat waktu resmi mulai kerja dan membuka peta navigasi GIS ke titik lokasi kerusakan.\n'
            '4. **Perhatikan Batas Kuota:** Setiap petugas dibatasi maksimal mengerjakan **3 tugas aktif** secara bersamaan agar penyelesaian tetap fokus dan tepat waktu (SLA).';
      }

      if (lower.contains('pakai') || lower.contains('aplikasi') || lower.contains('panduan') || lower.contains('cara kerja')) {
        return '📱 **Panduan Penggunaan Aplikasi Petugas Lapangan:**\n\n'
            '1. **Pantau SPK:** Tab Tugas Aktif menampilkan ringkasan tugas: Total Aduan, Menunggu Verifikasi, Ditugaskan, Dikerjakan, Menunggu Validasi, dan Tuntas.\n'
            '2. **Tab Semua Tugas vs Tugas Saya:** Gunakan tab untuk beralih antara melihat seluruh tugas tim atau penugasan khusus akun Anda.\n'
            '3. **Navigasi GIS:** Tekan kartu tugas untuk melihat rute jalan nyata (OSRM) menuju lokasi koordinat satelit.\n'
            '4. **Kirim Bukti:** Setelah penanganan selesai, tekan tombol **"Selesai + Kirim Bukti"** untuk mengambil foto fisik dan mengirimkan catatan teknis ke Operator.\n'
            '5. **Validasi:** Operator Dinas akan meninjau foto dan mengubah status menjadi Tuntas.';
      }

      if (lower.contains('foto') || lower.contains('validasi') || lower.contains('bukti') || lower.contains('kamera')) {
        return '📸 **Tips Foto Bukti yang Baik untuk Validasi Operator:**\n\n'
            '1. **Sudut & Ketinggian:** Ambil foto dengan sudut yang memperlihatkan permukaan perbaikan yang telah rata dan menyatu dengan bidang jalan atau fasilitas umum.\n'
            '2. **Pencahayaan Jelas:** Pastikan pencahayaan terang dan foto tidak blur/goyang.\n'
            '3. **Bebas Hambatan:** Hindari objek manusia/selfie di depan kamera, pastikan fokus pada hasil fisik infrastruktur.\n'
            '4. **Catatan Teknis Ringkas:** Sertakan informasi material (contoh: "Penambalan hotmix AC-WC 2x3m padat tuntas").\n'
            '5. Foto yang valid akan mempercepat Operator menyelesaikan status tiket Anda.';
      }

      if (lower.contains('3') || lower.contains('maksimal') || lower.contains('batas tugas') || lower.contains('kuota')) {
        return '⚠️ **Ketentuan Batas Maksimal 3 Tugas Lapangan:**\n\n'
            '• Setiap petugas lapangan hanya dapat memiliki maksimal **3 tugas berstatus "Sedang Dikerjakan"** secara bersamaan.\n'
            '• Aturan ini diterapkan oleh Dinas PUPR agar waktu penanganan (SLA) tetap terjamin dan kualitas fisik maksimal.\n'
            '• Jika Anda telah mencapai kuota 3 tugas, selesaikan dan kirimkan bukti salah satu tugas terlebih dahulu sebelum memulai tugas baru.';
      }

      if (lower.contains('sop') || lower.contains('tambal') || lower.contains('lubang') || lower.contains('aspal') || lower.contains('hotmix') || lower.contains('coldmix')) {
        return '🚧 **SOP Teknis Penambalan Jalan Berlubang (PUPR):**\n\n'
            '1. **Pemasangan Rambu:** Pasang kerucut pengaman (traffic cone) minimal 25m sebelum titik kerja.\n'
            '2. **Marking & Pemotongan:** Tandai batas lubang berbentuk persegi, potong tepi vertikal menggunakan asphalt cutter.\n'
            '3. **Pembersihan & Pengeringan:** Bersihkan debu, kotoran, dan genangan air dengan kompresor/sapu kawat.\n'
            '4. **Lapis Perekat (Tack Coat):** Semprotkan aspal emulsi secara merata pada dasar dan tepi galian.\n'
            '5. **Penghamparan Aspal:** Hampar Hotmix AC-WC (atau Coldmix darurat) dengan ketebalan padat minimal 4 cm.\n'
            '6. **Pemadatan (Compaction):** Gilas dengan baby roller / stamper sampai rata (flush) dengan aspal eksisting.\n'
            '7. **Foto Hasil Akhir:** Ambil foto bukti tuntas & kirim ke Operator via tombol "Selesai + Kirim Bukti".';
      }

      if (lower.contains('pju') || lower.contains('lampu') || lower.contains('dishub') || lower.contains('kabel')) {
        return '💡 **SOP Teknis Perbaikan Penerangan Jalan Umum (PJU):**\n\n'
            '1. **K3 Kelistrikan:** Gunakan sarung tangan isolasi tegangan tinggi (rubber gloves) & sabuk pengaman tiang.\n'
            '2. **Pengecekan Sumber Daya:** Cek tegangan MCB pada box panel meteran & fungsi photo-cell otomatis.\n'
            '3. **Inspeksi Armatur:** Periksa kondisi driver LED, kabel instalasi tiang, dan grounding penangkal petir.\n'
            '4. **Penggantian Komponen:** Ganti lampu atau komponen rusak dengan standar dinas (LED 60W/90W/120W).\n'
            '5. **Uji Nyala:** Lakukan testing pencahayaan untuk memastikan lampu menyala terang & stabil.';
      }

      if (lower.contains('jembatan') || lower.contains('darurat') || lower.contains('kritis')) {
        return '🌉 **SOP Penanganan Darurat Kerusakan Jembatan:**\n\n'
            '1. **Batas Waktu (SLA):** Wajib ditangani dalam **maksimal 24 jam**.\n'
            '2. **Pengamanan Jalur:** Pasang barikade dan rambu pembatas tonase jika expansion joint / abutment retak parah.\n'
            '3. **Inspeksi Visual Cepat:** Dokumentasikan kondisi gelagar, lantai jembatan, dan oprit penopang.\n'
            '4. **Koordinasi URC Dinas:** Laporkan segera ke Koordinator URC Jalan & Jembatan untuk pengerahan alat berat.';
      }

      if (lower.contains('sla') || lower.contains('waktu') || lower.contains('tenggat')) {
        return '⏱️ **Standar Batas Waktu Kerja Lapangan (SLA):**\n\n'
            '• **Lubang Jalan / Aspal:** Maksimal **48 Jam** (2 Hari Kerja).\n'
            '• **Jembatan Darurat / Kritis:** Maksimal **24 Jam** (Prioritas Darurat).\n'
            '• **Lampu Jalan PJU:** Maksimal **72 Jam** (3 Hari Kerja).\n'
            '• Pastikan menekan **"Mulai Kerja Lapangan"** saat tiba agar waktu terekam transparan.';
      }

      if (lower.contains('aspal') || lower.contains('hitung') || lower.contains('material') || lower.contains('volume')) {
        return '🧮 **Perhitungan Kebutuhan Aspal (Patching):**\n\n'
            '• **Rumus:** `Panjang (m) × Lebar (m) × Tebal (m) × Berat Jenis (2.25 ton/m³)`\n'
            '• **Contoh:** Lubang 2m × 1m tebal 5cm (0.05m):\n'
            '  -> Volume = 2 × 1 × 0.05 = **0.1 m³**\n'
            '  -> Hotmix = 0.1 × 2.25 = **~225 kg (0.225 ton)**\n'
            '  -> Tack Coat: **0.3 - 0.5 Liter/m²**\n\n'
            'Gilas dengan baby roller / stamper sampai padat rata.';
      }

      if (lower.contains('k3') || lower.contains('helm') || lower.contains('rompi') || lower.contains('safety')) {
        return '🛡️ **Standar K3 Petugas Lapangan PUPR:**\n\n'
            '1. **APD Wajib:** Rompi reflektif, helm proyek, sarung tangan, & safety boots.\n'
            '2. **Rambu Pengaman:** Pasang traffic cone minimal 50m sebelum titik kerja.\n'
            '3. **Flagman:** Siagakan 1 petugas pengatur buka-tutup jalur.\n'
            '4. **P3K:** Siapkan kotak P3K & air minum di kendaraan tim.';
      }
    }

    // ─── Respon Umum Warga & Pertanyaan Aplikasi ─────────────────────

    // Tanya Tentang Aplikasi
    if (lower.contains('tentang aplikasi') || lower.contains('apa itu aplikasi') || lower.contains('seputar aplikasi') || lower.contains('info aplikasi')) {
      return '📱 **Tentang Aplikasi Pengaduan Infrastruktur Kolaka Utara:**\n\n'
          'Aplikasi ini merupakan platform digital resmi Pemerintah Kabupaten Kolaka Utara (Dinas PUPR & PRKP) untuk partisipasi aktif warga dalam mempercepat penanganan fasilitas umum rusak.\n\n'
          '🌟 **Fitur-Fitur Utama:**\n'
          '• 🤖 **AI Computer Vision (YOLOv8):** Memeriksa foto kerusakan (lubang jalan, retakan, lampu PJU) dan mendeteksi kondisi jalan secara otomatis.\n'
          '• 🛣️ **2 Skema Laporan:** Kerusakan Ringan (< 500m, 1 titik GPS) dan Kerusakan Berat (≥ 500m, rute 2 titik GPS awal & akhir).\n'
          '• 📍 **Geofencing Kabupaten:** Menolak otomatis lokasi di luar batas administratif 15 kecamatan Kolaka Utara.\n'
          '• ⏱️ **Transparansi SLA:** Batas waktu penanganan terukur (Jembatan 24 jam, Jalan berlubang 48 jam, Lampu PJU 72 jam).\n'
          '• 🔒 **Privasi Terjaga:** Nomor HP pelapor disamarkan untuk sesama warga demi kenyamanan privasi.\n'
          '• 💬 **Asisten SI-APPIK:** Siap memandu pembuatan laporan dan melacak status tiket kapan saja.';
    }

    // Tanya Cara Verifikasi OTP
    if (lower.contains('otp') || lower.contains('kode otp') || lower.contains('verifikasi otp') || lower.contains('cara otp')) {
      return '✉️ **Panduan Lengkap Verifikasi Kode OTP:**\n\n'
          'Kode OTP (*One-Time Password*) adalah 6 digit kode keamanan rahasia yang dikirimkan ke email terdaftar Anda.\n\n'
          '1. **Untuk Pendaftaran Akun Warga Baru:**\n'
          '   • Isi form registrasi (Nama, Email, Nomor HP, Password).\n'
          '   • Sistem mengirimkan 6 digit kode OTP ke email Anda.\n'
          '   • Masukkan kode OTP di aplikasi mobile sebelum waktu habis (5 menit).\n'
          '   • Akun Anda langsung aktif sebagai **Warga Terdaftar**.\n\n'
          '2. **Untuk Lupa / Reset Password:**\n'
          '   • Tekan **"Lupa Kata Sandi?"** pada halaman login.\n'
          '   • Masukkan email akun terdaftar Anda.\n'
          '   • Salin 6 digit kode OTP dari email, lalu masukkan kata sandi baru.\n\n'
          '💡 **Tips:** Periksa folder Spam/Junk jika kode belum muncul di kotak masuk utama!';
    }

    // Tanya Ganti Password & Lupa Sandi
    if (lower.contains('password') || lower.contains('sandi') || lower.contains('ganti password') || lower.contains('lupa sandi') || lower.contains('ubah sandi') || lower.contains('reset')) {
      return '🔑 **Panduan Mengubah & Mengatur Ulang Kata Sandi:**\n\n'
          '1. **Jika Sudah Login ke Aplikasi:**\n'
          '   • Masuk ke tab **Profil** di pojok kanan bawah.\n'
          '   • Pilih menu **"Ubah Profil & Kata Sandi"**.\n'
          '   • Masukkan kata sandi lama, lalu tentukan kata sandi baru (minimal 8 karakter) dan konfirmasi kata sandi baru.\n'
          '   • Tekan tombol **"Simpan Perubahan"**.\n\n'
          '2. **Jika Lupa Sandi di Halaman Login:**\n'
          '   • Tekan tautan **"Lupa Kata Sandi?"** pada form login.\n'
          '   • Masukkan email terdaftar Anda, lalu cek 6 digit kode OTP di email.\n'
          '   • Masukkan kode OTP di aplikasi mobile dan masukkan kata sandi baru Anda.';
    }

    // Tanya Mengenai Petugas Lapangan (Dari Sudut Pandang Warga)
    if (lower.contains('apa itu petugas') || lower.contains('petugas lapangan') || lower.contains('siapa yang perbaiki') || lower.contains('siapa petugas')) {
      return '👷‍♂️ **Siapa Itu Petugas Lapangan?**\n\n'
          'Petugas Lapangan adalah **tim teknis operasional resmi** dari Dinas PUPR & PRKP Kabupaten Kolaka Utara yang diterjunkan ke lokasi untuk melakukan perbaikan fisik infrastruktur.\n\n'
          '• **Menerima SPK:** Mendapat perintah kerja resmi setelah laporan Anda diverifikasi oleh Operator Dinas.\n'
          '• **Navigasi Langsung:** Datang ke lokasi titik kerusakan berpandukan koordinat GPS di aplikasi.\n'
          '• **Pengerjaan Fisik:** Melakukan penambalan jalan aspal, penggantian lampu PJU, atau perbaikan darurat jembatan.\n'
          '• **Unggah Bukti Hasil:** Mengunggah foto hasil pekerjaan fisik (*After-Work*) untuk divalidasi mutunya oleh dinas sebelum tiket ditutup tuntas.';
    }

    // Tanya Privasi Nomor Pelapor
    if (lower.contains('nomor') && (lower.contains('privasi') || lower.contains('pelapor') || lower.contains('telepon') || lower.contains('sembunyi') || lower.contains('tampil'))) {
      return '🔒 **Kebijakan Privasi Nomor Telepon Pelapor:**\n\n'
          '• **Untuk Warga Lain:** Nomor HP pelapor disamarkan (contoh: *******890) dan dikunci dengan gembok privasi.\n'
          '• **Untuk Pelapor Sendiri:** Anda dapat melihat nomor telepon Anda sendiri pada detail laporan Anda.\n'
          '• **Untuk Petugas Lapangan & Operator:** Nomor telepon pelapor dapat dilihat secara utuh agar petugas dapat menghubungi Anda jika membutuhkan konfirmasi patokan lokasi di lapangan.';
    }

    if (lower.contains('beda') || lower.contains('ruas') || lower.contains('titik') || lower.contains('500') || lower.contains('panjang') || lower.contains('ringan') || lower.contains('berat')) {
      return '🛣️ **Perbedaan 2 Tipe Pelaporan Kerusakan:**\n\n'
          '1. **Kerusakan Ringan (< 500m):**\n'
          '• Untuk lubang/retak jalan lokal, lampu PJU padam, atau fasilitas umum.\n'
          '• Cukup tentukan **1 titik koordinat GPS** di peta.\n'
          '• Didukung AI YOLOv8 mendeteksi lubang, retakan, lampu, fasilitas umum, dan jalan mulus.\n\n'
          '2. **Kerusakan Berat (≥ 500m):**\n'
          '• Khusus jalan rusak parah/panjang berskala masif (≥ 500 meter).\n'
          '• **Terkunci khusus kategori Jalan & Jembatan** 🔒.\n'
          '• Wajib menentukan **2 titik GPS**: Titik Awal Ruas & Titik Akhir Ruas.\n'
          '• Estimasi panjang dihitung otomatis dari rute jalan nyata (road routing), bukan diketik manual.';
    }

    if (lower.contains('foto') || lower.contains('ai') || lower.contains('kamera') || lower.contains('vision')) {
      return '🤖 **AI Computer Vision & Tips Foto Bukti:**\n\n'
          '• Sistem menggunakan multi-model AI YOLOv8 ONNX untuk mendeteksi lubang (potholes), retakan aspal RDD2022, lampu jalan, dan kondisi mulus.\n'
          '• Otomatis menolak foto bukan infrastruktur (wajah orang, selfie, hewan, makanan, kendaraan).\n'
          '• **Tips:** Ambil foto di tempat terang, arahkan kamera langsung ke kerusakan jalan, dan hindari objek manusia di depan kamera.';
    }

    if (lower.contains('sla') || lower.contains('lama') || lower.contains('alur') || lower.contains('proses')) {
      return '⏱️ **Standar Waktu Penanganan (SLA Dinas):**\n\n'
          '1. **Verifikasi Operator:** < 2 Jam Kerja.\n'
          '2. **Jalan Rusak / Lubang:** Maksimal 48 Jam (2 Hari Kerja).\n'
          '3. **Jembatan Darurat / Kritis:** Maksimal 24 Jam (Prioritas Darurat).\n'
          '4. **Lampu Jalan PJU:** Maksimal 72 Jam (3 Hari Kerja).\n'
          '5. **Kerusakan Berat (Ruas Panjang):** Penanganan darurat dan masuk program rehabilitasi berkala.';
    }

    if (lower.contains('wilayah') || lower.contains('kolaka') || lower.contains('kecamatan') || lower.contains('geofence')) {
      return '📍 **Wilayah Layanan Kabupaten Kolaka Utara:**\n\n'
          'Meliputi 15 kecamatan: Lasusua, Wawo, Ngapa, Kodeoha, Pakue, Pakue Tengah, Pakue Utara, Tiwu, Tolala, Ranteangin, Lambai, Katoi, Porehu, Watunohu, dan Batu Putih.\n'
          'Dilengkapi Geofencing: Pelaporan di luar batas wilayah Kolaka Utara tidak dapat diproses.';
    }

    if (lower.contains('profil') || lower.contains('akun') || lower.contains('username') || lower.contains('notifikasi')) {
      return '👤 **Fitur Profil & Akun Pengguna:**\n\n'
          'Di menu Profil, Anda dapat:\n'
          '• **Ubah Profil & Kata Sandi:** Ganti nama/username, email, nomor HP, dan password baru.\n'
          '• **Preview Akun:** Lihat kartu identitas resmi sebagai Warga Terdaftar.\n'
          '• **Foto Profil:** Ubah avatar dari Kamera atau Galeri ponsel.\n'
          '• **Pengaturan Notifikasi:** Atur preferensi push notification status aduan & info dinas.';
    }

    if (lower.contains('kontak') || lower.contains('darurat') || lower.contains('112') || lower.contains('telepon')) {
      return '🚨 **Kontak Darurat Kabupaten Kolaka Utara:**\n\n'
          '• **Call Center Darurat:** **112** (Siaga 24 Jam Bebas Pulsa)\n'
          '• **Dinas PUPR (Operator):** (0405) 2321100\n\n'
          'Gunakan nomor darurat di atas untuk kondisi genting yang memerlukan pengamanan kilat di jalan raya.';
    }

    if (lower.contains('adu-') || lower.contains('tiket') || lower.contains('spk') || lower.contains('lacak') || RegExp(r'\d{4}\/(?:ringan|berat)\/\d{4}').hasMatch(lower)) {
      return '🔍 **Pelacakan Tiket Aduan:**\n\n'
          'Untuk melacak tiket aduan atau SPK secara real-time, pastikan Anda terhubung ke server lalu ketik kode tiket lengkap Anda (contoh: **0005/ringan/2026** atau **0001/berat/2026**).\n'
          'Sistem akan menampilkan status penanganan, kategori, lokasi, dan tanggal laporan!';
    }

    return _isPetugas
        ? 'Halo Rekan Petugas Lapangan Dinas PUPR! 👷‍♂️\n'
            'Saya **Asisten Teknis SI-APPIK** Kabupaten Kolaka Utara. Anda dapat menanyakan seputar operasional aplikasi:\n\n'
            '• 👷‍♂️ **Apa Itu Petugas Lapangan** (peran, tugas & wewenang)\n'
            '• 📋 **Cara Mengambil Tugas** (panduan ambil & mulai tugas SPK)\n'
            '• 📱 **Tentang Aplikasi** (fitur aplikasi & navigasi GIS)\n'
            '• ✉️ **Verifikasi OTP** (panduan OTP & keamanan akun)\n'
            '• 🔑 **Ganti Password** (ubah & reset kata sandi mandiri)\n'
            '• 📸 **Tips Foto Bukti Validasi** (ketentuan foto hasil perbaikan)\n'
            '• ⚠️ **Batas Maksimal 3 Tugas** (aturan kuota pengerjaan)\n'
            '• ⏱️ **Batas Waktu (SLA)** (tenggat waktu standar dinas)\n'
            '• 🔍 **Lacak tiket aduan** (ketik nomor tiket, misal: **0005/ringan/2026**)'
        : 'Halo! Saya **Asisten SI-APPIK** Kabupaten Kolaka Utara. Anda dapat menanyakan:\n\n'
            '• 📱 **Tentang Aplikasi** (fitur unggulan & sistem pengaduan)\n'
            '• ✉️ **Cara Verifikasi OTP** (kode OTP pendaftaran & akun)\n'
            '• 🔑 **Lupa / Ganti Password** (ubah & reset kata sandi)\n'
            '• 🛣️ **Beda Kerusakan Ringan vs Berat** (titik vs ruas jalan)\n'
            '• 🤖 **Tips Foto AI Computer Vision** (deteksi otomatis foto)\n'
            '• ⏱️ **Standar waktu pengerjaan dinas (SLA)**\n'
            '• 📍 **Batas wilayah 15 Kecamatan Kolaka Utara**\n'
            '• 🔍 **Cek nomor tiket** (misal: **0005/ringan/2026**)\n'
            '• 🚨 **Kontak Darurat 112**';
  }

  void _scrollToBottom() {
    Future.delayed(const Duration(milliseconds: 100), () {
      if (_scrollController.hasClients) {
        _scrollController.animateTo(
          _scrollController.position.maxScrollExtent,
          duration: const Duration(milliseconds: 250),
          curve: Curves.easeOut,
        );
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(6),
              decoration: BoxDecoration(
                color: AppColors.primary.withValues(alpha: 0.1),
                shape: BoxShape.circle,
              ),
              child: Icon(_isPetugas ? LucideIcons.hardHat : LucideIcons.sparkles, color: AppColors.primary, size: 20),
            ),
            const SizedBox(width: 10),
            Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  _isPetugas ? 'Asisten Teknis SI-APPIK' : 'Asisten SI-APPIK',
                  style: GoogleFonts.outfit(
                    fontWeight: FontWeight.bold,
                    fontSize: 15,
                    color: AppColors.textPrimary,
                  ),
                ),
                Text(
                  _isPetugas ? 'Operasional Petugas Dinas PUPR' : 'Dukungan Warga Kolaka Utara',
                  style: GoogleFonts.inter(
                    fontSize: 11,
                    color: AppColors.textSecondary,
                  ),
                ),
              ],
            ),
          ],
        ),
        backgroundColor: Colors.white,
        elevation: 0,
      ),
      body: Column(
        children: [
          // Quick suggestion chips
          Container(
            padding: const EdgeInsets.symmetric(vertical: 8, horizontal: 12),
            color: Colors.white,
            child: SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              child: Row(
                children: _isPetugas
                    ? [
                        _buildQuickChip('Apa Itu Petugas Lapangan'),
                        _buildQuickChip('Cara Mengambil Tugas'),
                        _buildQuickChip('Tentang Aplikasi'),
                        _buildQuickChip('Verifikasi OTP'),
                        _buildQuickChip('Ganti Password'),
                        _buildQuickChip('Tips Foto Bukti Validasi'),
                        _buildQuickChip('Batas Maksimal 3 Tugas'),
                        _buildQuickChip('Batas Waktu Penanganan (SLA)'),
                        _buildQuickChip('SOP Penambalan Aspal'),
                        _buildQuickChip('Lacak tiket aduan'),
                      ]
                    : [
                        _buildQuickChip('Tentang Aplikasi'),
                        _buildQuickChip('Cara Verifikasi OTP'),
                        _buildQuickChip('Lupa / Ganti Password'),
                        _buildQuickChip('Panduan Cara Lapor'),
                        _buildQuickChip('Beda Kerusakan Ringan vs Berat'),
                        _buildQuickChip('Tips Foto AI Computer Vision'),
                        _buildQuickChip('Berapa lama penanganan (SLA)?'),
                        _buildQuickChip('15 Kecamatan Kolaka Utara'),
                        _buildQuickChip('Lacak tiket aduan'),
                        _buildQuickChip('Kontak Darurat 112'),
                      ],
              ),
            ),
          ),
          const Divider(height: 1),

          // Chat Messages List
          Expanded(
            child: ListView.builder(
              controller: _scrollController,
              padding: const EdgeInsets.all(16),
              itemCount: _messages.length,
              itemBuilder: (context, index) {
                final msg = _messages[index];
                return _buildChatBubble(msg);
              },
            ),
          ),

          if (_isLoading)
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 8),
              child: Row(
                children: [
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(14),
                      border: Border.all(color: AppColors.border),
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        const SizedBox(width: 14, height: 14, child: CircularProgressIndicator(strokeWidth: 2)),
                        const SizedBox(width: 8),
                        Text('Menyiapkan panduan...', style: GoogleFonts.inter(fontSize: 12, color: AppColors.textSecondary)),
                      ],
                    ),
                  ),
                ],
              ),
            ),

          // Message Input Field
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: Colors.white,
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withValues(alpha: 0.05),
                  blurRadius: 10,
                  offset: const Offset(0, -2),
                ),
              ],
            ),
            child: SafeArea(
              child: Row(
                children: [
                  Expanded(
                    child: TextField(
                      controller: _messageController,
                      decoration: InputDecoration(
                        hintText: 'Tanyakan sesuatu atau masukkan kode tiket...',
                        hintStyle: const TextStyle(fontSize: 13),
                        filled: true,
                        fillColor: AppColors.background,
                        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(24),
                          borderSide: BorderSide.none,
                        ),
                      ),
                      onSubmitted: (_) => _sendMessage(),
                    ),
                  ),
                  const SizedBox(width: 8),
                  CircleAvatar(
                    backgroundColor: AppColors.primary,
                    radius: 22,
                    child: IconButton(
                      icon: const Icon(LucideIcons.send, color: Colors.white, size: 18),
                      onPressed: () => _sendMessage(),
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildQuickChip(String text) {
    return Padding(
      padding: const EdgeInsets.only(right: 8),
      child: ActionChip(
        label: Text(text, style: GoogleFonts.inter(fontSize: 11, fontWeight: FontWeight.w600, color: AppColors.primary)),
        backgroundColor: AppColors.primary.withValues(alpha: 0.08),
        side: BorderSide(color: AppColors.primary.withValues(alpha: 0.2)),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        onPressed: () => _sendMessage(text),
      ),
    );
  }

  Widget _buildChatBubble(ChatMessageModel msg) {
    final isUser = msg.isUser;
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Row(
        mainAxisAlignment: isUser ? MainAxisAlignment.end : MainAxisAlignment.start,
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          if (!isUser) ...[
            CircleAvatar(
              backgroundColor: AppColors.primary,
              radius: 16,
              child: Icon(_isPetugas ? LucideIcons.hardHat : LucideIcons.bot, color: Colors.white, size: 18),
            ),
            const SizedBox(width: 8),
          ],
          Flexible(
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
              decoration: BoxDecoration(
                color: isUser ? AppColors.primary : Colors.white,
                borderRadius: BorderRadius.only(
                  topLeft: const Radius.circular(18),
                  topRight: const Radius.circular(18),
                  bottomLeft: Radius.circular(isUser ? 18 : 4),
                  bottomRight: Radius.circular(isUser ? 4 : 18),
                ),
                border: isUser ? null : Border.all(color: AppColors.border),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withValues(alpha: 0.04),
                    blurRadius: 6,
                    offset: const Offset(0, 2),
                  ),
                ],
              ),
              child: FormattedMarkdownText(
                msg.text,
                style: GoogleFonts.inter(
                  color: isUser ? Colors.white : AppColors.textPrimary,
                  fontSize: 13,
                  height: 1.45,
                ),
                boldStyle: GoogleFonts.inter(
                  color: isUser ? Colors.white : AppColors.textPrimary,
                  fontWeight: FontWeight.w700,
                  fontSize: 13,
                  height: 1.45,
                ),
              ),
            ),
          ),
          if (isUser) const SizedBox(width: 8),
        ],
      ),
    );
  }
}
