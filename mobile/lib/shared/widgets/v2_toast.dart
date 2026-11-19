import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:lucide_icons_flutter/lucide_icons.dart';
import '../theme/app_colors.dart';

enum V2ToastType { success, error, warning, info }

/// Notifikasi Toast SI-APPIK V2 yang muncul halus dari bagian ATAS layar
/// dengan kartu putih bersih, ikon berwarna, dan animasi slide + fade.
void showV2Toast(
  BuildContext context, {
  required String message,
  V2ToastType type = V2ToastType.info,
  Duration duration = const Duration(seconds: 3),
}) {
  final overlay = Overlay.maybeOf(context, rootOverlay: true);
  if (overlay == null) return;

  late OverlayEntry entry;
  bool dismissed = false;

  void removeEntry() {
    if (!dismissed) {
      dismissed = true;
      entry.remove();
    }
  }

  entry = OverlayEntry(
    builder: (ctx) => _V2TopToastOverlay(
      message: message,
      type: type,
      duration: duration,
      onDismissed: removeEntry,
    ),
  );

  overlay.insert(entry);
}

class _V2TopToastOverlay extends StatefulWidget {
  final String message;
  final V2ToastType type;
  final Duration duration;
  final VoidCallback onDismissed;

  const _V2TopToastOverlay({
    required this.message,
    required this.type,
    required this.duration,
    required this.onDismissed,
  });

  @override
  State<_V2TopToastOverlay> createState() => _V2TopToastOverlayState();
}

class _V2TopToastOverlayState extends State<_V2TopToastOverlay>
    with SingleTickerProviderStateMixin {
  late final AnimationController _controller;
  late final Animation<Offset> _slideAnimation;
  late final Animation<double> _fadeAnimation;

  @override
  void initState() {
    super.initState();
    _controller = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 280),
      reverseDuration: const Duration(milliseconds: 220),
    );
    _slideAnimation = Tween<Offset>(
      begin: const Offset(0, -1.0),
      end: Offset.zero,
    ).animate(CurvedAnimation(parent: _controller, curve: Curves.easeOutCubic));
    _fadeAnimation = CurvedAnimation(parent: _controller, curve: Curves.easeOut);

    _controller.forward();
    Future.delayed(widget.duration, _dismiss);
  }

  Future<void> _dismiss() async {
    if (!mounted) return;
    await _controller.reverse();
    if (mounted) widget.onDismissed();
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final (Color accent, Color bg, IconData icon) = switch (widget.type) {
      V2ToastType.success => (
          AppColors.statusCompleted,
          const Color(0xFFECFDF5),
          LucideIcons.checkCircle2,
        ),
      V2ToastType.error => (
          AppColors.statusRejected,
          const Color(0xFFFEF2F2),
          LucideIcons.alertCircle,
        ),
      V2ToastType.warning => (
          AppColors.statusInProgress,
          const Color(0xFFFFFBEB),
          LucideIcons.alertTriangle,
        ),
      V2ToastType.info => (
          AppColors.primary,
          AppColors.primaryContainer,
          LucideIcons.info,
        ),
    };

    return Positioned(
      top: MediaQuery.of(context).padding.top + 12,
      left: 16,
      right: 16,
      child: Material(
        color: Colors.transparent,
        child: SlideTransition(
          position: _slideAnimation,
          child: FadeTransition(
            opacity: _fadeAnimation,
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
              decoration: BoxDecoration(
                color: AppColors.surface,
                borderRadius: BorderRadius.circular(16),
                border: Border.all(color: AppColors.border),
                boxShadow: [
                  BoxShadow(
                    color: AppColors.textPrimary.withValues(alpha: 0.1),
                    blurRadius: 20,
                    offset: const Offset(0, 8),
                  ),
                ],
              ),
              child: Row(
                children: [
                  Container(
                    width: 34,
                    height: 34,
                    decoration: BoxDecoration(
                      color: bg,
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: Icon(icon, color: accent, size: 18),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Text(
                      widget.message,
                      style: GoogleFonts.inter(
                        fontSize: 12.5,
                        fontWeight: FontWeight.w600,
                        color: AppColors.textPrimary,
                        height: 1.35,
                      ),
                    ),
                  ),
                  const SizedBox(width: 8),
                  InkWell(
                    onTap: _dismiss,
                    borderRadius: BorderRadius.circular(8),
                    child: const Padding(
                      padding: EdgeInsets.all(4),
                      child: Icon(LucideIcons.x, size: 15, color: AppColors.textSecondary),
                    ),
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}
