import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:intl/intl.dart';
import 'package:lucide_icons_flutter/lucide_icons.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../../core/network/api_client.dart';
import '../../../shared/theme/app_colors.dart';
import '../../../shared/widgets/status_badge.dart';
import '../../../shared/widgets/v2_components.dart';
import '../../../shared/widgets/v2_toast.dart';
import '../../complaints/presentation/complaint_detail_screen.dart';

class FieldPerformanceScreen extends StatefulWidget {
  const FieldPerformanceScreen({super.key});

  @override
  State<FieldPerformanceScreen> createState() => _FieldPerformanceScreenState();
}

class _FieldPerformanceScreenState extends State<FieldPerformanceScreen> {
  final _apiClient = ApiClient();

  bool _isLoading = true;
  String? _errorMessage;
  String _activeTab = 'individual'; // 'individual' | 'team' | 'operator'
  String _selectedPeriod = 'ALL';
  String _selectedPeriodLabel = 'Semua Periode';
  List<Map<String, String>> _availablePeriods = const [
    {'value': 'ALL', 'label': 'Semua Periode'},
  ];

  Map<String, dynamic>? _individual;
  List<dynamic> _individualTasks = [];
  Map<String, dynamic>? _team;
  List<dynamic> _teamMembers = [];
  Map<String, dynamic>? _operatorResp;
  Map<String, dynamic> _printUrls = {};

  @override
  void initState() {
    super.initState();
    _fetchPerformance();
  }

