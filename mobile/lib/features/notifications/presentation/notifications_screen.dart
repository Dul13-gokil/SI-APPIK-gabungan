import 'dart:async';
import 'package:flutter/material.dart';
import 'package:lucide_icons_flutter/lucide_icons.dart';
import '../../../core/auth/auth_service.dart';
import '../../../core/network/api_client.dart';
import '../../../shared/theme/app_colors.dart';
import '../../../shared/widgets/v2_toast.dart';
import '../../complaints/presentation/complaint_detail_screen.dart';

class NotificationsScreen extends StatefulWidget {
  const NotificationsScreen({super.key});

  @override
  State<NotificationsScreen> createState() => _NotificationsScreenState();
}

class _NotificationsScreenState extends State<NotificationsScreen> {
  final _apiClient = ApiClient();
  final _authService = AuthService();

  List<Map<String, dynamic>> _notifications = [];
  bool _isLoading = true;
  int _unreadCount = 0;
  Timer? _pollingTimer;

  @override
  void initState() {
    super.initState();
    _fetchNotifications();
    // Polling setiap 30 detik untuk mendapat notifikasi real-time
    _pollingTimer = Timer.periodic(const Duration(seconds: 30), (_) {
      _fetchNotifications(silent: true);
    });
  }

  @override
  void dispose() {
    _pollingTimer?.cancel();
    super.dispose();
  }

  Future<void> _fetchNotifications({bool silent = false}) async {
    if (!silent && mounted) setState(() => _isLoading = true);

    // Mode tamu tidak punya notifikasi
    final isGuest = await _authService.isGuestMode();
    if (isGuest) {
      if (mounted) setState(() => _isLoading = false);
      return;
    }

    try {
      final res = await _apiClient.dio.get('/notifications');
      if (res.statusCode == 200 && res.data['data'] != null) {
        final data = res.data['data'];
        if (mounted) {
          setState(() {
            _notifications = List<Map<String, dynamic>>.from(data['notifications'] ?? []);
            _unreadCount = data['unread_count'] ?? 0;
          });
        }
      }
    } catch (_) {}

    if (mounted) setState(() => _isLoading = false);
  }

  Future<void> _markAllRead() async {
    setState(() {
      for (var n in _notifications) {
        n['is_read'] = true;
      }
      _unreadCount = 0;
    });
    try {
      await _apiClient.dio.post('/notifications/read-all');
    } catch (_) {}
    _fetchNotifications(silent: true);
  }

  Future<void> _markRead(int id) async {
    setState(() {
      final item = _notifications.firstWhere((n) => n['id'] == id, orElse: () => {});
      if (item.isNotEmpty && item['is_read'] != true) {
        item['is_read'] = true;
        if (_unreadCount > 0) _unreadCount--;
      }
    });
    try {
      await _apiClient.dio.post('/notifications/$id/read');
    } catch (_) {}
  }

  Future<void> _deleteNotification(int id) async {
    final target = _notifications.firstWhere((n) => n['id'] == id, orElse: () => {});
    setState(() {
      _notifications.removeWhere((n) => n['id'] == id);
      if (target.isNotEmpty && target['is_read'] != true && _unreadCount > 0) {
        _unreadCount--;
      }
    });

    try {
      await _apiClient.dio.delete('/notifications/$id');
    } catch (_) {}

    if (mounted) {
      showV2Toast(
        context,
        message: 'Notifikasi berhasil dihapus',
        type: V2ToastType.success,
        duration: const Duration(seconds: 2),
      );
    }
  }

