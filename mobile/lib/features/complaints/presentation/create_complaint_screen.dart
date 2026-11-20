import 'dart:convert';
import 'dart:io';
import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:latlong2/latlong.dart';
import 'package:geolocator/geolocator.dart';
import 'package:lucide_icons_flutter/lucide_icons.dart';
import '../../../core/auth/auth_service.dart';
import '../../../core/config/app_config.dart';
import '../../../core/location/location_service.dart';
import '../../../core/location/road_routing_service.dart';
import '../../../core/media/media_service.dart';
import '../../../core/network/api_client.dart';
import '../../../shared/data/kolaka_utara_boundary.dart';
import '../../../shared/theme/app_colors.dart';
import '../../../shared/widgets/v2_components.dart';
import '../../../shared/widgets/v2_toast.dart';
import 'complaint_detail_screen.dart';

class CreateComplaintScreen extends StatefulWidget {
  final bool initialUseAi;

  const CreateComplaintScreen({super.key, this.initialUseAi = true});

  @override
  State<CreateComplaintScreen> createState() => _CreateComplaintScreenState();
}

class _CreateComplaintScreenState extends State<CreateComplaintScreen> {
  final _formKey = GlobalKey<FormState>();
  final _titleController = TextEditingController();
  final _descriptionController = TextEditingController();
  final _addressController = TextEditingController();
  final _roadLengthController = TextEditingController();
  final _customRoadConditionController = TextEditingController();
  final MapController _mapController = MapController();

  final LocationService _locationService = LocationService();
  final MediaService _mediaService = MediaService();
  final ApiClient _apiClient = ApiClient();
  final AuthService _authService = AuthService();
  final RoadRoutingService _routingService = RoadRoutingService();

  final List<File> _selectedPhotos = [];
  double _latitude = -3.50601;
  double _longitude = 120.88780;
  double? _endLatitude;
  double? _endLongitude;
  bool _isPickingEndPoint = false;
  String _damageType = 'minor'; // 'minor' = Kerusakan Ringan, 'major' = Kerusakan Berat
  String _selectedRoadCondition = 'Jalan Tanah';
  bool _isLoadingLocation = false;
  int _selectedCategoryId = 1;
  bool _isSubmitting = false;
  late bool _useAi;

  List<LatLng> _routePoints = [];
  bool _isRouting = false;
  List<Map<String, dynamic>> _nearbyDuplicates = [];

  Future<void> _updateRoadRoute() async {
    if (_damageType != 'major' || _endLatitude == null || _endLongitude == null) {
      return;
    }
    setState(() => _isRouting = true);
    try {
      final start = LatLng(_latitude, _longitude);
      final end = LatLng(_endLatitude!, _endLongitude!);
      final result = await _routingService.getRoadRoute(start, end);
      if (mounted) {
        setState(() {
          _routePoints = result.points;
          _roadLengthController.text = result.formattedDistance;
          _isRouting = false;
        });

        if (result.distanceMeters < 500) {
          showV2Toast(
            context,
            message: 'Panjang jalan di bawah 500 meter (${result.formattedDistance}). Pertimbangkan tipe "Kerusakan Ringan".',
            type: V2ToastType.warning,
            duration: const Duration(seconds: 5),
          );
        }
      }
    } catch (e) {
      debugPrint('Routing calculation error: $e');
      if (mounted) setState(() => _isRouting = false);
    }
  }

  bool _isRoadLengthUnder500() {
    final text = _roadLengthController.text.trim().toLowerCase();
    if (text.isEmpty) return false;
    if (text.contains('km') || text.contains('kilo')) {
      final clean = text.replaceAll(RegExp(r'[^0-9\.]'), '');
      final val = double.tryParse(clean);
      if (val != null && val < 0.5) return true;
      return false;
    }
    final clean = text.replaceAll(RegExp(r'[^0-9\.]'), '');
    final val = double.tryParse(clean);
    if (val != null && val > 0 && val < 500) return true;
    return false;
  }

  int get _maxPhotos => _damageType == 'major' ? 10 : 4;
  int get _minPhotos => 1;

  void _resetFormForDamageType(String damageType) {
    setState(() {
      _damageType = damageType;
      _selectedPhotos.clear();
      _titleController.clear();
      _descriptionController.clear();
      _addressController.clear();
      _roadLengthController.clear();
      _customRoadConditionController.clear();
      _selectedRoadCondition = 'Jalan Tanah';
      _selectedCategoryId = 1;
      _aiDiagnostic = null;
      _routePoints = [];
      _endLatitude = null;
      _endLongitude = null;
      _isPickingEndPoint = false;
    });
  }

  // Validasi Geofencing Akurat: Menggunakan Garis Batas Polygon Administratif Resmi Kabupaten
  bool _isWithinKolakaUtara(double lat, double lon) {
    return isPointInKolakaUtaraPolygon(lat, lon);
  }

