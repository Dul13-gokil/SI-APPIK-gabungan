import 'package:flutter/material.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:latlong2/latlong.dart';
import 'package:lucide_icons_flutter/lucide_icons.dart';
import '../../../core/auth/auth_service.dart';
import '../../../core/network/api_client.dart';
import '../../../shared/data/kolaka_utara_boundary.dart';
import '../../../shared/theme/app_colors.dart';
import '../../../shared/widgets/v2_components.dart';
import '../../complaints/presentation/complaint_detail_screen.dart';

class MapScreen extends StatefulWidget {
  final bool? isPetugas;
  const MapScreen({super.key, this.isPetugas});

  @override
  State<MapScreen> createState() => _MapScreenState();
}

class _MapScreenState extends State<MapScreen> {
  final MapController _mapController = MapController();
  final ApiClient _apiClient = ApiClient();
  final AuthService _authService = AuthService();

  UserModel? _currentUser;
  bool get _isPetugas => widget.isPetugas ?? (_currentUser?.role == 'PETUGAS');

  List<dynamic> _markersData = [];
  bool _isLoading = true;
  String _selectedFilter = 'ALL';
  Map<String, dynamic>? _selectedComplaintPreview;

  // Titik Pusat Ibukota Lasusua, Kabupaten
  static const LatLng _initialCenter = LatLng(-3.50601, 120.88780);

  @override
  void initState() {
    super.initState();
    _loadUser();
    _fetchMapMarkers();
  }

  Future<void> _loadUser() async {
    final user = await _authService.getCurrentUser();
    if (mounted) {
      setState(() => _currentUser = user);
    }
  }

  Future<void> _fetchMapMarkers() async {
    setState(() => _isLoading = true);
    try {
      final res = await _apiClient.dio.get('/dashboard/map');
      if (res.statusCode == 200 && res.data['data'] != null) {
        setState(() {
          _markersData = res.data['data'];
          _isLoading = false;
        });
      }
    } catch (_) {
      setState(() => _isLoading = false);
    }
  }

  Color _getStatusColor(String? status) {
    switch (status) {
      case 'SUBMITTED':
      case 'PENDING':
      case 'VERIFIED':
      case 'VERIFICATION':
        return const Color(0xFFEF4444); // Verifikasi (Merah)
      case 'DISPATCHED':
        return AppColors.statusDispatched; // Ditugaskan (Biru Terang V2)
      case 'IN_PROGRESS':
      case 'REVISION_REQUIRED':
      case 'WAITING_VERIFICATION':
        return const Color(0xFFF59E0B); // Ditangani (Kuning / Amber)
      case 'COMPLETED':
      case 'RESOLVED':
      case 'CLOSED':
        return const Color(0xFF10B981); // Selesai (Hijau Emerald)
      case 'REJECTED':
        return const Color(0xFFDC2626);
      default:
        return const Color(0xFFEF4444);
    }
  }

  Widget _getStatusIcon(String? status) {
    switch (status) {
      case 'SUBMITTED':
      case 'PENDING':
      case 'VERIFIED':
      case 'VERIFICATION':
        return const Icon(LucideIcons.alertCircle, color: Colors.white, size: 13);
      case 'DISPATCHED':
        return const Icon(LucideIcons.send, color: Colors.white, size: 12);
      case 'IN_PROGRESS':
      case 'REVISION_REQUIRED':
      case 'WAITING_VERIFICATION':
        return const Icon(LucideIcons.wrench, color: Colors.white, size: 12);
      case 'COMPLETED':
      case 'RESOLVED':
      case 'CLOSED':
        return const Icon(LucideIcons.check, color: Colors.white, size: 14);
      case 'REJECTED':
        return const Icon(LucideIcons.x, color: Colors.white, size: 14);
      default:
        return const Icon(LucideIcons.alertCircle, color: Colors.white, size: 13);
    }
  }

