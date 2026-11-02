import 'package:flutter/material.dart';

class AppColors {
  // Brand / Primary
  static const Color primary = Color(0xFF1E3A8A); // Deep Slate Blue
  static const Color primaryLight = Color(0xFF3B82F6);
  static const Color primaryDark = Color(0xFF172554);

  // Secondary / Accent
  static const Color secondary = Color(0xFF0D9488); // Teal
  static const Color accent = Color(0xFFF59E0B); // Amber / Warning

  // Status Colors (WCAG compliant contrast - Selaras dengan Web & Peta)
  static const Color statusSubmitted = Color(0xFFEF4444); // Merah - Belum Ditangani / Menunggu
  static const Color statusVerification = Color(0xFFEF4444);
  static const Color statusDispatched = Color(0xFF1E40AF); // Biru Indigo - Ditugaskan
  static const Color statusInProgress = Color(0xFFF59E0B); // Kuning - Sedang Dikerjakan
  static const Color statusWaitingVerification = Color(0xFFF59E0B); // Kuning - Menunggu Validasi Operator (Selaras Dikerjakan)
  static const Color statusCompleted = Color(0xFF10B981); // Hijau - Selesai
  static const Color statusVerified = Color(0xFF10B981); // Hijau - Warga Terdaftar / Terverifikasi
  static const Color statusRejected = Color(0xFFDC2626); // Merah Tua - Ditolak

  // Damage Type Colors (Konsisten di semua platform: Unified Blue Palette)
  static const Color damageTypeMinor = Color(0xFF1E40AF);
  static const Color damageTypeMinorBg = Color(0xFFEFF6FF);
  static const Color damageTypeMinorBorder = Color(0xFFBFDBFE);

  static const Color damageTypeMajor = Color(0xFF1E3A8A); // Deep Royal Blue (Konsisten dengan Gambar 2)
  static const Color damageTypeMajorBg = Color(0xFFEFF6FF);
  static const Color damageTypeMajorBorder = Color(0xFF93C5FD);

  // Neutral / Background
  static const Color background = Color(0xFFF8FAFC);
  static const Color surface = Colors.white;
  static const Color textPrimary = Color(0xFF0F172A);
  static const Color textSecondary = Color(0xFF64748B);
  static const Color border = Color(0xFFE2E8F0);

  // Extended Semantic Design Tokens (WCAG & Material 3 compliant)
  static const Color primaryContainer = Color(0xFFEFF6FF); // Blue 50
  static const Color primaryBorder = Color(0xFFBFDBFE); // Blue 200
  static const Color cardSurface = Color(0xFFF8FAFC); // Slate 50
  static const Color borderMuted = Color(0xFFE2E8F0); // Slate 200
}