  Future<void> _fetchPerformance() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });
    try {
      final res = await _apiClient.dio.get(
        '/field/performance',
        queryParameters: {'period': _selectedPeriod},
      );
      if (res.statusCode == 200 && res.data['data'] != null) {
        final data = res.data['data'] as Map<String, dynamic>;
        final rawPeriods = (data['available_periods'] as List<dynamic>?) ?? [];
        final parsedPeriods = rawPeriods
            .whereType<Map>()
            .map((p) => {
                  'value': p['value']?.toString() ?? 'ALL',
                  'label': p['label']?.toString() ?? 'Semua Periode',
                })
            .toList();
        if (mounted) {
          setState(() {
            _selectedPeriod = data['selected_period']?.toString() ?? _selectedPeriod;
            _selectedPeriodLabel = data['selected_period_label']?.toString() ?? 'Semua Periode';
            if (parsedPeriods.isNotEmpty) {
              _availablePeriods = parsedPeriods;
            }
            _individual = data['individual'] as Map<String, dynamic>?;
            _individualTasks = (data['individual_tasks'] as List<dynamic>?) ?? [];
            _team = data['team'] as Map<String, dynamic>?;
            _teamMembers = (data['team_members'] as List<dynamic>?) ?? [];
            _operatorResp = data['operator_responsiveness'] as Map<String, dynamic>?;
            _printUrls = (data['print_urls'] as Map<String, dynamic>?) ?? {};
            _isLoading = false;
          });
        }
        return;
      }
      if (mounted) {
        setState(() {
          _errorMessage = 'Gagal memuat data laporan kinerja.';
          _isLoading = false;
        });
      }
    } catch (_) {
      if (mounted) {
        setState(() {
          _errorMessage = 'Tidak dapat terhubung ke server. Tarik ke bawah untuk memuat ulang.';
          _isLoading = false;
        });
      }
    }
  }

  Future<void> _openExternalUrl(String? rawUrl) async {
    if (rawUrl == null || rawUrl.isEmpty) {
      if (mounted) {
        showV2Toast(
          context,
          message: 'Dokumen PDF belum tersedia untuk akun ini.',
          type: V2ToastType.info,
        );
      }
      return;
    }
    final uri = Uri.tryParse(rawUrl);
    if (uri == null) return;
    final launched = await launchUrl(uri, mode: LaunchMode.externalApplication);
    if (!launched && mounted) {
      showV2Toast(
        context,
        message: 'Gagal membuka dokumen cetak.',
        type: V2ToastType.error,
      );
    }
  }

  Color _scoreBgColor(num score) {
    if (score >= 80) return AppColors.statusCompleted.withValues(alpha: 0.12);
    if (score >= 50) return AppColors.accent.withValues(alpha: 0.14);
    return AppColors.cardSurface;
  }

  Color _scoreTextColor(num score) {
    if (score >= 80) return AppColors.statusCompleted;
    if (score >= 50) return AppColors.accent;
    return AppColors.textSecondary;
  }

  String _formattedNowWita() {
    try {
      return DateFormat('dd MMM yyyy, HH:mm').format(DateTime.now());
    } catch (_) {
      return DateTime.now().toString().substring(0, 16);
    }
  }

  @override
  Widget build(BuildContext context) {
    final officerName = _individual?['name']?.toString() ?? 'Pekerja Lapangan';
    final teamName = _individual?['team_name']?.toString() ?? 'Dinas PUPR Kabupaten';

    return Scaffold(
      backgroundColor: AppColors.background,
      body: SafeArea(
        bottom: false,
        child: Column(
          children: [
            // ─── Header (Seragam V2FloatingHeader dengan Tugas & Peta GIS) ───
            V2FloatingHeader(
              title: 'Laporan Kinerja',
              subtitle: '$officerName • $teamName',
              leading: Container(
                width: 36,
                height: 36,
                decoration: BoxDecoration(
                  color: AppColors.primaryContainer,
                  borderRadius: BorderRadius.circular(11),
                  border: Border.all(color: AppColors.primaryBorder),
                ),
                child: const Icon(
                  LucideIcons.barChart3,
                  color: AppColors.primary,
                  size: 18,
                ),
              ),
              actions: [
                InkWell(
                  borderRadius: BorderRadius.circular(11),
                  onTap: _fetchPerformance,
                  child: Container(
                    width: 36,
                    height: 36,
                    decoration: BoxDecoration(
                      color: AppColors.cardSurface,
                      borderRadius: BorderRadius.circular(11),
                      border: Border.all(color: AppColors.border),
                    ),
                    child: const Icon(
                      LucideIcons.refreshCw,
                      color: AppColors.textPrimary,
                      size: 17,
                    ),
                  ),
                ),
              ],
            ),

            // ─── Konten Utama ───
            Expanded(
              child: RefreshIndicator(
                color: AppColors.primary,
                onRefresh: _fetchPerformance,
                child: _isLoading
                    ? const Center(
                        child: CircularProgressIndicator(color: AppColors.primary),
                      )
                    : _errorMessage != null
                        ? ListView(
                            padding: const EdgeInsets.all(24),
                            children: [
                              const SizedBox(height: 60),
                              const Icon(LucideIcons.wifiOff, size: 42, color: AppColors.textSecondary),
                              const SizedBox(height: 12),
                              Text(
                                _errorMessage!,
                                textAlign: TextAlign.center,
                                style: GoogleFonts.inter(
                                  fontSize: 13,
                                  color: AppColors.textSecondary,
                                ),
                              ),
                              const SizedBox(height: 16),
                              Center(
                                child: ElevatedButton.icon(
                                  onPressed: _fetchPerformance,
                                  icon: const Icon(LucideIcons.refreshCw, size: 16),
                                  label: const Text('Coba Lagi'),
                                  style: ElevatedButton.styleFrom(
                                    backgroundColor: AppColors.primary,
                                    foregroundColor: Colors.white,
                                    shape: RoundedRectangleBorder(
                                      borderRadius: BorderRadius.circular(12),
                                    ),
                                  ),
                                ),
                              ),
                            ],
                          )
                        : ListView(
                            padding: const EdgeInsets.fromLTRB(16, 12, 16, 110),
                            children: [
                              _buildPeriodSelector(),
                              const SizedBox(height: 10),
                              _buildModeSwitcher(),
                              if (_activeTab != 'operator') ...[
                                const SizedBox(height: 10),
                                _buildPrintActionButton(),
                              ],
                              const SizedBox(height: 12),
                              if (_activeTab == 'individual') _buildIndividualView(),
                              if (_activeTab == 'team') _buildTeamView(),
                              if (_activeTab == 'operator') _buildOperatorRespView(),
                            ],
                          ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  /// Filter Periode Bulanan (mis. Semua Periode, November 2026, September 2026)
  Widget _buildPeriodSelector() {
    final validValue = _availablePeriods.any((p) => p['value'] == _selectedPeriod)
        ? _selectedPeriod
        : 'ALL';

    return Container(
      width: double.infinity,
      constraints: const BoxConstraints(minHeight: 48),
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: AppColors.border),
      ),
      child: Row(
        children: [
          const Icon(LucideIcons.calendar, size: 18, color: AppColors.primary),
          const SizedBox(width: 10),
          Text(
            'Periode:',
            style: GoogleFonts.inter(
              fontSize: 13,
              fontWeight: FontWeight.w600,
              color: AppColors.textSecondary,
            ),
          ),
          const SizedBox(width: 8),
          Expanded(
            child: DropdownButtonHideUnderline(
              child: DropdownButton<String>(
                value: validValue,
                isExpanded: true,
                itemHeight: 48,
                icon: const Icon(LucideIcons.chevronDown, size: 18, color: AppColors.textSecondary),
                style: GoogleFonts.inter(
                  fontSize: 13.5,
                  fontWeight: FontWeight.bold,
                  color: AppColors.textPrimary,
                ),
                dropdownColor: AppColors.surface,
                borderRadius: BorderRadius.circular(12),
                items: _availablePeriods.map((p) {
                  return DropdownMenuItem<String>(
                    value: p['value'],
                    child: Text(
                      p['label'] ?? 'Semua Periode',
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                  );
                }).toList(),
                onChanged: (val) {
                  if (val == null || val == _selectedPeriod) return;
                  setState(() => _selectedPeriod = val);
                  _fetchPerformance();
                },
              ),
            ),
          ),
        ],
      ),
    );
  }

  /// Segmented Tab Switcher: Individu | Regu | Responsivitas (Penuh rapat kiri-kanan)
  Widget _buildModeSwitcher() {
    final tabs = [
      {'key': 'individual', 'label': 'Individu', 'icon': LucideIcons.user},
      {'key': 'team', 'label': 'Regu', 'icon': LucideIcons.users},
      {'key': 'operator', 'label': 'Responsivitas', 'icon': LucideIcons.shieldCheck},
    ];

    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(4),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: AppColors.border),
      ),
      child: Row(
        children: tabs.map((t) {
          final isSelected = _activeTab == t['key'];
          return Expanded(
            child: InkWell(
              onTap: () => setState(() => _activeTab = t['key'] as String),
              borderRadius: BorderRadius.circular(10),
              child: Container(
                padding: const EdgeInsets.symmetric(vertical: 9, horizontal: 6),
                decoration: BoxDecoration(
                  color: isSelected ? AppColors.primary : Colors.transparent,
                  borderRadius: BorderRadius.circular(10),
                ),
                child: FittedBox(
                  fit: BoxFit.scaleDown,
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(
                        t['icon'] as IconData,
                        size: 13.5,
                        color: isSelected ? Colors.white : AppColors.textSecondary,
                      ),
                      const SizedBox(width: 5),
                      Text(
                        t['label'] as String,
                        maxLines: 1,
                        style: GoogleFonts.inter(
                          fontSize: 12,
                          fontWeight: isSelected ? FontWeight.bold : FontWeight.w600,
                          color: isSelected ? Colors.white : AppColors.textSecondary,
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ),
          );
        }).toList(),
      ),
    );
  }

  /// Tombol Cetak / Pratinjau Laporan Kinerja (Tampil dalam Modal di dalam App, disembunyikan di tab Responsivitas)
  Widget _buildPrintActionButton() {
    final isTeam = _activeTab == 'team';
    final isDisabled = isTeam && _team == null;

    return SizedBox(
      width: double.infinity,
      child: ElevatedButton.icon(
        onPressed: isDisabled ? null : () => _showPerformancePreviewModal(isTeam: isTeam),
        icon: const Icon(LucideIcons.printer, size: 16),
        label: Text(
          isTeam ? 'Pratinjau & Cetak Laporan Regu' : 'Pratinjau & Cetak Laporan Individu',
          style: GoogleFonts.inter(fontSize: 12.5, fontWeight: FontWeight.bold),
        ),
        style: ElevatedButton.styleFrom(
          backgroundColor: AppColors.textPrimary,
          foregroundColor: Colors.white,
          disabledBackgroundColor: AppColors.border,
          disabledForegroundColor: AppColors.textSecondary,
          elevation: 0,
          padding: const EdgeInsets.symmetric(vertical: 11, horizontal: 14),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
        ),
      ),
    );
  }

  // ─── TAB 1: INDIVIDU ──────────────────────────────────────────────────────
  Widget _buildIndividualView() {
    final m = _individual ?? {};
    final totalTasks = m['total_tasks'] ?? 0;
    final completed = m['completed'] ?? 0;
    final score = (m['score'] ?? 0) as num;
    final firstPass = m['first_pass_rate'] ?? 100;
    final avgRepair = m['avg_repair_hours'];
    final onTime = m['on_time_rate'] ?? 100;
    final avgRating = m['avg_rating'];
    final revisions = m['revision_count'] ?? 0;
    final teamRole = m['team_role']?.toString() ?? 'Anggota Regu';
    final teamName = m['team_name']?.toString() ?? 'Belum Tergabung Regu';

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        _buildKpiGrid([
          _KpiCardData(
            title: 'Tugas Ditangani',
            value: '$totalTasks',
            sub: '$completed tuntas',
            valueColor: AppColors.textPrimary,
          ),
          _KpiCardData(
            title: 'Skor Kinerja',
            value: '$score',
            suffix: '/100',
            sub: 'Lolos 1x validasi: $firstPass%',
            valueColor: AppColors.primary,
          ),
          _KpiCardData(
            title: 'Rata-rata Pengerjaan',
            value: avgRepair != null ? '$avgRepair jam' : '-',
            sub: 'Tepat waktu: $onTime%',
            valueColor: AppColors.textPrimary,
          ),
          _KpiCardData(
            title: 'Kepuasan Warga',
            value: avgRating != null ? '$avgRating / 5' : '-',
            sub: 'Revisi: $revisions kali',
            valueColor: AppColors.accent,
          ),
        ]),
        const SizedBox(height: 12),
        _buildSectionContainer(
          title: 'Rincian Pekerjaan Individu',
          trailing: '$teamName ($teamRole)',
          child: _individualTasks.isEmpty
              ? _buildEmptyBox('Belum ada riwayat pengerjaan individu tercatat.')
              : Column(
                  children: _individualTasks.map((raw) {
                    final t = raw as Map<String, dynamic>;
                    final bapUrl = t['bap_url']?.toString();
                    final rating = t['rating'];
                    return _buildTaskRowCard(
                      id: (t['id'] as num).toInt(),
                      code: t['public_code']?.toString() ?? '-',
                      title: t['title']?.toString() ?? '-',
                      address: t['address']?.toString() ?? '-',
                      category: t['category']?.toString() ?? 'Umum',
                      status: t['status']?.toString() ?? 'SUBMITTED',
                      ratingText: rating != null ? '$rating/5' : '-',
                      bapUrl: bapUrl,
                    );
                  }).toList(),
                ),
        ),
      ],
    );
  }

  // ─── TAB 2: REGU ──────────────────────────────────────────────────────────
  Widget _buildTeamView() {
    if (_team == null) {
      return _buildSectionContainer(
        title: 'Kinerja Regu Lapangan',
        child: _buildEmptyBox('Akun Anda belum tergabung dalam Regu Lapangan resmi.'),
      );
    }

    final tm = _team!;
    final totalTasks = tm['total_tasks'] ?? 0;
    final completed = tm['completed'] ?? 0;
    final compRate = tm['completion_rate'] ?? 0;
    final score = (tm['score'] ?? 0) as num;
    final activeQuota = tm['active_quota'] ?? 0;
    final avgResp = tm['avg_response_hours'];
    final avgRep = tm['avg_repair_hours'];
    final onTime = tm['on_time_rate'] ?? 100;
    final avgRating = tm['avg_rating'];
    final ratingsCount = tm['ratings_count'] ?? 0;
    final teamName = tm['team_name']?.toString() ?? 'Regu Lapangan';
    final teamCode = tm['team_code']?.toString() ?? '';

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        _buildKpiGrid([
          _KpiCardData(
            title: 'Total Tugas Regu',
            value: '$totalTasks',
            sub: '$completed tuntas ($compRate%)',
            valueColor: AppColors.textPrimary,
          ),
          _KpiCardData(
            title: 'Skor Kinerja Regu',
            value: '$score',
            suffix: '/100',
            sub: 'Kuota Aktif: $activeQuota/3',
            valueColor: AppColors.primary,
          ),
          _KpiCardData(
            title: 'Respon / Perbaikan',
            value: '${avgResp != null ? '${avgResp}j' : '-'} / ${avgRep != null ? '${avgRep}j' : '-'}',
            sub: 'Tepat waktu: $onTime%',
            valueColor: AppColors.textPrimary,
          ),
          _KpiCardData(
            title: 'Rating Kepuasan Warga',
            value: avgRating != null ? '$avgRating / 5' : '-',
            sub: '$ratingsCount ulasan warga',
            valueColor: AppColors.accent,
          ),
        ]),
        const SizedBox(height: 12),
        _buildSectionContainer(
          title: 'Kontribusi Anggota Regu',
          trailing: teamCode.isNotEmpty ? '$teamName ($teamCode)' : teamName,
          child: _teamMembers.isEmpty
              ? _buildEmptyBox('Belum ada anggota regu terdaftar.')
              : Column(
                  children: _teamMembers.asMap().entries.map((entry) {
                    final idx = entry.key;
                    final mb = entry.value as Map<String, dynamic>;
                    final mScore = (mb['score'] ?? 0) as num;
                    return Container(
                      padding: const EdgeInsets.symmetric(vertical: 10),
                      decoration: BoxDecoration(
                        border: idx == _teamMembers.length - 1
                            ? null
                            : const Border(bottom: BorderSide(color: AppColors.border)),
                      ),
                      child: Row(
                        children: [
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  mb['name']?.toString() ?? '-',
                                  style: GoogleFonts.inter(
                                    fontSize: 13,
                                    fontWeight: FontWeight.bold,
                                    color: AppColors.textPrimary,
                                  ),
                                ),
                                const SizedBox(height: 2),
                                Text(
                                  '${mb['team_role'] ?? 'Anggota'} • Tuntas ${mb['completed'] ?? 0}/${mb['total_tasks'] ?? 0} • Revisi ${mb['revision_count'] ?? 0}',
                                  style: GoogleFonts.inter(
                                    fontSize: 11.5,
                                    color: AppColors.textSecondary,
                                  ),
                                ),
                              ],
                            ),
                          ),
                          const SizedBox(width: 8),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                            decoration: BoxDecoration(
                              color: _scoreBgColor(mScore),
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: Text(
                              '$mScore',
                              style: GoogleFonts.outfit(
                                fontSize: 13,
                                fontWeight: FontWeight.bold,
                                color: _scoreTextColor(mScore),
                              ),
                            ),
                          ),
                        ],
                      ),
                    );
                  }).toList(),
                ),
        ),
      ],
    );
  }

  // ─── TAB 3: RESPONSIVITAS OPERATOR ────────────────────────────────────────
  Widget _buildOperatorRespView() {
    final op = _operatorResp ?? {};
    final avgWait = op['avg_validation_wait_hours'];
    final pendingCount = op['pending_validation_count'] ?? 0;
    final approvalRate = op['approval_rate'] ?? 0;
    final approvedCount = op['approved_count'] ?? 0;
    final revisionCount = op['revision_count'] ?? 0;
    final pendingItems = (op['pending_validation_items'] as List<dynamic>?) ?? [];

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        _buildKpiSingleWideCard(
          title: 'Rata-rata Waktu Validasi Operator',
          value: avgWait != null ? '$avgWait jam' : '-',
          sub: 'Sejak bukti hasil kerja dikirim hingga keputusan Operator',
          valueColor: AppColors.primary,
          icon: LucideIcons.clock,
        ),
        const SizedBox(height: 10),
        _buildKpiGrid([
          _KpiCardData(
            title: 'Menunggu Validasi',
            value: '$pendingCount',
            sub: 'Dalam antrean uji mutu',
            valueColor: AppColors.accent,
          ),
          _KpiCardData(
            title: 'Rasio Persetujuan',
            value: '$approvalRate%',
            sub: '$approvedCount setuju • $revisionCount revisi',
            valueColor: AppColors.statusCompleted,
          ),
        ]),
        const SizedBox(height: 12),
        _buildSectionContainer(
          title: 'Antrean Menunggu Validasi Operator',
          child: pendingItems.isEmpty
              ? _buildEmptyBox('Tidak ada pekerjaan yang tertahan di antrean validasi Operator.')
              : Column(
                  children: pendingItems.asMap().entries.map((entry) {
                    final idx = entry.key;
                    final item = entry.value as Map<String, dynamic>;
                    final waitHours = (item['wait_hours'] ?? 0) as num;
                    final itemId = item['id'];
                    return InkWell(
                      onTap: itemId != null
                          ? () => Navigator.push(
                                context,
                                MaterialPageRoute(
                                  builder: (_) => ComplaintDetailScreen(
                                    complaintId: (itemId as num).toInt(),
                                  ),
                                ),
                              )
                          : null,
                      child: Container(
                        padding: const EdgeInsets.symmetric(vertical: 10),
                        decoration: BoxDecoration(
                          border: idx == pendingItems.length - 1
                              ? null
                              : const Border(bottom: BorderSide(color: AppColors.border)),
                        ),
                        child: Row(
                          children: [
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    '#${item['public_code'] ?? '-'}',
                                    style: GoogleFonts.inter(
                                      fontSize: 11,
                                      fontWeight: FontWeight.bold,
                                      color: AppColors.primary,
                                    ),
                                  ),
                                  const SizedBox(height: 2),
                                  Text(
                                    item['title']?.toString() ?? '-',
                                    maxLines: 2,
                                    overflow: TextOverflow.ellipsis,
                                    style: GoogleFonts.inter(
                                      fontSize: 13,
                                      fontWeight: FontWeight.w600,
                                      color: AppColors.textPrimary,
                                    ),
                                  ),
                                  const SizedBox(height: 2),
                                  Text(
                                    'Dikirim: ${item['submitted_at'] ?? '-'}',
                                    style: GoogleFonts.inter(
                                      fontSize: 11,
                                      color: AppColors.textSecondary,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                            const SizedBox(width: 10),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 5),
                              decoration: BoxDecoration(
                                color: (waitHours >= 24 ? AppColors.accent : AppColors.primary)
                                    .withValues(alpha: 0.12),
                                borderRadius: BorderRadius.circular(8),
                              ),
                              child: Text(
                                '$waitHours jam',
                                style: GoogleFonts.inter(
                                  fontSize: 11.5,
                                  fontWeight: FontWeight.bold,
                                  color: waitHours >= 24 ? AppColors.accent : AppColors.primary,
                                ),
                              ),
                            ),
                          ],
                        ),
                      ),
                    );
                  }).toList(),
                ),
        ),
      ],
    );
  }

  // ─── MODAL 1: PRATINJAU LAPORAN KINERJA RESMI (DALAM APP, ZERO OVERFLOW) ──
  void _showPerformancePreviewModal({required bool isTeam}) {
    final m = isTeam ? (_team ?? {}) : (_individual ?? {});
    final pdfUrl = isTeam ? _printUrls['team']?.toString() : _printUrls['individual']?.toString();

    showModalBottomSheet<void>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) {
        return DraggableScrollableSheet(
          initialChildSize: 0.88,
          minChildSize: 0.55,
          maxChildSize: 0.95,
          expand: false,
          builder: (_, scrollController) {
            return Container(
              decoration: const BoxDecoration(
                color: AppColors.surface,
                borderRadius: BorderRadius.vertical(top: Radius.circular(22)),
              ),
              child: Column(
                children: [
                  // Handle & Header Modal
                  Padding(
                    padding: const EdgeInsets.fromLTRB(16, 10, 12, 10),
                    child: Column(
                      children: [
                        Container(
                          width: 40,
                          height: 4,
                          decoration: BoxDecoration(
                            color: AppColors.border,
                            borderRadius: BorderRadius.circular(4),
                          ),
                        ),
                        const SizedBox(height: 10),
                        Row(
                          children: [
                            const Icon(LucideIcons.fileText, size: 18, color: AppColors.primary),
                            const SizedBox(width: 8),
                            Expanded(
                              child: Text(
                                isTeam ? 'Pratinjau Laporan Kinerja Regu' : 'Pratinjau Laporan Kinerja Individu',
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                                style: GoogleFonts.outfit(
                                  fontSize: 15,
                                  fontWeight: FontWeight.bold,
                                  color: AppColors.textPrimary,
                                ),
                              ),
                            ),
                            IconButton(
                              onPressed: () => Navigator.pop(ctx),
                              icon: const Icon(LucideIcons.x, size: 19, color: AppColors.textSecondary),
                              visualDensity: VisualDensity.compact,
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                  const Divider(height: 1, color: AppColors.border),

                  // Isi Dokumen Resmi (Scrollable & Responsif Rapi)
                  Expanded(
                    child: SingleChildScrollView(
                      controller: scrollController,
                      padding: const EdgeInsets.all(16),
                      child: Container(
                        width: double.infinity,
                        padding: const EdgeInsets.all(16),
                        decoration: BoxDecoration(
                          color: AppColors.surface,
                          borderRadius: BorderRadius.circular(14),
                          border: Border.all(color: AppColors.border),
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.stretch,
                          children: [
                            // Kop Resmi Dinas PUPR
                            _buildOfficialLetterhead(
                              docTitle: isTeam
                                  ? 'LAPORAN KINERJA REGU LAPANGAN'
                                  : 'LAPORAN KINERJA INDIVIDU PEKERJA LAPANGAN',
                              subtitle: 'Periode: $_selectedPeriodLabel',
                              printedAt: _formattedNowWita(),
                            ),
                            const SizedBox(height: 14),

                            // Identitas
                            Container(
                              padding: const EdgeInsets.all(12),
                              decoration: BoxDecoration(
                                color: AppColors.cardSurface,
                                borderRadius: BorderRadius.circular(10),
                                border: Border.all(color: AppColors.border),
                              ),
                              child: Column(
                                children: isTeam
                                    ? [
                                        _buildDocInfoRow('Nama & Kode Regu', '${m['team_name'] ?? '-'} (${m['team_code'] ?? '-'})'),
                                        _buildDocInfoRow('Ketua Regu', '${m['leader_name'] ?? '-'} (${m['members_count'] ?? 0} Personel)'),
                                        _buildDocInfoRow('Wilayah Tugas', '${m['district'] ?? 'Kabupaten'}', isLast: true),
                                      ]
                                    : [
                                        _buildDocInfoRow('Nama Petugas', '${m['name'] ?? '-'}'),
                                        _buildDocInfoRow('Email / Telepon', '${m['email'] ?? '-'} • ${m['phone'] ?? '-'}'),
                                        _buildDocInfoRow('Regu & Jabatan', '${m['team_name'] ?? '-'} (${m['team_role'] ?? '-'})', isLast: true),
                                      ],
                              ),
                            ),
                            const SizedBox(height: 12),

                            // Ringkasan 4 Indikator Kinerja
                            _buildKpiGrid([
                              _KpiCardData(
                                title: 'Tuntas / Total',
                                value: '${m['completed'] ?? 0}/${m['total_tasks'] ?? 0}',
                                sub: '${m['completion_rate'] ?? 0}% selesai',
                                valueColor: AppColors.textPrimary,
                              ),
                              _KpiCardData(
                                title: 'Skor Kinerja',
                                value: '${m['score'] ?? 0}',
                                suffix: '/100',
                                sub: 'Lolos 1x: ${m['first_pass_rate'] ?? 100}%',
                                valueColor: AppColors.primary,
                              ),
                              _KpiCardData(
                                title: 'Rata-rata Perbaikan',
                                value: m['avg_repair_hours'] != null ? '${m['avg_repair_hours']} jam' : '-',
                                sub: 'Tepat waktu: ${m['on_time_rate'] ?? 100}%',
                                valueColor: AppColors.textPrimary,
                              ),
                              _KpiCardData(
                                title: 'Rating Warga',
                                value: m['avg_rating'] != null ? '${m['avg_rating']} / 5' : '-',
                                sub: 'Revisi: ${m['revision_count'] ?? 0}x',
                                valueColor: AppColors.accent,
                              ),
                            ]),
                            const SizedBox(height: 14),

                            // Rincian Anggota (Mode Regu) atau Pekerjaan (Mode Individu)
                            if (isTeam) ...[
                              Text(
                                'KONTRIBUSI ANGGOTA REGU',
                                style: GoogleFonts.inter(
                                  fontSize: 11,
                                  fontWeight: FontWeight.bold,
                                  letterSpacing: 0.4,
                                  color: AppColors.textSecondary,
                                ),
                              ),
                              const SizedBox(height: 8),
                              if (_teamMembers.isEmpty)
                                _buildEmptyBox('Belum ada data anggota regu.')
                              else
                                ..._teamMembers.map((raw) {
                                  final mb = raw as Map<String, dynamic>;
                                  return Container(
                                    margin: const EdgeInsets.only(bottom: 8),
                                    padding: const EdgeInsets.all(10),
                                    decoration: BoxDecoration(
                                      color: AppColors.cardSurface,
                                      borderRadius: BorderRadius.circular(10),
                                      border: Border.all(color: AppColors.border),
                                    ),
                                    child: Row(
                                      children: [
                                        Expanded(
                                          child: Column(
                                            crossAxisAlignment: CrossAxisAlignment.start,
                                            children: [
                                              Text(
                                                mb['name']?.toString() ?? '-',
                                                style: GoogleFonts.inter(
                                                  fontSize: 12.5,
                                                  fontWeight: FontWeight.bold,
                                                  color: AppColors.textPrimary,
                                                ),
                                              ),
                                              Text(
                                                '${mb['team_role'] ?? 'Anggota'} • Tuntas ${mb['completed'] ?? 0}/${mb['total_tasks'] ?? 0}',
                                                style: GoogleFonts.inter(
                                                  fontSize: 11,
                                                  color: AppColors.textSecondary,
                                                ),
                                              ),
                                            ],
                                          ),
                                        ),
                                        const SizedBox(width: 8),
                                        Text(
                                          'Skor ${mb['score'] ?? 0}',
                                          style: GoogleFonts.outfit(
                                            fontSize: 12.5,
                                            fontWeight: FontWeight.bold,
                                            color: AppColors.primary,
                                          ),
                                        ),
                                      ],
                                    ),
                                  );
                                }),
                            ] else ...[
                              Text(
                                'RINCIAN PENANGANAN PEKERJAAN',
                                style: GoogleFonts.inter(
                                  fontSize: 11,
                                  fontWeight: FontWeight.bold,
                                  letterSpacing: 0.4,
                                  color: AppColors.textSecondary,
                                ),
                              ),
                              const SizedBox(height: 8),
                              if (_individualTasks.isEmpty)
                                _buildEmptyBox('Belum ada data pekerjaan tercatat.')
                              else
                                ..._individualTasks.map((raw) {
                                  final t = raw as Map<String, dynamic>;
                                  return Container(
                                    margin: const EdgeInsets.only(bottom: 8),
                                    padding: const EdgeInsets.all(10),
                                    decoration: BoxDecoration(
                                      color: AppColors.cardSurface,
                                      borderRadius: BorderRadius.circular(10),
                                      border: Border.all(color: AppColors.border),
                                    ),
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        Row(
                                          children: [
                                            Text(
                                              '#${t['public_code'] ?? '-'}',
                                              style: GoogleFonts.inter(
                                                fontSize: 11,
                                                fontWeight: FontWeight.bold,
                                                color: AppColors.primary,
                                              ),
                                            ),
                                            const Spacer(),
                                            Text(
                                              t['status_label']?.toString() ?? '-',
                                              style: GoogleFonts.inter(
                                                fontSize: 11,
                                                fontWeight: FontWeight.bold,
                                                color: AppColors.textPrimary,
                                              ),
                                            ),
                                          ],
                                        ),
                                        const SizedBox(height: 3),
                                        Text(
                                          t['title']?.toString() ?? '-',
                                          style: GoogleFonts.inter(
                                            fontSize: 12.5,
                                            fontWeight: FontWeight.bold,
                                            color: AppColors.textPrimary,
                                          ),
                                        ),
                                        Text(
                                          '${t['address'] ?? '-'} • ${t['category'] ?? 'Umum'}',
                                          style: GoogleFonts.inter(
                                            fontSize: 11,
                                            color: AppColors.textSecondary,
                                          ),
                                        ),
                                      ],
                                    ),
                                  );
                                }),
                            ],

                            const SizedBox(height: 16),
                            // Blok Tanda Tangan Rapi (2 Kolom Seimbang)
                            _buildSignatureBlock(
                              rightRole: isTeam ? 'Ketua Regu Lapangan' : 'Pekerja Lapangan',
                              rightName: isTeam
                                  ? (m['leader_name']?.toString() ?? '-')
                                  : (m['name']?.toString() ?? '-'),
                            ),
                          ],
                        ),
                      ),
                    ),
                  ),

                  // Footer Aksi Modal
                  Container(
                    padding: const EdgeInsets.fromLTRB(16, 10, 16, 14),
                    decoration: const BoxDecoration(
                      color: AppColors.surface,
                      border: Border(top: BorderSide(color: AppColors.border)),
                    ),
                    child: SafeArea(
                      top: false,
                      child: Row(
                        children: [
                          Expanded(
                            child: OutlinedButton(
                              onPressed: () => Navigator.pop(ctx),
                              style: OutlinedButton.styleFrom(
                                foregroundColor: AppColors.textPrimary,
                                side: const BorderSide(color: AppColors.border),
                                padding: const EdgeInsets.symmetric(vertical: 11),
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                              ),
                              child: Text(
                                'Tutup',
                                style: GoogleFonts.inter(fontSize: 12.5, fontWeight: FontWeight.bold),
                              ),
                            ),
                          ),
                          if (pdfUrl != null && pdfUrl.isNotEmpty) ...[
                            const SizedBox(width: 10),
                            Expanded(
                              child: ElevatedButton.icon(
                                onPressed: () => _openExternalUrl(pdfUrl),
                                icon: const Icon(LucideIcons.download, size: 15),
                                label: Text(
                                  'Simpan PDF',
                                  style: GoogleFonts.inter(fontSize: 12.5, fontWeight: FontWeight.bold),
                                ),
                                style: ElevatedButton.styleFrom(
                                  backgroundColor: AppColors.primary,
                                  foregroundColor: Colors.white,
                                  elevation: 0,
                                  padding: const EdgeInsets.symmetric(vertical: 11),
                                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                ),
                              ),
                            ),
                          ],
                        ],
                      ),
                    ),
                  ),
                ],
              ),
            );
          },
        );
      },
    );
  }

  // ─── MODAL 2: PRATINJAU BERITA ACARA PENYELESAIAN (BAP) DALAM APP ─────────
  void _showBapPreviewModal({required int complaintId, String? bapUrl}) {
    showModalBottomSheet<void>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) {
        return DraggableScrollableSheet(
          initialChildSize: 0.88,
          minChildSize: 0.55,
          maxChildSize: 0.95,
          expand: false,
          builder: (_, scrollController) {
            return FutureBuilder(
              future: _apiClient.dio.get('/complaints/$complaintId'),
              builder: (context, snapshot) {
                Map<String, dynamic>? c;
                if (snapshot.hasData && snapshot.data?.data?['data'] != null) {
                  c = snapshot.data!.data['data'] as Map<String, dynamic>;
                }

                final beforeUrl = c?['photo_url']?.toString();
                final afterUrl = (c?['completion_photo_url'] ?? c?['photo_after_url'])?.toString();
                final ratingObj = c?['rating'];
                final ratingVal = ratingObj is Map ? ratingObj['rating'] : null;

                return Container(
                  decoration: const BoxDecoration(
                    color: AppColors.surface,
                    borderRadius: BorderRadius.vertical(top: Radius.circular(22)),
                  ),
                  child: Column(
                    children: [
                      Padding(
                        padding: const EdgeInsets.fromLTRB(16, 10, 12, 10),
                        child: Column(
                          children: [
                            Container(
                              width: 40,
                              height: 4,
                              decoration: BoxDecoration(
                                color: AppColors.border,
                                borderRadius: BorderRadius.circular(4),
                              ),
                            ),
                            const SizedBox(height: 10),
                            Row(
                              children: [
                                const Icon(LucideIcons.fileCheck2, size: 18, color: AppColors.statusCompleted),
                                const SizedBox(width: 8),
                                Expanded(
                                  child: Text(
                                    'Berita Acara Penyelesaian (BAP)',
                                    maxLines: 1,
                                    overflow: TextOverflow.ellipsis,
                                    style: GoogleFonts.outfit(
                                      fontSize: 15,
                                      fontWeight: FontWeight.bold,
                                      color: AppColors.textPrimary,
                                    ),
                                  ),
                                ),
                                IconButton(
                                  onPressed: () => Navigator.pop(ctx),
                                  icon: const Icon(LucideIcons.x, size: 19, color: AppColors.textSecondary),
                                  visualDensity: VisualDensity.compact,
                                ),
                              ],
                            ),
                          ],
                        ),
                      ),
                      const Divider(height: 1, color: AppColors.border),

                      Expanded(
                        child: snapshot.connectionState == ConnectionState.waiting
                            ? const Center(child: CircularProgressIndicator(color: AppColors.primary))
                            : c == null
                                ? Center(
                                    child: Text(
                                      'Gagal memuat detail Berita Acara.',
                                      style: GoogleFonts.inter(fontSize: 13, color: AppColors.textSecondary),
                                    ),
                                  )
                                : SingleChildScrollView(
                                    controller: scrollController,
                                    padding: const EdgeInsets.all(16),
                                    child: Container(
                                      width: double.infinity,
                                      padding: const EdgeInsets.all(16),
                                      decoration: BoxDecoration(
                                        color: AppColors.surface,
                                        borderRadius: BorderRadius.circular(14),
                                        border: Border.all(color: AppColors.border),
                                      ),
                                      child: Column(
                                        crossAxisAlignment: CrossAxisAlignment.stretch,
                                        children: [
                                          _buildOfficialLetterhead(
                                            docTitle: 'BERITA ACARA PENYELESAIAN (BAP)',
                                            subtitle: 'Kode Tiket: #${c['public_code'] ?? '-'}',
                                            printedAt: _formattedNowWita(),
                                          ),
                                          const SizedBox(height: 14),

                                          Container(
                                            padding: const EdgeInsets.all(12),
                                            decoration: BoxDecoration(
                                              color: AppColors.cardSurface,
                                              borderRadius: BorderRadius.circular(10),
                                              border: Border.all(color: AppColors.border),
                                            ),
                                            child: Column(
                                              children: [
                                                _buildDocInfoRow('Judul Laporan', '${c['title'] ?? '-'}'),
                                                _buildDocInfoRow('Kategori / Tipe', '${c['category_name'] ?? 'Umum'} • ${c['damage_type'] == 'major' ? 'Kerusakan Berat' : 'Kerusakan Ringan'}'),
                                                _buildDocInfoRow('Lokasi', '${c['address'] ?? '-'}'),
                                                _buildDocInfoRow('Pelapor', c['is_anonymous'] == true ? 'Anonim' : '${c['citizen_name'] ?? c['reporter_name'] ?? 'Warga'}'),
                                                _buildDocInfoRow('Regu Pelaksana', '${c['team_name'] ?? _team?['team_name'] ?? '-'}'),
                                                _buildDocInfoRow('Status / Rating', 'Selesai • ${ratingVal != null ? '$ratingVal / 5' : 'Belum dinilai'}', isLast: true),
                                              ],
                                            ),
                                          ),
                                          const SizedBox(height: 14),

                                          Text(
                                            'DOKUMENTASI SEBELUM & SESUDAH',
                                            style: GoogleFonts.inter(
                                              fontSize: 11,
                                              fontWeight: FontWeight.bold,
                                              letterSpacing: 0.4,
                                              color: AppColors.textSecondary,
                                            ),
                                          ),
                                          const SizedBox(height: 8),
                                          Row(
                                            children: [
                                              Expanded(
                                                child: _buildBapPhotoBox(
                                                  label: 'Kondisi Awal',
                                                  imageUrl: beforeUrl,
                                                ),
                                              ),
                                              const SizedBox(width: 10),
                                              Expanded(
                                                child: _buildBapPhotoBox(
                                                  label: 'Hasil Perbaikan',
                                                  imageUrl: afterUrl,
                                                ),
                                              ),
                                            ],
                                          ),
                                          const SizedBox(height: 16),

                                          _buildSignatureBlock(
                                            rightRole: 'Petugas / Regu Pelaksana',
                                            rightName: _individual?['name']?.toString() ?? '-',
                                          ),
                                        ],
                                      ),
                                    ),
                                  ),
                      ),

                      Container(
                        padding: const EdgeInsets.fromLTRB(16, 10, 16, 14),
                        decoration: const BoxDecoration(
                          color: AppColors.surface,
                          border: Border(top: BorderSide(color: AppColors.border)),
                        ),
                        child: SafeArea(
                          top: false,
                          child: Row(
                            children: [
                              Expanded(
                                child: OutlinedButton(
                                  onPressed: () => Navigator.pop(ctx),
                                  style: OutlinedButton.styleFrom(
                                    foregroundColor: AppColors.textPrimary,
                                    side: const BorderSide(color: AppColors.border),
                                    padding: const EdgeInsets.symmetric(vertical: 11),
                                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                  ),
                                  child: Text(
                                    'Tutup',
                                    style: GoogleFonts.inter(fontSize: 12.5, fontWeight: FontWeight.bold),
                                  ),
                                ),
                              ),
                              if (bapUrl != null && bapUrl.isNotEmpty) ...[
                                const SizedBox(width: 10),
                                Expanded(
                                  child: ElevatedButton.icon(
                                    onPressed: () => _openExternalUrl(bapUrl),
                                    icon: const Icon(LucideIcons.download, size: 15),
                                    label: Text(
                                      'Simpan PDF',
                                      style: GoogleFonts.inter(fontSize: 12.5, fontWeight: FontWeight.bold),
                                    ),
                                    style: ElevatedButton.styleFrom(
                                      backgroundColor: AppColors.primary,
                                      foregroundColor: Colors.white,
                                      elevation: 0,
                                      padding: const EdgeInsets.symmetric(vertical: 11),
                                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                    ),
                                  ),
                                ),
                              ],
                            ],
                          ),
                        ),
                      ),
                    ],
                  ),
                );
              },
            );
          },
        );
      },
    );
  }

  Widget _buildOfficialLetterhead({
    required String docTitle,
    String? subtitle,
    required String printedAt,
  }) {
    return Column(
      children: [
        Text(
          'PEMERINTAH KABUPATEN',
          textAlign: TextAlign.center,
          style: GoogleFonts.inter(
            fontSize: 10,
            fontWeight: FontWeight.bold,
            letterSpacing: 0.8,
            color: AppColors.textSecondary,
          ),
        ),
        const SizedBox(height: 2),
        Text(
          'DINAS PEKERJAAN UMUM DAN PENATAAN RUANG (PUPR)',
          textAlign: TextAlign.center,
          style: GoogleFonts.outfit(
            fontSize: 13,
            fontWeight: FontWeight.bold,
            color: AppColors.textPrimary,
          ),
        ),
        const SizedBox(height: 2),
        Text(
          'SI-APPIK • Lasusua, Sulawesi Tenggara',
          textAlign: TextAlign.center,
          style: GoogleFonts.inter(
            fontSize: 10.5,
            color: AppColors.textSecondary,
          ),
        ),
        const SizedBox(height: 8),
        const Divider(thickness: 1.5, color: AppColors.textPrimary),
        const SizedBox(height: 8),
        Text(
          docTitle,
          textAlign: TextAlign.center,
          style: GoogleFonts.outfit(
            fontSize: 13.5,
            fontWeight: FontWeight.bold,
            color: AppColors.textPrimary,
          ),
        ),
        if (subtitle != null) ...[
          const SizedBox(height: 2),
          Text(
            subtitle,
            textAlign: TextAlign.center,
            style: GoogleFonts.inter(
              fontSize: 11.5,
              fontWeight: FontWeight.bold,
              color: AppColors.primary,
            ),
          ),
        ],
        const SizedBox(height: 2),
        Text(
          'Dicetak: $printedAt WITA',
          textAlign: TextAlign.center,
          style: GoogleFonts.inter(fontSize: 10.5, color: AppColors.textSecondary),
        ),
      ],
    );
  }

  Widget _buildDocInfoRow(String label, String value, {bool isLast = false}) {
    return Container(
      padding: EdgeInsets.only(bottom: isLast ? 0 : 7, top: 3),
      margin: EdgeInsets.only(bottom: isLast ? 0 : 5),
      decoration: BoxDecoration(
        border: isLast ? null : const Border(bottom: BorderSide(color: AppColors.border)),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 108,
            child: Text(
              label,
              style: GoogleFonts.inter(
                fontSize: 11,
                fontWeight: FontWeight.w600,
                color: AppColors.textSecondary,
              ),
            ),
          ),
          const SizedBox(width: 8),
          Expanded(
            child: Text(
              value,
              softWrap: true,
              style: GoogleFonts.inter(
                fontSize: 11.5,
                fontWeight: FontWeight.w600,
                color: AppColors.textPrimary,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildBapPhotoBox({required String label, String? imageUrl}) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        ClipRRect(
          borderRadius: BorderRadius.circular(10),
          child: AspectRatio(
            aspectRatio: 16 / 10,
            child: Container(
              color: AppColors.cardSurface,
              child: (imageUrl != null && imageUrl.isNotEmpty)
                  ? Image.network(
                      imageUrl,
                      fit: BoxFit.cover,
                      errorBuilder: (_, __, ___) => const Center(
                        child: Icon(LucideIcons.imageOff, size: 22, color: AppColors.textSecondary),
                      ),
                    )
                  : const Center(
                      child: Icon(LucideIcons.image, size: 22, color: AppColors.textSecondary),
                    ),
            ),
          ),
        ),
        const SizedBox(height: 4),
        Text(
          label,
          maxLines: 1,
          overflow: TextOverflow.ellipsis,
          style: GoogleFonts.inter(
            fontSize: 11,
            fontWeight: FontWeight.w600,
            color: AppColors.textSecondary,
          ),
        ),
      ],
    );
  }

  Widget _buildSignatureBlock({required String rightRole, required String rightName}) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Expanded(
          child: Column(
            children: [
              Text('Mengetahui,', style: GoogleFonts.inter(fontSize: 10.5, color: AppColors.textSecondary)),
              Text(
                'Operator Dinas PUPR',
                textAlign: TextAlign.center,
                style: GoogleFonts.inter(fontSize: 11, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
              ),
              const SizedBox(height: 36),
              const Divider(color: AppColors.textSecondary),
              Text(
                'Kabupaten',
                textAlign: TextAlign.center,
                style: GoogleFonts.inter(fontSize: 10.5, fontWeight: FontWeight.w600, color: AppColors.textPrimary),
              ),
            ],
          ),
        ),
        const SizedBox(width: 16),
        Expanded(
          child: Column(
            children: [
              Text('Lasusua,', style: GoogleFonts.inter(fontSize: 10.5, color: AppColors.textSecondary)),
              Text(
                rightRole,
                textAlign: TextAlign.center,
                style: GoogleFonts.inter(fontSize: 11, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
              ),
              const SizedBox(height: 36),
              const Divider(color: AppColors.textSecondary),
              Text(
                rightName,
                textAlign: TextAlign.center,
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
                style: GoogleFonts.inter(fontSize: 10.5, fontWeight: FontWeight.w600, color: AppColors.textPrimary),
              ),
            ],
          ),
        ),
      ],
    );
  }

  // ─── REUSABLE WIDGETS (KOMPAK & TANPA GAP EKSTRA) ─────────────────────────
  Widget _buildKpiGrid(List<_KpiCardData> items) {
    final rows = <Widget>[];
    for (int i = 0; i < items.length; i += 2) {
      final left = items[i];
      final right = (i + 1 < items.length) ? items[i + 1] : null;
      rows.add(
        IntrinsicHeight(
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Expanded(child: _buildSingleKpiCard(left)),
              const SizedBox(width: 10),
              Expanded(
                child: right != null ? _buildSingleKpiCard(right) : const SizedBox.shrink(),
              ),
            ],
          ),
        ),
      );
      if (i + 2 < items.length) {
        rows.add(const SizedBox(height: 10));
      }
    }
    return Column(
      mainAxisSize: MainAxisSize.min,
      children: rows,
    );
  }

  Widget _buildSingleKpiCard(_KpiCardData d) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 13, vertical: 12),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: AppColors.border),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        mainAxisSize: MainAxisSize.min,
        children: [
          Text(
            d.title,
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
            style: GoogleFonts.inter(
              fontSize: 11,
              fontWeight: FontWeight.w600,
              color: AppColors.textSecondary,
            ),
          ),
          const SizedBox(height: 6),
          FittedBox(
            fit: BoxFit.scaleDown,
            alignment: Alignment.centerLeft,
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.baseline,
              textBaseline: TextBaseline.alphabetic,
              children: [
                Text(
                  d.value,
                  style: GoogleFonts.outfit(
                    fontSize: 21,
                    fontWeight: FontWeight.bold,
                    color: d.valueColor,
                  ),
                ),
                if (d.suffix != null) ...[
                  const SizedBox(width: 2),
                  Text(
                    d.suffix!,
                    style: GoogleFonts.inter(
                      fontSize: 11.5,
                      color: AppColors.textSecondary,
                    ),
                  ),
                ],
              ],
            ),
          ),
          const SizedBox(height: 4),
          Text(
            d.sub,
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
            style: GoogleFonts.inter(
              fontSize: 10.5,
              color: AppColors.textSecondary,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildKpiSingleWideCard({
    required String title,
    required String value,
    required String sub,
    required Color valueColor,
    required IconData icon,
  }) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(13),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: AppColors.border),
      ),
      child: Row(
        children: [
          Container(
            width: 38,
            height: 38,
            decoration: BoxDecoration(
              color: AppColors.primaryContainer,
              borderRadius: BorderRadius.circular(11),
            ),
            child: Icon(icon, color: AppColors.primary, size: 18),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: GoogleFonts.inter(
                    fontSize: 11.5,
                    fontWeight: FontWeight.w600,
                    color: AppColors.textSecondary,
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  value,
                  style: GoogleFonts.outfit(
                    fontSize: 21,
                    fontWeight: FontWeight.bold,
                    color: valueColor,
                  ),
                ),
                Text(
                  sub,
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
    );
  }

  Widget _buildSectionContainer({
    required String title,
    String? trailing,
    required Widget child,
  }) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.border),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            title,
            style: GoogleFonts.outfit(
              fontSize: 14.5,
              fontWeight: FontWeight.bold,
              color: AppColors.textPrimary,
            ),
          ),
          if (trailing != null && trailing.isNotEmpty) ...[
            const SizedBox(height: 2),
            Text(
              trailing,
              style: GoogleFonts.inter(
                fontSize: 11.5,
                color: AppColors.textSecondary,
              ),
            ),
          ],
          const SizedBox(height: 10),
          child,
        ],
      ),
    );
  }

  Widget _buildEmptyBox(String text) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 16, horizontal: 8),
      child: Center(
        child: Text(
          text,
          textAlign: TextAlign.center,
          style: GoogleFonts.inter(
            fontSize: 12.5,
            color: AppColors.textSecondary,
          ),
        ),
      ),
    );
  }

  Widget _buildTaskRowCard({
    required int id,
    required String code,
    required String title,
    required String address,
    required String category,
    required String status,
    required String ratingText,
    String? bapUrl,
  }) {
    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: AppColors.cardSurface,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AppColors.border),
      ),
      child: InkWell(
        onTap: () => Navigator.push(
          context,
          MaterialPageRoute(builder: (_) => ComplaintDetailScreen(complaintId: id)),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Text(
                  '#$code',
                  style: GoogleFonts.inter(
                    fontSize: 11.5,
                    fontWeight: FontWeight.bold,
                    color: AppColors.textPrimary,
                  ),
                ),
                const Spacer(),
                StatusBadge(status: status),
              ],
            ),
            const SizedBox(height: 6),
            Text(
              title,
              maxLines: 2,
              overflow: TextOverflow.ellipsis,
              style: GoogleFonts.outfit(
                fontSize: 13.5,
                fontWeight: FontWeight.bold,
                color: AppColors.textPrimary,
              ),
            ),
            const SizedBox(height: 2),
            Text(
              address,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              style: GoogleFonts.inter(
                fontSize: 11.5,
                color: AppColors.textSecondary,
              ),
            ),
            const SizedBox(height: 8),
            Row(
              children: [
                Expanded(
                  child: Text(
                    '$category • Rating: $ratingText',
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: GoogleFonts.inter(
                      fontSize: 11,
                      fontWeight: FontWeight.w600,
                      color: AppColors.textSecondary,
                    ),
                  ),
                ),
                if (bapUrl != null) ...[
                  const SizedBox(width: 8),
                  InkWell(
                    onTap: () => _showBapPreviewModal(complaintId: id, bapUrl: bapUrl),
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
                          const Icon(LucideIcons.fileCheck2, size: 12, color: AppColors.primary),
                          const SizedBox(width: 4),
                          Text(
                            'Cetak BAP',
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
              ],
            ),
          ],
        ),
      ),
    );
  }
}

class _KpiCardData {
  final String title;
  final String value;
  final String? suffix;
  final String sub;
  final Color valueColor;

  const _KpiCardData({
    required this.title,
    required this.value,
    this.suffix,
    required this.sub,
    required this.valueColor,
  });
}
