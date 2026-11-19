import 'dart:io';
import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:lucide_icons_flutter/lucide_icons.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../../core/auth/auth_service.dart';
import '../../../core/media/media_service.dart';
import '../../../core/network/api_client.dart';
import '../../../core/offline/offline_resolution_queue_service.dart';
import '../../../shared/models/complaint_model.dart';
import '../../../shared/theme/app_colors.dart';
import '../../../shared/widgets/report_pagination_controls.dart';
import '../../../shared/widgets/status_badge.dart';
import '../../../shared/widgets/v2_components.dart';
import '../../../shared/widgets/v2_toast.dart';
import '../../../core/utils/phone_validator.dart';
import '../../complaints/presentation/complaint_detail_screen.dart';
import '../../notifications/presentation/notifications_screen.dart';

class FieldTasksScreen extends StatefulWidget {
  const FieldTasksScreen({super.key});

  @override
  State<FieldTasksScreen> createState() => _FieldTasksScreenState();
}

class _FieldTasksScreenState extends State<FieldTasksScreen> {
  final _apiClient = ApiClient();
  final _mediaService = MediaService();
  final _authService = AuthService();
  final _offlineQueueService = OfflineResolutionQueueService();
  List<OfflineResolutionItem> _pendingOfflineItems = [];
  bool _isSyncingOffline = false;

  UserModel? _currentUser;
  Map<String, dynamic>? _myTeam;
  String _dispatchMode = 'HYBRID';
  int _unreadNotifications = 0;

  List<ComplaintModel> _tasks = [];
  bool _isLoading = true;
  String _selectedFilter = 'ALL';
  String _taskScope = 'ALL'; // 'ALL' = Semua Tugas, 'MY' = Tugas Regu Saya
  String _searchQuery = '';

  // Status KPI Pekerja Lapangan
  int _totalTasks = 0;
  int _dispatchedCount = 0;
  int _inProgressCount = 0;
  int _waitingVerificationCount = 0;
  int _completedCount = 0;
  int _myActiveTasksCount = 0;
  // Gabungan Dikerjakan + Validasi = Diproses
  int get _diprosesCount => _inProgressCount + _waitingVerificationCount;
  String _selectedDamageType = 'ALL';
  int? _selectedCategoryFilter;
  bool _isGridView = false;
  int _currentPage = 1;
  int _pageSize = 5;

  @override
  void initState() {
    super.initState();
    _loadUserAndData();
    _loadAndSyncOfflineQueue(silent: true);
  }

  Future<void> _loadAndSyncOfflineQueue({bool silent = false}) async {
    final queued = await _offlineQueueService.getQueue();
    if (mounted) {
      setState(() => _pendingOfflineItems = queued);
    }
    if (queued.isEmpty) return;

    if (mounted) setState(() => _isSyncingOffline = true);
    final synced = await _offlineQueueService.syncAll(_apiClient);
    final remaining = await _offlineQueueService.getQueue();
    if (!mounted) return;
    setState(() {
      _pendingOfflineItems = remaining;
      _isSyncingOffline = false;
    });
    if (synced > 0) {
      showV2Toast(
        context,
        message: '$synced bukti perbaikan offline berhasil disinkronkan!',
        type: V2ToastType.success,
      );
      _fetchTasks();
    } else if (!silent) {
      showV2Toast(
        context,
        message: 'Masih menunggu koneksi internet stabil untuk sinkronisasi.',
        type: V2ToastType.warning,
      );
    }
  }

  Future<void> _loadUserAndData() async {
    final user = await _authService.getCurrentUser();
    if (mounted) setState(() => _currentUser = user);

    await Future.wait([
      _fetchTasks(),
      _fetchUnreadNotifications(),
    ]);
  }

