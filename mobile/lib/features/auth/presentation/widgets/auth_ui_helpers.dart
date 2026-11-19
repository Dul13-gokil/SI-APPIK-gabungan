import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../../../../shared/theme/app_colors.dart';

/// InputDecoration seragam SI-APPIK V2 untuk layar Login, Daftar Akun, dan Lupa Sandi.
InputDecoration buildAuthInputDecoration({
  required String hintText,
  required IconData prefixIcon,
  Widget? suffixIcon,
  bool isDense = false,
}) {
  return InputDecoration(
    hintText: hintText,
    hintStyle: GoogleFonts.inter(
      fontSize: 13.5,
      color: AppColors.textSecondary.withValues(alpha: 0.7),
    ),
    prefixIcon: Icon(prefixIcon, size: 18, color: AppColors.textSecondary),
    suffixIcon: suffixIcon,
    filled: true,
    fillColor: AppColors.cardSurface,
    isDense: isDense,
    contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 14),
    border: OutlineInputBorder(
      borderRadius: BorderRadius.circular(14),
      borderSide: const BorderSide(color: AppColors.border),
    ),
    enabledBorder: OutlineInputBorder(
      borderRadius: BorderRadius.circular(14),
      borderSide: const BorderSide(color: AppColors.border),
    ),
    focusedBorder: OutlineInputBorder(
      borderRadius: BorderRadius.circular(14),
      borderSide: const BorderSide(color: AppColors.primary, width: 1.5),
    ),
    errorBorder: OutlineInputBorder(
      borderRadius: BorderRadius.circular(14),
      borderSide: const BorderSide(color: AppColors.statusRejected),
    ),
    focusedErrorBorder: OutlineInputBorder(
      borderRadius: BorderRadius.circular(14),
      borderSide: const BorderSide(color: AppColors.statusRejected, width: 1.5),
    ),
  );
}

/// Banner status (error / sukses) seragam SI-APPIK V2 untuk form Auth.
class AuthStatusBanner extends StatelessWidget {
  final String message;
  final IconData icon;
  final Color color;

  const AuthStatusBanner({
    super.key,
    required this.message,
    required this.icon,
    required this.color,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: 16),
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.08),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: color.withValues(alpha: 0.28)),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(icon, color: color, size: 18),
          const SizedBox(width: 10),
          Expanded(
            child: Text(
              message,
              style: GoogleFonts.inter(
                color: color,
                fontSize: 12.5,
                fontWeight: FontWeight.w500,
                height: 1.4,
              ),
            ),
          ),
        ],
      ),
    );
  }
}