  void _showOutOfBoundaryDialog(double lat, double lon) {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        titlePadding: const EdgeInsets.fromLTRB(20, 20, 20, 10),
        contentPadding: const EdgeInsets.symmetric(horizontal: 20, vertical: 10),
        actionsPadding: const EdgeInsets.all(16),
        title: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: Colors.red.shade50,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: Colors.red.shade200),
              ),
              child: const Icon(LucideIcons.alertTriangle, color: Colors.red, size: 22),
            ),
            const SizedBox(width: 10),
            const Expanded(
              child: Text(
                'Di Luar Wilayah Layanan',
                style: TextStyle(
                  fontSize: 15,
                  fontWeight: FontWeight.bold,
                  color: Color(0xFF0F172A),
                ),
              ),
            ),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Sistem Pengaduan Infrastruktur saat ini khusus melayani area administratif Kabupaten.',
              style: TextStyle(fontSize: 13, height: 1.4, color: Color(0xFF334155)),
            ),
            const SizedBox(height: 12),
            Container(
              padding: const EdgeInsets.all(10),
              decoration: BoxDecoration(
                color: const Color(0xFFF8FAFC),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: const Color(0xFFE2E8F0)),
              ),
              child: Row(
                children: [
                  const Icon(LucideIcons.mapPinOff, size: 16, color: Colors.red),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      'Titik: ${lat.toStringAsFixed(4)}, ${lon.toStringAsFixed(4)} (Di Luar Kabupaten)',
                      style: const TextStyle(fontSize: 11, color: Color(0xFF64748B), fontFamily: 'monospace'),
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 10),
            const Text(
              'Silakan pilih titik lokasi kerusakan jalan atau infrastruktur di dalam wilayah 15 kecamatan Kabupaten.',
              style: TextStyle(fontSize: 12, color: Color(0xFF64748B)),
            ),
          ],
        ),
        actions: [
          SizedBox(
            width: double.infinity,
            child: ElevatedButton(
              style: ElevatedButton.styleFrom(
                backgroundColor: AppColors.primary,
                foregroundColor: Colors.white,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                padding: const EdgeInsets.symmetric(vertical: 12),
              ),
              onPressed: () {
                Navigator.of(ctx).pop();
                setState(() {
                  _latitude = -3.50601;
                  _longitude = 120.88780;
                });
                try {
                  _mapController.move(const LatLng(-3.50601, 120.88780), 15);
                } catch (_) {}
                _reverseGeocode(-3.50601, 120.88780);
              },
              child: const Text('Paham, Pilih Titik di Kabupaten', style: TextStyle(fontWeight: FontWeight.bold)),
            ),
          ),
        ],
      ),
    );
  }

  void _showGpsGuidanceDialog() {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Row(
          children: [
            Icon(LucideIcons.mapPin, color: AppColors.primary, size: 22),
            SizedBox(width: 10),
            Text('Panduan GPS & Lokasi', style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold)),
          ],
        ),
        content: const Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Untuk mendeteksi lokasi otomatis:',
              style: TextStyle(fontSize: 12.5, fontWeight: FontWeight.bold, color: Color(0xFF1E293B)),
            ),
            SizedBox(height: 6),
            Text('1. Pastikan fitur Lokasi / GPS di HP Android sudah dinyalakan (di menu bilah atas / status bar).',
                style: TextStyle(fontSize: 12, color: Color(0xFF475569))),
            SizedBox(height: 4),
            Text('2. Berikan izin akses lokasi "Saat Aplikasi Digunakan".',
                style: TextStyle(fontSize: 12, color: Color(0xFF475569))),
            SizedBox(height: 4),
            Text('3. Anda juga dapat langsung mengetuk peta untuk membidik titik kerusakan secara manual.',
                style: TextStyle(fontSize: 12, color: Color(0xFF475569))),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () {
              Navigator.of(ctx).pop();
              setState(() {
                _latitude = -3.50601;
                _longitude = 120.88780;
              });
              try {
                _mapController.move(const LatLng(-3.50601, 120.88780), 15);
              } catch (_) {}
              _reverseGeocode(-3.50601, 120.88780);
            },
            child: const Text('Pusatkan ke Lasusua', style: TextStyle(fontWeight: FontWeight.bold, color: AppColors.primary)),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.primary,
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
            ),
            onPressed: () {
              Navigator.of(ctx).pop();
              Geolocator.openLocationSettings();
            },
            child: const Text('Buka Pengaturan GPS', style: TextStyle(fontWeight: FontWeight.bold)),
          ),
        ],
      ),
    );
  }

  // AI Diagnostic State
  Map<String, dynamic>? _aiDiagnostic;
  bool _isAnalyzingAI = false;

  final List<Map<String, dynamic>> _categories = [
    {'id': 1, 'name': 'Jalan & Jembatan', 'icon': LucideIcons.truck},
    {'id': 2, 'name': 'Penerangan Jalan Umum (PJU)', 'icon': LucideIcons.lightbulb},
    {'id': 3, 'name': 'Fasilitas Umum', 'icon': LucideIcons.building2},
  ];

  @override
  void initState() {
    super.initState();
    _useAi = widget.initialUseAi;
    _fetchLocation();
  }

  @override
  void dispose() {
    _titleController.dispose();
    _descriptionController.dispose();
    _addressController.dispose();
    _roadLengthController.dispose();
    _customRoadConditionController.dispose();
    super.dispose();
  }

  // Location & Reverse Geocoding State
  bool _isGeocodingAddress = false;
  int? _regionId;
  Map<String, String>? _administrativeArea;

  Future<void> _reverseGeocode(double lat, double lon) async {
    if (!mounted) return;
    setState(() => _isGeocodingAddress = true);
    try {
      final dio = Dio(BaseOptions(
        connectTimeout: const Duration(seconds: 4),
        receiveTimeout: const Duration(seconds: 4),
      ));

      final url = '${AppConfig.baseUrl}/reverse-geocode';
      final res = await dio.get(url, queryParameters: {'lat': lat, 'lon': lon});

      if (res.statusCode == 200 && res.data != null && mounted) {
        final data = res.data['data'] as Map<String, dynamic>?;
        if (data != null) {
          final formatted = data['formatted_address'] as String? ?? '';
          final road = data['road'] as String? ?? '';
          final village = data['village'] as String? ?? '';
          final district = data['district'] as String? ?? '';
          final regency = data['regency'] as String? ?? '';
          final regionId = data['region_id'] as int?;

          setState(() {
            _regionId = regionId;
            _administrativeArea = {
              'road': road,
              'village': village,
              'district': district,
              'regency': regency,
            };
            if (formatted.isNotEmpty && !formatted.startsWith('Area Infrastruktur')) {
              _addressController.text = formatted;
            }
          });
          return;
        }
      }
    } catch (e) {
      debugPrint('Reverse geocode error: $e');
    } finally {
      if (mounted) {
        setState(() => _isGeocodingAddress = false);
      }
    }

    if (_addressController.text.isEmpty || _addressController.text.startsWith('Titik Koordinat')) {
      if (mounted) {
        setState(() {
          _addressController.text = 'Jalan Kabupaten (Lat: ${lat.toStringAsFixed(4)}, Lon: ${lon.toStringAsFixed(4)})';
        });
      }
    }
  }

  Future<void> _fetchLocation() async {
    setState(() => _isLoadingLocation = true);
    try {
      final pos = await _locationService.getCurrentLocation();
      if (mounted) {
        if (pos != null) {
          if (!_isWithinKolakaUtara(pos.latitude, pos.longitude)) {
            _showOutOfBoundaryDialog(pos.latitude, pos.longitude);
            return;
          }
          setState(() {
            _latitude = pos.latitude;
            _longitude = pos.longitude;
          });
          try {
            _mapController.move(LatLng(_latitude, _longitude), 16);
          } catch (_) {}
          await _reverseGeocode(_latitude, _longitude);
          _checkNearbyDuplicates();
          if (mounted) {
            showV2Toast(
              context,
              message: 'Lokasi GPS berhasil diperbarui sesuai posisi Anda.',
              type: V2ToastType.success,
              duration: const Duration(seconds: 2),
            );
          }
        } else {
          _showGpsGuidanceDialog();
        }
      }
    } catch (e) {
      debugPrint('Error fetch location: $e');
      if (mounted) _showGpsGuidanceDialog();
    } finally {
      if (mounted) {
        setState(() => _isLoadingLocation = false);
      }
    }
  }

  Future<void> _checkNearbyDuplicates() async {
    try {
      final res = await _apiClient.dio.get(
        '/complaints/nearby-duplicates',
        queryParameters: {
          'lat': _latitude,
          'lng': _longitude,
          'category_id': _selectedCategoryId,
          'radius': 100,
        },
      );
      if (res.statusCode == 200 && res.data['data'] is List && mounted) {
        setState(() {
          _nearbyDuplicates = List<Map<String, dynamic>>.from(
            (res.data['data'] as List).map((e) => Map<String, dynamic>.from(e as Map)),
          );
        });
      }
    } catch (_) {}
  }

  Widget _buildNearbyDuplicateBanner() {
    final first = _nearbyDuplicates.first;
    final dist = first['distance_meters'] ?? 0;
    final title = first['title'] ?? 'Laporan Infrastruktur';
    final code = first['public_code'] ?? '';
    final id = first['id'];

    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: const Color(0xFFFFFBEB),
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: const Color(0xFFFDE68A)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              const Icon(LucideIcons.mapPin, size: 16, color: AppColors.statusInProgress),
              const SizedBox(width: 6),
              Expanded(
                child: Text(
                  'Laporan serupa ditemukan (${dist}m dari titik ini)',
                  style: GoogleFonts.outfit(
                    fontSize: 13,
                    fontWeight: FontWeight.bold,
                    color: const Color(0xFF92400E),
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 4),
          Text(
            '#$code — $title. Dukung laporan ini agar lebih cepat diprioritaskan oleh Dinas PUPR.',
            style: GoogleFonts.inter(fontSize: 11.5, color: const Color(0xFF78350F), height: 1.4),
          ),
          const SizedBox(height: 8),
          Align(
            alignment: Alignment.centerRight,
            child: OutlinedButton.icon(
              onPressed: id == null
                  ? null
                  : () {
                      Navigator.push(
                        context,
                        MaterialPageRoute(
                          builder: (_) => ComplaintDetailScreen(complaintId: id as int),
                        ),
                      );
                    },
              icon: const Icon(LucideIcons.thumbsUp, size: 13),
              label: const Text('Lihat & Dukung', style: TextStyle(fontSize: 11.5, fontWeight: FontWeight.bold)),
              style: OutlinedButton.styleFrom(
                foregroundColor: const Color(0xFF92400E),
                side: const BorderSide(color: Color(0xFFF59E0B)),
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Future<void> _takePhoto() async {
    if (_selectedPhotos.length >= _maxPhotos) {
      showV2Toast(
        context,
        message: 'Maksimal $_maxPhotos foto bukti kerusakan untuk tipe ini.',
        type: V2ToastType.error,
      );
      return;
    }
    final photo = await _mediaService.takePhoto();
    if (photo != null) {
      final file = File(photo.path);
      setState(() {
        _selectedPhotos.add(file);
      });
      if (_useAi) {
        _processPhotosAI(_selectedPhotos);
      }
    }
  }

  Future<void> _pickGallery() async {
    if (_selectedPhotos.length >= _maxPhotos) {
      showV2Toast(
        context,
        message: 'Maksimal $_maxPhotos foto bukti kerusakan untuk tipe ini.',
        type: V2ToastType.error,
      );
      return;
    }
    final photo = await _mediaService.pickFromGallery();
    if (photo != null) {
      final file = File(photo.path);
      setState(() {
        _selectedPhotos.add(file);
      });
      if (_useAi) {
        _processPhotosAI(_selectedPhotos);
      }
    }
  }

  void _removePhoto(int index) {
    setState(() {
      _selectedPhotos.removeAt(index);
    });

    if (_useAi) {
      if (_selectedPhotos.isEmpty) {
        setState(() {
          _aiDiagnostic = null;
          _titleController.clear();
          _descriptionController.clear();
        });
      } else {
        // Re-analisis seluruh foto yang tersisa secara otomatis
        _processPhotosAI(_selectedPhotos);
      }
    }
  }

  void _showAddPhotoSheet() {
    if (_selectedPhotos.length >= _maxPhotos) {
      showV2Toast(
        context,
        message: 'Maksimal $_maxPhotos foto bukti kerusakan.',
        type: V2ToastType.warning,
      );
      return;
    }
    showModalBottomSheet(
      context: context,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
      builder: (ctx) => SafeArea(
        child: Padding(
          padding: const EdgeInsets.all(20),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Text(
                'Tambah Foto Bukti (${_selectedPhotos.length + 1}/$_maxPhotos)',
                style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
              ),
              const SizedBox(height: 16),
              ListTile(
                leading: const Icon(LucideIcons.camera, color: AppColors.primary),
                title: const Text('Ambil dari Kamera'),
                onTap: () {
                  Navigator.pop(ctx);
                  _takePhoto();
                },
              ),
              ListTile(
                leading: const Icon(LucideIcons.image, color: AppColors.secondary),
                title: const Text('Pilih dari Galeri'),
                onTap: () {
                  Navigator.pop(ctx);
                  _pickGallery();
                },
              ),
            ],
          ),
        ),
      ),
    );
  }

  Future<void> _processPhotosAI(List<File> files) async {
    if (files.isEmpty) {
      setState(() {
        _aiDiagnostic = null;
        _titleController.clear();
        _descriptionController.clear();
      });
      return;
    }

    setState(() {
      _isAnalyzingAI = true;
      _aiDiagnostic = null;
    });

    try {
      final formData = FormData();
      for (var i = 0; i < files.length; i++) {
        final bytes = await files[i].readAsBytes();
        formData.files.add(MapEntry(
          'photos[]',
          MultipartFile.fromBytes(bytes, filename: 'damage_$i.jpg'),
        ));
      }

      final response = await _apiClient.dio.post('/ai/classify-image', data: formData);
      if (response.statusCode == 200 && response.data['data'] != null && mounted) {
        final data = response.data['data'];
        final isValid = data['is_valid_infrastructure'] as bool? ?? false;
        final isDamaged = data['is_damaged'] as bool? ?? false;
        final categoryId = data['category_id'] as int? ?? 1;
        final categoryLabel = data['category_label'] as String? ?? 'Kerusakan Jalan';
        final llmDesc = data['llm_description'] as String? ?? '';
        final suggestedTitle = data['suggested_title'] as String? ?? '';
        final suggestedCondition = data['suggested_road_condition'] as String? ?? '';

        setState(() {
          _aiDiagnostic = data;

          const validConditions = [
            'Jalan Tanah',
            'Batu Lepas / Makadam',
            'Aspal Hancur Bergelombang',
            'Genangan Lumpur Parah',
            'Longsor / Amblas Badan Jalan',
            'Lainnya',
          ];

          if (_damageType == 'major') {
            _selectedCategoryId = 1; // Khusus Ruas Panjang selalu Jalan & Jembatan
            if (suggestedCondition.isNotEmpty && validConditions.contains(suggestedCondition)) {
              _selectedRoadCondition = suggestedCondition;
            }
          } else {
            _selectedCategoryId = categoryId;
          }

          if (!isValid) {
            showV2Toast(
              context,
              message: data['damage_detail'] as String? ??
                  'Peringatan AI: Foto tidak terdeteksi sebagai objek infrastruktur fisik (jalan, jembatan, lampu penerangan, atau fasilitas umum). Silakan pastikan foto menampilkan objek yang jelas.',
              type: V2ToastType.error,
              duration: const Duration(seconds: 4),
            );
          } else if (!isDamaged) {
            _titleController.text = suggestedTitle.isNotEmpty ? suggestedTitle : 'Kondisi Jalan Mulus / Normal';
            _descriptionController.text = llmDesc;
          } else {
            _titleController.text = suggestedTitle.isNotEmpty ? suggestedTitle : 'Laporan $categoryLabel';
            _descriptionController.text = llmDesc;
          }
        });
      }
    } catch (e) {
      debugPrint('AI process error: $e');
      if (mounted) {
        setState(() {
          _aiDiagnostic = {
            'is_valid_infrastructure': false,
            'is_damaged': false,
            'category_label': 'Foto Belum Terverifikasi',
            'confidence': 0.0,
            'severity_level': 0,
            'severity_text': 'ANALISIS TIDAK TERSEDIA',
            'damage_type': 'Analisis Foto Gagal',
            'damage_detail': 'AI tidak dapat memverifikasi foto. Pastikan layanan AI aktif lalu coba unggah ulang foto jalan yang jelas.',
            'llm_description': '',
          };
          if (_damageType == 'major') {
            _selectedCategoryId = 1;
          }
        });
      }
    } finally {
      if (mounted) setState(() => _isAnalyzingAI = false);
    }
  }

  Future<void> _submitComplaint() async {
    if (!_formKey.currentState!.validate()) return;

    if (!_isWithinKolakaUtara(_latitude, _longitude)) {
      _showOutOfBoundaryDialog(_latitude, _longitude);
      return;
    }

    if (_useAi && _aiDiagnostic?['is_valid_infrastructure'] == false) {
      showV2Toast(
        context,
        message: 'Foto belum terverifikasi sebagai kerusakan jalan. Unggah foto jalan yang jelas terlebih dahulu.',
        type: V2ToastType.error,
      );
      return;
    }

    setState(() => _isSubmitting = true);

    try {
      final currentUser = await _authService.getCurrentUser();

      final formData = FormData();
      formData.fields.add(MapEntry('title', _titleController.text.trim()));
      formData.fields.add(MapEntry('description', _descriptionController.text.trim()));
      // Kerusakan Ruas Panjang selalu dikhususkan untuk Jalan & Jembatan (ID 1)
      final effectiveCategoryId = _damageType == 'major' ? 1 : _selectedCategoryId;
      formData.fields.add(MapEntry('category_id', effectiveCategoryId.toString()));
      formData.fields.add(MapEntry(
        'address',
        _addressController.text.trim().isNotEmpty
            ? _addressController.text.trim()
            : 'Koordinat ($_latitude, $_longitude)',
      ));
      formData.fields.add(MapEntry('latitude', _latitude.toString()));
      formData.fields.add(MapEntry('longitude', _longitude.toString()));
      formData.fields.add(const MapEntry('is_anonymous', '0')); // Anonim dinonaktifkan
      if (_regionId != null) {
        formData.fields.add(MapEntry('region_id', _regionId.toString()));
      }
      if (currentUser != null) {
        formData.fields.add(MapEntry('user_id', currentUser.id.toString()));
      }

      if (_damageType == 'major') {
        if (_selectedPhotos.length < _minPhotos) {
          if (mounted) {
            showV2Toast(
              context,
              message: 'Kerusakan Berat wajib melampirkan minimal $_minPhotos foto bukti (saat ini ${_selectedPhotos.length}/$_minPhotos).',
              type: V2ToastType.error,
            );
            setState(() => _isSubmitting = false);
          }
          return;
        }

        if (_roadLengthController.text.trim().isEmpty) {
          if (mounted) {
            showV2Toast(
              context,
              message: 'Mohon cantumkan estimasi panjang ruas jalan (misal: 10 km atau 500 meter).',
              type: V2ToastType.error,
            );
            setState(() => _isSubmitting = false);
          }
          return;
        }

        if (_isRoadLengthUnder500()) {
          if (mounted) {
            showV2Toast(
              context,
              message: 'Panjang kerusakan di bawah 500 meter. Gunakan tipe Kerusakan Ringan.',
              type: V2ToastType.warning,
            );
            setState(() => _isSubmitting = false);
          }
          return;
        }

        formData.fields.add(const MapEntry('damage_type', 'major'));
        formData.fields.add(MapEntry('road_length', _roadLengthController.text.trim()));
        final condition = _selectedRoadCondition == 'Lainnya' && _customRoadConditionController.text.trim().isNotEmpty
            ? _customRoadConditionController.text.trim()
            : _selectedRoadCondition;
        formData.fields.add(MapEntry('road_condition', condition));
        final endLat = _endLatitude ?? _latitude;
        final endLng = _endLongitude ?? _longitude;
        formData.fields.add(MapEntry('end_latitude', endLat.toString()));
        formData.fields.add(MapEntry('end_longitude', endLng.toString()));

        // Menyimpan lintasan rute berkelok (snap to road) yang presisi
        final List<List<double>> routeJson = _routePoints.isNotEmpty
            ? _routePoints.map((p) => [p.latitude, p.longitude]).toList()
            : [
                [_latitude, _longitude],
                [endLat, endLng],
              ];
        formData.fields.add(MapEntry(
          'route_coordinates',
          jsonEncode(routeJson),
        ));
      } else {
        if (_selectedPhotos.isEmpty) {
          if (mounted) {
            showV2Toast(
              context,
              message: 'Wajib melampirkan minimal 1 foto bukti kerusakan.',
              type: V2ToastType.error,
            );
            setState(() => _isSubmitting = false);
          }
          return;
        }
        formData.fields.add(const MapEntry('damage_type', 'minor'));
      }

      for (var i = 0; i < _selectedPhotos.length; i++) {
        final bytes = await _selectedPhotos[i].readAsBytes();
        formData.files.add(MapEntry(
          'photos[]',
          MultipartFile.fromBytes(bytes, filename: 'evidence_$i.jpg'),
        ));
      }

      final response = await _apiClient.dio.post('/complaints', data: formData);

      if (!mounted) return;

      if (response.statusCode == 201 || response.statusCode == 200) {
        showV2Toast(
          context,
          message: 'Pengaduan baru berhasil dibuat',
          type: V2ToastType.success,
          duration: const Duration(seconds: 3),
        );
        Navigator.pop(context, true);
      }
    } on DioException catch (e) {
      final msg = e.response?.data?['message'] ?? 'Gagal mengirim pengaduan. Periksa koneksi backend.';
      if (mounted) {
        showV2Toast(
          context,
          message: msg,
          type: V2ToastType.error,
        );
      }
    } catch (e) {
      if (mounted) {
        showV2Toast(
          context,
          message: 'Error: $e',
          type: V2ToastType.error,
        );
      }
    } finally {
      if (mounted) setState(() => _isSubmitting = false);
    }
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
              title: 'Buat Laporan Baru',
              subtitle: 'Kabupaten',
              onBack: () => Navigator.of(context).pop(),
            ),
            Expanded(
              child: Form(
                key: _formKey,
                child: ListView(
                  padding: const EdgeInsets.all(16.0),
          children: [
            // 1. Tipe Pelaporan Kerusakan (Kerusakan Ringan vs Kerusakan Berat)
            _buildDamageTypeSelector(),
            const SizedBox(height: 16),

            // 2. Photo Picker (Foto Kerusakan di Atas sesuai permintaan)
            _buildPhotoPicker(),
            const SizedBox(height: 12),

            // 3. AI Diagnostics Card (Otomatis menganalisis saat foto diunggah)
            if (_isAnalyzingAI)
              Container(
                margin: const EdgeInsets.only(bottom: 14),
                padding: const EdgeInsets.all(14),
                decoration: BoxDecoration(
                  color: AppColors.primaryLight.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(color: AppColors.primaryLight.withValues(alpha: 0.3)),
                ),
                child: const Row(
                  children: [
                    SizedBox(width: 18, height: 18, child: CircularProgressIndicator(strokeWidth: 2)),
                    SizedBox(width: 12),
                    Expanded(
                      child: Text(
                        'AI Vision sedang menganalisis permukaan jalan, mengukur dimensi, dan menyusun narasi...',
                        style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600),
                      ),
                    ),
                  ],
                ),
              )
            else if (_aiDiagnostic != null) ...[
              _buildAiDiagnosticCard(),
              const SizedBox(height: 14),
            ],

            // 3B. Peringatan Duplikat Berbasis Radius GPS (Geo-Clustering <= 100m)
            if (_nearbyDuplicates.isNotEmpty) ...[
              _buildNearbyDuplicateBanner(),
              const SizedBox(height: 14),
            ],

            // 4. Judul Pengaduan (Title Field di Bawah Foto)
            const Text('Judul Pengaduan *', style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold)),
            const SizedBox(height: 6),
            TextFormField(
              controller: _titleController,
              decoration: InputDecoration(
                hintText: 'Misal: Laporan Kerusakan Badan Jalan dan Aspal Berlubang',
                filled: true,
                fillColor: Colors.white,
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
              ),
              validator: (v) => v == null || v.isEmpty ? 'Judul laporan wajib diisi' : null,
            ),
            const SizedBox(height: 16),

            // 5. Kategori Fasilitas / Kerusakan (Terkunci khusus Jalan & Jembatan jika Ruas Panjang)
            _buildCategorySelector(),
            const SizedBox(height: 16),

            // 6. Detail Khusus Ruas Panjang (Estimasi Panjang Otomatis & Kondisi Permukaan Jalan)
            if (_damageType == 'major') ...[
              _buildMajorRoadSegmentSection(),
              const SizedBox(height: 16),
            ],

            // 7. Deskripsi Kerusakan (Auto-filled by AI with professional narrative)
            const Text('Deskripsi Kerusakan *', style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold)),
            const SizedBox(height: 6),
            TextFormField(
              controller: _descriptionController,
              maxLines: 4,
              decoration: InputDecoration(
                hintText: 'Jelaskan kondisi kerusakan, patokan jalan, atau dampak ke warga...',
                filled: true,
                fillColor: Colors.white,
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
              ),
              validator: (v) => v == null || v.isEmpty ? 'Deskripsi wajib diisi' : null,
            ),
            const SizedBox(height: 18),

            // 8. Interactive GPS Map Picker
            _buildMapPickerSection(),
            const SizedBox(height: 24),

            // 9. Submit Button
            SizedBox(
              height: 50,
              child: ElevatedButton(
                onPressed: _isSubmitting ? null : _submitComplaint,
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppColors.primary,
                  foregroundColor: Colors.white,
                  elevation: 0,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                ),
                child: _isSubmitting
                    ? const SizedBox(
                        width: 20,
                        height: 20,
                        child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                      )
                    : Text('Kirim Laporan Aduan', style: GoogleFonts.outfit(fontSize: 15, fontWeight: FontWeight.bold)),
              ),
            ),
            const SizedBox(height: 40),
          ],
        ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildAiDiagnosticCard() {
    final d = _aiDiagnostic!;
    final isValid = d['is_valid_infrastructure'] as bool? ?? true;
    final isDamaged = d['is_damaged'] as bool? ?? true;

    // 1. Tampilan jika BUKAN Foto Infrastruktur Jalan (Non-road / Selfie / Gambar Asal-asalan)
    if (!isValid) {
      return Container(
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: const Color(0xFFFEF2F2),
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: const Color(0xFFFECACA)),
        ),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(10),
                border: Border.all(color: const Color(0xFFFCA5A5)),
              ),
              child: const Icon(LucideIcons.alertOctagon, color: Color(0xFFDC2626), size: 20),
            ),
            const SizedBox(width: 10),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text(
                    'Bukan Foto Kerusakan',
                    style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFF991B1B)),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    d['damage_detail'] as String? ??
                        'Foto yang diunggah tidak terdeteksi sebagai perkerasan jalan raya atau infrastruktur fisik. Mohon unggah foto fisik kerusakan jalan yang jelas.',
                    style: const TextStyle(fontSize: 11.5, color: Color(0xFF7F1D1D), height: 1.3),
                  ),
                ],
              ),
            ),
          ],
        ),
      );
    }

    // 2. Tampilan jika JALAN MULUS / KONDISI BAIK (Tidak ada kerusakan)
    if (!isDamaged) {
      return Container(
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: const Color(0xFFF0FDF4),
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: const Color(0xFFBBF7D0)),
        ),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(10),
                border: Border.all(color: const Color(0xFF86EFAC)),
              ),
              child: const Icon(LucideIcons.checkCircle2, color: Color(0xFF16A34A), size: 20),
            ),
            const SizedBox(width: 10),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text(
                    'Jalan Mulus (Kondisi Baik)',
                    style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: Color(0xFF15803D)),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    d['damage_detail'] as String? ??
                        'Hasil analisis computer vision menunjukkan permukaan perkerasan jalan dalam kondisi rata dan mulus. Tidak terdeteksi lubang atau kerusakan.',
                    style: const TextStyle(fontSize: 11.5, color: Color(0xFF166534), height: 1.3),
                  ),
                ],
              ),
            ),
          ],
        ),
      );
    }

    // 3. Tampilan KERUSAKAN JALAN NYATA
    final damageType = d['damage_type'] ?? d['category_label'] ?? 'Kerusakan Jalan';
    final damageDetail = d['damage_detail'] as String? ?? 'Terdeteksi kerusakan pada permukaan jalan.';
    final multiCount = d['multi_image_count'] as int?;

    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.primary.withValues(alpha: 0.35)),
        boxShadow: [
          BoxShadow(
            color: AppColors.primary.withValues(alpha: 0.08),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(6),
                decoration: BoxDecoration(
                  color: AppColors.primary.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: const Icon(LucideIcons.sparkles, color: AppColors.primary, size: 18),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        const Text('Hasil Deteksi Objek AI',
                            style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: AppColors.primary)),
                        if (multiCount != null && multiCount > 1) ...[
                          const SizedBox(width: 6),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 1.5),
                            decoration: BoxDecoration(
                              color: const Color(0xFFEFF6FF),
                              borderRadius: BorderRadius.circular(4),
                              border: Border.all(color: const Color(0xFFBFDBFE)),
                            ),
                            child: Text(
                              '$multiCount Sudut Foto',
                              style: const TextStyle(fontSize: 9.5, fontWeight: FontWeight.bold, color: AppColors.primary),
                            ),
                          ),
                        ],
                      ],
                    ),
                    Text('Akurasi Keyakinan: ${((d['confidence'] ?? 0.94) * 100).toInt()}%',
                        style: const TextStyle(fontSize: 11, color: AppColors.textSecondary)),
                  ],
                ),
              ),
            ],
          ),
          const Divider(height: 18),
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Icon(LucideIcons.search, size: 16, color: AppColors.primary),
              const SizedBox(width: 8),
              Expanded(
                child: RichText(
                  text: TextSpan(
                    style: const TextStyle(fontSize: 12, color: AppColors.textPrimary),
                    children: [
                      const TextSpan(text: 'Objek Terdeteksi: ', style: TextStyle(fontWeight: FontWeight.bold)),
                      TextSpan(text: damageType),
                    ],
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 8),
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Icon(LucideIcons.fileText, size: 16, color: AppColors.secondary),
              const SizedBox(width: 8),
              Expanded(
                child: RichText(
                  text: TextSpan(
                    style: const TextStyle(fontSize: 12, color: AppColors.textPrimary),
                    children: [
                      const TextSpan(text: 'Detail Kerusakan: ', style: TextStyle(fontWeight: FontWeight.bold)),
                      TextSpan(text: damageDetail),
                    ],
                  ),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildCategorySelector() {
    if (_damageType == 'major') {
      return Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text('Kategori Fasilitas / Kerusakan *', style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold)),
          const SizedBox(height: 8),
          Container(
            height: 48,
            padding: const EdgeInsets.symmetric(horizontal: 14),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(14),
              border: Border.all(color: AppColors.border),
            ),
            child: const Row(
              children: [
                Icon(LucideIcons.truck, size: 18, color: AppColors.primary),
                SizedBox(width: 10),
                Expanded(
                  child: Text(
                    'Jalan & Jembatan',
                    style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.textPrimary),
                  ),
                ),
                Icon(LucideIcons.lock, size: 18, color: AppColors.textSecondary),
              ],
            ),
          ),
        ],
      );
    }

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text('Kategori Fasilitas / Kerusakan *', style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold)),
        const SizedBox(height: 8),
        Container(
          padding: const EdgeInsets.symmetric(horizontal: 14),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(14),
            border: Border.all(color: AppColors.border),
          ),
          child: DropdownButtonHideUnderline(
            child: DropdownButton<int>(
              value: _categories.any((c) => c['id'] == _selectedCategoryId)
                  ? _selectedCategoryId
                  : (_categories.isNotEmpty ? _categories.first['id'] as int : 1),
              isExpanded: true,
              items: _categories.map((c) {
                return DropdownMenuItem<int>(
                  value: c['id'] as int,
                  child: Row(
                    children: [
                      Icon(c['icon'] as IconData, size: 18, color: AppColors.primary),
                      const SizedBox(width: 10),
                      Text(c['name'] as String, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
                    ],
                  ),
                );
              }).toList(),
              onChanged: (val) {
                if (val != null) setState(() => _selectedCategoryId = val);
              },
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildDamageTypeSelector() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          'Tipe Pelaporan Kerusakan *',
          style: GoogleFonts.inter(
            fontSize: 12.5,
            fontWeight: FontWeight.w600,
            color: AppColors.textPrimary,
          ),
        ),
        const SizedBox(height: 8),
        Container(
          padding: const EdgeInsets.all(4),
          decoration: BoxDecoration(
            color: AppColors.surface,
            borderRadius: BorderRadius.circular(14),
            border: Border.all(color: AppColors.border),
          ),
          child: Row(
            children: [
              Expanded(
                child: GestureDetector(
                  onTap: () {
                    if (_damageType != 'minor') {
                      _resetFormForDamageType('minor');
                    }
                  },
                  child: AnimatedContainer(
                    duration: const Duration(milliseconds: 160),
                    height: 42,
                    decoration: BoxDecoration(
                      color: _damageType == 'minor' ? AppColors.primary : AppColors.cardSurface,
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: Center(
                      child: Text(
                        'Titik (Ringan)',
                        style: GoogleFonts.inter(
                          fontSize: 12.5,
                          fontWeight: FontWeight.bold,
                          color: _damageType == 'minor' ? Colors.white : AppColors.textSecondary,
                        ),
                      ),
                    ),
                  ),
                ),
              ),
              const SizedBox(width: 6),
              Expanded(
                child: GestureDetector(
                  onTap: () {
                    if (_damageType != 'major') {
                      _resetFormForDamageType('major');
                    }
                  },
                  child: AnimatedContainer(
                    duration: const Duration(milliseconds: 160),
                    height: 42,
                    decoration: BoxDecoration(
                      color: _damageType == 'major' ? AppColors.primary : AppColors.cardSurface,
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: Center(
                      child: Text(
                        'Ruas Jalan (Berat)',
                        style: GoogleFonts.inter(
                          fontSize: 12.5,
                          fontWeight: FontWeight.w600,
                          color: _damageType == 'major' ? Colors.white : AppColors.textSecondary,
                        ),
                      ),
                    ),
                  ),
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }

  Widget _buildMajorRoadSegmentSection() {
    final conditionOptions = [
      'Jalan Tanah',
      'Batu Lepas / Makadam',
      'Aspal Hancur Bergelombang',
      'Genangan Lumpur Parah',
      'Longsor / Amblas Badan Jalan',
      'Lainnya',
    ];

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
            children: [
              Container(
                padding: const EdgeInsets.all(6),
                decoration: const BoxDecoration(
                  color: Color(0xFFEFF6FF),
                  shape: BoxShape.circle,
                ),
                child: const Icon(LucideIcons.route, size: 16, color: AppColors.primary),
              ),
              const SizedBox(width: 8),
              const Expanded(
                child: Text(
                  'Detail Ruas Jalan Panjang (≥ 500 Meter)',
                  style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
                ),
              ),
              if (_isRouting) ...[
                const SizedBox(
                  width: 14,
                  height: 14,
                  child: CircularProgressIndicator(strokeWidth: 2, color: AppColors.primary),
                ),
                const SizedBox(width: 6),
                const Text('Menghitung rute...', style: TextStyle(fontSize: 10, color: AppColors.primary)),
              ],
            ],
          ),
          const SizedBox(height: 12),
          const Text(
            'Estimasi Panjang Kerusakan Jalan *',
            style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
          ),
          const SizedBox(height: 6),
          TextFormField(
            controller: _roadLengthController,
            readOnly: true,
            decoration: InputDecoration(
              hintText: 'Ditentukan dari titik awal dan titik akhir pada peta',
              prefixIcon: const Icon(LucideIcons.ruler, size: 18, color: AppColors.primary),
              filled: true,
              fillColor: Colors.white,
              contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12),
                borderSide: const BorderSide(color: AppColors.border),
              ),
              enabledBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12),
                borderSide: const BorderSide(color: AppColors.border),
              ),
            ),
          ),
          if (_isRoadLengthUnder500()) ...[
            const SizedBox(height: 8),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
              decoration: BoxDecoration(
                color: const Color(0xFFFEF3C7),
                borderRadius: BorderRadius.circular(10),
                border: Border.all(color: const Color(0xFFFCD34D)),
              ),
              child: const Row(
                children: [
                  Icon(LucideIcons.alertTriangle, size: 16, color: Color(0xFFD97706)),
                  SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      'Panjang jalan di bawah 500 meter ini. Tipe kerusakan berat dikhususkan untuk kerusakan jalan panjang (≥ 500 meter).',
                      style: TextStyle(fontSize: 11.5, color: Color(0xFF92400E), fontWeight: FontWeight.bold),
                    ),
                  ),
                ],
              ),
            ),
          ],
          const SizedBox(height: 14),
          const Text(
            'Kondisi Fisik / Permukaan Jalan *',
            style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
          ),
          const SizedBox(height: 6),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 12),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: AppColors.border),
            ),
            child: DropdownButtonHideUnderline(
              child: DropdownButton<String>(
                value: conditionOptions.contains(_selectedRoadCondition)
                    ? _selectedRoadCondition
                    : conditionOptions.first,
                isExpanded: true,
                items: conditionOptions.map((opt) {
                  return DropdownMenuItem<String>(
                    value: opt,
                    child: Text(opt, style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.w600, color: Color(0xFF0F172A))),
                  );
                }).toList(),
                onChanged: (val) {
                  if (val != null) setState(() => _selectedRoadCondition = val);
                },
              ),
            ),
          ),
          if (_selectedRoadCondition == 'Lainnya') ...[
            const SizedBox(height: 8),
            TextFormField(
              controller: _customRoadConditionController,
              decoration: InputDecoration(
                hintText: 'Tuliskan kondisi fisik jalan...',
                filled: true,
                fillColor: Colors.white,
                contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
              ),
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildMapPickerSection() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            const Text('Titik Koordinat & Peta Presisi *', style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold)),
            TextButton.icon(
              onPressed: _isLoadingLocation ? null : _fetchLocation,
              icon: _isLoadingLocation
                  ? const SizedBox(width: 14, height: 14, child: CircularProgressIndicator(strokeWidth: 2))
                  : const Icon(LucideIcons.locateFixed, size: 14),
              label: const Text('GPS Otomatis', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
            ),
          ],
        ),
        if (_damageType == 'major') ...[
          const SizedBox(height: 4),
          Container(
            padding: const EdgeInsets.all(3),
            decoration: BoxDecoration(
              color: const Color(0xFFF1F5F9),
              borderRadius: BorderRadius.circular(10),
              border: Border.all(color: const Color(0xFFE2E8F0)),
            ),
            child: Row(
              children: [
                Expanded(
                  child: InkWell(
                    onTap: () => setState(() => _isPickingEndPoint = false),
                    borderRadius: BorderRadius.circular(8),
                    child: Container(
                      padding: const EdgeInsets.symmetric(vertical: 6),
                      decoration: BoxDecoration(
                        color: !_isPickingEndPoint ? Colors.white : Colors.transparent,
                        borderRadius: BorderRadius.circular(8),
                        boxShadow: [
                          if (!_isPickingEndPoint)
                            const BoxShadow(color: Colors.black12, blurRadius: 4, offset: Offset(0, 1)),
                        ],
                      ),
                      child: const Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Icon(LucideIcons.mapPin, size: 14, color: Color(0xFF16A34A)),
                          SizedBox(width: 4),
                          Text(
                            'Atur Titik Awal',
                            style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Color(0xFF0F172A)),
                          ),
                        ],
                      ),
                    ),
                  ),
                ),
                const SizedBox(width: 4),
                Expanded(
                  child: InkWell(
                    onTap: () => setState(() => _isPickingEndPoint = true),
                    borderRadius: BorderRadius.circular(8),
                    child: Container(
                      padding: const EdgeInsets.symmetric(vertical: 6),
                      decoration: BoxDecoration(
                        color: _isPickingEndPoint ? Colors.white : Colors.transparent,
                        borderRadius: BorderRadius.circular(8),
                        boxShadow: [
                          if (_isPickingEndPoint)
                            const BoxShadow(color: Colors.black12, blurRadius: 4, offset: Offset(0, 1)),
                        ],
                      ),
                      child: const Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Icon(LucideIcons.mapPin, size: 14, color: Color(0xFFDC2626)),
                          SizedBox(width: 4),
                          Text(
                            'Atur Titik Akhir',
                            style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Color(0xFF0F172A)),
                          ),
                        ],
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ),
        ],
        const SizedBox(height: 6),
        Container(
          height: 220,
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: AppColors.border),
          ),
          clipBehavior: Clip.antiAlias,
          child: Stack(
            children: [
              FlutterMap(
                mapController: _mapController,
                options: MapOptions(
                  initialCenter: LatLng(_latitude, _longitude),
                  initialZoom: 15,
                  onTap: (tapPosition, point) {
                    if (!_isWithinKolakaUtara(point.latitude, point.longitude)) {
                      _showOutOfBoundaryDialog(point.latitude, point.longitude);
                      return;
                    }
                    if (_damageType == 'major' && _isPickingEndPoint) {
                      setState(() {
                        _endLatitude = point.latitude;
                        _endLongitude = point.longitude;
                      });
                      _updateRoadRoute();
                    } else {
                      setState(() {
                        _latitude = point.latitude;
                        _longitude = point.longitude;
                      });
                      _reverseGeocode(_latitude, _longitude);
                      if (_damageType == 'major' && _endLatitude != null) {
                        _updateRoadRoute();
                      }
                    }
                  },
                ),
                children: [
                  TileLayer(
                    urlTemplate: 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
                    userAgentPackageName: 'com.kabupaten.aduan',
                  ),
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
                  if (_damageType == 'major' && _endLatitude != null)
                    PolylineLayer(
                      polylines: [
                        Polyline(
                          points: _routePoints.isNotEmpty
                              ? _routePoints
                              : [
                                  LatLng(_latitude, _longitude),
                                  LatLng(_endLatitude!, _endLongitude!),
                                ],
                          borderColor: AppColors.primary,
                          borderStrokeWidth: 1.5,
                          color: const Color(0xFF2563EB),
                          strokeWidth: 5.0,
                        ),
                      ],
                    ),
                  MarkerLayer(
                    markers: [
                      Marker(
                        point: LatLng(_latitude, _longitude),
                        width: 44,
                        height: 44,
                        child: Column(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            if (_damageType == 'major')
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 1),
                                decoration: BoxDecoration(
                                  color: const Color(0xFF16A34A),
                                  borderRadius: BorderRadius.circular(4),
                                ),
                                child: const Text('Awal', style: TextStyle(color: Colors.white, fontSize: 8, fontWeight: FontWeight.bold)),
                              ),
                            const Icon(
                              LucideIcons.mapPin,
                              color: Color(0xFF16A34A),
                              size: 30,
                            ),
                          ],
                        ),
                      ),
                      if (_damageType == 'major' && _endLatitude != null)
                        Marker(
                          point: LatLng(_endLatitude!, _endLongitude!),
                          width: 44,
                          height: 44,
                          child: Column(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 1),
                                decoration: BoxDecoration(
                                  color: AppColors.damageTypeMajor,
                                  borderRadius: BorderRadius.circular(4),
                                ),
                                child: const Text('Akhir', style: TextStyle(color: Colors.white, fontSize: 8, fontWeight: FontWeight.bold)),
                              ),
                              const Icon(
                                LucideIcons.mapPin,
                                color: AppColors.damageTypeMajor,
                                size: 30,
                              ),
                            ],
                          ),
                        ),
                    ],
                  ),
                ],
              ),
              if (_damageType == 'major' && _roadLengthController.text.isNotEmpty)
                Positioned(
                  top: 8,
                  left: 8,
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                    decoration: BoxDecoration(
                      color: Colors.white.withValues(alpha: 0.95),
                      borderRadius: BorderRadius.circular(8),
                      border: Border.all(color: AppColors.damageTypeMajorBorder),
                      boxShadow: const [BoxShadow(color: Colors.black12, blurRadius: 4)],
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        const Icon(LucideIcons.route, size: 12, color: AppColors.damageTypeMajor),
                        const SizedBox(width: 4),
                        Text(
                          'Rute Berkelok: ${_roadLengthController.text}',
                          style: const TextStyle(fontSize: 10.5, fontWeight: FontWeight.bold, color: Color(0xFF9A3412)),
                        ),
                      ],
                    ),
                  ),
                ),
              Positioned(
                bottom: 8,
                left: 8,
                right: 8,
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                  decoration: BoxDecoration(
                    color: Colors.white.withValues(alpha: 0.92),
                    borderRadius: BorderRadius.circular(8),
                    boxShadow: const [BoxShadow(color: Colors.black12, blurRadius: 4)],
                  ),
                  child: Row(
                    children: [
                      Expanded(
                        child: Text(
                          _damageType == 'major' && _isPickingEndPoint
                              ? 'Titik Akhir: ${_endLatitude?.toStringAsFixed(4) ?? "-"}, ${_endLongitude?.toStringAsFixed(4) ?? "-"}'
                              : 'Titik Awal: ${_latitude.toStringAsFixed(4)}, ${_longitude.toStringAsFixed(4)}',
                          style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold, fontFamily: 'monospace'),
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                      const SizedBox(width: 8),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                        decoration: BoxDecoration(
                          color: _damageType == 'major' && _isPickingEndPoint
                              ? const Color(0xFFDC2626).withValues(alpha: 0.1)
                              : AppColors.primary.withValues(alpha: 0.1),
                          borderRadius: BorderRadius.circular(4),
                        ),
                        child: Text(
                          _damageType == 'major' && _isPickingEndPoint ? 'Bidik Akhir' : 'Bidik Awal',
                          style: TextStyle(
                            fontSize: 10,
                            color: _damageType == 'major' && _isPickingEndPoint ? const Color(0xFFDC2626) : AppColors.primary,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ],
          ),
        ),
        if (_isGeocodingAddress)
          Container(
            margin: const EdgeInsets.only(top: 8),
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
            decoration: BoxDecoration(
              color: AppColors.primary.withValues(alpha: 0.05),
              borderRadius: BorderRadius.circular(10),
              border: Border.all(color: AppColors.primary.withValues(alpha: 0.2)),
            ),
            child: const Row(
              children: [
                SizedBox(width: 14, height: 14, child: CircularProgressIndicator(strokeWidth: 2)),
                SizedBox(width: 8),
                Text(
                  'Mendeteksi nama desa, kecamatan, & jalan...',
                  style: TextStyle(fontSize: 11, color: AppColors.primary, fontWeight: FontWeight.bold),
                ),
              ],
            ),
          )
        else if (_administrativeArea != null &&
            (_administrativeArea!['village']?.isNotEmpty == true ||
                _administrativeArea!['district']?.isNotEmpty == true))
          Container(
            margin: const EdgeInsets.only(top: 8),
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: AppColors.border),
            ),
            child: Row(
              children: [
                const Icon(LucideIcons.landmark, size: 16, color: AppColors.primary),
                const SizedBox(width: 8),
                Expanded(
                  child: Text(
                    'Wilayah Terdeteksi: ${_administrativeArea!['village']?.isNotEmpty == true ? "Desa ${_administrativeArea!['village']}" : ""} ${_administrativeArea!['district']?.isNotEmpty == true ? "• Kec. ${_administrativeArea!['district']}" : ""} • ${_administrativeArea!['regency'] ?? "Kabupaten"}',
                    style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
                  ),
                ),
              ],
            ),
          ),
        const SizedBox(height: 10),
        TextFormField(
          controller: _addressController,
          decoration: InputDecoration(
            labelText: 'Alamat Lengkap / Nama Jalan & Patokan *',
            hintText: 'Contoh: Dusun Krajan, RT 02 / Depan Balai Desa',
            prefixIcon: const Icon(LucideIcons.mapPin, size: 18),
            filled: true,
            fillColor: Colors.white,
            border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
          ),
          validator: (v) => v == null || v.isEmpty ? 'Alamat / patokan lokasi wajib diisi' : null,
        ),
      ],
    );
  }

  Widget _buildPhotoPicker() {
    if (_selectedPhotos.isNotEmpty) {
      return Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text(
                'Upload Foto Kerusakan *',
                style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold),
              ),
              Text(
                '${_selectedPhotos.length} Foto Terpilih',
                style: const TextStyle(
                  fontSize: 12,
                  color: AppColors.primary,
                  fontWeight: FontWeight.bold,
                ),
              ),
            ],
          ),
          const SizedBox(height: 8),
          SizedBox(
            height: 135,
            child: ListView.separated(
              scrollDirection: Axis.horizontal,
              itemCount: _selectedPhotos.length + (_selectedPhotos.length < _maxPhotos ? 1 : 0),
              separatorBuilder: (ctx, idx) => const SizedBox(width: 10),
              itemBuilder: (context, index) {
                if (index == _selectedPhotos.length) {
                  return GestureDetector(
                    onTap: _showAddPhotoSheet,
                    child: Container(
                      width: 120,
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(14),
                        border: Border.all(color: AppColors.primary.withValues(alpha: 0.3)),
                      ),
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Container(
                            padding: const EdgeInsets.all(8),
                            decoration: BoxDecoration(
                              color: AppColors.primary.withValues(alpha: 0.1),
                              shape: BoxShape.circle,
                            ),
                            child: const Icon(LucideIcons.plus, color: AppColors.primary, size: 20),
                          ),
                          const SizedBox(height: 6),
                          const Text(
                            'Tambah Foto',
                            textAlign: TextAlign.center,
                            style: TextStyle(fontSize: 11, color: AppColors.primary, fontWeight: FontWeight.bold),
                          ),
                        ],
                      ),
                    ),
                  );
                }

                final file = _selectedPhotos[index];
                final isPrimary = index == 0;

                return Stack(
                  children: [
                    Container(
                      width: 130,
                      decoration: BoxDecoration(
                        borderRadius: BorderRadius.circular(14),
                        image: DecorationImage(
                          image: FileImage(file),
                          fit: BoxFit.cover,
                        ),
                      ),
                    ),
                    Positioned(
                      top: 6,
                      left: 6,
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                        decoration: BoxDecoration(
                          color: isPrimary ? AppColors.primary : Colors.black87,
                          borderRadius: BorderRadius.circular(6),
                        ),
                        child: Text(
                          isPrimary ? 'Foto 1 (Utama)' : 'Foto ${index + 1}',
                          style: const TextStyle(color: Colors.white, fontSize: 9, fontWeight: FontWeight.bold),
                        ),
                      ),
                    ),
                    Positioned(
                      top: 6,
                      right: 6,
                      child: GestureDetector(
                        onTap: () => _removePhoto(index),
                        child: Container(
                          padding: const EdgeInsets.all(4),
                          decoration: const BoxDecoration(
                            color: Colors.black54,
                            shape: BoxShape.circle,
                          ),
                          child: const Icon(LucideIcons.x, color: Colors.white, size: 14),
                        ),
                      ),
                    ),
                  ],
                );
              },
            ),
          ),
        ],
      );
    }

    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.primary.withValues(alpha: 0.2)),
      ),
      child: Column(
        children: [
          const Icon(LucideIcons.camera, size: 36, color: AppColors.primary),
          const SizedBox(height: 8),
          const Text(
            'Upload Foto Kerusakan',
            style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
          ),
          const SizedBox(height: 4),
          const Text(
            'Ambil foto atau pilih foto kerusakan jalan dari galeri.',
            textAlign: TextAlign.center,
            style: TextStyle(fontSize: 11, color: AppColors.textSecondary),
          ),
          const SizedBox(height: 14),
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              ElevatedButton.icon(
                onPressed: _takePhoto,
                icon: const Icon(LucideIcons.camera, size: 16),
                label: const Text('Kamera', style: TextStyle(fontSize: 12)),
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppColors.primary,
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                ),
              ),
              const SizedBox(width: 12),
              OutlinedButton.icon(
                onPressed: _pickGallery,
                icon: const Icon(LucideIcons.image, size: 16),
                label: const Text('Galeri', style: TextStyle(fontSize: 12)),
                style: OutlinedButton.styleFrom(
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}