  Future<void> _fetchUnreadNotifications() async {
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

  List<ComplaintModel> get _paginatedTasks {
    final all = _filteredTasks;
    if (all.isEmpty) return [];
    final totalPages = (all.length / _pageSize).ceil().clamp(1, 9999);
    final safePage = _currentPage.clamp(1, totalPages);
    final start = (safePage - 1) * _pageSize;
    final end = (start + _pageSize).clamp(0, all.length);
    return all.sublist(start, end);
  }

  Widget _buildTaskGridRows(List<ComplaintModel> items) {
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
                Expanded(child: _buildTaskGridCard(first)),
                const SizedBox(width: 10),
                Expanded(
                  child: second != null
                      ? _buildTaskGridCard(second)
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

  Future<void> _fetchTasks() async {
    setState(() => _isLoading = true);
    try {
      final Map<String, dynamic> params = {};
      if (_selectedFilter != 'ALL') params['status'] = _selectedFilter;
      params['scope'] = _taskScope == 'MY' ? 'my' : 'all';

      final res = await _apiClient.dio.get('/field-tasks', queryParameters: params);
      if (res.statusCode == 200 && res.data['data'] != null) {
        final data = res.data['data'];
        final List items = data['items'] ?? [];
        final summary = data['summary'];

        if (mounted) {
          setState(() {
            _tasks = items.map((e) => ComplaintModel.fromJson(e)).toList();
            if (summary != null) {
              _totalTasks = summary['total_tasks'] ?? _tasks.length;
              _dispatchedCount = summary['dispatched_count'] ?? 0;
              _inProgressCount = summary['in_progress_count'] ?? 0;
              _waitingVerificationCount = summary['waiting_verification_count'] ?? 0;
              _completedCount = summary['completed_count'] ?? 0;
              _myActiveTasksCount = summary['my_active_tasks_count'] ?? 0;
              _myTeam = summary['my_team'];
              _dispatchMode = summary['dispatch_mode'] ?? 'HYBRID';
            } else {
              _totalTasks = _tasks.length;
              _dispatchedCount = _tasks.where((t) => t.status == 'DISPATCHED').length;
              _inProgressCount = _tasks.where((t) => ['IN_PROGRESS', 'REVISION_REQUIRED'].contains(t.status)).length;
              _waitingVerificationCount = _tasks.where((t) => t.status == 'WAITING_VERIFICATION').length;
              _completedCount = _tasks.where((t) => ['COMPLETED', 'CLOSED'].contains(t.status)).length;
            }
          });
        }
      }
    } catch (_) {}
    if (mounted) setState(() => _isLoading = false);
  }

  Map<int, String> get _activeCategoryMap {
    return const {
      1: 'Jalan & Jembatan',
      2: 'Penerangan Jalan Umum (PJU)',
      3: 'Fasilitas Umum',
    };
  }

  List<ComplaintModel> get _filteredTasks {
    var list = _tasks.where((t) => !['SUBMITTED', 'VERIFICATION'].contains(t.status)).toList();
    if (_taskScope == 'MY') {
      list = list.where((t) {
        // Tab Tugas Regu Saya: hanya laporan yang sudah diambil/dikerjakan oleh regu saya atau akun saya
        final isTaken = ['IN_PROGRESS', 'WAITING_VERIFICATION', 'REVISION_REQUIRED', 'COMPLETED', 'CLOSED'].contains(t.status);
        if (!isTaken) return false;
        if (_myTeam != null && t.teamId != null) {
          return t.teamId == _myTeam!['id'] || t.assignedOfficerId == _currentUser?.id;
        }
        if (_currentUser != null && _currentUser!.id > 0 && t.assignedOfficerId != null) {
          return t.assignedOfficerId == _currentUser!.id;
        }
        return true;
      }).toList();
    }

    if (_selectedFilter != 'ALL') {
      if (_selectedFilter == 'IN_PROGRESS') {
        list = list.where((t) => ['IN_PROGRESS', 'REVISION_REQUIRED'].contains(t.status)).toList();
      } else if (_selectedFilter == 'COMPLETED') {
        list = list.where((t) => ['COMPLETED', 'CLOSED'].contains(t.status)).toList();
      } else {
        list = list.where((t) => t.status == _selectedFilter).toList();
      }
    }
    if (_selectedCategoryFilter != null) {
      final targetCatId = _selectedCategoryFilter!;
      final targetCatName = (_activeCategoryMap[targetCatId] ?? '').toLowerCase();
      list = list.where((t) {
        final matchesId = t.categoryId == targetCatId;
        final compCatName = (t.categoryName ?? '').toLowerCase();
        final matchesName = targetCatName.isNotEmpty &&
            (compCatName.contains(targetCatName) || targetCatName.contains(compCatName));
        return matchesId || matchesName;
      }).toList();
    }
    if (_selectedDamageType != 'ALL') {
      final targetDamageType = _selectedDamageType.toLowerCase().trim();
      list = list.where((t) => (t.damageType ?? 'minor').toLowerCase().trim() == targetDamageType).toList();
    }
    if (_searchQuery.trim().isEmpty) return list;
    final q = _searchQuery.toLowerCase();
    return list.where((t) {
      return t.publicCode.toLowerCase().contains(q) ||
          t.title.toLowerCase().contains(q) ||
          t.address.toLowerCase().contains(q);
    }).toList();
  }

  bool get _needsProfileCompletion =>
      _currentUser?.needsFirstLoginSetup == true ||
      (_currentUser?.phone == null || _currentUser!.phone!.trim().isEmpty);

  Future<void> _showPetugasQuickSetupSheet() async {
    final user = _currentUser;
    if (user == null) return;

    final nameCtrl = TextEditingController(text: user.name);
    final phoneCtrl = TextEditingController(text: user.phone ?? '');
    final newPasswordCtrl = TextEditingController();
    final confirmPasswordCtrl = TextEditingController();

    bool isSaving = false;
    bool obscurePassword = true;
    String? phoneFeedback;
    bool? isPhoneValid;
    String? passwordError;

    if (phoneCtrl.text.isNotEmpty) {
      final initialCheck = PhoneValidator.validate(phoneCtrl.text, isRequired: true);
      phoneFeedback = initialCheck.message;
      isPhoneValid = initialCheck.isValid;
    } else {
      phoneFeedback = 'Nomor WhatsApp Indonesia 10 - 13 digit (contoh: 081234567890)';
      isPhoneValid = null;
    }

    await showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (ctx) => StatefulBuilder(
        builder: (context, setSheetState) => Padding(
          padding: EdgeInsets.only(
            left: 20,
            right: 20,
            top: 20,
            bottom: MediaQuery.of(context).viewInsets.bottom + 20,
          ),
          child: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(8),
                      decoration: BoxDecoration(
                        color: AppColors.primary.withValues(alpha: 0.1),
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: const Icon(LucideIcons.shieldCheck, color: AppColors.primary, size: 22),
                    ),
                    const SizedBox(width: 12),
                    const Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'Verifikasi Profil Petugas',
                            style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
                          ),
                          Text(
                            'Lengkapi identitas resmi & perbarui sandi dinas',
                            style: TextStyle(fontSize: 12, color: AppColors.textSecondary),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
                const Divider(height: 24),
                const Text('Nama Lengkap (Sesuai SK / Tanda Pengenal) *', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600)),
                const SizedBox(height: 6),
                TextField(
                  controller: nameCtrl,
                  decoration: InputDecoration(
                    hintText: 'Nama lengkap petugas',
                    prefixIcon: const Icon(LucideIcons.user, size: 18),
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                    contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                  ),
                ),
                const SizedBox(height: 14),
                const Text('Nomor WhatsApp Aktif (10 - 13 digit) *', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600)),
                const SizedBox(height: 6),
                TextField(
                  controller: phoneCtrl,
                  keyboardType: TextInputType.phone,
                  onChanged: (val) {
                    if (val.trim().isEmpty) {
                      setSheetState(() {
                        phoneFeedback = 'Nomor WhatsApp Indonesia 10 - 13 digit (contoh: 081234567890)';
                        isPhoneValid = null;
                      });
                      return;
                    }
                    final res = PhoneValidator.validate(val, isRequired: true);
                    setSheetState(() {
                      phoneFeedback = res.message;
                      isPhoneValid = res.isValid;
                    });
                  },
                  decoration: InputDecoration(
                    hintText: '08xxxxxxxxxx',
                    prefixIcon: const Icon(LucideIcons.phone, size: 18),
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                    contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                  ),
                ),
                if (phoneFeedback != null) ...[
                  const SizedBox(height: 4),
                  Row(
                    children: [
                      Icon(
                        isPhoneValid == true
                            ? LucideIcons.checkCircle2
                            : (isPhoneValid == false ? LucideIcons.alertCircle : LucideIcons.info),
                        size: 13,
                        color: isPhoneValid == true
                            ? const Color(0xFF10B981)
                            : (isPhoneValid == false ? const Color(0xFFEF4444) : AppColors.textSecondary),
                      ),
                      const SizedBox(width: 5),
                      Expanded(
                        child: Text(
                          phoneFeedback!,
                          style: TextStyle(
                            fontSize: 11,
                            color: isPhoneValid == true
                                ? const Color(0xFF10B981)
                                : (isPhoneValid == false ? const Color(0xFFEF4444) : AppColors.textSecondary),
                          ),
                        ),
                      ),
                    ],
                  ),
                ],
                const SizedBox(height: 14),
                const Text('Kata Sandi Baru *', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600)),
                const SizedBox(height: 2),
                const Text(
                  'Ketentuan: Minimal 8 karakter, wajib memiliki setidaknya 1 huruf besar (A-Z) dan 1 angka (0-9).',
                  style: TextStyle(fontSize: 11, color: AppColors.textSecondary, height: 1.3),
                ),
                const SizedBox(height: 6),
                TextField(
                  controller: newPasswordCtrl,
                  obscureText: obscurePassword,
                  onChanged: (_) {
                    if (passwordError != null) setSheetState(() => passwordError = null);
                  },
                  decoration: InputDecoration(
                    hintText: 'Min. 8 karakter (huruf besar, kecil, angka)',
                    prefixIcon: const Icon(LucideIcons.lock, size: 18),
                    suffixIcon: IconButton(
                      icon: Icon(obscurePassword ? LucideIcons.eyeOff : LucideIcons.eye, size: 18),
                      onPressed: () => setSheetState(() => obscurePassword = !obscurePassword),
                    ),
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                    contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                  ),
                ),
                const SizedBox(height: 14),
                const Text('Konfirmasi Sandi Baru *', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600)),
                const SizedBox(height: 6),
                TextField(
                  controller: confirmPasswordCtrl,
                  obscureText: obscurePassword,
                  onChanged: (_) {
                    if (passwordError != null) setSheetState(() => passwordError = null);
                  },
                  decoration: InputDecoration(
                    hintText: 'Ulangi kata sandi baru',
                    prefixIcon: const Icon(LucideIcons.checkCheck, size: 18),
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                    contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                  ),
                ),
                if (passwordError != null) ...[
                  const SizedBox(height: 6),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                    decoration: BoxDecoration(
                      color: const Color(0xFFFEF2F2),
                      borderRadius: BorderRadius.circular(8),
                      border: Border.all(color: const Color(0xFFFCA5A5)),
                    ),
                    child: Row(
                      children: [
                        const Icon(LucideIcons.alertCircle, size: 16, color: Color(0xFFEF4444)),
                        const SizedBox(width: 8),
                        Expanded(
                          child: Text(
                            passwordError!,
                            style: const TextStyle(fontSize: 11.5, fontWeight: FontWeight.w500, color: Color(0xFF991B1B)),
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
                const SizedBox(height: 20),
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton(
                    onPressed: isSaving
                        ? null
                        : () async {
                            final name = nameCtrl.text.trim();
                            final phone = phoneCtrl.text.trim();
                            final pass = newPasswordCtrl.text.trim();
                            final confirmPass = confirmPasswordCtrl.text.trim();

                            if (name.isEmpty) {
                              setSheetState(() => passwordError = 'Nama lengkap wajib diisi.');
                              return;
                            }

                            final validation = PhoneValidator.validate(phone, isRequired: true);
                            if (!validation.isValid) {
                              setSheetState(() {
                                phoneFeedback = validation.message;
                                isPhoneValid = false;
                                passwordError = validation.message ?? 'Format nomor handphone tidak valid.';
                              });
                              return;
                            }

                            if (pass.length < 8) {
                              setSheetState(() {
                                passwordError = 'Kata sandi baru minimal 8 karakter.';
                              });
                              return;
                            }

                            if (!RegExp(r'[A-Z]').hasMatch(pass)) {
                              setSheetState(() {
                                passwordError = 'Kata sandi baru wajib memiliki setidaknya 1 huruf besar (A-Z).';
                              });
                              return;
                            }

                            if (!RegExp(r'[0-9]').hasMatch(pass)) {
                              setSheetState(() {
                                passwordError = 'Kata sandi baru wajib memiliki setidaknya 1 angka (0-9).';
                              });
                              return;
                            }

                            if (!RegExp(r'[a-z]').hasMatch(pass)) {
                              setSheetState(() {
                                passwordError = 'Kata sandi baru wajib kombinasi huruf besar, kecil, dan angka.';
                              });
                              return;
                            }

                            if (pass.toLowerCase() == 'password') {
                              setSheetState(() {
                                passwordError = 'Kata sandi baru tidak boleh sama dengan kata sandi default "password".';
                              });
                              return;
                            }

                            if (pass != confirmPass) {
                              setSheetState(() {
                                passwordError = 'Konfirmasi kata sandi baru tidak cocok.';
                              });
                              return;
                            }

                            setSheetState(() {
                              isSaving = true;
                              passwordError = null;
                            });

                            final nav = Navigator.of(context);

                            final checkRes = await _authService.checkPhone(validation.normalized, userId: user.id);
                            if (checkRes['registered'] == true) {
                              setSheetState(() {
                                isSaving = false;
                                phoneFeedback = 'Nomor handphone sudah digunakan oleh akun lain.';
                                isPhoneValid = false;
                                passwordError = 'Nomor handphone ini sudah digunakan oleh akun lain.';
                              });
                              return;
                            }

                            final res = await _authService.updateProfile(
                              name: name,
                              email: user.email,
                              phone: validation.normalized,
                              password: pass,
                              currentPassword: 'password',
                            );

                            if (!mounted) return;

                            if (res['success'] == true) {
                              nav.pop();
                              final updatedUser = await _authService.getCurrentUser();
                              if (mounted) {
                                setState(() {
                                  _currentUser = updatedUser;
                                });
                                showV2Toast(
                                  this.context,
                                  message: 'Profil dan kata sandi baru Anda berhasil disimpan!',
                                  type: V2ToastType.success,
                                );
                              }
                            } else {
                              setSheetState(() {
                                isSaving = false;
                                passwordError = res['message'] ?? 'Gagal menyimpan data akun. Silakan coba lagi.';
                              });
                            }
                          },
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppColors.primary,
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(vertical: 14),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    ),
                    child: isSaving
                        ? const SizedBox(
                            width: 20,
                            height: 20,
                            child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                          )
                        : const Text('Simpan & Aktifkan Akun', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Future<void> _startWork(ComplaintModel task) async {
    if (_needsProfileCompletion) {
      showV2Toast(
        context,
        message: 'Silakan lengkapi profil resmi dan ubah kata sandi awal terlebih dahulu sebelum memulai tugas.',
        type: V2ToastType.error,
      );
      _showPetugasQuickSetupSheet();
      return;
    }

    final hasSpecificAssignment = task.teamId != null || task.assignedOfficerId != null;
    final isMyTeamAssigned = _myTeam != null && task.teamId != null && task.teamId == _myTeam!['id'];
    final isMyAccountAssigned = _currentUser != null && task.assignedOfficerId != null && task.assignedOfficerId == _currentUser!.id;
    final isAssignedToMeOrMyTeam = isMyTeamAssigned || isMyAccountAssigned || !hasSpecificAssignment;
    final isAssignedToOtherTeam = task.teamId != null && (_myTeam == null || task.teamId != _myTeam!['id']);

    if (isAssignedToOtherTeam) {
      final teamName = task.teamName ?? 'Regu Lain';
      showV2Toast(
        context,
        message: 'Tugas ini telah ditugaskan khusus untuk $teamName. Regu Anda tidak dapat mengambil alih tugas ini.',
        type: V2ToastType.error,
      );
      return;
    }

    if (_dispatchMode == 'OPERATOR_ONLY' && !isAssignedToMeOrMyTeam) {
      showV2Toast(
        context,
        message: 'Mode Operator Saja aktif. Tugas ini menunggu penunjukan regu secara resmi oleh Operator Dinas.',
        type: V2ToastType.error,
      );
      return;
    }

    if (_myActiveTasksCount >= 3) {
      showDialog(
        context: context,
        builder: (ctx) => AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
          title: const Row(
            children: [
              Icon(LucideIcons.triangleAlert, color: Color(0xFFD97706), size: 22),
              SizedBox(width: 8),
              Text('Batas Kuota Tugas Penuh', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
            ],
          ),
          content: const Text(
            'Anda sudah memiliki 3 tugas yang sedang aktif dikerjakan. Berdasarkan SOP teknis Dinas PUPR, selesaikan dan kirimkan bukti foto minimal salah satu tugas terlebih dahulu sebelum memulai tugas baru.',
            style: TextStyle(fontSize: 13, height: 1.4),
          ),
          actions: [
            ElevatedButton(
              onPressed: () => Navigator.pop(ctx),
              style: ElevatedButton.styleFrom(backgroundColor: AppColors.primary, foregroundColor: Colors.white),
              child: const Text('Mengerti'),
            ),
          ],
        ),
      );
      return;
    }

    try {
      final res = await _apiClient.dio.post('/complaints/${task.id}/start-work');
      if (res.statusCode == 200) {
        if (mounted) {
          showV2Toast(
            context,
            message: 'Pekerjaan resmi dimulai! Menuju rute peta GIS ke lokasi penanganan...',
            type: V2ToastType.success,
          );
          Navigator.push(
            context,
            MaterialPageRoute(
              builder: (_) => ComplaintDetailScreen(complaintId: task.id),
            ),
          ).then((_) => _fetchTasks());
        }
      }
    } on DioException catch (e) {
      final msg = e.response?.data?['message'] ?? 'Gagal memulai pekerjaan';
      if (mounted) {
        showV2Toast(
          context,
          message: msg,
          type: V2ToastType.error,
        );
      }
    }
  }

  Future<void> _submitResolutionWithPhoto(ComplaintModel task) async {
    if (_needsProfileCompletion) {
      showV2Toast(
        context,
        message: 'Silakan lengkapi profil resmi dan ubah kata sandi awal terlebih dahulu.',
        type: V2ToastType.error,
      );
      _showPetugasQuickSetupSheet();
      return;
    }

    final hasSpecificAssignment = task.teamId != null || task.assignedOfficerId != null;
    final isMyTeamAssigned = _myTeam != null && task.teamId != null && task.teamId == _myTeam!['id'];
    final isMyAccountAssigned = _currentUser != null && task.assignedOfficerId != null && task.assignedOfficerId == _currentUser!.id;
    final isAssignedToMeOrMyTeam = isMyTeamAssigned || isMyAccountAssigned || !hasSpecificAssignment;

    if (!isAssignedToMeOrMyTeam) {
      final teamName = task.teamName ?? 'regu lain';
      showV2Toast(
        context,
        message: 'Anda tidak dapat mengunggah bukti untuk tugas yang dikerjakan oleh $teamName.',
        type: V2ToastType.error,
      );
      return;
    }

    final photo = await _mediaService.takePhoto();
    if (photo == null || !mounted) return;

    final noteController = TextEditingController();
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Row(
          children: [
            Icon(LucideIcons.camera, color: AppColors.primary, size: 20),
            SizedBox(width: 8),
            Text('Bukti Penyelesaian Fisik', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Foto berhasil diambil. Masukkan catatan teknis pekerjaan lapangan untuk divalidasi oleh Operator:',
              style: TextStyle(fontSize: 12, color: AppColors.textSecondary),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: noteController,
              maxLines: 3,
              style: const TextStyle(fontSize: 13),
              decoration: InputDecoration(
                hintText: 'Contoh: Tambal lubang aspal 2 titik menggunakan hotmix AC-WC telah tuntas & rata.',
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
              ),
            ),
          ],
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Batal')),
          ElevatedButton(
            onPressed: () => Navigator.pop(ctx, true),
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.statusCompleted,
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
            ),
            child: const Text('Kirim ke Operator'),
          ),
        ],
      ),
    );

    if (confirmed != true || !mounted) return;

    try {
      final formData = FormData();
      formData.fields.add(MapEntry(
        'note',
        noteController.text.isNotEmpty ? noteController.text : 'Pekerjaan perbaikan fisik telah diselesaikan di lapangan',
      ));
      formData.files.add(MapEntry(
        'photos[]',
        await MultipartFile.fromFile(File(photo.path).path, filename: 'resolution.jpg'),
      ));

      final res = await _apiClient.dio.post('/complaints/${task.id}/finish-work', data: formData);
      if (res.statusCode == 200 && mounted) {
        showV2Toast(
          context,
          message: 'Bukti penyelesaian berhasil dikirim! Menunggu validasi akhir dari Operator.',
          type: V2ToastType.success,
        );
        _fetchTasks();
      }
    } on DioException catch (e) {
      final isNetworkErr = e.response == null ||
          e.type == DioExceptionType.connectionError ||
          e.type == DioExceptionType.connectionTimeout ||
          e.type == DioExceptionType.receiveTimeout;
      if (isNetworkErr) {
        final finalNote = noteController.text.isNotEmpty
            ? noteController.text
            : 'Pekerjaan perbaikan fisik telah diselesaikan di lapangan';
        await _offlineQueueService.enqueue(
          OfflineResolutionItem(
            complaintId: task.id,
            publicCode: task.publicCode,
            title: task.title,
            note: finalNote,
            photoPath: File(photo.path).path,
            queuedAt: DateTime.now().toIso8601String(),
          ),
        );
        final updatedQueue = await _offlineQueueService.getQueue();
        if (mounted) {
          setState(() => _pendingOfflineItems = updatedQueue);
          showV2Toast(
            context,
            message: 'Sinyal lemah/offline. Bukti disimpan di antrean sinkronisasi otomatis.',
            type: V2ToastType.warning,
            duration: const Duration(seconds: 4),
          );
        }
        return;
      }
      final msg = e.response?.data?['message'] ?? 'Gagal mengirim bukti';
      if (mounted) {
        showV2Toast(
          context,
          message: msg,
          type: V2ToastType.error,
        );
      }
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
              title: 'SI-APPIK',
              subtitle: 'Kabupaten',
              leading: Container(
                width: 36,
                height: 36,
                padding: const EdgeInsets.all(6),
                decoration: BoxDecoration(
                  color: AppColors.primaryContainer,
                  borderRadius: BorderRadius.circular(11),
                  border: Border.all(color: AppColors.primaryBorder),
                ),
                child: Image.asset(
                  'assets/images/logo_pin.png',
                  fit: BoxFit.contain,
                ),
              ),
              actions: [
                Container(
                  margin: const EdgeInsets.only(right: 8),
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                  decoration: BoxDecoration(
                    color: AppColors.primaryContainer,
                    borderRadius: BorderRadius.circular(99),
                    border: Border.all(color: AppColors.primaryBorder),
                  ),
                  child: Text(
                    'Kuota ${_dispatchedCount + _inProgressCount}/3 Aktif',
                    style: GoogleFonts.inter(
                      fontSize: 10.5,
                      fontWeight: FontWeight.bold,
                      color: AppColors.primary,
                    ),
                  ),
                ),
                InkWell(
                  borderRadius: BorderRadius.circular(11),
                  onTap: () async {
                    await Navigator.of(context).push(
                      MaterialPageRoute(builder: (_) => const NotificationsScreen()),
                    );
                    _fetchUnreadNotifications();
                  },
                  child: Container(
                    width: 36,
                    height: 36,
                    decoration: BoxDecoration(
                      color: AppColors.cardSurface,
                      borderRadius: BorderRadius.circular(11),
                      border: Border.all(color: AppColors.border),
                    ),
                    child: Stack(
                      alignment: Alignment.center,
                      clipBehavior: Clip.none,
                      children: [
                        const Icon(LucideIcons.bell, color: AppColors.textPrimary, size: 18),
                        if (_unreadNotifications > 0)
                          Positioned(
                            top: 4,
                            right: 4,
                            child: Container(
                              padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 1),
                              decoration: const BoxDecoration(
                                color: AppColors.statusRejected,
                                shape: BoxShape.circle,
                              ),
                              constraints: const BoxConstraints(minWidth: 14, minHeight: 14),
                              child: Text(
                                '$_unreadNotifications',
                                textAlign: TextAlign.center,
                                style: const TextStyle(color: Colors.white, fontSize: 8.5, fontWeight: FontWeight.bold),
                              ),
                            ),
                          ),
                      ],
                    ),
                  ),
                ),
              ],
            ),
            Expanded(
              child: RefreshIndicator(
                onRefresh: () async {
                  await Future.wait([
                    _fetchTasks(),
                    _fetchUnreadNotifications(),
                    _loadAndSyncOfflineQueue(silent: true),
                  ]);
                },
                child: ListView(
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
          children: [
            if (_pendingOfflineItems.isNotEmpty) ...[
              Container(
                margin: const EdgeInsets.only(bottom: 12),
                padding: const EdgeInsets.all(14),
                decoration: BoxDecoration(
                  color: const Color(0xFFFFFBEB),
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(color: const Color(0xFFFDE68A)),
                ),
                child: Row(
                  children: [
                    const Icon(LucideIcons.cloudOff, size: 18, color: AppColors.statusInProgress),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            '${_pendingOfflineItems.length} Bukti Menunggu Sinkronisasi',
                            style: GoogleFonts.outfit(
                              fontSize: 13,
                              fontWeight: FontWeight.bold,
                              color: const Color(0xFF92400E),
                            ),
                          ),
                          Text(
                            'Akan otomatis diunggah saat sinyal kembali stabil.',
                            style: GoogleFonts.inter(fontSize: 11, color: const Color(0xFF78350F)),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(width: 8),
                    ElevatedButton(
                      onPressed: _isSyncingOffline ? null : () => _loadAndSyncOfflineQueue(silent: false),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppColors.statusInProgress,
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                      ),
                      child: _isSyncingOffline
                          ? const SizedBox(
                              width: 14,
                              height: 14,
                              child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                            )
                          : const Text('Sinkronkan', style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold)),
                    ),
                  ],
                ),
              ),
            ],

            // Hero Banner Sambutan Pekerja Lapangan
            _buildWelcomeCard(),
            const SizedBox(height: 14),

            // 4 Top KPI Cards for Pekerja Lapangan
            _buildKpiSection(),
            const SizedBox(height: 14),

            // Segmented Switch: Semua Tugas / Tugas Regu Saya
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
                        if (_taskScope != 'ALL') {
                          setState(() => _taskScope = 'ALL');
                          _fetchTasks();
                        }
                      },
                      child: Container(
                        padding: const EdgeInsets.symmetric(vertical: 10),
                        decoration: BoxDecoration(
                          color: _taskScope == 'ALL' ? AppColors.primary : Colors.transparent,
                          borderRadius: BorderRadius.circular(10),
                        ),
                        child: Center(
                          child: Text(
                            'Daftar Penugasan',
                            style: TextStyle(
                              fontSize: 12,
                              fontWeight: FontWeight.bold,
                              color: _taskScope == 'ALL' ? Colors.white : AppColors.textSecondary,
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
                        if (_taskScope != 'MY') {
                          setState(() => _taskScope = 'MY');
                          _fetchTasks();
                        }
                      },
                      child: Container(
                        padding: const EdgeInsets.symmetric(vertical: 10),
                        decoration: BoxDecoration(
                          color: _taskScope == 'MY' ? AppColors.primary : Colors.transparent,
                          borderRadius: BorderRadius.circular(10),
                        ),
                        child: Center(
                          child: Row(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Icon(
                                _myTeam != null ? LucideIcons.users : LucideIcons.user,
                                size: 14,
                                color: _taskScope == 'MY' ? Colors.white : AppColors.textSecondary,
                              ),
                              const SizedBox(width: 6),
                              Text(
                                _myTeam != null ? 'Tugas Regu Saya' : 'Tugas Saya',
                                style: TextStyle(
                                  fontSize: 12,
                                  fontWeight: FontWeight.bold,
                                  color: _taskScope == 'MY' ? Colors.white : AppColors.textSecondary,
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

            // Search Bar (Seragam dengan Halaman Warga)
            TextField(
              style: GoogleFonts.inter(fontSize: 13, color: AppColors.textPrimary),
              decoration: InputDecoration(
                hintText: 'Cari tugas, kode (#0003/ringan/2026), atau lokasi...',
                hintStyle: GoogleFonts.inter(fontSize: 12.5, color: AppColors.textSecondary),
                prefixIcon: const Icon(LucideIcons.search, size: 18, color: AppColors.textSecondary),
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
            const SizedBox(height: 12),

            // Filter Chips
            _buildFilterChips(),
            const SizedBox(height: 14),

            // Task List Header + Toggle Daftar / Petak di Kanan Atas
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              crossAxisAlignment: CrossAxisAlignment.center,
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        _taskScope == 'MY' ? 'Daftar Tugas Regu' : 'Daftar Aduan Ditugaskan',
                        style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        '${_filteredTasks.length} Tugas ditemukan',
                        style: const TextStyle(fontSize: 11.5, color: AppColors.textSecondary, fontWeight: FontWeight.w500),
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

            // Task list items
            if (_isLoading)
              const Center(
                child: Padding(
                  padding: EdgeInsets.all(40),
                  child: CircularProgressIndicator(),
                ),
              )
            else if (_filteredTasks.isEmpty)
              Container(
                padding: const EdgeInsets.symmetric(vertical: 48, horizontal: 24),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: AppColors.border),
                ),
                child: Column(
                  children: [
                    const Icon(LucideIcons.clipboardList, size: 52, color: AppColors.primary),
                    const SizedBox(height: 12),
                    Text(
                      _taskScope == 'MY' ? 'Tidak Ada Aduan Dikerjakan Regu' : 'Tidak Ada Tugas Lapangan',
                      style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: AppColors.textPrimary),
                    ),
                    const SizedBox(height: 6),
                    Text(
                      _selectedFilter != 'ALL' || _selectedDamageType != 'ALL' || _searchQuery.isNotEmpty
                          ? 'Tidak ada tugas yang cocok dengan filter atau kata kunci pencarian Anda.'
                          : (_taskScope == 'MY'
                              ? 'Silakan ambil aduan yang sudah ditugaskan pada tab Semua Tugas untuk dikerjakan bersama regu Anda.'
                              : 'Tugas akan muncul ketika Operator menugaskan laporan baru kepada Anda atau regu Anda.'),
                      textAlign: TextAlign.center,
                      style: const TextStyle(color: AppColors.textSecondary, fontSize: 12),
                    ),
                  ],
                ),
              )
            else ...[
              if (_isGridView)
                _buildTaskGridRows(_paginatedTasks)
              else
                ..._paginatedTasks.map((t) => _buildTaskCard(t)),
              ReportPaginationBar(
                totalItems: _filteredTasks.length,
                currentPage: _currentPage,
                pageSize: _pageSize,
                itemUnitLabel: 'tugas',
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
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildWelcomeCard() {
    final fullName = (_currentUser?.name ?? '').trim();
    final userName = fullName.isNotEmpty ? fullName : 'Pekerja Lapangan';
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: AppColors.primary,
        borderRadius: BorderRadius.circular(22),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Selamat Datang, $userName',
            style: GoogleFonts.inter(
              color: const Color(0xFFDBEAFE),
              fontSize: 12,
              fontWeight: FontWeight.w500,
            ),
          ),
          const SizedBox(height: 6),
          Text(
            'Lakukan pengerjaan dan selesaikan tugas perbaikan tepat waktu',
            style: GoogleFonts.outfit(
              color: Colors.white,
              fontWeight: FontWeight.bold,
              fontSize: 18,
              height: 1.3,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildKpiSection() {
    return Column(
      children: [
        Row(
          children: [
            _buildKpiCard('Total Laporan', '$_totalTasks', AppColors.textPrimary),
            const SizedBox(width: 10),
            _buildKpiCard('Ditugaskan', '$_dispatchedCount', AppColors.primary),
          ],
        ),
        const SizedBox(height: 10),
        Row(
          children: [
            _buildKpiCard('Diproses', '$_diprosesCount', AppColors.statusInProgress),
            const SizedBox(width: 10),
            _buildKpiCard('Selesai', '$_completedCount', AppColors.statusCompleted),
          ],
        ),
      ],
    );
  }

  Widget _buildKpiCard(String label, String value, Color valueColor) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 12),
        decoration: BoxDecoration(
          color: AppColors.surface,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: AppColors.border),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              value,
              style: GoogleFonts.outfit(
                fontSize: 22,
                fontWeight: FontWeight.bold,
                color: valueColor,
                height: 1.1,
              ),
            ),
            const SizedBox(height: 4),
            Text(
              label,
              style: GoogleFonts.inter(
                fontSize: 11,
                color: AppColors.textSecondary,
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



  Future<void> _openNavigation(double lat, double lng) async {
    // 1. Coba native intent Google Maps navigation mode driving
    final nativeNavUri = Uri.parse('google.navigation:q=$lat,$lng&mode=d');
    // 2. Coba geo URI standard
    final geoUri = Uri.parse('geo:$lat,$lng?q=$lat,$lng');
    // 3. Fallback web URL Google Maps
    final webMapsUri = Uri.parse('https://www.google.com/maps/dir/?api=1&destination=$lat,$lng');

    try {
      if (await canLaunchUrl(nativeNavUri)) {
        await launchUrl(nativeNavUri, mode: LaunchMode.externalApplication);
        return;
      }
      if (await canLaunchUrl(geoUri)) {
        await launchUrl(geoUri, mode: LaunchMode.externalApplication);
        return;
      }
      if (await canLaunchUrl(webMapsUri)) {
        await launchUrl(webMapsUri, mode: LaunchMode.externalApplication);
        return;
      }
      // Direct launch fallback
      await launchUrl(webMapsUri, mode: LaunchMode.externalApplication);
    } catch (_) {
      try {
        await launchUrl(webMapsUri, mode: LaunchMode.platformDefault);
      } catch (err) {
        if (mounted) {
          showV2Toast(
            context,
            message: 'Tidak dapat membuka aplikasi Google Maps',
            type: V2ToastType.error,
          );
        }
      }
    }
  }

  Widget _buildFilterChips() {
    final statusChips = [
      {'label': 'Semua Aduan', 'value': 'ALL'},
      {'label': 'Ditugaskan', 'value': 'DISPATCHED'},
      {'label': 'Dikerjakan', 'value': 'IN_PROGRESS'},
      {'label': 'Validasi', 'value': 'WAITING_VERIFICATION'},
      {'label': 'Selesai', 'value': 'COMPLETED'},
    ];

    final categoryFilters = <Map<String, dynamic>>[
      {'label': 'Semua Kategori', 'id': null},
      ..._activeCategoryMap.entries.map((e) => {'label': e.value, 'id': e.key}),
    ];

    String selectedCategoryLabel = 'Semua Kategori';
    for (final cat in categoryFilters) {
      if (cat['id'] == _selectedCategoryFilter) {
        selectedCategoryLabel = cat['label'] as String;
        break;
      }
    }

    final damageTypeChips = [
      {'label': 'Semua Tipe', 'value': 'ALL'},
      {'label': 'Kerusakan Ringan', 'value': 'minor'},
      {'label': 'Kerusakan Berat', 'value': 'major'},
    ];

    String selectedDamageTypeLabel = 'Semua Tipe';
    if (_selectedDamageType == 'minor') selectedDamageTypeLabel = 'Kerusakan Ringan';
    if (_selectedDamageType == 'major') selectedDamageTypeLabel = 'Kerusakan Berat';

    final hasActiveSubFilter = _selectedCategoryFilter != null || _selectedDamageType != 'ALL';

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // 1. Filter Status Pengerjaan (Baris 1: Penuh simetris rapat kiri-kanan tanpa gap)
        Row(
          children: statusChips.asMap().entries.map((entry) {
            final idx = entry.key;
            final f = entry.value;
            final isSelected = _selectedFilter == f['value'];
            return Expanded(
              child: Padding(
                padding: EdgeInsets.only(right: idx == statusChips.length - 1 ? 0 : 6),
                child: InkWell(
                  onTap: () {
                    setState(() => _selectedFilter = f['value']!);
                    _fetchTasks();
                  },
                  borderRadius: BorderRadius.circular(10),
                  child: Container(
                    padding: const EdgeInsets.symmetric(vertical: 8, horizontal: 4),
                    alignment: Alignment.center,
                    decoration: BoxDecoration(
                      color: isSelected ? AppColors.primary.withValues(alpha: 0.12) : Colors.white,
                      borderRadius: BorderRadius.circular(10),
                      border: Border.all(
                        color: isSelected ? AppColors.primary : AppColors.border,
                        width: isSelected ? 1.4 : 1.0,
                      ),
                    ),
                    child: FittedBox(
                      fit: BoxFit.scaleDown,
                      child: Text(
                        f['label']!,
                        maxLines: 1,
                        style: GoogleFonts.inter(
                          fontSize: 11.5,
                          color: isSelected ? AppColors.primary : AppColors.textSecondary,
                          fontWeight: isSelected ? FontWeight.bold : FontWeight.w500,
                        ),
                      ),
                    ),
                  ),
                ),
              ),
            );
          }).toList(),
        ),
        const SizedBox(height: 8),

        // 2. Baris Dropdown Filter (Kategori & Tipe Laporan) - Persis Gaya Halaman Warga
        Row(
          children: [
            // Dropdown Filter Kategori
            Expanded(
              child: PopupMenuButton<int>(
                tooltip: 'Pilih Kategori',
                initialValue: _selectedCategoryFilter ?? -1,
                onSelected: (int catId) {
                  setState(() => _selectedCategoryFilter = catId == -1 ? null : catId);
                },
                itemBuilder: (context) => categoryFilters.map((c) {
                  final catId = c['id'] as int?;
                  final isCatSelected = _selectedCategoryFilter == catId;
                  return PopupMenuItem<int>(
                    value: catId ?? -1,
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

            // Dropdown Filter Tipe Laporan (Kerusakan Ringan vs Berat)
            Expanded(
              child: PopupMenuButton<String>(
                tooltip: 'Pilih Tipe Laporan',
                initialValue: _selectedDamageType,
                onSelected: (String val) {
                  setState(() => _selectedDamageType = val);
                },
                itemBuilder: (context) => damageTypeChips.map((d) {
                  final isTypeSelected = _selectedDamageType == d['value'];
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
                    color: _selectedDamageType != 'ALL' ? AppColors.primary.withValues(alpha: 0.08) : Colors.white,
                    borderRadius: BorderRadius.circular(10),
                    border: Border.all(
                      color: _selectedDamageType != 'ALL' ? AppColors.primary : AppColors.border,
                    ),
                  ),
                  child: Row(
                    children: [
                      Icon(
                        LucideIcons.alertCircle,
                        size: 13,
                        color: _selectedDamageType != 'ALL' ? AppColors.primary : AppColors.textSecondary,
                      ),
                      const SizedBox(width: 5),
                      Expanded(
                        child: Text(
                          selectedDamageTypeLabel,
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: TextStyle(
                            fontSize: 11,
                            fontWeight: _selectedDamageType != 'ALL' ? FontWeight.bold : FontWeight.w500,
                            color: _selectedDamageType != 'ALL' ? AppColors.primary : AppColors.textPrimary,
                          ),
                        ),
                      ),
                      Icon(
                        LucideIcons.chevronDown,
                        size: 13,
                        color: _selectedDamageType != 'ALL' ? AppColors.primary : AppColors.textSecondary,
                      ),
                    ],
                  ),
                ),
              ),
            ),

            // Tombol Reset Cepat (Tampil jika kategori atau tipe aktif)
            if (hasActiveSubFilter) ...[
              const SizedBox(width: 6),
              InkWell(
                onTap: () {
                  setState(() {
                    _selectedCategoryFilter = null;
                    _selectedDamageType = 'ALL';
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

  Widget _buildTaskGridCard(ComplaintModel task) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: AppColors.border),
      ),
      child: InkWell(
        onTap: () => Navigator.push(
          context,
          MaterialPageRoute(builder: (_) => ComplaintDetailScreen(complaintId: task.id)),
        ).then((_) => _fetchTasks()),
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
                          task.publicCode.startsWith('#') ? task.publicCode : '#${task.publicCode}',
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: GoogleFonts.inter(
                            fontWeight: FontWeight.bold,
                            fontSize: 10.5,
                            color: AppColors.textPrimary,
                          ),
                        ),
                      ),
                      const SizedBox(width: 4),
                      StatusBadge(status: task.status),
                    ],
                  ),
                  const SizedBox(height: 8),
                  Text(
                    task.title,
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                    style: GoogleFonts.outfit(
                      fontSize: 13.5,
                      fontWeight: FontWeight.bold,
                      color: AppColors.textPrimary,
                      height: 1.25,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    task.address,
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                    style: GoogleFonts.inter(
                      fontSize: 11,
                      color: AppColors.textSecondary,
                      height: 1.3,
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 8),
              Row(
                children: [
                  const Icon(LucideIcons.user, size: 11, color: AppColors.textSecondary),
                  const SizedBox(width: 4),
                  Expanded(
                    child: Text(
                      'Pelapor: ${task.isAnonymous ? 'Anonim' : task.reporterName}',
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: GoogleFonts.inter(
                        fontSize: 10.5,
                        color: AppColors.textSecondary,
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildTaskCard(ComplaintModel task) {
    final isRevision = task.status == 'REVISION_REQUIRED';
    final canStart = task.status == 'DISPATCHED' || task.status == 'VERIFIED';
    final isWorking = task.status == 'IN_PROGRESS';
    final isWaiting = task.status == 'WAITING_VERIFICATION';
    final isCompleted = ['COMPLETED', 'CLOSED'].contains(task.status);

    final hasSpecificAssignment = task.teamId != null || task.assignedOfficerId != null;
    final isMyTeamAssigned = _myTeam != null && task.teamId != null && task.teamId == _myTeam!['id'];
    final isMyAccountAssigned = _currentUser != null && task.assignedOfficerId != null && task.assignedOfficerId == _currentUser!.id;
    final isAssignedToMeOrMyTeam = isMyTeamAssigned || isMyAccountAssigned || !hasSpecificAssignment;
    final isAssignedToOtherTeam = task.teamId != null && (_myTeam == null || task.teamId != _myTeam!['id']);

    final canUploadRevision = isRevision && isAssignedToMeOrMyTeam;
    final canUploadFinish = isWorking && isAssignedToMeOrMyTeam;

    bool isStartAllowed = true;
    String? startBlockedReason;

    if (canStart) {
      if (isAssignedToOtherTeam) {
        isStartAllowed = false;
        startBlockedReason = 'Ditugaskan ke ${task.teamName ?? "Regu Lain"}';
      } else if (_dispatchMode == 'OPERATOR_ONLY') {
        if (isAssignedToMeOrMyTeam) {
          isStartAllowed = true;
        } else {
          isStartAllowed = false;
          startBlockedReason = 'Menunggu penunjukan regu oleh Operator Dinas';
        }
      } else {
        isStartAllowed = true;
      }
    }

    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.border),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Content Card (Seragam dengan Kartu Laporan Halaman Warga)
          InkWell(
            onTap: () => Navigator.push(
              context,
              MaterialPageRoute(builder: (_) => ComplaintDetailScreen(complaintId: task.id)),
            ).then((_) => _fetchTasks()),
            borderRadius: const BorderRadius.vertical(top: Radius.circular(16)),
            child: Padding(
              padding: const EdgeInsets.all(14),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    crossAxisAlignment: CrossAxisAlignment.center,
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(
                        task.publicCode.startsWith('#') ? task.publicCode : '#${task.publicCode}',
                        style: GoogleFonts.inter(
                          fontWeight: FontWeight.bold,
                          fontSize: 11.5,
                          color: AppColors.textPrimary,
                        ),
                      ),
                      StatusBadge(status: task.status),
                    ],
                  ),
                  const SizedBox(height: 8),
                  Text(
                    task.title,
                    style: GoogleFonts.outfit(
                      fontSize: 15,
                      fontWeight: FontWeight.bold,
                      color: AppColors.textPrimary,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    task.address,
                    style: GoogleFonts.inter(fontSize: 12, color: AppColors.textSecondary),
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
                          'Pelapor: ${task.isAnonymous ? 'Anonim' : task.reporterName}${task.teamName != null ? ' • ${task.teamName}' : ''}',
                          style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: AppColors.textPrimary),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                    ],
                  ),

                  // Catatan Revisi Ringkas (Jika status REVISION_REQUIRED)
                  if (isRevision && task.revisionNotes != null && task.revisionNotes!.trim().isNotEmpty) ...[
                    const SizedBox(height: 8),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                      decoration: BoxDecoration(
                        color: const Color(0xFFFFF7ED),
                        borderRadius: BorderRadius.circular(8),
                        border: Border.all(color: const Color(0xFFFED7AA)),
                      ),
                      child: Row(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Icon(LucideIcons.messageSquareWarning, size: 13, color: Color(0xFFEA580C)),
                          const SizedBox(width: 6),
                          Expanded(
                            child: Text(
                              'Catatan Perbaikan: ${task.revisionNotes}',
                              style: const TextStyle(fontSize: 11, color: Color(0xFF9A3412), fontWeight: FontWeight.w500, height: 1.3),
                              maxLines: 2,
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],

                  const SizedBox(height: 8),
                  // Baris Kategori & Tenggat Waktu
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      if (task.categoryName != null)
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                          decoration: BoxDecoration(
                            color: AppColors.background,
                            borderRadius: BorderRadius.circular(6),
                            border: Border.all(color: AppColors.border),
                          ),
                          child: Text(
                            task.categoryName!,
                            style: const TextStyle(fontSize: 11, color: AppColors.textSecondary),
                          ),
                        )
                      else
                        const SizedBox.shrink(),
                      if (!isCompleted && task.deadlineHuman != null)
                        Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            const Icon(LucideIcons.calendarClock, size: 12, color: AppColors.textSecondary),
                            const SizedBox(width: 4),
                            Text(
                              'Tenggat: ${task.deadlineHuman}',
                              style: const TextStyle(fontSize: 10.5, color: AppColors.textSecondary, fontWeight: FontWeight.w500),
                            ),
                          ],
                        ),
                    ],
                  ),
                ],
              ),
            ),
          ),

          // Action buttons berdasarkan status
          const Divider(height: 1, color: AppColors.border),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
            child: Row(
              children: [
                if (isRevision) ...[
                  if (canUploadRevision)
                    Expanded(
                      child: ElevatedButton.icon(
                        onPressed: () => _submitResolutionWithPhoto(task),
                        icon: const Icon(LucideIcons.camera, size: 16),
                        label: const Text('Perbaiki & Kirim Ulang Bukti', style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold)),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: const Color(0xFFEA580C),
                          foregroundColor: Colors.white,
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                          padding: const EdgeInsets.symmetric(vertical: 12),
                        ),
                      ),
                    )
                  else
                    Expanded(
                      child: Container(
                        padding: const EdgeInsets.symmetric(vertical: 11, horizontal: 12),
                        decoration: BoxDecoration(
                          color: const Color(0xFFFFF7ED),
                          borderRadius: BorderRadius.circular(10),
                          border: Border.all(color: const Color(0xFFFED7AA)),
                        ),
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            const Icon(LucideIcons.lock, size: 14, color: Color(0xFFEA580C)),
                            const SizedBox(width: 6),
                            Flexible(
                              child: Text(
                                'Perbaikan Khusus ${task.teamName ?? "Regu Lain"}',
                                style: const TextStyle(fontSize: 11.5, fontWeight: FontWeight.w600, color: Color(0xFFC2410C)),
                                textAlign: TextAlign.center,
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                ] else if (canStart) ...[
                  if (isStartAllowed)
                    Expanded(
                      child: ElevatedButton.icon(
                        onPressed: () => _startWork(task),
                        icon: const Icon(LucideIcons.play, size: 16),
                        label: const Text('Mulai Kerja Lapangan', style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold)),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: AppColors.primary,
                          foregroundColor: Colors.white,
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                          padding: const EdgeInsets.symmetric(vertical: 12),
                        ),
                      ),
                    )
                  else
                    Expanded(
                      child: Container(
                        padding: const EdgeInsets.symmetric(vertical: 11, horizontal: 12),
                        decoration: BoxDecoration(
                          color: const Color(0xFFF1F5F9),
                          borderRadius: BorderRadius.circular(10),
                          border: Border.all(color: const Color(0xFFCBD5E1)),
                        ),
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            const Icon(LucideIcons.lock, size: 14, color: AppColors.textSecondary),
                            const SizedBox(width: 6),
                            Flexible(
                              child: Text(
                                startBlockedReason ?? 'Penugasan Khusus Operator',
                                style: const TextStyle(fontSize: 11.5, fontWeight: FontWeight.w600, color: AppColors.textSecondary),
                                textAlign: TextAlign.center,
                                maxLines: 2,
                                overflow: TextOverflow.ellipsis,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                ],
                if (isWorking) ...[
                  if (canUploadFinish) ...[
                    Expanded(
                      flex: 2,
                      child: OutlinedButton.icon(
                        onPressed: () => _openNavigation(task.latitude, task.longitude),
                        icon: const Icon(LucideIcons.navigation, size: 15),
                        label: Text(
                          'Rute',
                          style: GoogleFonts.inter(fontSize: 12.5, fontWeight: FontWeight.bold),
                        ),
                        style: OutlinedButton.styleFrom(
                          foregroundColor: AppColors.primary,
                          backgroundColor: AppColors.primaryContainer,
                          side: const BorderSide(color: AppColors.primaryBorder),
                          elevation: 0,
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                          padding: const EdgeInsets.symmetric(vertical: 11, horizontal: 10),
                        ),
                      ),
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      flex: 3,
                      child: ElevatedButton.icon(
                        onPressed: () => _submitResolutionWithPhoto(task),
                        icon: const Icon(LucideIcons.camera, size: 15),
                        label: Text(
                          'Kirim Bukti',
                          style: GoogleFonts.inter(fontSize: 12.5, fontWeight: FontWeight.bold),
                        ),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: AppColors.primary,
                          foregroundColor: Colors.white,
                          elevation: 0,
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                          padding: const EdgeInsets.symmetric(vertical: 11, horizontal: 12),
                        ),
                      ),
                    ),
                  ] else
                    Expanded(
                      child: Container(
                        padding: const EdgeInsets.symmetric(vertical: 11, horizontal: 12),
                        decoration: BoxDecoration(
                          color: const Color(0xFFF8FAFC),
                          borderRadius: BorderRadius.circular(10),
                          border: Border.all(color: const Color(0xFFCBD5E1)),
                        ),
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            const Icon(LucideIcons.lock, size: 14, color: AppColors.textSecondary),
                            const SizedBox(width: 6),
                            Flexible(
                              child: Text(
                                'Dikerjakan oleh ${task.teamName ?? "Regu Lain"}',
                                style: const TextStyle(fontSize: 11.5, fontWeight: FontWeight.w600, color: AppColors.textSecondary),
                                textAlign: TextAlign.center,
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                ],
                if (isWaiting)
                  Expanded(
                    child: Container(
                      padding: const EdgeInsets.symmetric(vertical: 12),
                      decoration: BoxDecoration(
                        color: AppColors.primary.withValues(alpha: 0.08),
                        borderRadius: BorderRadius.circular(10),
                        border: Border.all(color: AppColors.primary.withValues(alpha: 0.25)),
                      ),
                      child: const Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Icon(LucideIcons.clock, size: 16, color: AppColors.primary),
                          SizedBox(width: 6),
                          Text(
                            'Menunggu Validasi Operator',
                            style: TextStyle(fontSize: 12, color: AppColors.primary, fontWeight: FontWeight.bold),
                          ),
                        ],
                      ),
                    ),
                  ),
                if (isCompleted)
                  Expanded(
                    child: Container(
                      padding: const EdgeInsets.symmetric(vertical: 9, horizontal: 12),
                      decoration: BoxDecoration(
                        color: AppColors.statusCompleted.withValues(alpha: 0.08),
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          const Icon(LucideIcons.checkCheck, size: 15, color: AppColors.statusCompleted),
                          const SizedBox(width: 6),
                          Flexible(
                            child: Text(
                              'Tuntas & Divalidasi Operator',
                              style: GoogleFonts.inter(
                                fontSize: 11.5,
                                color: AppColors.statusCompleted,
                                fontWeight: FontWeight.w600,
                              ),
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
