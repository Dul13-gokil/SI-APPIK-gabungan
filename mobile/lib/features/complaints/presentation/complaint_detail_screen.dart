import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:lucide_icons_flutter/lucide_icons.dart';
import 'package:intl/intl.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../../core/auth/auth_service.dart';
import '../../../core/config/app_config.dart';
import '../../../core/network/api_client.dart';
import '../../../shared/models/complaint_model.dart';
import '../../../shared/theme/app_colors.dart';
import '../../../shared/widgets/status_badge.dart';
import '../../../shared/widgets/v2_components.dart';
import '../../../shared/widgets/v2_toast.dart';

class ComplaintDetailScreen extends StatefulWidget {
  final int complaintId;

  const ComplaintDetailScreen({super.key, required this.complaintId});

  @override
  State<ComplaintDetailScreen> createState() => _ComplaintDetailScreenState();
}

class _ComplaintDetailScreenState extends State<ComplaintDetailScreen> {
  final _apiClient = ApiClient();
  final _authService = AuthService();
  final PageController _photoPageController = PageController();
  final PageController _completionPageController = PageController();

  UserModel? _currentUser;
  ComplaintModel? _complaint;
  List<dynamic> _timeline = [];
  bool _isLoading = true;
  String? _errorMessage;
  bool _isSupporting = false;
  int _selectedRating = 0;
  bool _isSubmittingRating = false;
  final TextEditingController _ratingCommentCtrl = TextEditingController();
  int _currentPhotoIndex = 0;
  int _currentCompletionIndex = 0;

  @override
  void initState() {
    super.initState();
    _loadUserAndDetail();
  }

  @override
  void dispose() {
    _photoPageController.dispose();
    _completionPageController.dispose();
    _ratingCommentCtrl.dispose();
    super.dispose();
  }

  Future<void> _loadUserAndDetail() async {
    final user = await _authService.getCurrentUser();
    if (mounted) setState(() => _currentUser = user);
    await _fetchDetail();
  }

  Future<void> _fetchDetail() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final response = await _apiClient.dio.get('/complaints/${widget.complaintId}');
      final timelineRes = await _apiClient.dio.get('/complaints/${widget.complaintId}/timeline');

