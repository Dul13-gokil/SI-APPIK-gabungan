import 'package:flutter/material.dart';
import 'package:lucide_icons_flutter/lucide_icons.dart';
import '../../../core/auth/auth_service.dart';
import '../../../core/network/api_client.dart';
import '../../../shared/models/complaint_model.dart';
import '../../../shared/theme/app_colors.dart';
import '../../../shared/widgets/report_pagination_controls.dart';
import '../../../shared/widgets/status_badge.dart';
import '../../complaints/presentation/complaint_detail_screen.dart';
import '../../complaints/presentation/create_complaint_screen.dart';
import '../../notifications/presentation/notifications_screen.dart';

class HomeScreen extends StatefulWidget {
  final VoidCallback? onNavigateToMap;
  final VoidCallback? onNavigateToAi;

  const HomeScreen({
    super.key,
    this.onNavigateToMap,
    this.onNavigateToAi,
  });

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  final _apiClient = ApiClient();
  final _authService = AuthService();

  UserModel? _currentUser;
  List<ComplaintModel> _complaints = [];
  Map<String, dynamic> _summary = {};
  bool _isLoading = true;
  String _selectedStatusFilter = 'ALL'; // 'ALL', 'IN_PROGRESS', 'COMPLETED', 'REJECTED'
  int? _selectedCategoryFilter; // null = Semua Kategori, 1..5
  String _selectedDamageTypeFilter = 'ALL'; // 'ALL', 'minor', 'major'
  String _feedTab = 'ALL'; // 'ALL' = Semua Laporan, 'MY' = Laporan Saya
  String _searchQuery = '';
  int _unreadNotifications = 0;
  bool _isGridView = false;
  int _currentPage = 1;
  int _pageSize = 5;

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData() async {
    setState(() => _isLoading = true);

    final user = await _authService.getCurrentUser();
    setState(() => _currentUser = user);

    await Future.wait([
      _fetchSummary(),
      _fetchComplaints(),
      _fetchUnreadNotifications(),
    ]);

    if (mounted) {
      setState(() => _isLoading = false);
    }
  }

  Future<void> _fetchUnreadNotifications() async {
    final isGuest = await _authService.isGuestMode();
    if (isGuest) return;
    try {
      final res = await _apiClient.dio.get('/notifications');
      if (res.statusCode == 200 && res.data['data'] != null) {
        if (mounted) {
          setState(() {
            _unreadNotifications = res.data['data']['unread_count'] ?? 0;
          });
        }
      }
    } catch (_) {}
  }

  Future<void> _fetchSummary() async {
    final isGuest = await _authService.isGuestMode();
    final bool isMyTab = _feedTab == 'MY';

    if (isGuest && isMyTab) {
      if (mounted) {
        setState(() {
          _summary = {
            'total_complaints': 0,
            'verification_count': 0,
            'dispatched_count': 0,
            'in_progress_count': 0,
            'waiting_verification_count': 0,
            'completed_count': 0,
          };
        });
      }
      return;
    }

    try {
      final Map<String, dynamic> queryParams = {};
      if (isMyTab && !isGuest) {
        queryParams['scope'] = 'me';
      }
      final res = await _apiClient.dio.get('/dashboard/summary', queryParameters: queryParams);
      if (res.statusCode == 200 && res.data['data'] != null) {
        if (mounted) {
          setState(() => _summary = res.data['data']);
        }
        return;
      }
    } catch (_) {}

    // Fallback: hitung langsung dari endpoint laporan
    try {
      final endpoint = (isMyTab && !isGuest) ? '/my-complaints' : '/complaints';
      final myRes = await _apiClient.dio.get(endpoint, queryParameters: {'per_page': 100});
      if (myRes.statusCode == 200 && myRes.data['data']?['items'] != null) {
        final List items = myRes.data['data']['items'];
        final myItems = items.map((e) => ComplaintModel.fromJson(e)).toList();
        final total = myItems.length;
        final verification = myItems.where((c) => ['SUBMITTED', 'VERIFICATION', 'VERIFIED'].contains(c.status)).length;
        final dispatched = myItems.where((c) => c.status == 'DISPATCHED').length;
        final inProg = myItems.where((c) => ['IN_PROGRESS', 'REVISION_REQUIRED'].contains(c.status)).length;
        final waitingVerif = myItems.where((c) => c.status == 'WAITING_VERIFICATION').length;
        final completed = myItems.where((c) => ['COMPLETED', 'CLOSED'].contains(c.status)).length;
        if (mounted) {
          setState(() {
            _summary = {
              'total_complaints': total,
              'verification_count': verification,
              'dispatched_count': dispatched,
              'in_progress_count': inProg,
              'waiting_verification_count': waitingVerif,
              'completed_count': completed,
            };
          });
        }
      }
    } catch (_) {}
  }

