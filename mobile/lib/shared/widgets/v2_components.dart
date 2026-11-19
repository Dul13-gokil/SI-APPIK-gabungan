import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../theme/app_colors.dart';

/// AppBar SI-APPIK V2 yang bersih, modern, dengan border bawah halus #E2E8F0.
PreferredSizeWidget buildV2AppBar({
  required String title,
  String? subtitle,
  IconData? leadingIcon,
  Widget? leadingWidget,
  List<Widget>? actions,
  bool automaticallyImplyLeading = false,
}) {
  return AppBar(
    backgroundColor: AppColors.surface,
    surfaceTintColor: Colors.transparent,
    foregroundColor: AppColors.textPrimary,
    elevation: 0,
    scrolledUnderElevation: 0,
    centerTitle: false,
    automaticallyImplyLeading: automaticallyImplyLeading,
    titleSpacing: automaticallyImplyLeading ? 4 : 16,
    title: Row(
      children: [
        if (leadingWidget != null) ...[
          leadingWidget,
          const SizedBox(width: 10),
        ] else if (leadingIcon != null) ...[
          Container(
            width: 36,
            height: 36,
            decoration: BoxDecoration(
              color: AppColors.primaryContainer,
              borderRadius: BorderRadius.circular(11),
              border: Border.all(color: AppColors.primaryBorder),
            ),
            child: Icon(leadingIcon, size: 18, color: AppColors.primary),
          ),
          const SizedBox(width: 10),
        ],
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            mainAxisSize: MainAxisSize.min,
            children: [
              Text(
                title,
                style: GoogleFonts.outfit(
                  fontSize: 17,
                  fontWeight: FontWeight.bold,
                  color: AppColors.textPrimary,
                  letterSpacing: -0.2,
                ),
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
              ),
              if (subtitle != null && subtitle.isNotEmpty)
                Text(
                  subtitle,
                  style: GoogleFonts.inter(
                    fontSize: 11,
                    fontWeight: FontWeight.w500,
                    color: AppColors.textSecondary,
                  ),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
            ],
          ),
        ),
      ],
    ),
    actions: actions,
    bottom: PreferredSize(
      preferredSize: const Size.fromHeight(1),
      child: Container(height: 1, color: AppColors.border),
    ),
  );
}

/// Chip kode laporan seragam hitam (#0F172A) selaras dengan tampilan Web V2.
class V2TicketCodeChip extends StatelessWidget {
  final String code;

  const V2TicketCodeChip({super.key, required this.code});

  @override
  Widget build(BuildContext context) {
    final formatted = code.startsWith('#') ? code : '#$code';
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
      decoration: BoxDecoration(
        color: AppColors.cardSurface,
        borderRadius: BorderRadius.circular(8),
        border: Border.all(color: AppColors.border),
      ),
        child: Text(
        formatted,
        style: GoogleFonts.inter(
          fontSize: 11,
          fontWeight: FontWeight.w700,
          color: AppColors.textPrimary,
          letterSpacing: -0.1,
        ),
      ),
    );
  }
}

/// Header Floating Pill SI-APPIK V2 (sesuai desain FigCraft Gambar 4).
class V2FloatingHeader extends StatelessWidget {
  final String title;
  final String? subtitle;
  final Widget? leading;
  final List<Widget>? actions;
  final VoidCallback? onBack;

  const V2FloatingHeader({
    super.key,
    required this.title,
    this.subtitle,
    this.leading,
    this.actions,
    this.onBack,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: EdgeInsets.zero,
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      decoration: const BoxDecoration(
        color: AppColors.surface,
        border: Border(bottom: BorderSide(color: AppColors.border)),
      ),
      child: Row(
        children: [
          if (onBack != null) ...[
            InkWell(
              onTap: onBack,
              borderRadius: BorderRadius.circular(11),
              child: Container(
                width: 36,
                height: 36,
                decoration: BoxDecoration(
                  color: AppColors.cardSurface,
                  borderRadius: BorderRadius.circular(11),
                  border: Border.all(color: AppColors.border),
                ),
                child: const Icon(Icons.arrow_back_rounded, size: 18, color: AppColors.textPrimary),
              ),
            ),
            const SizedBox(width: 10),
          ] else if (leading != null) ...[
            leading!,
            const SizedBox(width: 10),
          ],
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisSize: MainAxisSize.min,
              children: [
                Text(
                  title,
                  style: GoogleFonts.outfit(
                    fontSize: 15.5,
                    fontWeight: FontWeight.bold,
                    color: AppColors.textPrimary,
                    letterSpacing: -0.2,
                  ),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
                if (subtitle != null && subtitle!.isNotEmpty)
                  Text(
                    subtitle!,
                    style: GoogleFonts.inter(
                      fontSize: 11,
                      fontWeight: FontWeight.w500,
                      color: AppColors.textSecondary,
                    ),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),
              ],
            ),
          ),
          if (actions != null) ...actions!,
        ],
      ),
    );
  }
}