      if (response.statusCode == 200 && response.data['data'] != null) {
        setState(() {
          _complaint = ComplaintModel.fromJson(response.data['data']);
          _timeline = timelineRes.data['data'] ?? [];
          _isLoading = false;
        });
      }
    } catch (e) {
      setState(() {
        _errorMessage = 'Gagal memuat detail aduan: $e';
        _isLoading = false;
      });
    }
  }

  Future<void> _handleToggleSupport() async {
    if (_complaint == null || _isSupporting) return;

    setState(() => _isSupporting = true);

    try {
      final response = await _apiClient.dio.post('/complaints/${_complaint!.id}/supports');
      if (response.statusCode == 200 && response.data['data'] != null) {
        final supported = response.data['data']['supported'] ?? false;
        final count = response.data['data']['supports_count'] ?? _complaint!.supportsCount;

        setState(() {
          _complaint = _complaint!.copyWith(
            supportsCount: count,
            hasSupported: supported,
          );
        });

        if (mounted) {
          showV2Toast(
            context,
            message: supported ? 'Dukungan berhasil ditambahkan 👍' : 'Dukungan dibatalkan',
            type: supported ? V2ToastType.success : V2ToastType.info,
            duration: const Duration(seconds: 2),
          );
        }
      }
    } catch (e) {
      if (mounted) {
        showV2Toast(
          context,
          message: 'Gagal memberikan dukungan: $e',
          type: V2ToastType.error,
        );
      }
    } finally {
      if (mounted) setState(() => _isSupporting = false);
    }
  }

  Future<void> _openWhatsApp(String? phone, {String? defaultMessage}) async {
    if (phone == null || phone.trim().isEmpty) return;
    String clean = phone.replaceAll(RegExp(r'[^0-9]'), '');
    if (clean.startsWith('0')) {
      clean = '62${clean.substring(1)}';
    } else if (clean.startsWith('8')) {
      clean = '62$clean';
    }
    final text = defaultMessage != null ? Uri.encodeComponent(defaultMessage) : '';
    final url = 'https://wa.me/$clean${text.isNotEmpty ? '?text=$text' : ''}';
    final uri = Uri.parse(url);
    try {
      if (await canLaunchUrl(uri)) {
        await launchUrl(uri, mode: LaunchMode.externalApplication);
      } else {
        await launchUrl(uri);
      }
    } catch (_) {
      if (mounted) {
        Clipboard.setData(ClipboardData(text: phone));
        showV2Toast(
          context,
          message: 'Nomor $phone disalin ke clipboard',
          type: V2ToastType.info,
        );
      }
    }
  }

  Future<void> _callPhone(String? phone) async {
    if (phone == null || phone.trim().isEmpty) return;
    final cleanPhone = phone.replaceAll(RegExp(r'[^0-9+]'), '');
    final uri = Uri.parse('tel:$cleanPhone');
    try {
      if (await canLaunchUrl(uri)) {
        await launchUrl(uri);
      } else {
        await launchUrl(uri, mode: LaunchMode.externalApplication);
      }
    } catch (_) {
      if (mounted) {
        Clipboard.setData(ClipboardData(text: phone));
        showV2Toast(
          context,
          message: 'Nomor $phone disalin ke clipboard',
          type: V2ToastType.info,
        );
      }
    }
  }

  void _copyToClipboard(String text, String label) {
    Clipboard.setData(ClipboardData(text: text));
    showV2Toast(
      context,
      message: '$label berhasil disalin ke clipboard',
      type: V2ToastType.success,
      duration: const Duration(seconds: 2),
    );
  }

  Future<void> _confirmCancelComplaint() async {
    final reasonCtrl = TextEditingController();
    final confirm = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Row(
          children: [
            Icon(LucideIcons.xCircle, color: AppColors.statusRejected, size: 22),
            SizedBox(width: 8),
            Text('Batalkan Laporan', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Laporan yang dibatalkan tidak akan diproses oleh dinas dan akan tercatat pada riwayat "Dibatalkan" di Aduan Saya.',
              style: TextStyle(fontSize: 13, color: AppColors.textSecondary),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: reasonCtrl,
              maxLines: 2,
              decoration: InputDecoration(
                hintText: 'Alasan pembatalan (opsional)...',
                hintStyle: const TextStyle(fontSize: 12.5, color: AppColors.textSecondary),
                filled: true,
                fillColor: AppColors.background,
                contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(10),
                  borderSide: const BorderSide(color: AppColors.border),
                ),
                enabledBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(10),
                  borderSide: const BorderSide(color: AppColors.border),
                ),
              ),
            ),
          ],
        ),
        actionsPadding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
        actions: [
          Row(
            children: [
              Expanded(
                child: OutlinedButton(
                  onPressed: () => Navigator.pop(ctx, false),
                  style: OutlinedButton.styleFrom(
                    foregroundColor: AppColors.textSecondary,
                    side: const BorderSide(color: AppColors.border),
                    padding: const EdgeInsets.symmetric(vertical: 11),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                  ),
                  child: const Text('Kembali', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: ElevatedButton(
                  onPressed: () => Navigator.pop(ctx, true),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppColors.statusRejected,
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 11),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                  ),
                  child: const Text('Batalkan', style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold)),
                ),
              ),
            ],
          ),
        ],
      ),
    );

    final reasonText = reasonCtrl.text.trim();
    reasonCtrl.dispose();

    if (confirm != true) return;

    setState(() => _isLoading = true);

    try {
      final res = await _apiClient.dio.post(
        '/complaints/${widget.complaintId}/cancel',
        data: {
          if (reasonText.isNotEmpty) 'reason': reasonText,
        },
      );
      if (res.statusCode == 200) {
        if (!mounted) return;
        final rootCtx = Navigator.of(context, rootNavigator: true).context;
        Navigator.pop(context, true);
        if (rootCtx.mounted) {
          showV2Toast(
            rootCtx,
            message: 'Laporan berhasil dibatalkan.',
            type: V2ToastType.success,
          );
        }
      }
    } catch (e) {
      if (mounted) {
        setState(() => _isLoading = false);
        showV2Toast(
          context,
          message: 'Gagal membatalkan laporan: $e',
          type: V2ToastType.error,
        );
      }
    }
  }

  void _showPhotoDialog(BuildContext context, String imageUrl, String title) {
    showDialog(
      context: context,
      builder: (ctx) => Dialog(
        backgroundColor: Colors.transparent,
        insetPadding: const EdgeInsets.all(16),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Expanded(
                  child: Text(
                    title,
                    style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 14),
                  ),
                ),
                IconButton(
                  icon: const Icon(LucideIcons.x, color: Colors.white),
                  onPressed: () => Navigator.of(ctx).pop(),
                ),
              ],
            ),
            const SizedBox(height: 8),
            ClipRRect(
              borderRadius: BorderRadius.circular(16),
              child: Image.network(
                imageUrl,
                fit: BoxFit.contain,
                loadingBuilder: (c, child, progress) {
                  if (progress == null) return child;
                  return Container(
                    height: 250,
                    color: Colors.black26,
                    child: const Center(child: CircularProgressIndicator(color: Colors.white)),
                  );
                },
                errorBuilder: (_, __, ___) => Container(
                  height: 200,
                  color: Colors.black45,
                  child: const Center(
                    child: Icon(LucideIcons.imageOff, color: Colors.white, size: 40),
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  bool get _canCancel {
    if (_currentUser == null || _complaint == null) return false;
    if (_complaint!.status != 'SUBMITTED') {
      return false;
    }
    final isOwner = _complaint!.userId != null && _complaint!.userId == _currentUser!.id;
    return isOwner;
  }

  String _formatStatusTimeline(String? rawStatus) {
    if (rawStatus == null) return 'Pembaruan Status';
    switch (rawStatus.toUpperCase()) {
      case 'SUBMITTED':
      case 'VERIFICATION':
        return 'Tahap Verifikasi';
      case 'VERIFIED':
        return 'Laporan Terverifikasi';
      case 'DISPATCHED':
        return 'Ditugaskan ke Petugas';
      case 'IN_PROGRESS':
        return 'Pengerjaan di Lapangan';
      case 'WAITING_VERIFICATION':
        return 'Tahap Validasi Mutu';
      case 'REVISION_REQUIRED':
        return 'Perlu Revisi Teknis';
      case 'COMPLETED':
      case 'CLOSED':
        return 'Penanganan Tuntas Selesai';
      case 'REJECTED':
        return 'Laporan Ditolak';
      case 'CANCELLED':
        return 'Laporan Dibatalkan';
      default:
        return rawStatus;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: buildV2AppBar(
        title: 'Detail Laporan',
        subtitle: _complaint != null ? '#${_complaint!.publicCode}' : null,
        automaticallyImplyLeading: true,
        actions: [
          if (_canCancel)
            IconButton(
              icon: const Icon(LucideIcons.xCircle, color: AppColors.statusRejected, size: 20),
              tooltip: 'Batalkan Laporan Ini',
              onPressed: _confirmCancelComplaint,
            ),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _errorMessage != null
              ? Center(
                  child: Padding(
                    padding: const EdgeInsets.all(24),
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        const Icon(LucideIcons.alertTriangle, size: 48, color: AppColors.statusRejected),
                        const SizedBox(height: 12),
                        Text(_errorMessage!, textAlign: TextAlign.center),
                        const SizedBox(height: 16),
                        ElevatedButton(onPressed: _fetchDetail, child: const Text('Coba Lagi')),
                      ],
                    ),
                  ),
                )
              : _buildContent(),
    );
  }

  Widget _buildContent() {
    final c = _complaint!;
    final dateFormat = DateFormat('d MMMM yyyy, HH:mm', 'id_ID');

    return SingleChildScrollView(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // ─── 1. TOP BAR: TIPE KERUSAKAN, KATEGORI & STATUS (SELARAS) ───
          _buildTopBarBadges(c),
          const SizedBox(height: 12),

          // ─── 2. FOTO BUKTI KERUSAKAN (ATAS) ───
          _buildBeforePhotoSection(c),
          const SizedBox(height: 12),

          // ─── 3. FOTO BUKTI PENANGANAN LAPANGAN (BAWAH) ───
          if (c.completionPhotos.isNotEmpty || c.status == 'WAITING_VERIFICATION') ...[
            _buildAfterPhotoSection(c),
            const SizedBox(height: 12),
          ],

          // ─── 4. INFORMASI UTAMA LAPORAN (KODE, JUDUL, TANGGAL, DESKRIPSI) ───
          _buildMainInfoCard(c, dateFormat),
          const SizedBox(height: 12),

          // ─── 5. IDENTITAS PELAPOR ADUAN ───
          _buildReporterCard(c),
          const SizedBox(height: 12),

          // ─── 6. REGU PELAKSANA LAPANGAN & KETUA REGU (KOORDINATOR LAPANGAN) ───
          if (_hasTeamOrAssignee(c)) ...[
            _buildTeamCard(c),
            const SizedBox(height: 12),
          ],

          // ─── 7. LOKASI KERUSAKAN & DATA TEKNIS ───
          _buildLocationAndTechnicalCard(c),
          const SizedBox(height: 12),

          // ─── 8. BATAS WAKTU PENGERJAAN (SLA) (Di Bawah Lokasi Kerusakan) ───
          // Catatan: Pada tahap Verifikasi / Draft / Ditolak / Dibatalkan, penugasan teknis belum diterbitkan sehingga belum ada batas waktu pengerjaan
          if (!const {'SUBMITTED', 'VERIFICATION', 'VERIFIED', 'DRAFT', 'REJECTED', 'CANCELLED'}.contains(c.status.toUpperCase())) ...[
            _buildDeadlineCard(c),
            const SizedBox(height: 12),
          ],

          // ─── 9. DUKUNGAN MASYARAKAT (UPVOTE) ───
          // Hanya warga (CITIZEN) yang dapat mendukung, dan disembunyikan jika laporan sudah Selesai / Dibatalkan / Ditolak
          if (_currentUser != null &&
              const {'CITIZEN', 'WARGA', 'USER'}.contains(_currentUser!.role.toUpperCase()) &&
              !const {'COMPLETED', 'CLOSED', 'CANCELLED', 'REJECTED'}.contains(c.status.toUpperCase())) ...[
            _buildSupportButton(c),
            const SizedBox(height: 14),
          ],

          // ─── 9B. SURVEI KEPUASAN WARGA PASCA-PERBAIKAN (RATING 1-5 BINTANG) ───
          if (c.status == 'COMPLETED' || c.status == 'CLOSED') ...[
            _buildCitizenRatingCard(c),
            const SizedBox(height: 14),
          ],

          // ─── 10. ALUR TINDAK LANJUT PENANGANAN (TIMELINE) ───
          _buildTimelineCard(),
          const SizedBox(height: 30),
        ],
      ),
    );
  }

  /// Top Bar Badges: Status -> Tipe Kerusakan -> Kategori (Penuh Kiri-Kanan & Terpusat di Tengah)
  Widget _buildTopBarBadges(ComplaintModel c) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: AppColors.border),
      ),
      child: Center(
        child: FittedBox(
          fit: BoxFit.scaleDown,
          child: Row(
            mainAxisSize: MainAxisSize.min,
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              // Status (Pertama Paling Kiri)
              StatusBadge(status: c.status),
              const SizedBox(width: 6),
              // Tipe Kerusakan
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                decoration: BoxDecoration(
                  color: c.damageType == 'major' ? AppColors.damageTypeMajorBg : AppColors.damageTypeMinorBg,
                  borderRadius: BorderRadius.circular(8),
                  border: Border.all(
                    color: c.damageType == 'major' ? AppColors.damageTypeMajorBorder : AppColors.damageTypeMinorBorder,
                  ),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(
                      c.damageType == 'major' ? LucideIcons.gitCommit : LucideIcons.mapPin,
                      size: 11,
                      color: c.damageType == 'major' ? AppColors.damageTypeMajor : AppColors.damageTypeMinor,
                    ),
                    const SizedBox(width: 4),
                    Text(
                      c.damageType == 'major' ? 'Kerusakan Berat' : 'Kerusakan Ringan',
                      style: TextStyle(
                        color: c.damageType == 'major' ? AppColors.damageTypeMajor : AppColors.damageTypeMinor,
                        fontWeight: FontWeight.bold,
                        fontSize: 10.5,
                      ),
                    ),
                  ],
                ),
              ),
              // Kategori
              if (c.categoryName != null) ...[
                const SizedBox(width: 6),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(
                    color: const Color(0xFFF1F5F9),
                    borderRadius: BorderRadius.circular(8),
                    border: Border.all(color: const Color(0xFFE2E8F0)),
                  ),
                  child: Text(
                    c.categoryName!,
                    style: const TextStyle(
                      color: Color(0xFF334155),
                      fontWeight: FontWeight.w600,
                      fontSize: 10.5,
                    ),
                  ),
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }

  /// Foto Bukti Kerusakan Awal (Bagian Atas)
  Widget _buildBeforePhotoSection(ComplaintModel c) {
    if (c.photos.isEmpty) {
      return Container(
        height: 120,
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: AppColors.border),
        ),
        child: const Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(LucideIcons.image, size: 32, color: AppColors.textSecondary),
              SizedBox(height: 6),
              Text('Tidak ada lampiran foto bukti kerusakan', style: TextStyle(color: AppColors.textSecondary, fontSize: 12)),
            ],
          ),
        ),
      );
    }

    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.border),
      ),
      padding: const EdgeInsets.all(12),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Banner Foto Utama
          Stack(
            children: [
              ClipRRect(
                borderRadius: BorderRadius.circular(14),
                child: SizedBox(
                  height: 220,
                  child: PageView.builder(
                    controller: _photoPageController,
                    itemCount: c.photos.length,
                    onPageChanged: (idx) => setState(() => _currentPhotoIndex = idx),
                    itemBuilder: (context, index) {
                      final url = AppConfig.fixMediaUrl(c.photos[index]);
                      return GestureDetector(
                        onTap: () => _showPhotoDialog(
                          context,
                          url,
                          'Foto Bukti Kerusakan (${index + 1}/${c.photos.length})',
                        ),
                        child: Image.network(
                          url,
                          fit: BoxFit.cover,
                          width: double.infinity,
                          loadingBuilder: (ctx, child, progress) {
                            if (progress == null) return child;
                            return const Center(child: CircularProgressIndicator(strokeWidth: 2));
                          },
                          errorBuilder: (ctx, err, stack) => Container(
                            color: Colors.grey.shade100,
                            child: const Center(
                              child: Column(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  Icon(LucideIcons.imageOff, color: AppColors.textSecondary, size: 36),
                                  SizedBox(height: 4),
                                  Text('Gagal memuat foto', style: TextStyle(fontSize: 11, color: AppColors.textSecondary)),
                                ],
                              ),
                            ),
                          ),
                        ),
                      );
                    },
                  ),
                ),
              ),
              // Label Overlay di Kiri Bawah
              Positioned(
                bottom: 10,
                left: 10,
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                  decoration: BoxDecoration(
                    color: Colors.black.withValues(alpha: 0.75),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: const Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(LucideIcons.image, color: Colors.white, size: 12),
                      SizedBox(width: 5),
                      Text(
                        'Foto Bukti Kerusakan',
                        style: TextStyle(color: Colors.white, fontSize: 10.5, fontWeight: FontWeight.bold),
                      ),
                    ],
                  ),
                ),
              ),
              // Counter Overlay di Kanan Bawah
              if (c.photos.length > 1)
                Positioned(
                  bottom: 10,
                  right: 10,
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                    decoration: BoxDecoration(
                      color: Colors.black.withValues(alpha: 0.75),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Text(
                      '${_currentPhotoIndex + 1} / ${c.photos.length} (Geser)',
                      style: const TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.bold),
                    ),
                  ),
                ),
            ],
          ),
          // Thumbnails Strip jika ada lebih dari 1 foto
          if (c.photos.length > 1) ...[
            const SizedBox(height: 10),
            SizedBox(
              height: 48,
              child: ListView.separated(
                scrollDirection: Axis.horizontal,
                itemCount: c.photos.length,
                separatorBuilder: (_, __) => const SizedBox(width: 8),
                itemBuilder: (context, idx) {
                  final isSelected = idx == _currentPhotoIndex;
                  final url = AppConfig.fixMediaUrl(c.photos[idx]);
                  return GestureDetector(
                    onTap: () {
                      _photoPageController.animateToPage(
                        idx,
                        duration: const Duration(milliseconds: 300),
                        curve: Curves.easeInOut,
                      );
                    },
                    child: Container(
                      width: 54,
                      height: 48,
                      decoration: BoxDecoration(
                        borderRadius: BorderRadius.circular(8),
                        border: Border.all(
                          color: isSelected ? AppColors.primary : AppColors.border,
                          width: isSelected ? 2 : 1,
                        ),
                      ),
                      clipBehavior: Clip.antiAlias,
                      child: Image.network(url, fit: BoxFit.cover),
                    ),
                  );
                },
              ),
            ),
          ],
        ],
      ),
    );
  }

  /// Foto Bukti Penanganan Pekerja Lapangan (Bagian Bawah)
  Widget _buildAfterPhotoSection(ComplaintModel c) {
    if (c.completionPhotos.isEmpty) {
      if (c.status == 'WAITING_VERIFICATION') {
        return Container(
          padding: const EdgeInsets.all(14),
          decoration: BoxDecoration(
            color: const Color(0xFFFEF3C7),
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: const Color(0xFFF59E0B).withValues(alpha: 0.4)),
          ),
          child: const Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Icon(LucideIcons.clock3, size: 20, color: Color(0xFFD97706)),
              SizedBox(width: 10),
              Expanded(
                child: Text(
                  'Pekerjaan fisik telah selesai dikerjakan oleh pekerja lapangan dan saat ini sedang dalam tahap inspeksi mutu oleh operator dinas sebelum ditutup tuntas.',
                  style: TextStyle(fontSize: 12, color: Color(0xFF92400E), height: 1.4, fontWeight: FontWeight.w500),
                ),
              ),
            ],
          ),
        );
      }
      return const SizedBox.shrink();
    }

    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: const Color(0xFFF0FDF4),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFF86EFAC)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Header Baris Bukti Penyelesaian
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Row(
                children: [
                  Icon(LucideIcons.checkCircle2, size: 18, color: Color(0xFF16A34A)),
                  SizedBox(width: 6),
                  Text(
                    'Bukti Penyelesaian Lapangan',
                    style: TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.bold,
                      color: Color(0xFF15803D),
                    ),
                  ),
                ],
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: const Color(0xFF16A34A),
                  borderRadius: BorderRadius.circular(6),
                ),
                child: Text(
                  c.status == 'WAITING_VERIFICATION' ? 'Menunggu Validasi' : 'Tuntas Selesai',
                  style: const TextStyle(color: Colors.white, fontSize: 9.5, fontWeight: FontWeight.bold),
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),

          // Banner Foto Utama Hasil Pengerjaan
          Stack(
            children: [
              ClipRRect(
                borderRadius: BorderRadius.circular(14),
                child: SizedBox(
                  height: 220,
                  child: PageView.builder(
                    controller: _completionPageController,
                    itemCount: c.completionPhotos.length,
                    onPageChanged: (idx) => setState(() => _currentCompletionIndex = idx),
                    itemBuilder: (context, index) {
                      final url = AppConfig.fixMediaUrl(c.completionPhotos[index]);
                      return GestureDetector(
                        onTap: () => _showPhotoDialog(
                          context,
                          url,
                          'Bukti Pengerjaan Lapangan (${index + 1}/${c.completionPhotos.length})',
                        ),
                        child: Image.network(
                          url,
                          fit: BoxFit.cover,
                          width: double.infinity,
                          loadingBuilder: (ctx, child, progress) {
                            if (progress == null) return child;
                            return const Center(child: CircularProgressIndicator(strokeWidth: 2));
                          },
                          errorBuilder: (ctx, err, stack) => Container(
                            color: Colors.green.shade100,
                            child: const Center(
                              child: Column(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  Icon(LucideIcons.imageOff, color: Color(0xFF4B7A55), size: 36),
                                  SizedBox(height: 4),
                                  Text('Gagal memuat foto hasil', style: TextStyle(fontSize: 11, color: Color(0xFF4B7A55))),
                                ],
                              ),
                            ),
                          ),
                        ),
                      );
                    },
                  ),
                ),
              ),
              // Label Overlay di Kiri Bawah
              Positioned(
                bottom: 10,
                left: 10,
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                  decoration: BoxDecoration(
                    color: const Color(0xFF064E3B).withValues(alpha: 0.85),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: const Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(LucideIcons.badgeCheck, color: Color(0xFF86EFAC), size: 12),
                      SizedBox(width: 5),
                      Text(
                        'Hasil Fisik Selesai',
                        style: TextStyle(color: Colors.white, fontSize: 10.5, fontWeight: FontWeight.bold),
                      ),
                    ],
                  ),
                ),
              ),
              // Counter Overlay di Kanan Bawah
              if (c.completionPhotos.length > 1)
                Positioned(
                  bottom: 10,
                  right: 10,
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                    decoration: BoxDecoration(
                      color: Colors.black.withValues(alpha: 0.75),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Text(
                      '${_currentCompletionIndex + 1} / ${c.completionPhotos.length}',
                      style: const TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.bold),
                    ),
                  ),
                ),
            ],
          ),
          // Thumbnails Strip
          if (c.completionPhotos.length > 1) ...[
            const SizedBox(height: 10),
            SizedBox(
              height: 48,
              child: ListView.separated(
                scrollDirection: Axis.horizontal,
                itemCount: c.completionPhotos.length,
                separatorBuilder: (_, __) => const SizedBox(width: 8),
                itemBuilder: (context, idx) {
                  final isSelected = idx == _currentCompletionIndex;
                  final url = AppConfig.fixMediaUrl(c.completionPhotos[idx]);
                  return GestureDetector(
                    onTap: () {
                      _completionPageController.animateToPage(
                        idx,
                        duration: const Duration(milliseconds: 300),
                        curve: Curves.easeInOut,
                      );
                    },
                    child: Container(
                      width: 54,
                      height: 48,
                      decoration: BoxDecoration(
                        borderRadius: BorderRadius.circular(8),
                        border: Border.all(
                          color: isSelected ? const Color(0xFF16A34A) : const Color(0xFF86EFAC),
                          width: isSelected ? 2 : 1,
                        ),
                      ),
                      clipBehavior: Clip.antiAlias,
                      child: Image.network(url, fit: BoxFit.cover),
                    ),
                  );
                },
              ),
            ),
          ],
        ],
      ),
    );
  }

  /// Informasi Utama Aduan (Kode, Judul, Tanggal, Deskripsi)
  Widget _buildMainInfoCard(ComplaintModel c, DateFormat dateFormat) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.border),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Baris Kode Tiket & Tombol Salin
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Row(
                children: [
                  V2TicketCodeChip(code: c.publicCode),
                  const SizedBox(width: 6),
                  InkWell(
                    onTap: () => _copyToClipboard(c.publicCode, 'Kode Laporan'),
                    borderRadius: BorderRadius.circular(6),
                    child: const Padding(
                      padding: EdgeInsets.all(4),
                      child: Icon(LucideIcons.copy, size: 13, color: AppColors.textSecondary),
                    ),
                  ),
                ],
              ),
            ],
          ),
          const SizedBox(height: 10),

          // Judul Laporan
          Text(
            c.title,
            style: GoogleFonts.outfit(
              fontSize: 17,
              fontWeight: FontWeight.bold,
              color: AppColors.textPrimary,
              height: 1.3,
            ),
          ),
          const SizedBox(height: 6),

          // Tanggal Laporan (Di Bawah Judul & Di Atas Deskripsi - Mencegah Overflow)
          Row(
            children: [
              const Icon(LucideIcons.calendar, size: 12, color: AppColors.textSecondary),
              const SizedBox(width: 5),
              Text(
                dateFormat.format(c.createdAt),
                style: const TextStyle(fontSize: 11.5, color: AppColors.textSecondary, fontWeight: FontWeight.w500),
              ),
            ],
          ),
          const SizedBox(height: 10),

          // Deskripsi
          Text(
            c.description,
            style: const TextStyle(
              fontSize: 13,
              color: AppColors.textSecondary,
              height: 1.5,
            ),
          ),
        ],
      ),
    );
  }

  String _maskPhoneNumber(String phone) {
    final clean = phone.replaceAll(RegExp(r'[^0-9]'), '');
    if (clean.length <= 3) return clean;
    final last3 = clean.substring(clean.length - 3);
    return '••••••••$last3';
  }

  /// Identitas Pelapor Aduan
  Widget _buildReporterCard(ComplaintModel c) {
    final phone = c.reporterPhone;
    final hasPhone = phone != null && phone.trim().isNotEmpty;
    final isOfficer = _currentUser != null && _currentUser!.role == 'PETUGAS';
    final isReporterSelf = _currentUser != null && c.userId != null && _currentUser!.id == c.userId;
    final canSeeFullPhone = isOfficer || isReporterSelf;

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: AppColors.border),
      ),
      child: Row(
        children: [
          CircleAvatar(
            radius: 16,
            backgroundColor: AppColors.primary.withValues(alpha: 0.1),
            child: const Icon(LucideIcons.user, size: 16, color: AppColors.primary),
          ),
          const SizedBox(width: 10),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'Pelapor Aduan',
                  style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: AppColors.textSecondary),
                ),
                Text(
                  c.reporterName,
                  style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
              ],
            ),
          ),
          if (c.isAnonymous)
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3),
              decoration: BoxDecoration(
                color: Colors.grey.shade100,
                borderRadius: BorderRadius.circular(6),
              ),
              child: const Text('Anonim', style: TextStyle(fontSize: 10, color: Colors.grey, fontWeight: FontWeight.bold)),
            )
          else if (hasPhone)
            if (canSeeFullPhone)
              InkWell(
                onTap: () => _openWhatsApp(
                  phone,
                  defaultMessage: 'Halo, saya menghubungi terkait pengaduan infrastruktur #${widget.complaintId} di Kolaka Utara.',
                ),
                borderRadius: BorderRadius.circular(8),
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(
                    color: const Color(0xFFECFDF5),
                    borderRadius: BorderRadius.circular(8),
                    border: Border.all(color: const Color(0xFFA7F3D0)),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      const Icon(LucideIcons.messageSquare, size: 11, color: Color(0xFF059669)),
                      const SizedBox(width: 4),
                      Text(
                        phone,
                        style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Color(0xFF059669), fontFamily: 'monospace'),
                      ),
                      const SizedBox(width: 5),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 1),
                        decoration: BoxDecoration(
                          color: const Color(0xFF059669),
                          borderRadius: BorderRadius.circular(4),
                        ),
                        child: const Text('WA', style: TextStyle(fontSize: 8, color: Colors.white, fontWeight: FontWeight.bold)),
                      ),
                    ],
                  ),
                ),
              )
            else
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                decoration: BoxDecoration(
                  color: Colors.grey.shade50,
                  borderRadius: BorderRadius.circular(8),
                  border: Border.all(color: AppColors.border),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    const Icon(LucideIcons.lock, size: 11, color: AppColors.textSecondary),
                    const SizedBox(width: 4),
                    Text(
                      _maskPhoneNumber(phone),
                      style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: AppColors.textSecondary, fontFamily: 'monospace'),
                    ),
                  ],
                ),
              ),
        ],
      ),
    );
  }

  bool _hasTeamOrAssignee(ComplaintModel c) {
    return c.teamName != null ||
        c.teamLeaderName != null ||
        c.assigneeName != null ||
        ['DISPATCHED', 'IN_PROGRESS', 'WAITING_VERIFICATION', 'COMPLETED', 'CLOSED'].contains(c.status);
  }

  /// Regu Lapangan Pelaksana & Koordinator Lapangan
  Widget _buildTeamCard(ComplaintModel c) {
    final leaderName = c.teamLeaderName ?? c.assigneeName ?? 'Pekerja Lapangan';
    final leaderPhone = c.teamLeaderPhone ?? c.effectiveAssigneePhone;
    final teamName = c.teamName ?? 'Regu Lapangan PUPR';

    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: AppColors.primaryContainer,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: AppColors.primaryBorder),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Header Baris Regu (Selaras Web V2)
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                width: 40,
                height: 40,
                decoration: BoxDecoration(
                  color: AppColors.primary,
                  borderRadius: BorderRadius.circular(12),
                ),
                child: const Icon(LucideIcons.hardHat, size: 20, color: Colors.white),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'REGU LAPANGAN PELAKSANA',
                      style: GoogleFonts.inter(
                        fontSize: 10,
                        fontWeight: FontWeight.w800,
                        color: AppColors.primary,
                        letterSpacing: 0.5,
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      teamName,
                      style: GoogleFonts.outfit(
                        fontSize: 14.5,
                        fontWeight: FontWeight.bold,
                        color: AppColors.textPrimary,
                      ),
                    ),
                    Text(
                      'Dinas Pekerjaan Umum dan Penataan Ruang (PUPR)',
                      style: GoogleFonts.inter(
                        fontSize: 11,
                        color: AppColors.textSecondary,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),

          // Card Putih Kontak Ketua Regu (Wrap agar aman di layar sempit)
          Container(
            width: double.infinity,
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
            decoration: BoxDecoration(
              color: AppColors.surface,
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: AppColors.primaryBorder),
            ),
            child: Wrap(
              alignment: WrapAlignment.spaceBetween,
              crossAxisAlignment: WrapCrossAlignment.center,
              spacing: 8,
              runSpacing: 8,
              children: [
                Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    const CircleAvatar(
                      radius: 13,
                      backgroundColor: AppColors.primaryContainer,
                      child: Icon(LucideIcons.crown, size: 13, color: AppColors.primary),
                    ),
                    const SizedBox(width: 8),
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Ketua Regu:',
                          style: GoogleFonts.inter(
                            fontSize: 10,
                            color: AppColors.textSecondary,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                        Text(
                          leaderName,
                          style: GoogleFonts.inter(
                            fontSize: 12.5,
                            fontWeight: FontWeight.bold,
                            color: AppColors.textPrimary,
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
                if (leaderPhone != null && leaderPhone.trim().isNotEmpty)
                  InkWell(
                    onTap: () => _callPhone(leaderPhone),
                    borderRadius: BorderRadius.circular(8),
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                      decoration: BoxDecoration(
                        color: AppColors.primaryContainer,
                        borderRadius: BorderRadius.circular(8),
                        border: Border.all(color: AppColors.primaryBorder),
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          const Icon(LucideIcons.phoneCall, size: 12, color: AppColors.primary),
                          const SizedBox(width: 5),
                          Text(
                            leaderPhone,
                            style: GoogleFonts.inter(
                              fontSize: 11.5,
                              fontWeight: FontWeight.bold,
                              color: AppColors.primary,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
              ],
            ),
          ),

          // Instruksi Pengerjaan (Jika Ada)
          if (c.revisionNotes != null && c.revisionNotes!.trim().isNotEmpty) ...[
            const SizedBox(height: 10),
            const Divider(height: 1, color: AppColors.primaryBorder),
            const SizedBox(height: 8),
            Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Icon(LucideIcons.clipboardList, size: 13, color: AppColors.primary),
                const SizedBox(width: 6),
                Expanded(
                  child: Text(
                    'Instruksi: ${c.revisionNotes}',
                    style: GoogleFonts.inter(
                      fontSize: 11.5,
                      fontStyle: FontStyle.italic,
                      color: AppColors.textSecondary,
                      height: 1.4,
                    ),
                  ),
                ),
              ],
            ),
          ],
        ],
      ),
    );
  }

  /// Batas Waktu Pengerjaan (SLA) - Ditaruh di Bawah Section Lokasi Kerusakan
  Widget _buildDeadlineCard(ComplaintModel c) {
    // Sesuai aturan alur bisnis, laporan berstatus verifikasi belum diterbitkan penugasan resmi / waktu pengerjaan
    if (c.status == 'SUBMITTED' || c.status == 'VERIFICATION' || c.status == 'VERIFIED' || c.status == 'DRAFT' || c.status == 'REJECTED') {
      return const SizedBox.shrink();
    }

    final deadlineText = c.deadlineHuman ?? 'Sesuai Standar SLA';

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.border),
      ),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(8),
            decoration: BoxDecoration(
              color: const Color(0xFFEFF6FF),
              borderRadius: BorderRadius.circular(10),
            ),
            child: const Icon(LucideIcons.calendarClock, size: 18, color: AppColors.primary),
          ),
          const SizedBox(width: 12),
          const Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Batas Waktu Pengerjaan (SLA)',
                  style: TextStyle(
                    fontSize: 12,
                    fontWeight: FontWeight.bold,
                    color: AppColors.textPrimary,
                  ),
                ),
                SizedBox(height: 1),
                Text(
                  'Target Penyelesaian Penanganan',
                  style: TextStyle(fontSize: 10.5, color: AppColors.textSecondary),
                ),
              ],
            ),
          ),
          const SizedBox(width: 8),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 4),
            decoration: BoxDecoration(
              color: const Color(0xFFEFF6FF),
              borderRadius: BorderRadius.circular(8),
              border: Border.all(color: const Color(0xFFBFDBFE)),
            ),
            child: Text(
              deadlineText,
              style: const TextStyle(
                fontSize: 11,
                fontWeight: FontWeight.bold,
                color: AppColors.primary,
              ),
            ),
          ),
        ],
      ),
    );
  }

  /// Lokasi Kerusakan & Data Teknis
  Widget _buildLocationAndTechnicalCard(ComplaintModel c) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.border),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Row(
                children: [
                  Icon(LucideIcons.mapPin, size: 16, color: AppColors.primary),
                  SizedBox(width: 6),
                  Text(
                    'Lokasi Kerusakan',
                    style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
                  ),
                ],
              ),
              InkWell(
                onTap: () async {
                  final uri = Uri.parse('https://www.google.com/maps/search/?api=1&query=${c.latitude},${c.longitude}');
                  if (await canLaunchUrl(uri)) {
                    await launchUrl(uri, mode: LaunchMode.externalApplication);
                  }
                },
                borderRadius: BorderRadius.circular(8),
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                  decoration: BoxDecoration(
                    color: AppColors.primaryContainer,
                    borderRadius: BorderRadius.circular(8),
                    border: Border.all(color: AppColors.primaryBorder),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      const Icon(LucideIcons.mapPin, size: 12, color: AppColors.primary),
                      const SizedBox(width: 4),
                      Text(
                        'Buka di Peta',
                        style: GoogleFonts.inter(
                          fontSize: 11,
                          fontWeight: FontWeight.bold,
                          color: AppColors.primary,
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 6),
          Text(
            c.address,
            style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.w600, color: AppColors.textPrimary, height: 1.4),
          ),

          // Jika Kerusakan Berat (Ruas Panjang)
          if (c.damageType == 'major' || c.roadLength != null) ...[
            const SizedBox(height: 12),
            Container(
              padding: const EdgeInsets.all(10),
              decoration: BoxDecoration(
                color: AppColors.damageTypeMajorBg,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: AppColors.damageTypeMajorBorder),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Row(
                    children: [
                      Icon(LucideIcons.gitCommit, size: 14, color: AppColors.damageTypeMajor),
                      SizedBox(width: 6),
                      Text(
                        'Detail Ruas Panjang',
                        style: TextStyle(fontSize: 11.5, fontWeight: FontWeight.bold, color: AppColors.damageTypeMajor),
                      ),
                    ],
                  ),
                  const SizedBox(height: 8),
                  Row(
                    children: [
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text('Estimasi Panjang Ruas:', style: TextStyle(fontSize: 9.5, color: AppColors.textSecondary)),
                            Text(
                              c.roadLength ?? '-',
                              style: const TextStyle(fontSize: 11.5, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
                            ),
                          ],
                        ),
                      ),
                      if (c.roadCondition != null && c.roadCondition!.isNotEmpty)
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Text('Kondisi Permukaan:', style: TextStyle(fontSize: 9.5, color: AppColors.textSecondary)),
                              Text(
                                c.roadCondition!,
                                style: const TextStyle(fontSize: 11.5, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
                              ),
                            ],
                          ),
                        ),
                    ],
                  ),
                ],
              ),
            ),
          ],
        ],
      ),
    );
  }

  /// Tombol Dukungan Warga (Upvote)
  Widget _buildSupportButton(ComplaintModel c) {
    return ElevatedButton.icon(
      onPressed: _isSupporting ? null : _handleToggleSupport,
      icon: Icon(
        LucideIcons.thumbsUp,
        size: 15,
        color: c.hasSupported ? Colors.white : AppColors.primary,
      ),
      label: Text(
        c.hasSupported ? 'Didukung (${c.supportsCount})' : 'Dukung Aduan Ini (${c.supportsCount})',
        style: TextStyle(
          color: c.hasSupported ? Colors.white : AppColors.primary,
          fontWeight: FontWeight.bold,
          fontSize: 12.5,
        ),
      ),
      style: ElevatedButton.styleFrom(
        backgroundColor: c.hasSupported ? AppColors.primary : AppColors.primary.withValues(alpha: 0.08),
        elevation: 0,
        minimumSize: const Size.fromHeight(42),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      ),
    );
  }

  /// Alur Tindak Lanjut Penanganan (Timeline Status)
  Widget _buildTimelineCard() {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.border),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Row(
            children: [
              Icon(LucideIcons.gitBranch, size: 16, color: AppColors.primary),
              SizedBox(width: 6),
              Text(
                'Alur Tindak Lanjut Penanganan',
                style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
              ),
            ],
          ),
          const SizedBox(height: 14),
          if (_timeline.isEmpty)
            const Text('Belum ada histori perubahan status.', style: TextStyle(color: AppColors.textSecondary, fontSize: 12))
          else
            Column(
              children: _timeline.asMap().entries.map((entry) {
                final idx = entry.key;
                final item = entry.value;
                final isLast = idx == _timeline.length - 1;

                return Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Column(
                      children: [
                        Container(
                          width: 12,
                          height: 12,
                          decoration: BoxDecoration(
                            color: isLast ? AppColors.primary : const Color(0xFFCBD5E1),
                            shape: BoxShape.circle,
                          ),
                        ),
                        if (!isLast)
                          Container(
                            width: 2,
                            height: 42,
                            color: const Color(0xFFE2E8F0),
                          ),
                      ],
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Padding(
                        padding: const EdgeInsets.only(bottom: 12),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                Text(
                                  _formatStatusTimeline(item['to_status']),
                                  style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12, color: AppColors.textPrimary),
                                ),
                                Text(
                                  item['created_at'] != null ? item['created_at'].toString().substring(0, 10) : '',
                                  style: const TextStyle(fontSize: 10, color: AppColors.textSecondary),
                                ),
                              ],
                            ),
                            const SizedBox(height: 2),
                            Text(
                              item['note'] ?? 'Status laporan diperbarui oleh petugas.',
                              style: const TextStyle(fontSize: 11, color: AppColors.textSecondary),
                            ),
                          ],
                        ),
                      ),
                    ),
                  ],
                );
              }).toList(),
            ),
        ],
      ),
    );
  }

  Future<void> _submitRating() async {
    if (_selectedRating < 1 || _selectedRating > 5) {
      showV2Toast(
        context,
        message: 'Silakan pilih 1 hingga 5 bintang terlebih dahulu.',
        type: V2ToastType.warning,
      );
      return;
    }

    setState(() => _isSubmittingRating = true);
    try {
      final res = await _apiClient.dio.post(
        '/complaints/${widget.complaintId}/rating',
        data: {
          'rating': _selectedRating,
          if (_ratingCommentCtrl.text.trim().isNotEmpty)
            'comment': _ratingCommentCtrl.text.trim(),
        },
      );
      if (res.statusCode == 200 && mounted) {
        setState(() {
          _complaint = _complaint!.copyWith(
            ratingScore: _selectedRating,
            ratingComment: _ratingCommentCtrl.text.trim().isNotEmpty
                ? _ratingCommentCtrl.text.trim()
                : null,
          );
          _isSubmittingRating = false;
        });
        showV2Toast(
          context,
          message: 'Terima kasih! Penilaian Anda telah disimpan.',
          type: V2ToastType.success,
        );
      }
    } catch (e) {
      if (mounted) {
        setState(() => _isSubmittingRating = false);
        showV2Toast(
          context,
          message: 'Gagal menyimpan penilaian: $e',
          type: V2ToastType.error,
        );
      }
    }
  }

  Widget _buildCitizenRatingCard(ComplaintModel c) {
    final isOwner = _currentUser != null && c.userId != null && c.userId == _currentUser!.id;
    final hasRated = c.ratingScore != null && c.ratingScore! > 0;

    if (!hasRated && !isOwner) {
      return const SizedBox.shrink();
    }

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.border),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                width: 32,
                height: 32,
                decoration: BoxDecoration(
                  color: const Color(0xFFFFFBEB),
                  borderRadius: BorderRadius.circular(10),
                  border: Border.all(color: const Color(0xFFFDE68A)),
                ),
                child: const Icon(LucideIcons.star, size: 16, color: AppColors.statusInProgress),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      hasRated ? 'Penilaian Kepuasan Warga' : 'Beri Nilai Hasil Perbaikan',
                      style: GoogleFonts.outfit(
                        fontSize: 14,
                        fontWeight: FontWeight.bold,
                        color: AppColors.textPrimary,
                      ),
                    ),
                    Text(
                      hasRated
                          ? 'Penilaian resmi pelapor terhadap kualitas penanganan'
                          : 'Seberapa puas Anda dengan hasil perbaikan di lapangan?',
                      style: GoogleFonts.inter(fontSize: 11, color: AppColors.textSecondary),
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          if (hasRated) ...[
            Row(
              children: [
                ...List.generate(5, (i) {
                  final filled = i < c.ratingScore!;
                  return Padding(
                    padding: const EdgeInsets.only(right: 4),
                    child: Icon(
                      Icons.star_rounded,
                      size: 22,
                      color: filled ? AppColors.statusInProgress : AppColors.border,
                    ),
                  );
                }),
                const SizedBox(width: 6),
                Text(
                  '${c.ratingScore} / 5',
                  style: GoogleFonts.outfit(
                    fontSize: 14,
                    fontWeight: FontWeight.bold,
                    color: AppColors.textPrimary,
                  ),
                ),
              ],
            ),
            if (c.ratingComment != null && c.ratingComment!.trim().isNotEmpty) ...[
              const SizedBox(height: 8),
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                  color: AppColors.background,
                  borderRadius: BorderRadius.circular(10),
                  border: Border.all(color: AppColors.border),
                ),
                child: Text(
                  '"${c.ratingComment}"',
                  style: GoogleFonts.inter(
                    fontSize: 12,
                    color: AppColors.textPrimary,
                    fontStyle: FontStyle.italic,
                  ),
                ),
              ),
            ],
          ] else ...[
            Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: List.generate(5, (i) {
                final star = i + 1;
                final active = star <= _selectedRating;
                return IconButton(
                  onPressed: () => setState(() => _selectedRating = star),
                  icon: Icon(
                    Icons.star_rounded,
                    size: 32,
                    color: active ? AppColors.statusInProgress : AppColors.border,
                  ),
                );
              }),
            ),
            const SizedBox(height: 8),
            TextField(
              controller: _ratingCommentCtrl,
              maxLines: 2,
              style: GoogleFonts.inter(fontSize: 12.5, color: AppColors.textPrimary),
              decoration: InputDecoration(
                hintText: 'Tulis ulasan singkat Anda (opsional)...',
                hintStyle: GoogleFonts.inter(fontSize: 12, color: AppColors.textSecondary),
                filled: true,
                fillColor: AppColors.background,
                contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(10),
                  borderSide: const BorderSide(color: AppColors.border),
                ),
                enabledBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(10),
                  borderSide: const BorderSide(color: AppColors.border),
                ),
              ),
            ),
            const SizedBox(height: 10),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: _isSubmittingRating ? null : _submitRating,
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppColors.primary,
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(vertical: 11),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                ),
                child: _isSubmittingRating
                    ? const SizedBox(
                        width: 16,
                        height: 16,
                        child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                      )
                    : Text(
                        'Kirim Penilaian',
                        style: GoogleFonts.outfit(fontSize: 13, fontWeight: FontWeight.bold),
                      ),
              ),
            ),
          ],
        ],
      ),
    );
  }
}