  Future<void> _fetchComplaints() async {
    try {
      final Map<String, dynamic> params = {'per_page': 50};

      String endpoint;
      if (_feedTab == 'MY') {
        endpoint = '/my-complaints';
      } else {
        endpoint = '/complaints';
      }
      final res = await _apiClient.dio.get(endpoint, queryParameters: params);
      if (res.statusCode == 200 && res.data['data']?['items'] != null) {
        final List items = res.data['data']['items'];
        if (mounted) {
          setState(() {
            _complaints = items.map((e) => ComplaintModel.fromJson(e)).toList();
          });
        }
      }
    } catch (_) {}
  }

  Map<int, String> get _activeCategoryMap {
    return const {
      1: 'Jalan & Jembatan',
      2: 'Penerangan Jalan Umum (PJU)',
      3: 'Fasilitas Umum',
    };
  }

  List<ComplaintModel> get _filteredComplaints {
    return _complaints.where((c) {
      // 0. Kebijakan Tab: Laporan ditolak (REJECTED) hanya tampil di 'Laporan Saya' (_feedTab == 'MY').
      // Di tab publik (Semua Laporan), seluruh status aktif (Verifikasi, Ditugaskan, Dikerjakan, Validasi/Inspeksi Mutu, Tuntas) ditampilkan.
      if (_feedTab != 'MY' && c.status == 'REJECTED') {
        return false;
      }

      // 1. Filter Kategori Fasilitas
      if (_selectedCategoryFilter != null) {
        final targetCatId = _selectedCategoryFilter!;
        final matchesId = c.categoryId == targetCatId;
        final targetCatName = (_activeCategoryMap[targetCatId] ?? '').toLowerCase();
        final compCatName = (c.categoryName ?? '').toLowerCase();
        final matchesName = targetCatName.isNotEmpty &&
            (compCatName.contains(targetCatName) || targetCatName.contains(compCatName));
        if (!matchesId && !matchesName) {
          return false;
        }
      }

      // 1b. Filter Tipe Laporan (Kerusakan Ringan vs Kerusakan Berat)
      if (_selectedDamageTypeFilter != 'ALL') {
        final compDamageType = (c.damageType ?? 'minor').toLowerCase().trim();
        final targetDamageType = _selectedDamageTypeFilter.toLowerCase().trim();
        if (compDamageType != targetDamageType) {
          return false;
        }
      }

      // 2. Filter Status Sederhana: Semua, Sedang Diproses, Selesai, dan Ditolak
      if (_selectedStatusFilter != 'ALL') {
        if (_selectedStatusFilter == 'PROCESSING') {
          // Sedang diproses mencakup seluruh tahapan aktif: verifikasi, penugasan dinas, pengerjaan lapangan, dan pengecekan akhir
          final isProcessing = ['SUBMITTED', 'VERIFICATION', 'VERIFIED', 'DISPATCHED', 'IN_PROGRESS', 'REVISION_REQUIRED', 'WAITING_VERIFICATION'].contains(c.status);
          if (!isProcessing) return false;
        } else if (_selectedStatusFilter == 'COMPLETED') {
          if (!['COMPLETED', 'CLOSED'].contains(c.status)) {
            return false;
          }
        } else if (_selectedStatusFilter == 'REJECTED') {
          if (c.status != 'REJECTED') {
            return false;
          }
        } else if (c.status != _selectedStatusFilter) {
          return false;
        }
      }

      // 3. Filter Pencarian Teks
      if (_searchQuery.isNotEmpty) {
        final title = c.title.toLowerCase();
        final address = c.address.toLowerCase();
        final code = c.publicCode.toLowerCase();
        final q = _searchQuery.toLowerCase();
        if (!title.contains(q) && !address.contains(q) && !code.contains(q)) {
          return false;
        }
      }

      return true;
    }).toList();
  }

  List<ComplaintModel> get _paginatedComplaints {
    final all = _filteredComplaints;
    if (all.isEmpty) return [];
    final totalPages = (all.length / _pageSize).ceil().clamp(1, 9999);
    final safePage = _currentPage.clamp(1, totalPages);
    final start = (safePage - 1) * _pageSize;
    final end = (start + _pageSize).clamp(0, all.length);
    return all.sublist(start, end);
  }