  Future<void> _clearAllNotifications() async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Row(
          children: [
            Icon(LucideIcons.trash2, color: AppColors.statusRejected, size: 20),
            SizedBox(width: 8),
            Text('Hapus Semua', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
          ],
        ),
        content: const Text(
          'Apakah Anda yakin ingin menghapus seluruh notifikasi? Riwayat pemberitahuan akan dibersihkan.',
          style: TextStyle(fontSize: 13, color: AppColors.textSecondary),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(ctx).pop(false),
            child: const Text('Batal', style: TextStyle(color: AppColors.textSecondary)),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.statusRejected,
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
            ),
            onPressed: () => Navigator.of(ctx).pop(true),
            child: const Text('Hapus Semua', style: TextStyle(fontWeight: FontWeight.bold)),
          ),
        ],
      ),
    );

    if (confirm != true) return;

    setState(() {
      _notifications.clear();
      _unreadCount = 0;
    });

    try {
      await _apiClient.dio.delete('/notifications');
    } catch (_) {}

    if (mounted) {
      showV2Toast(
        context,
        message: 'Semua notifikasi berhasil dibersihkan',
        type: V2ToastType.success,
        duration: const Duration(seconds: 2),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: Row(
          children: [
            const Text('Notifikasi', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
            if (_unreadCount > 0) ...[
              const SizedBox(width: 8),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                decoration: BoxDecoration(
                  color: AppColors.statusRejected,
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Text(
                  '$_unreadCount baru',
                  style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold),
                ),
              ),
            ],
          ],
        ),
        backgroundColor: Colors.white,
        elevation: 0,
        foregroundColor: AppColors.textPrimary,
        actions: [
          if (_unreadCount > 0)
            TextButton.icon(
              onPressed: _markAllRead,
              icon: const Icon(LucideIcons.checkCheck, size: 15, color: AppColors.primary),
              label: const Text('Tandai Dibaca', style: TextStyle(color: AppColors.primary, fontSize: 11)),
            ),
          if (_notifications.isNotEmpty)
            IconButton(
              icon: const Icon(LucideIcons.trash2, size: 19, color: AppColors.statusRejected),
              tooltip: 'Hapus Semua Notifikasi',
              onPressed: _clearAllNotifications,
            ),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _fetchNotifications,
              child: _notifications.isEmpty
                  ? const Center(
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Icon(LucideIcons.bellOff, size: 56, color: AppColors.textSecondary),
                          SizedBox(height: 12),
                          Text('Belum ada notifikasi', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
                          SizedBox(height: 4),
                          Text(
                            'Notifikasi akan muncul ketika status\nlaporan Anda berubah',
                            textAlign: TextAlign.center,
                            style: TextStyle(color: AppColors.textSecondary, fontSize: 13),
                          ),
                        ],
                      ),
                    )
                  : ListView.builder(
                      padding: const EdgeInsets.all(16),
                      itemCount: _notifications.length,
                      itemBuilder: (context, index) {
                        final n = _notifications[index];
                        final isRead = n['is_read'] == true;
                        final type = n['type'] ?? 'INFO';
                        final notifId = n['id'] as int;
                        final complaintId = n['data']?['complaint_id'];

                        return Dismissible(
                          key: ValueKey('notif_$notifId'),
                          direction: DismissDirection.endToStart,
                          background: Container(
                            margin: const EdgeInsets.only(bottom: 12),
                            decoration: BoxDecoration(
                              color: AppColors.statusRejected,
                              borderRadius: BorderRadius.circular(16),
                            ),
                            alignment: Alignment.centerRight,
                            padding: const EdgeInsets.symmetric(horizontal: 20),
                            child: const Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Icon(LucideIcons.trash2, color: Colors.white, size: 18),
                                SizedBox(width: 6),
                                Text(
                                  'Hapus',
                                  style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 12),
                                ),
                              ],
                            ),
                          ),
                          onDismissed: (_) {
                            _deleteNotification(notifId);
                          },
                          child: GestureDetector(
                            onTap: () async {
                              final nav = Navigator.of(context);
                              if (!isRead) {
                                await _markRead(notifId);
                                _fetchNotifications(silent: true);
                              }
                              if (complaintId != null && mounted) {
                                final cid = complaintId is int ? complaintId : int.tryParse(complaintId.toString());
                                if (cid != null) {
                                  nav.push(
                                    MaterialPageRoute(
                                      builder: (_) => ComplaintDetailScreen(complaintId: cid),
                                    ),
                                  );
                                }
                              }
                            },
                            child: Container(
                              margin: const EdgeInsets.only(bottom: 12),
                              padding: const EdgeInsets.all(14),
                              decoration: BoxDecoration(
                                color: isRead ? Colors.white : AppColors.primary.withValues(alpha: 0.04),
                                borderRadius: BorderRadius.circular(16),
                                border: Border.all(
                                  color: isRead
                                      ? AppColors.border
                                      : AppColors.primary.withValues(alpha: 0.25),
                                  width: isRead ? 1 : 1.5,
                                ),
                              ),
                              child: Row(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Container(
                                    padding: const EdgeInsets.all(9),
                                    decoration: BoxDecoration(
                                      color: _typeColor(type).withValues(alpha: 0.12),
                                      shape: BoxShape.circle,
                                    ),
                                    child: Icon(_typeIcon(type), size: 18, color: _typeColor(type)),
                                  ),
                                  const SizedBox(width: 10),
                                  Expanded(
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        Row(
                                          children: [
                                            Expanded(
                                              child: Text(
                                                n['title'] ?? '',
                                                style: TextStyle(
                                                  fontWeight: isRead ? FontWeight.w600 : FontWeight.bold,
                                                  fontSize: 13,
                                                  color: AppColors.textPrimary,
                                                ),
                                              ),
                                            ),
                                            if (!isRead)
                                              Container(
                                                width: 8,
                                                height: 8,
                                                margin: const EdgeInsets.only(left: 6, right: 6),
                                                decoration: const BoxDecoration(
                                                  color: AppColors.primary,
                                                  shape: BoxShape.circle,
                                                ),
                                              ),
                                            // Tombol Hapus per notifikasi
                                            InkWell(
                                              onTap: () => _deleteNotification(notifId),
                                              borderRadius: BorderRadius.circular(6),
                                              child: const Padding(
                                                padding: EdgeInsets.all(2),
                                                child: Icon(
                                                  LucideIcons.trash2,
                                                  size: 14,
                                                  color: AppColors.statusRejected,
                                                ),
                                              ),
                                            ),
                                          ],
                                        ),
                                        const SizedBox(height: 4),
                                        Text(
                                          n['body'] ?? '',
                                          style: const TextStyle(
                                            fontSize: 12,
                                            color: AppColors.textSecondary,
                                            height: 1.4,
                                          ),
                                        ),
                                        const SizedBox(height: 6),
                                        Row(
                                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                          children: [
                                            Text(
                                              _formatTime(n['created_at']),
                                              style: const TextStyle(
                                                fontSize: 10,
                                                color: AppColors.textSecondary,
                                              ),
                                            ),
                                            if (complaintId != null)
                                              const Text(
                                                'Lihat Detail →',
                                                style: TextStyle(
                                                  fontSize: 10.5,
                                                  fontWeight: FontWeight.bold,
                                                  color: AppColors.primary,
                                                ),
                                              ),
                                          ],
                                        ),
                                      ],
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ),
                        );
                      },
                    ),
            ),
    );
  }

  String _formatTime(String? iso) {
    if (iso == null) return '';
    try {
      final dt = DateTime.parse(iso).toLocal();
      final diff = DateTime.now().difference(dt);
      if (diff.inMinutes < 1) return 'Baru saja';
      if (diff.inMinutes < 60) return '${diff.inMinutes} menit yang lalu';
      if (diff.inHours < 24) return '${diff.inHours} jam yang lalu';
      return '${diff.inDays} hari yang lalu';
    } catch (_) {
      return '';
    }
  }

  IconData _typeIcon(String type) {
    switch (type) {
      case 'COMPLAINT_VERIFIED':
        return LucideIcons.shieldCheck;
      case 'COMPLAINT_DISPATCHED':
      case 'FIELD_TASK_ASSIGNED':
        return LucideIcons.truck;
      case 'COMPLAINT_IN_PROGRESS':
        return LucideIcons.wrench;
      case 'COMPLAINT_WAITING_VERIFICATION':
        return LucideIcons.clock;
      case 'COMPLAINT_COMPLETED':
        return LucideIcons.checkCircle;
      case 'COMPLAINT_REJECTED':
      case 'COMPLAINT_MERGED':
        return LucideIcons.xCircle;
      default:
        return LucideIcons.bell;
    }
  }

  Color _typeColor(String type) {
    switch (type) {
      case 'COMPLAINT_COMPLETED':
        return AppColors.statusCompleted;
      case 'COMPLAINT_REJECTED':
        return AppColors.statusRejected;
      case 'COMPLAINT_VERIFIED':
        return AppColors.primary;
      case 'COMPLAINT_DISPATCHED':
      case 'FIELD_TASK_ASSIGNED':
        return AppColors.statusDispatched;
      case 'COMPLAINT_IN_PROGRESS':
        return AppColors.accent;
      case 'COMPLAINT_WAITING_VERIFICATION':
        return AppColors.secondary;
      default:
        return AppColors.primaryLight;
    }
  }
}