  String _getStatusText(String? status) {
    switch (status) {
      case 'SUBMITTED':
      case 'PENDING':
      case 'VERIFIED':
      case 'VERIFICATION':
        return 'Verifikasi';
      case 'DISPATCHED':
        return 'Ditugaskan';
      case 'IN_PROGRESS':
      case 'REVISION_REQUIRED':
        return 'Dikerjakan';
      case 'WAITING_VERIFICATION':
        return 'Validasi';
      case 'COMPLETED':
      case 'RESOLVED':
      case 'CLOSED':
        return 'Selesai';
      case 'REJECTED':
        return 'Ditolak';
      default:
        return 'Verifikasi';
    }
  }

  int get _allEligibleCount {
    if (_isPetugas) {
      const allowed = ['DISPATCHED', 'IN_PROGRESS', 'REVISION_REQUIRED', 'WAITING_VERIFICATION', 'COMPLETED', 'RESOLVED', 'CLOSED'];
      return _markersData.where((m) => allowed.contains(m['status'])).length;
    }
    return _markersData.where((m) => m['status'] != 'REJECTED').length;
  }

  int get _verifCount {
    return _markersData.where((m) => ['SUBMITTED', 'PENDING', 'VERIFIED', 'VERIFICATION'].contains(m['status'])).length;
  }

  int get _dispatchedCount {
    return _markersData.where((m) => m['status'] == 'DISPATCHED').length;
  }

  int get _diprosesCount {
    if (_isPetugas) {
      const officerProcessing = ['DISPATCHED', 'IN_PROGRESS', 'REVISION_REQUIRED', 'WAITING_VERIFICATION'];
      return _markersData.where((m) => officerProcessing.contains(m['status'])).length;
    }
    const allProcessing = [
      'SUBMITTED', 'PENDING', 'VERIFIED', 'VERIFICATION',
      'DISPATCHED',
      'IN_PROGRESS', 'REVISION_REQUIRED',
      'WAITING_VERIFICATION'
    ];
    return _markersData.where((m) => allProcessing.contains(m['status'])).length;
  }

  int get _inProgressCount {
    return _markersData.where((m) => ['IN_PROGRESS', 'REVISION_REQUIRED'].contains(m['status'])).length;
  }

  int get _waitingVerifCount {
    return _markersData.where((m) => m['status'] == 'WAITING_VERIFICATION').length;
  }

  int get _completedCount {
    return _markersData.where((m) => ['COMPLETED', 'RESOLVED', 'CLOSED'].contains(m['status'])).length;
  }

  List<dynamic> get _filteredMarkers {
    List<dynamic> baseList;
    if (_isPetugas) {
      // Pengawas Lapangan: Sembunyikan laporan yang berstatus verifikasi (SUBMITTED/PENDING/VERIFICATION/VERIFIED) dan REJECTED.
      // Hanya laporan berstatus Ditugaskan, Dikerjakan, Validasi, dan Selesai yang muncul pada peta GIS.
      const allowedOfficerStatuses = [
        'DISPATCHED',
        'IN_PROGRESS',
        'REVISION_REQUIRED',
        'WAITING_VERIFICATION',
        'COMPLETED',
        'RESOLVED',
        'CLOSED',
      ];
      baseList = _markersData.where((m) => allowedOfficerStatuses.contains(m['status'])).toList();
    } else {
      // Warga: Sembunyikan laporan yang ditolak (REJECTED)
      baseList = _markersData.where((m) => m['status'] != 'REJECTED').toList();
    }

    if (_selectedFilter == 'ALL') return baseList;
    if (_selectedFilter == 'PROCESSING') {
      const processingStatuses = [
        'SUBMITTED', 'PENDING', 'VERIFIED', 'VERIFICATION',
        'DISPATCHED',
        'IN_PROGRESS', 'REVISION_REQUIRED',
        'WAITING_VERIFICATION'
      ];
      return baseList.where((m) => processingStatuses.contains(m['status'])).toList();
    }
    if (_selectedFilter == 'VERIFICATION' || _selectedFilter == 'SUBMITTED') {
      const verifStatuses = ['SUBMITTED', 'PENDING', 'VERIFIED', 'VERIFICATION'];
      return baseList.where((m) => verifStatuses.contains(m['status'])).toList();
    }
    if (_selectedFilter == 'DISPATCHED') {
      return baseList.where((m) => m['status'] == 'DISPATCHED').toList();
    }
    if (_selectedFilter == 'IN_PROGRESS' || _selectedFilter == 'IN_HANDLING') {
      const inProgressStatuses = ['IN_PROGRESS', 'REVISION_REQUIRED'];
      return baseList.where((m) => inProgressStatuses.contains(m['status'])).toList();
    }
    if (_selectedFilter == 'WAITING_VERIFICATION') {
      return baseList.where((m) => m['status'] == 'WAITING_VERIFICATION').toList();
    }
    if (_selectedFilter == 'COMPLETED') {
      return baseList.where((m) => m['status'] == 'COMPLETED' || m['status'] == 'RESOLVED' || m['status'] == 'CLOSED').toList();
    }
    return baseList.where((m) => m['status'] == _selectedFilter).toList();
  }