  Widget _buildComplaintGridRows(List<ComplaintModel> items) {
    final rows = <Widget>[];
    for (int i = 0; i < items.length; i += 2) {
      final first = items[i];
      final second = (i + 1 < items.length) ? items[i + 1] : null;
      rows.add(
        Padding(
          padding: const EdgeInsets.only(bottom: 10),
          child: IntrinsicHeight(
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                Expanded(child: _buildComplaintGridCard(first)),
                const SizedBox(width: 10),
                Expanded(
                  child: second != null
                      ? _buildComplaintGridCard(second)
                      : const SizedBox.shrink(),
                ),
              ],
            ),
          ),
        ),
      );
    }
    return Column(children: rows);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0,
        title: Row(
          children: [
            Container(
              width: 36,
              height: 36,
              padding: const EdgeInsets.all(4),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(10),
                border: Border.all(color: AppColors.border),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withValues(alpha: 0.04),
                    blurRadius: 4,
                    offset: const Offset(0, 1),
                  ),
                ],
              ),
              child: Image.asset(
                'assets/images/logo_pin.png',
                fit: BoxFit.contain,
              ),
            ),
            const SizedBox(width: 10),
            Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  _currentUser?.name ?? 'Warga Kabupaten',
                  style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
                ),
                Text(
                  _currentUser?.isAnonymous == true ? 'Mode Anonim' : 'Warga Terdaftar',
                  style: const TextStyle(fontSize: 11, color: AppColors.textSecondary),
                ),
              ],
            ),
          ],
        ),
        actions: [
          IconButton(
            icon: Stack(
              clipBehavior: Clip.none,
              children: [
                const Icon(LucideIcons.bell, color: AppColors.textPrimary, size: 22),
                if (_unreadNotifications > 0)
                  Positioned(
                    top: -4,
                    right: -4,
                    child: Container(
                      padding: const EdgeInsets.all(3),
                      decoration: const BoxDecoration(
                        color: AppColors.statusRejected,
                        shape: BoxShape.circle,
                      ),
                      constraints: const BoxConstraints(minWidth: 16, minHeight: 16),
                      child: Text(
                        '$_unreadNotifications',
                        textAlign: TextAlign.center,
                        style: const TextStyle(
                          color: Colors.white,
                          fontSize: 9,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ),
                  ),
              ],
            ),
            onPressed: () async {
              await Navigator.of(context).push(
                MaterialPageRoute(builder: (_) => const NotificationsScreen()),
              );
              _fetchUnreadNotifications();
            },
          ),
          const SizedBox(width: 8),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: _loadData,
        child: ListView(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
          children: [
            // Quick KPI Banner
            _buildKpiBanner(),
            const SizedBox(height: 16),

            // Action Card Banner
            _buildActionCard(),
            const SizedBox(height: 20),

            // Search Bar
            TextField(
              decoration: InputDecoration(
                hintText: 'Cari aduan atau kode (ADU-...)...',
                prefixIcon: const Icon(LucideIcons.search, size: 18),
                filled: true,
                fillColor: Colors.white,
                contentPadding: const EdgeInsets.symmetric(vertical: 0, horizontal: 16),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(14),
                  borderSide: const BorderSide(color: AppColors.border),
                ),
                enabledBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(14),
                  borderSide: const BorderSide(color: AppColors.border),
                ),
              ),
              onChanged: (v) => setState(() => _searchQuery = v),
            ),
            const SizedBox(height: 14),

            // Segmented Switch: Semua Laporan / Laporan Saya (Arsip dihapus)
            Container(
              padding: const EdgeInsets.all(4),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(14),
                border: Border.all(color: AppColors.border),
              ),
              child: Row(
                children: [
                  Expanded(
                    child: GestureDetector(
                      behavior: HitTestBehavior.opaque,
                      onTap: () {
                        if (_feedTab != 'ALL') {
                          setState(() {
                            _feedTab = 'ALL';
                            if (_selectedStatusFilter == 'REJECTED' || _selectedStatusFilter == 'WAITING_VERIFICATION') {
                              _selectedStatusFilter = 'ALL';
                            }
                          });
                          _fetchComplaints();
                          _fetchSummary();
                        }
                      },
                      child: Container(
                        padding: const EdgeInsets.symmetric(vertical: 10),
                        decoration: BoxDecoration(
                          color: _feedTab == 'ALL' ? AppColors.primary : Colors.transparent,
                          borderRadius: BorderRadius.circular(10),
                        ),
                        child: Center(
                          child: Text(
                            'Semua Laporan',
                            style: TextStyle(
                              fontSize: 12,
                              fontWeight: FontWeight.bold,
                              color: _feedTab == 'ALL' ? Colors.white : AppColors.textSecondary,
                            ),
                          ),
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(width: 4),
                  Expanded(
                    child: GestureDetector(
                      behavior: HitTestBehavior.opaque,
                      onTap: () {
                        if (_feedTab != 'MY') {
                          setState(() => _feedTab = 'MY');
                          _fetchComplaints();
                          _fetchSummary();
                        }
                      },
                      child: Container(
                        padding: const EdgeInsets.symmetric(vertical: 10),
                        decoration: BoxDecoration(
                          color: _feedTab == 'MY' ? AppColors.primary : Colors.transparent,
                          borderRadius: BorderRadius.circular(10),
                        ),
                        child: Center(
                          child: Row(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Icon(
                                LucideIcons.user,
                                size: 14,
                                color: _feedTab == 'MY' ? Colors.white : AppColors.textSecondary,
                              ),
                              const SizedBox(width: 6),
                              Text(
                                'Laporan Saya',
                                style: TextStyle(
                                  fontSize: 12,
                                  fontWeight: FontWeight.bold,
                                  color: _feedTab == 'MY' ? Colors.white : AppColors.textSecondary,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 14),

            // Filter Section: Status (Dikerjakan, Selesai, Ditolak) & Tipe Laporan (Kategori)
            _buildFilterSection(),
            const SizedBox(height: 16),

            // Feed Header + Toggle Daftar / Petak di Kanan Atas
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              crossAxisAlignment: CrossAxisAlignment.center,
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        _feedTab == 'MY'
                            ? 'Riwayat Laporan Saya'
                            : 'Riwayat Laporan Warga',
                        style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        '${_filteredComplaints.length} Laporan ditemukan',
                        style: const TextStyle(fontSize: 11.5, color: AppColors.textSecondary),
                      ),
                    ],
                  ),
                ),
                const SizedBox(width: 8),
                ReportLayoutToggle(
                  isGrid: _isGridView,
                  onChanged: (val) => setState(() => _isGridView = val),
                ),
              ],
            ),
            const SizedBox(height: 12),

            // Complaint Feed Items
            if (_isLoading)
              const Center(
                child: Padding(
                  padding: EdgeInsets.all(40),
                  child: CircularProgressIndicator(),
                ),
              )
            else if (_filteredComplaints.isEmpty)
              Container(
                padding: const EdgeInsets.symmetric(vertical: 40, horizontal: 20),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: AppColors.border),
                ),
                child: const Column(
                  children: [
                    Icon(LucideIcons.inbox, size: 48, color: AppColors.textSecondary),
                    SizedBox(height: 12),
                    Text(
                      'Belum ada laporan pada kategori ini',
                      style: TextStyle(fontWeight: FontWeight.bold, color: AppColors.textPrimary),
                    ),
                    SizedBox(height: 4),
                    Text(
                      'Jadilah yang pertama melaporkan fasilitas rusak di sekitar Anda.',
                      textAlign: TextAlign.center,
                      style: TextStyle(color: AppColors.textSecondary, fontSize: 12),
                    ),
                  ],
                ),
              )
            else ...[
              if (_isGridView)
                _buildComplaintGridRows(_paginatedComplaints)
              else
                ..._paginatedComplaints.map((c) => _buildComplaintCard(c)),
              ReportPaginationBar(
                totalItems: _filteredComplaints.length,
                currentPage: _currentPage,
                pageSize: _pageSize,
                itemUnitLabel: 'laporan',
                onPageChanged: (page) => setState(() => _currentPage = page),
                onPageSizeChanged: (size) => setState(() {
                  _pageSize = size;
                  _currentPage = 1;
                }),
              ),
            ],

            const SizedBox(height: 80),
          ],
        ),
      ),
    );
  }

  Widget _buildKpiBanner() {
    final total = _summary['total_complaints'] ?? _summary['total'] ?? 0;
    final verification = _summary['verification_count'] ?? _summary['pending_verification'] ?? _summary['pending'] ?? 0;
    final dispatched = _summary['dispatched_count'] ?? _summary['dispatched'] ?? 0;
    final inProg = _summary['in_progress_count'] ?? _summary['in_progress'] ?? 0;
    final waitingVerif = _summary['waiting_verification_count'] ?? _summary['waiting_verification'] ?? 0;
    final completed = _summary['completed_count'] ?? _summary['completed'] ?? 0;

    final processingTotal = (verification as int) + (dispatched as int) + (inProg as int) + (waitingVerif as int);

    return Column(
      children: [
        // Baris 1: Semua Laporan (Full Width)
        Row(
          children: [
            _buildKpiCard('Semua Laporan', '$total', AppColors.primary, LucideIcons.layers, 'Total aduan masuk'),
          ],
        ),
        const SizedBox(height: 8),
        // Baris 2: Diproses + Selesai
        Row(
          children: [
            _buildKpiCard('Diproses', '$processingTotal', AppColors.statusInProgress, LucideIcons.clock, 'Sedang ditangani'),
            const SizedBox(width: 8),
            _buildKpiCard('Selesai', '$completed', AppColors.statusCompleted, LucideIcons.checkCircle2, 'Tuntas 100%'),
          ],
        ),
      ],
    );
  }


  Widget _buildKpiCard(String label, String value, Color color, IconData icon, String subtitle) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 8),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(14),
          border: Border.all(color: color.withValues(alpha: 0.25)),
          boxShadow: [
            BoxShadow(
              color: color.withValues(alpha: 0.04),
              blurRadius: 8,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              crossAxisAlignment: CrossAxisAlignment.center,
              children: [
                Expanded(
                  child: Text(
                    label,
                    style: const TextStyle(
                      fontSize: 10.5,
                      color: AppColors.textSecondary,
                      fontWeight: FontWeight.w700,
                    ),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),
                ),
                Icon(icon, size: 13, color: color),
              ],
            ),
            const SizedBox(height: 5),
            Text(
              value,
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.w800, color: color),
            ),
            const SizedBox(height: 2),
            Text(
              subtitle,
              style: TextStyle(
                fontSize: 9,
                color: AppColors.textSecondary.withValues(alpha: 0.8),
                fontWeight: FontWeight.w500,
              ),
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildActionCard() {
    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [AppColors.primary, AppColors.primaryLight],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(20),
        boxShadow: [
          BoxShadow(
            color: AppColors.primary.withValues(alpha: 0.25),
            blurRadius: 14,
            offset: const Offset(0, 6),
          ),
        ],
      ),
      child: Row(
        children: [
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'Laporkan Kerusakan',
                  style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 16),
                ),
                const SizedBox(height: 4),
                const Text(
                  'Foto jalan rusak, jembatan, atau lampu jalan. AI kami bantu triase cepat.',
                  style: TextStyle(color: Colors.white70, fontSize: 12),
                ),
                const SizedBox(height: 12),
                ElevatedButton.icon(
                  onPressed: () {
                    Navigator.of(context).push(
                      MaterialPageRoute(builder: (_) => const CreateComplaintScreen()),
                    ).then((_) => _loadData());
                  },
                  icon: const Icon(LucideIcons.camera, size: 16, color: AppColors.primary),
                  label: const Text('Buat Laporan', style: TextStyle(color: AppColors.primary, fontWeight: FontWeight.bold, fontSize: 13)),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Colors.white,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                    padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(width: 12),
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: Colors.white.withValues(alpha: 0.15),
              shape: BoxShape.circle,
            ),
            child: const Icon(LucideIcons.sparkles, color: Colors.white, size: 36),
          ),
        ],
      ),
    );
  }

  Widget _buildFilterSection() {
    // Filter Sederhana Warga: Semua, Diproses, Selesai, dan Ditolak (Khusus Laporan Saya)
    final statusFilters = [
      {'label': 'Semua', 'value': 'ALL', 'icon': LucideIcons.layers},
      {'label': 'Diproses', 'value': 'PROCESSING', 'icon': LucideIcons.clock},
      {'label': 'Selesai', 'value': 'COMPLETED', 'icon': LucideIcons.checkCircle2},
      if (_feedTab == 'MY')
        {'label': 'Ditolak', 'value': 'REJECTED', 'icon': LucideIcons.xCircle},
    ];

    final categoryFilters = <Map<String, dynamic>>[
      {'label': 'Semua Kategori', 'id': null},
      ..._activeCategoryMap.entries.map((e) => {'label': e.value, 'id': e.key}),
    ];

    // Cari label kategori yang sedang terpilih
    String selectedCategoryLabel = 'Semua Kategori';
    for (final cat in categoryFilters) {
      if (cat['id'] == _selectedCategoryFilter) {
        selectedCategoryLabel = cat['label'] as String;
        break;
      }
    }

    // Cari label tipe laporan yang sedang terpilih
    String selectedDamageTypeLabel = 'Semua Tipe';
    if (_selectedDamageTypeFilter == 'minor') selectedDamageTypeLabel = 'Kerusakan Ringan';
    if (_selectedDamageTypeFilter == 'major') selectedDamageTypeLabel = 'Kerusakan Berat';

    final hasActiveSubFilter = _selectedCategoryFilter != null || _selectedDamageTypeFilter != 'ALL';

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // 1. Status Filters: Simetris Rata Penuh (Full-Width Distributed, Tanpa Whitespace di Kanan)
        Row(
          children: statusFilters.asMap().entries.map((entry) {
            final idx = entry.key;
            final f = entry.value;
            final isSelected = _selectedStatusFilter == f['value'];
            final icon = f['icon'] as IconData;
            
            Color chipColor;
            if (f['value'] == 'PROCESSING') {
              chipColor = AppColors.statusInProgress;
            } else if (f['value'] == 'COMPLETED') {
              chipColor = AppColors.statusCompleted;
            } else if (f['value'] == 'REJECTED') {
              chipColor = AppColors.statusRejected;
            } else {
              chipColor = AppColors.primary;
            }

            return Expanded(
              child: Padding(
                padding: EdgeInsets.only(right: idx == statusFilters.length - 1 ? 0 : 6),
                child: InkWell(
                  onTap: () {
                    setState(() => _selectedStatusFilter = f['value'] as String);
                  },
                  borderRadius: BorderRadius.circular(10),
                  child: Container(
                    padding: const EdgeInsets.symmetric(vertical: 8, horizontal: 4),
                    decoration: BoxDecoration(
                      color: isSelected ? chipColor.withValues(alpha: 0.12) : Colors.white,
                      borderRadius: BorderRadius.circular(10),
                      border: Border.all(
                        color: isSelected ? chipColor : AppColors.border,
                        width: isSelected ? 1.4 : 1.0,
                      ),
                    ),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(icon, size: 12, color: isSelected ? chipColor : AppColors.textSecondary),
                        const SizedBox(width: 4),
                        Flexible(
                          child: Text(
                            f['label'] as String,
                            style: TextStyle(
                              color: isSelected ? chipColor : AppColors.textSecondary,
                              fontWeight: isSelected ? FontWeight.bold : FontWeight.w500,
                              fontSize: 11,
                            ),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
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
        const SizedBox(height: 8),

        // 2. Baris Dropdown Filter (Kategori & Tipe Laporan) - Satu Baris Rapi & Elegan
        Row(
          children: [
            // Dropdown Filter Kategori
            Expanded(
              child: PopupMenuButton<int?>(
                tooltip: 'Pilih Kategori',
                initialValue: _selectedCategoryFilter,
                onSelected: (int? catId) {
                  setState(() => _selectedCategoryFilter = catId);
                },
                itemBuilder: (context) => categoryFilters.map((c) {
                  final isCatSelected = _selectedCategoryFilter == c['id'];
                  return PopupMenuItem<int?>(
                    value: c['id'] as int?,
                    child: Row(
                      children: [
                        Icon(
                          isCatSelected ? LucideIcons.check : LucideIcons.layers,
                          size: 14,
                          color: isCatSelected ? AppColors.primary : AppColors.textSecondary,
                        ),
                        const SizedBox(width: 8),
                        Expanded(
                          child: Text(
                            c['label'] as String,
                            style: TextStyle(
                              fontSize: 12,
                              fontWeight: isCatSelected ? FontWeight.bold : FontWeight.normal,
                              color: isCatSelected ? AppColors.primary : AppColors.textPrimary,
                            ),
                          ),
                        ),
                      ],
                    ),
                  );
                }).toList(),
                child: Container(
                  padding: const EdgeInsets.symmetric(vertical: 8, horizontal: 10),
                  decoration: BoxDecoration(
                    color: _selectedCategoryFilter != null ? AppColors.primary.withValues(alpha: 0.08) : Colors.white,
                    borderRadius: BorderRadius.circular(10),
                    border: Border.all(
                      color: _selectedCategoryFilter != null ? AppColors.primary : AppColors.border,
                    ),
                  ),
                  child: Row(
                    children: [
                      Icon(
                        LucideIcons.layers,
                        size: 13,
                        color: _selectedCategoryFilter != null ? AppColors.primary : AppColors.textSecondary,
                      ),
                      const SizedBox(width: 5),
                      Expanded(
                        child: Text(
                          selectedCategoryLabel,
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: TextStyle(
                            fontSize: 11,
                            fontWeight: _selectedCategoryFilter != null ? FontWeight.bold : FontWeight.w500,
                            color: _selectedCategoryFilter != null ? AppColors.primary : AppColors.textPrimary,
                          ),
                        ),
                      ),
                      Icon(
                        LucideIcons.chevronDown,
                        size: 13,
                        color: _selectedCategoryFilter != null ? AppColors.primary : AppColors.textSecondary,
                      ),
                    ],
                  ),
                ),
              ),
            ),
            const SizedBox(width: 8),

            // Dropdown Filter Tipe Laporan (Ringan vs Berat)
            Expanded(
              child: PopupMenuButton<String>(
                tooltip: 'Pilih Tipe Laporan',
                initialValue: _selectedDamageTypeFilter,
                onSelected: (String val) {
                  setState(() => _selectedDamageTypeFilter = val);
                },
                itemBuilder: (context) => [
                  {'label': 'Semua Tipe', 'value': 'ALL'},
                  {'label': 'Kerusakan Ringan', 'value': 'minor'},
                  {'label': 'Kerusakan Berat', 'value': 'major'},
                ].map((d) {
                  final isTypeSelected = _selectedDamageTypeFilter == d['value'];
                  return PopupMenuItem<String>(
                    value: d['value']!,
                    child: Row(
                      children: [
                        Icon(
                          isTypeSelected ? LucideIcons.check : LucideIcons.alertCircle,
                          size: 14,
                          color: isTypeSelected ? AppColors.primary : AppColors.textSecondary,
                        ),
                        const SizedBox(width: 8),
                        Expanded(
                          child: Text(
                            d['label']!,
                            style: TextStyle(
                              fontSize: 12,
                              fontWeight: isTypeSelected ? FontWeight.bold : FontWeight.normal,
                              color: isTypeSelected ? AppColors.primary : AppColors.textPrimary,
                            ),
                          ),
                        ),
                      ],
                    ),
                  );
                }).toList(),
                child: Container(
                  padding: const EdgeInsets.symmetric(vertical: 8, horizontal: 10),
                  decoration: BoxDecoration(
                    color: _selectedDamageTypeFilter != 'ALL' ? AppColors.primary.withValues(alpha: 0.08) : Colors.white,
                    borderRadius: BorderRadius.circular(10),
                    border: Border.all(
                      color: _selectedDamageTypeFilter != 'ALL' ? AppColors.primary : AppColors.border,
                    ),
                  ),
                  child: Row(
                    children: [
                      Icon(
                        LucideIcons.alertCircle,
                        size: 13,
                        color: _selectedDamageTypeFilter != 'ALL' ? AppColors.primary : AppColors.textSecondary,
                      ),
                      const SizedBox(width: 5),
                      Expanded(
                        child: Text(
                          selectedDamageTypeLabel,
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: TextStyle(
                            fontSize: 11,
                            fontWeight: _selectedDamageTypeFilter != 'ALL' ? FontWeight.bold : FontWeight.w500,
                            color: _selectedDamageTypeFilter != 'ALL' ? AppColors.primary : AppColors.textPrimary,
                          ),
                        ),
                      ),
                      Icon(
                        LucideIcons.chevronDown,
                        size: 13,
                        color: _selectedDamageTypeFilter != 'ALL' ? AppColors.primary : AppColors.textSecondary,
                      ),
                    ],
                  ),
                ),
              ),
            ),

            // Tombol Reset Cepat (Tampil jika ada sub-filter aktif)
            if (hasActiveSubFilter) ...[
              const SizedBox(width: 6),
              InkWell(
                onTap: () {
                  setState(() {
                    _selectedCategoryFilter = null;
                    _selectedDamageTypeFilter = 'ALL';
                  });
                },
                borderRadius: BorderRadius.circular(10),
                child: Container(
                  padding: const EdgeInsets.all(8),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(10),
                    border: Border.all(color: AppColors.border),
                  ),
                  child: const Icon(LucideIcons.rotateCcw, size: 14, color: AppColors.textSecondary),
                ),
              ),
            ],
          ],
        ),
      ],
    );
  }

  Widget _buildComplaintGridCard(ComplaintModel c) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: AppColors.border),
      ),
      child: InkWell(
        onTap: () {
          Navigator.of(context).push(
            MaterialPageRoute(
              builder: (_) => ComplaintDetailScreen(complaintId: c.id),
            ),
          ).then((_) => _loadData());
        },
        borderRadius: BorderRadius.circular(14),
        child: Padding(
          padding: const EdgeInsets.all(11),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Flexible(
                        child: Text(
                          c.publicCode,
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: const TextStyle(
                            fontWeight: FontWeight.bold,
                            fontSize: 10.5,
                            color: AppColors.primary,
                          ),
                        ),
                      ),
                      const SizedBox(width: 4),
                      StatusBadge(status: c.status),
                    ],
                  ),
                  const SizedBox(height: 7),
                  Text(
                    c.title,
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(
                      fontSize: 12.5,
                      fontWeight: FontWeight.bold,
                      color: AppColors.textPrimary,
                      height: 1.25,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    c.address,
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(
                      fontSize: 10.5,
                      color: AppColors.textSecondary,
                      height: 1.3,
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 8),
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  if (c.categoryName != null)
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                      decoration: BoxDecoration(
                        color: AppColors.background,
                        borderRadius: BorderRadius.circular(5),
                        border: Border.all(color: AppColors.border),
                      ),
                      child: Text(
                        c.categoryName!,
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: const TextStyle(fontSize: 9.5, color: AppColors.textSecondary),
                      ),
                    ),
                  const SizedBox(height: 6),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(
                        c.damageType == 'major' ? 'Berat' : 'Ringan',
                        style: TextStyle(
                          fontSize: 9.5,
                          fontWeight: FontWeight.w700,
                          color: c.damageType == 'major' ? AppColors.damageTypeMajor : AppColors.textSecondary,
                        ),
                      ),
                      Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          const Icon(LucideIcons.thumbsUp, size: 11, color: AppColors.primaryLight),
                          const SizedBox(width: 3),
                          Text(
                            '${c.supportsCount}',
                            style: const TextStyle(
                              fontSize: 10,
                              color: AppColors.textSecondary,
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildComplaintCard(ComplaintModel c) {
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.border),
      ),
      child: InkWell(
        onTap: () {
          Navigator.of(context).push(
            MaterialPageRoute(
              builder: (_) => ComplaintDetailScreen(complaintId: c.id),
            ),
          ).then((_) => _loadData());
        },
        borderRadius: BorderRadius.circular(16),
        child: Padding(
          padding: const EdgeInsets.all(14),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                crossAxisAlignment: CrossAxisAlignment.center,
                children: [
                  Expanded(
                    child: Wrap(
                      spacing: 6,
                      runSpacing: 4,
                      crossAxisAlignment: WrapCrossAlignment.center,
                      children: [
                        Text(
                          c.publicCode,
                          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 11.5, color: AppColors.primary),
                        ),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                          decoration: BoxDecoration(
                            color: c.damageType == 'major' ? AppColors.damageTypeMajorBg : AppColors.cardSurface,
                            borderRadius: BorderRadius.circular(6),
                            border: Border.all(
                              color: c.damageType == 'major' ? AppColors.damageTypeMajorBorder : AppColors.border,
                            ),
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Icon(
                                c.damageType == 'major' ? LucideIcons.route : LucideIcons.circleDot,
                                size: 10,
                                color: c.damageType == 'major' ? AppColors.damageTypeMajor : AppColors.textSecondary,
                              ),
                              const SizedBox(width: 3),
                              Text(
                                c.damageType == 'major' ? 'Kerusakan Berat' : 'Kerusakan Ringan',
                                style: TextStyle(
                                  fontSize: 9.5,
                                  fontWeight: FontWeight.bold,
                                  color: c.damageType == 'major' ? AppColors.damageTypeMajor : AppColors.textSecondary,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(width: 6),
                  StatusBadge(status: c.status),
                ],
              ),
              const SizedBox(height: 8),
              Text(
                c.title,
                style: const TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
              ),
              const SizedBox(height: 4),
              Text(
                c.address,
                style: const TextStyle(fontSize: 12, color: AppColors.textSecondary),
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
              ),
              const SizedBox(height: 6),
              Row(
                children: [
                  const Icon(LucideIcons.user, size: 12, color: AppColors.primary),
                  const SizedBox(width: 4),
                  Expanded(
                    child: Text(
                      'Pelapor: ${c.reporterName}',
                      style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: AppColors.textPrimary),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 8),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  if (c.categoryName != null)
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                      decoration: BoxDecoration(
                        color: AppColors.background,
                        borderRadius: BorderRadius.circular(6),
                        border: Border.all(color: AppColors.border),
                      ),
                      child: Text(
                        c.categoryName!,
                        style: const TextStyle(fontSize: 11, color: AppColors.textSecondary),
                      ),
                    )
                  else
                    const SizedBox.shrink(),
                  Row(
                    children: [
                      const Icon(LucideIcons.thumbsUp, size: 13, color: AppColors.primaryLight),
                      const SizedBox(width: 4),
                      Text(
                        '${c.supportsCount} Dukungan',
                        style: const TextStyle(fontSize: 11, color: AppColors.textSecondary, fontWeight: FontWeight.w600),
                      ),
                    ],
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }
}