  void _selectComplaintAndCenter(dynamic item, double lat, double lng) {
    setState(() {
      _selectedComplaintPreview = item as Map<String, dynamic>;
    });
    // Pindahkan kamera peta ke selatan (lat - 0.0042) agar marker berada di viewport atas
    // dan tidak bertabrakan / tertutup oleh kartu preview detail di bawah
    _mapController.move(LatLng(lat - 0.0042, lng), 14.5);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      body: SafeArea(
        bottom: false,
        child: Column(
          children: [
            V2FloatingHeader(
              title: 'Peta GIS Sebaran',
              subtitle: 'Wilayah Kabupaten',
              leading: Container(
                width: 36,
                height: 36,
                decoration: BoxDecoration(
                  color: AppColors.primaryContainer,
                  borderRadius: BorderRadius.circular(11),
                  border: Border.all(color: AppColors.primaryBorder),
                ),
                child: const Icon(LucideIcons.mapPin, size: 18, color: AppColors.primary),
              ),
              actions: [
                InkWell(
                  borderRadius: BorderRadius.circular(11),
                  onTap: _fetchMapMarkers,
                  child: Container(
                    width: 36,
                    height: 36,
                    decoration: BoxDecoration(
                      color: AppColors.cardSurface,
                      borderRadius: BorderRadius.circular(11),
                      border: Border.all(color: AppColors.border),
                    ),
                    child: const Icon(LucideIcons.refreshCw, size: 17, color: AppColors.textPrimary),
                  ),
                ),
              ],
            ),
            Expanded(
              child: Stack(
                children: [
          // FlutterMap OpenStreetMap Layer
          FlutterMap(
            mapController: _mapController,
            options: const MapOptions(
              initialCenter: _initialCenter,
              initialZoom: 11.5,
              minZoom: 7.0,
              maxZoom: 18.0,
            ),
            children: [
              TileLayer(
                urlTemplate: 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
                userAgentPackageName: 'com.kabupaten.aduan',
              ),
              // Garis Batas Wilayah Administratif Resmi Kabupaten (OpenStreetMap Polygon)
              PolygonLayer(
                polygons: [
                  Polygon(
                    points: kolakaUtaraBoundary,
                    borderColor: AppColors.primary,
                    borderStrokeWidth: 2.5,
                    color: const Color(0xFF3B82F6).withValues(alpha: 0.08),
                  ),
                ],
              ),
              // Polyline Layer untuk Laporan Ruas Panjang
              PolylineLayer(
                polylines: _filteredMarkers.where((m) =>
                  (m['damage_type'] == 'major' || m['end_latitude'] != null) &&
                  m['latitude'] != null && m['longitude'] != null &&
                  m['end_latitude'] != null && m['end_longitude'] != null
                ).map((item) {
                  final startLat = (item['latitude'] as num).toDouble();
                  final startLng = (item['longitude'] as num).toDouble();
                  final endLat = (item['end_latitude'] as num).toDouble();
                  final endLng = (item['end_longitude'] as num).toDouble();
                  final color = _getStatusColor(item['status']);

                  List<LatLng> points = [];
                  if (item['route_coordinates'] != null && item['route_coordinates'] is List && (item['route_coordinates'] as List).isNotEmpty) {
                    try {
                      points = (item['route_coordinates'] as List).map((coord) {
                        final list = coord as List;
                        return LatLng((list[0] as num).toDouble(), (list[1] as num).toDouble());
                      }).toList();
                    } catch (_) {}
                  }
                  if (points.isEmpty) {
                    points = [
                      LatLng(startLat, startLng),
                      LatLng(endLat, endLng),
                    ];
                  }

                  return Polyline(
                    points: points,
                    strokeWidth: 4.5,
                    color: color,
                    borderColor: Colors.white,
                    borderStrokeWidth: 1.5,
                  );
                }).toList(),
              ),
              // Marker Layer dengan Pin Bundar Gambar 5
              MarkerLayer(
                markers: [
                  // Titik Awal Laporan
                  ..._filteredMarkers.map((item) {
                    final lat = (item['latitude'] as num?)?.toDouble() ?? -3.50601;
                    final lng = (item['longitude'] as num?)?.toDouble() ?? 120.88780;
                    final color = _getStatusColor(item['status']);

                    return Marker(
                      point: LatLng(lat, lng),
                      width: 32,
                      height: 32,
                      child: GestureDetector(
                        onTap: () {
                          _selectComplaintAndCenter(item, lat, lng);
                        },
                        child: Container(
                          width: 28,
                          height: 28,
                          decoration: BoxDecoration(
                            color: color,
                            shape: BoxShape.circle,
                            border: Border.all(color: Colors.white, width: 2.5),
                            boxShadow: [
                              BoxShadow(
                                color: Colors.black.withValues(alpha: 0.35),
                                blurRadius: 6,
                                offset: const Offset(0, 3),
                              ),
                            ],
                          ),
                          child: Center(
                            child: _getStatusIcon(item['status']),
                          ),
                        ),
                      ),
                    );
                  }),
                  // Titik Akhir Khusus Ruas Panjang
                  ..._filteredMarkers.where((m) =>
                    (m['damage_type'] == 'major' || m['end_latitude'] != null) &&
                    m['end_latitude'] != null && m['end_longitude'] != null
                  ).map((item) {
                    final endLat = (item['end_latitude'] as num).toDouble();
                    final endLng = (item['end_longitude'] as num).toDouble();

                    return Marker(
                      point: LatLng(endLat, endLng),
                      width: 24,
                      height: 24,
                      child: GestureDetector(
                        onTap: () {
                          _selectComplaintAndCenter(item, endLat, endLng);
                        },
                        child: Container(
                          width: 20,
                          height: 20,
                          decoration: BoxDecoration(
                            color: const Color(0xFFEF4444),
                            shape: BoxShape.circle,
                            border: Border.all(color: Colors.white, width: 2.0),
                            boxShadow: [
                              BoxShadow(
                                color: Colors.black.withValues(alpha: 0.3),
                                blurRadius: 4,
                                offset: const Offset(0, 2),
                              ),
                            ],
                          ),
                          child: const Icon(LucideIcons.flag, size: 10, color: Colors.white),
                        ),
                      ),
                    );
                  }),
                ],
              ),
            ],
          ),

          // Top Filter Bar (Warna, Ikon, Tulisan 100% Selaras Landing Page)
          Positioned(
            top: 12,
            left: 12,
            right: 12,
            child: SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              child: Row(
                children: _isPetugas
                    ? [
                        _buildMapFilterChip('Semua ($_allEligibleCount)', 'ALL', AppColors.primary, LucideIcons.layers),
                        _buildMapFilterChip('Diproses ($_diprosesCount)', 'PROCESSING', const Color(0xFFF59E0B), LucideIcons.clock),
                        _buildMapFilterChip('Ditugaskan ($_dispatchedCount)', 'DISPATCHED', AppColors.statusDispatched, LucideIcons.send),
                        _buildMapFilterChip('Dikerjakan ($_inProgressCount)', 'IN_PROGRESS', const Color(0xFFD97706), LucideIcons.wrench),
                        _buildMapFilterChip('Validasi ($_waitingVerifCount)', 'WAITING_VERIFICATION', const Color(0xFFF59E0B), LucideIcons.clipboardCheck),
                        _buildMapFilterChip('Selesai ($_completedCount)', 'COMPLETED', const Color(0xFF10B981), LucideIcons.check),
                      ]
                    : [
                        _buildMapFilterChip('Semua ($_allEligibleCount)', 'ALL', AppColors.primary, LucideIcons.layers),
                        _buildMapFilterChip('Diproses ($_diprosesCount)', 'PROCESSING', const Color(0xFFF59E0B), LucideIcons.clock),
                        _buildMapFilterChip('Verifikasi ($_verifCount)', 'VERIFICATION', const Color(0xFFEF4444), LucideIcons.alertCircle),
                        _buildMapFilterChip('Ditugaskan ($_dispatchedCount)', 'DISPATCHED', AppColors.statusDispatched, LucideIcons.send),
                        _buildMapFilterChip('Dikerjakan ($_inProgressCount)', 'IN_PROGRESS', const Color(0xFFD97706), LucideIcons.wrench),
                        _buildMapFilterChip('Validasi ($_waitingVerifCount)', 'WAITING_VERIFICATION', const Color(0xFFF59E0B), LucideIcons.clipboardCheck),
                        _buildMapFilterChip('Selesai ($_completedCount)', 'COMPLETED', const Color(0xFF10B981), LucideIcons.check),
                      ],
              ),
            ),
          ),

          // Floating Recenter Button
          Positioned(
            bottom: _selectedComplaintPreview != null ? 265 : 104,
            right: 16,
            child: FloatingActionButton.small(
              backgroundColor: Colors.white,
              foregroundColor: AppColors.primary,
              elevation: 4,
              onPressed: () {
                _mapController.move(_initialCenter, 13.0);
              },
              child: const Icon(LucideIcons.locateFixed, size: 20),
            ),
          ),

          // Floating Map Legend Pasif di Pojok Kiri Bawah (Standar Google Maps / GIS Modern persis Landing Page)
          Positioned(
            bottom: _selectedComplaintPreview != null ? 265 : 104,
            left: 16,
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
              decoration: BoxDecoration(
                color: Colors.white.withValues(alpha: 0.95),
                borderRadius: BorderRadius.circular(16),
                border: Border.all(color: AppColors.border),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withValues(alpha: 0.1),
                    blurRadius: 10,
                    offset: const Offset(0, 3),
                  ),
                ],
              ),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  if (!_isPetugas) ...[
                    _buildLegendDot(const Color(0xFFEF4444), LucideIcons.alertCircle, 'Verifikasi'),
                    const SizedBox(width: 8),
                  ],
                  _buildLegendDot(AppColors.statusDispatched, LucideIcons.send, 'Ditugaskan'),
                  const SizedBox(width: 8),
                  _buildLegendDot(const Color(0xFFF59E0B), LucideIcons.clock, 'Diproses'),
                  const SizedBox(width: 8),
                  _buildLegendDot(const Color(0xFF10B981), LucideIcons.check, 'Selesai'),
                ],
              ),
            ),
          ),

          // Bottom Preview Card
          if (_selectedComplaintPreview != null)
            Positioned(
              bottom: 100,
              left: 16,
              right: 16,
              child: Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(20),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withValues(alpha: 0.15),
                      blurRadius: 16,
                      offset: const Offset(0, 6),
                    ),
                  ],
                ),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
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
                                () {
                                  final raw = (_selectedComplaintPreview!['public_code'] ?? 'ADUAN').toString();
                                  return raw.startsWith('#') ? raw : '#$raw';
                                }(),
                                style: const TextStyle(
                                  color: AppColors.textPrimary,
                                  fontWeight: FontWeight.bold,
                                  fontSize: 12,
                                ),
                              ),
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                decoration: BoxDecoration(
                                  color: _getStatusColor(_selectedComplaintPreview!['status']).withValues(alpha: 0.1),
                                  borderRadius: BorderRadius.circular(99),
                                  border: Border.all(
                                    color: _getStatusColor(_selectedComplaintPreview!['status']).withValues(alpha: 0.35),
                                  ),
                                ),
                                child: Row(
                                  mainAxisSize: MainAxisSize.min,
                                  children: [
                                    Container(
                                      width: 6,
                                      height: 6,
                                      decoration: BoxDecoration(
                                        color: _getStatusColor(_selectedComplaintPreview!['status']),
                                        shape: BoxShape.circle,
                                      ),
                                    ),
                                    const SizedBox(width: 4),
                                    Text(
                                      _getStatusText(_selectedComplaintPreview!['status']),
                                      style: TextStyle(
                                        color: _getStatusColor(_selectedComplaintPreview!['status']),
                                        fontWeight: FontWeight.bold,
                                        fontSize: 10,
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            ],
                          ),
                        ),
                          IconButton(
                            padding: EdgeInsets.zero,
                            constraints: const BoxConstraints(),
                            icon: const Icon(LucideIcons.x, size: 18, color: AppColors.textSecondary),
                            onPressed: () => setState(() => _selectedComplaintPreview = null),
                          ),
                        ],
                      ),
                    const SizedBox(height: 8),
                    Text(
                      _selectedComplaintPreview!['title'] ?? 'Laporan Kerusakan',
                      style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15, color: AppColors.textPrimary),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      'Kategori: ${_selectedComplaintPreview!['category_name'] ?? 'Infrastruktur'}',
                      style: const TextStyle(fontSize: 12, color: AppColors.textSecondary),
                    ),
                    const SizedBox(height: 12),
                    ElevatedButton(
                      onPressed: () {
                        final id = _selectedComplaintPreview!['id'];
                        Navigator.of(context).push(
                          MaterialPageRoute(builder: (_) => ComplaintDetailScreen(complaintId: id)),
                        );
                      },
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppColors.primary,
                        foregroundColor: Colors.white,
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                        padding: const EdgeInsets.symmetric(vertical: 10),
                      ),
                      child: const Center(
                        child: Text('Buka Detail & Timeline', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                      ),
                    ),
                  ],
                ),
              ),
            ),

          if (_isLoading)
            const Positioned(
              top: 70,
              left: 0,
              right: 0,
              child: Center(
                child: Card(
                  child: Padding(
                    padding: EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                    child: Text('Memuat titik peta...', style: TextStyle(fontSize: 12)),
                  ),
                ),
              ),
            ),
        ],
      ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildMapFilterChip(String label, String value, Color activeColor, IconData icon) {
    final isSelected = _selectedFilter == value;
    return Padding(
      padding: const EdgeInsets.only(right: 8),
      child: GestureDetector(
        onTap: () => setState(() => _selectedFilter = value),
        child: AnimatedContainer(
          duration: const Duration(milliseconds: 180),
          padding: const EdgeInsets.symmetric(horizontal: 11, vertical: 7),
          decoration: BoxDecoration(
            color: isSelected ? activeColor : AppColors.surface,
            borderRadius: BorderRadius.circular(10),
            border: Border.all(
              color: isSelected ? activeColor : AppColors.border,
              width: isSelected ? 1.4 : 1.0,
            ),
          ),
          child: Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(
                icon,
                size: 13,
                color: isSelected ? Colors.white : activeColor,
              ),
              const SizedBox(width: 6),
              Text(
                label,
                style: TextStyle(
                  color: isSelected ? Colors.white : AppColors.textPrimary,
                  fontWeight: FontWeight.bold,
                  fontSize: 11.5,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildLegendDot(Color color, IconData icon, String label) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Container(
          width: 14,
          height: 14,
          decoration: BoxDecoration(
            color: color,
            shape: BoxShape.circle,
          ),
          child: Center(
            child: Icon(icon, size: 8, color: Colors.white),
          ),
        ),
        const SizedBox(width: 4),
        Text(
          label,
          style: const TextStyle(fontSize: 10.5, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
        ),
      ],
    );
  }
}
