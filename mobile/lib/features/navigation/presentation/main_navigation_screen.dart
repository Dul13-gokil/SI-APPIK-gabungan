import 'dart:async';
import 'package:flutter/material.dart';
import 'package:lucide_icons_flutter/lucide_icons.dart';
import '../../../core/auth/auth_service.dart';
import '../../../shared/theme/app_colors.dart';
import '../../auth/presentation/login_screen.dart';
import '../../chatbot/presentation/ai_assistant_screen.dart';
import '../../complaints/presentation/create_complaint_screen.dart';
import '../../field/presentation/field_completed_tasks_screen.dart';
import '../../field/presentation/field_tasks_screen.dart';
import '../../home/presentation/home_screen.dart';
import '../../map/presentation/map_screen.dart';
import '../../profile/presentation/profile_screen.dart';
import '../../../core/utils/phone_validator.dart';

class MainNavigationScreen extends StatefulWidget {
  final int initialIndex;
  final bool showGoogleProfileNotice;

  const MainNavigationScreen({
    super.key,
    this.initialIndex = 0,
    this.showGoogleProfileNotice = false,
  });

  @override
  State<MainNavigationScreen> createState() => _MainNavigationScreenState();
}

class _MainNavigationScreenState extends State<MainNavigationScreen> {
  late int _currentIndex;
  final _authService = AuthService();
  String _userRole = 'WARGA';
  int _refreshKey = 0;
  bool _hasCheckedIncompleteProfile = false;

  @override
  void initState() {
    super.initState();
    _currentIndex = widget.initialIndex;
    _loadUserRole();
    if (widget.showGoogleProfileNotice) {
      _hasCheckedIncompleteProfile = true;
      WidgetsBinding.instance.addPostFrameCallback((_) {
        _showGoogleOnboardingDialog();
      });
    }
  }

  Future<void> _showGoogleOnboardingDialog() async {
    final user = await _authService.getCurrentUser();
    if (!mounted || user == null) return;

    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (ctx) => PopScope(
        canPop: false,
        child: AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
          titlePadding: const EdgeInsets.fromLTRB(20, 20, 20, 10),
          contentPadding: const EdgeInsets.symmetric(horizontal: 20, vertical: 10),
          actionsPadding: const EdgeInsets.fromLTRB(20, 10, 20, 16),
          title: Row(
            children: [
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: AppColors.primary.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: const Icon(LucideIcons.userCheck, color: AppColors.primary, size: 20),
              ),
              const SizedBox(width: 10),
              const Expanded(
                child: Text(
                  'Lengkapi Identitas Diri',
                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
                ),
              ),
            ],
          ),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text(
                'Silakan lengkapi identitas akun Anda sebelum melanjutkan ke layanan SI-APPIK:',
                style: TextStyle(fontSize: 13, color: AppColors.textSecondary, height: 1.4),
              ),
              const SizedBox(height: 14),
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: const Color(0xFFF8FAFC),
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: AppColors.border),
                ),
                child: const Column(
                  children: [
                    Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Icon(LucideIcons.idCard, size: 16, color: AppColors.primary),
                        SizedBox(width: 10),
                        Expanded(
                          child: Text(
                            'Nama lengkap sesuai KTP resmi',
                            style: TextStyle(fontSize: 12.5, fontWeight: FontWeight.w600, color: AppColors.textPrimary),
                          ),
                        ),
                      ],
                    ),
                    SizedBox(height: 10),
                    Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Icon(LucideIcons.phone, size: 16, color: AppColors.primary),
                        SizedBox(width: 10),
                        Expanded(
                          child: Text(
                            'Nomor Handphone / WhatsApp aktif (10-13 digit)',
                            style: TextStyle(fontSize: 12.5, fontWeight: FontWeight.w600, color: AppColors.textPrimary),
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
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
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                ),
                onPressed: () {
                  Navigator.pop(ctx);
                  _showQuickProfileUpdateSheet(user);
                },
                child: const Text('Lengkapi Sekarang', style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold)),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Future<void> _showQuickProfileUpdateSheet(UserModel user) async {
    final nameCtrl = TextEditingController(text: user.name);
    final phoneCtrl = TextEditingController(text: user.phone ?? '');
    bool isSaving = false;
    String? phoneFeedback;
    bool? isPhoneValid;

    if (phoneCtrl.text.isNotEmpty) {
      final initialCheck = PhoneValidator.validate(phoneCtrl.text, isRequired: true);
      phoneFeedback = initialCheck.message;
      isPhoneValid = initialCheck.isValid;
    } else {
      phoneFeedback = 'Nomor HP Indonesia 10 - 13 digit (contoh: 081234567890)';
      isPhoneValid = null;
    }

    await showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      isDismissible: false,
      enableDrag: false,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (ctx) => PopScope(
        canPop: false,
        child: StatefulBuilder(
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
                        child: const Icon(LucideIcons.userCheck, color: AppColors.primary, size: 22),
                      ),
                      const SizedBox(width: 12),
                      const Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              'Lengkapi Identitas Diri',
                              style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
                            ),
                            Text(
                              'Nama sesuai KTP & no. HP aktif (10-13 digit)',
                              style: TextStyle(fontSize: 12, color: AppColors.textSecondary),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                const Divider(height: 24),
                const Text('Nama Lengkap (Sesuai KTP) *', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600)),
                const SizedBox(height: 6),
                TextField(
                  controller: nameCtrl,
                  decoration: InputDecoration(
                    hintText: 'Nama lengkap sesuai KTP',
                    prefixIcon: const Icon(LucideIcons.idCard, size: 18),
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                    contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                  ),
                ),
                const SizedBox(height: 14),
                const Text('Nomor Handphone / WhatsApp (10 - 13 digit) *', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600)),
                const SizedBox(height: 6),
                TextField(
                  controller: phoneCtrl,
                  keyboardType: TextInputType.phone,
                  onChanged: (val) {
                    if (val.trim().isEmpty) {
                      setSheetState(() {
                        phoneFeedback = 'Nomor HP Indonesia 10 - 13 digit (contoh: 081234567890)';
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
                if (phoneFeedback != null)
                  Padding(
                    padding: const EdgeInsets.only(top: 6, left: 4),
                    child: Text(
                      phoneFeedback!,
                      style: TextStyle(
                        fontSize: 11.5,
                        fontWeight: FontWeight.w500,
                        color: isPhoneValid == true
                            ? const Color(0xFF10B981)
                            : (isPhoneValid == false ? const Color(0xFFEF4444) : AppColors.textSecondary),
                      ),
                    ),
                  ),
                const SizedBox(height: 20),
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton(
                    onPressed: isSaving
                        ? null
                        : () async {
                            final name = nameCtrl.text.trim();
                            final phone = phoneCtrl.text.trim();
                            if (name.isEmpty) {
                              ScaffoldMessenger.of(context).showSnackBar(
                                const SnackBar(content: Text('Nama lengkap sesuai KTP wajib diisi')),
                              );
                              return;
                            }
                            final validation = PhoneValidator.validate(phone, isRequired: true);
                            if (!validation.isValid) {
                              ScaffoldMessenger.of(context).showSnackBar(
                                SnackBar(content: Text(validation.message ?? 'Nomor handphone tidak valid')),
                              );
                              return;
                            }

                            final messenger = ScaffoldMessenger.of(context);
                            final nav = Navigator.of(ctx);
                            setSheetState(() => isSaving = true);

                            // Cek ketersediaan nomor di sistem
                            final checkRes = await _authService.checkPhone(validation.normalized, userId: user.id);
                            if (checkRes['registered'] == true) {
                              setSheetState(() {
                                isSaving = false;
                                phoneFeedback = checkRes['message'] ?? 'Nomor ini sudah terdaftar oleh pengguna lain.';
                                isPhoneValid = false;
                              });
                              messenger.showSnackBar(
                                SnackBar(
                                  content: Text(checkRes['message'] ?? 'Nomor handphone sudah terdaftar.'),
                                  backgroundColor: const Color(0xFFEF4444),
                                ),
                              );
                              return;
                            }

                            final res = await _authService.updateProfile(
                              name: name,
                              email: user.email,
                              phone: validation.normalized,
                            );
                            if (!mounted) return;

                            if (res['success'] == true) {
                              nav.pop();
                              if (mounted) {
                                setState(() {
                                  _refreshKey++;
                                });
                              }
                              messenger.showSnackBar(
                                const SnackBar(
                                  content: Text('Identitas profil berhasil disimpan!'),
                                  backgroundColor: Color(0xFF10B981),
                                ),
                              );
                            } else {
                              setSheetState(() => isSaving = false);
                              messenger.showSnackBar(
                                SnackBar(content: Text(res['message'] ?? 'Gagal menyimpan profil')),
                              );
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
                        : const Text('Simpan & Lanjutkan', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
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

  Future<void> _showPetugasFirstLoginDialog(UserModel user) async {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (ctx) => PopScope(
        canPop: false,
        child: AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
          titlePadding: const EdgeInsets.fromLTRB(20, 20, 20, 10),
          contentPadding: const EdgeInsets.symmetric(horizontal: 20, vertical: 10),
          actionsPadding: const EdgeInsets.fromLTRB(20, 10, 20, 16),
          title: Row(
            children: [
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: AppColors.primary.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: const Icon(LucideIcons.hardHat, color: AppColors.primary, size: 20),
              ),
              const SizedBox(width: 10),
              const Expanded(
                child: Text(
                  'Aktivasi Akun Petugas',
                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
                ),
              ),
            ],
          ),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text(
                'Akun Anda terdaftar sebagai Petugas Lapangan. Silakan verifikasi identitas resmi dan perbarui kata sandi awal akun Anda sebelum menjalankan tugas:',
                style: TextStyle(fontSize: 13, color: AppColors.textSecondary, height: 1.4),
              ),
              const SizedBox(height: 14),
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: const Color(0xFFF8FAFC),
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: AppColors.border),
                ),
                child: const Column(
                  children: [
                    Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Icon(LucideIcons.user, size: 16, color: AppColors.primary),
                        SizedBox(width: 10),
                        Expanded(
                          child: Text(
                            'Nama lengkap sesuai penugasan dinas',
                            style: TextStyle(fontSize: 12.5, fontWeight: FontWeight.w600, color: AppColors.textPrimary),
                          ),
                        ),
                      ],
                    ),
                    SizedBox(height: 10),
                    Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Icon(LucideIcons.phone, size: 16, color: AppColors.primary),
                        SizedBox(width: 10),
                        Expanded(
                          child: Text(
                            'Nomor WhatsApp aktif (10-13 digit)',
                            style: TextStyle(fontSize: 12.5, fontWeight: FontWeight.w600, color: AppColors.textPrimary),
                          ),
                        ),
                      ],
                    ),
                    SizedBox(height: 10),
                    Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Icon(LucideIcons.lock, size: 16, color: AppColors.primary),
                        SizedBox(width: 10),
                        Expanded(
                          child: Text(
                            'Kata sandi baru (min. 8 karakter, A-Z, a-z, 0-9)',
                            style: TextStyle(fontSize: 12.5, fontWeight: FontWeight.w600, color: AppColors.textPrimary),
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
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
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                ),
                onPressed: () {
                  Navigator.pop(ctx);
                  _showPetugasQuickSetupSheet(user);
                },
                child: const Text('Aktivasi Sekarang', style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold)),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Future<void> _showPetugasQuickSetupSheet(UserModel user) async {
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
      isDismissible: false,
      enableDrag: false,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (ctx) => PopScope(
        canPop: false,
        child: StatefulBuilder(
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
                        child: const Icon(LucideIcons.hardHat, color: AppColors.primary, size: 22),
                      ),
                      const SizedBox(width: 12),
                      const Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              'Aktivasi Akun Petugas Lapangan',
                              style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
                            ),
                            Text(
                              'Cek nama, nomor WhatsApp & buat kata sandi baru',
                              style: TextStyle(fontSize: 12, color: AppColors.textSecondary),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                  const Divider(height: 24),
                  const Text('1. Nama Lengkap Petugas *', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600)),
                  const SizedBox(height: 6),
                  TextField(
                    controller: nameCtrl,
                    decoration: InputDecoration(
                      hintText: 'Nama lengkap petugas lapangan',
                      prefixIcon: const Icon(LucideIcons.user, size: 18),
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                      contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                    ),
                  ),
                  const SizedBox(height: 14),
                  const Text('2. Nomor WhatsApp / HP Aktif *', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600)),
                  const SizedBox(height: 6),
                  TextField(
                    controller: phoneCtrl,
                    keyboardType: TextInputType.phone,
                    onChanged: (val) {
                      if (val.trim().isEmpty) {
                        setSheetState(() {
                          phoneFeedback = 'Nomor HP Indonesia 10 - 13 digit (contoh: 081234567890)';
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
                  if (phoneFeedback != null)
                    Padding(
                      padding: const EdgeInsets.only(top: 6, left: 4),
                      child: Text(
                        phoneFeedback!,
                        style: TextStyle(
                          fontSize: 11.5,
                          fontWeight: FontWeight.w500,
                          color: isPhoneValid == true
                              ? const Color(0xFF10B981)
                              : (isPhoneValid == false ? const Color(0xFFEF4444) : AppColors.textSecondary),
                        ),
                      ),
                    ),
                  const SizedBox(height: 14),
                  const Text('3. Kata Sandi Baru (Min. 8 Karakter, Huruf Besar, Kecil, Angka) *', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600)),
                  const SizedBox(height: 6),
                  TextField(
                    controller: newPasswordCtrl,
                    obscureText: obscurePassword,
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
                  const SizedBox(height: 10),
                  TextField(
                    controller: confirmPasswordCtrl,
                    obscureText: obscurePassword,
                    decoration: InputDecoration(
                      hintText: 'Konfirmasi kata sandi baru...',
                      prefixIcon: const Icon(LucideIcons.checkCheck, size: 18),
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                      contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                    ),
                  ),
                  if (passwordError != null)
                    Padding(
                      padding: const EdgeInsets.only(top: 6, left: 4),
                      child: Text(
                        passwordError!,
                        style: const TextStyle(fontSize: 11.5, fontWeight: FontWeight.w500, color: Color(0xFFEF4444)),
                      ),
                    ),
                  const SizedBox(height: 20),
                  SizedBox(
                    width: double.infinity,
                    child: ElevatedButton(
                      onPressed: isSaving
                          ? null
                          : () async {
                              final name = nameCtrl.text.trim();
                              final phone = phoneCtrl.text.trim();
                              final newPwd = newPasswordCtrl.text;
                              final confirmPwd = confirmPasswordCtrl.text;

                              if (name.isEmpty) {
                                setSheetState(() => passwordError = 'Nama lengkap petugas wajib diisi');
                                return;
                              }
                              final validation = PhoneValidator.validate(phone, isRequired: true);
                              if (!validation.isValid) {
                                setSheetState(() {
                                  phoneFeedback = validation.message;
                                  isPhoneValid = false;
                                  passwordError = validation.message ?? 'Format nomor handphone tidak valid';
                                });
                                return;
                              }

                              if (newPwd.length < 8) {
                                setSheetState(() => passwordError = 'Kata sandi baru minimal 8 karakter.');
                                return;
                              }

                              if (!RegExp(r'[A-Z]').hasMatch(newPwd)) {
                                setSheetState(() => passwordError = 'Kata sandi baru wajib memiliki setidaknya 1 huruf besar (A-Z).');
                                return;
                              }

                              if (!RegExp(r'[0-9]').hasMatch(newPwd)) {
                                setSheetState(() => passwordError = 'Kata sandi baru wajib memiliki setidaknya 1 angka (0-9).');
                                return;
                              }

                              if (!RegExp(r'[a-z]').hasMatch(newPwd)) {
                                setSheetState(() => passwordError = 'Kata sandi baru wajib kombinasi huruf besar, kecil, dan angka.');
                                return;
                              }

                              if (newPwd.toLowerCase() == 'password') {
                                setSheetState(() => passwordError = 'Kata sandi tidak boleh sama dengan default "password".');
                                return;
                              }

                              if (newPwd != confirmPwd) {
                                setSheetState(() => passwordError = 'Konfirmasi kata sandi baru tidak cocok.');
                                return;
                              }

                              setSheetState(() => passwordError = null);

                              final messenger = ScaffoldMessenger.of(context);
                              final nav = Navigator.of(ctx);
                              setSheetState(() => isSaving = true);

                              // Cek ketersediaan nomor di sistem
                              final checkRes = await _authService.checkPhone(validation.normalized, userId: user.id);
                              if (checkRes['registered'] == true) {
                                setSheetState(() {
                                  isSaving = false;
                                  phoneFeedback = checkRes['message'] ?? 'Nomor ini sudah terdaftar.';
                                  isPhoneValid = false;
                                  passwordError = 'Nomor handphone sudah digunakan oleh akun lain.';
                                });
                                return;
                              }

                              final res = await _authService.updateProfile(
                                name: name,
                                email: user.email,
                                phone: validation.normalized,
                                password: newPwd,
                                currentPassword: 'password',
                              );
                              if (!mounted) return;

                              if (res['success'] == true) {
                                nav.pop();
                                if (mounted) {
                                  setState(() {
                                    _refreshKey++;
                                  });
                                }
                                messenger.showSnackBar(
                                  const SnackBar(
                                    content: Text('Akun Petugas Lapangan berhasil diaktifkan dengan sandi baru!'),
                                    backgroundColor: Color(0xFF10B981),
                                  ),
                                );
                              } else {
                                setSheetState(() {
                                  isSaving = false;
                                  passwordError = res['message'] ?? 'Gagal menyimpan profil & sandi baru.';
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
                          : const Text('Aktifkan Akun & Mulai Bertugas', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
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

  Future<void> _loadUserRole() async {
    final user = await _authService.getCurrentUser();
    if (mounted) {
      final role = user?.role ?? 'WARGA';
      setState(() => _userRole = role);

      // Verifikasi status aktif akun ke server secara langsung agar akun yang dinonaktifkan
      // (baik Warga maupun Petugas Lapangan) tidak dapat tetap menggunakan sesi lama
      if (user != null && role != 'TAMU') {
        final session = await _authService.verifyActiveSession();
        if (!mounted) return;
        if (session['active'] == false) {
          final msg = session['message']?.toString() ??
              'Akun Anda telah dinonaktifkan oleh Super Admin. Silakan hubungi administrator.';
          Navigator.of(context).pushAndRemoveUntil(
            MaterialPageRoute(builder: (_) => LoginScreen(initialErrorMessage: msg)),
            (route) => false,
          );
          return;
        }
      }

      // Tangani situasi jika akun keluar aplikasi sebelum melengkapi no HP:
      // Munculkan kembali dialog pelengkapan identitas sampai tuntas diisi
      if (!_hasCheckedIncompleteProfile && user != null) {
        _hasCheckedIncompleteProfile = true;
        if (role == 'WARGA' || role == 'TAMU') {
          final hasEmptyPhone = user.phone == null || user.phone!.trim().isEmpty;
          if (hasEmptyPhone && !widget.showGoogleProfileNotice) {
            WidgetsBinding.instance.addPostFrameCallback((_) {
              if (mounted) _showGoogleOnboardingDialog();
            });
          }
        } else if (role == 'PETUGAS') {
          final needsSetup = user.needsFirstLoginSetup || (user.phone == null || user.phone!.trim().isEmpty);
          if (needsSetup) {
            WidgetsBinding.instance.addPostFrameCallback((_) {
              if (mounted) _showPetugasFirstLoginDialog(user);
            });
          }
        }
      }
    }
  }

  bool get _isPetugas => _userRole == 'PETUGAS';
  bool get _isWarga => _userRole == 'WARGA' || _userRole == 'TAMU';

  void _onTabTapped(int index) {
    if (_isPetugas) {
      if (index == 4) {
        setState(() {
          _currentIndex = index;
          _refreshKey++;
        });
        return;
      }
      setState(() => _currentIndex = index);
      return;
    }

    // WARGA/ADMIN/BUPATI: 5 tab, tab tengah = buka form laporan
    if (index == 2) {
      _handleCreateComplaint();
      return;
    }
    if (index == 4) {
      setState(() {
        _currentIndex = index;
        _refreshKey++;
      });
      return;
    }
    setState(() => _currentIndex = index);
  }

  Future<void> _handleCreateComplaint() async {
    final u = await _authService.getCurrentUser();
    if (!mounted) return;
    if (u?.role == 'TAMU') {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Silakan daftar akun terlebih dahulu untuk membuat laporan'),
          backgroundColor: AppColors.accent,
        ),
      );
      return;
    }

    // Tampilkan 2 Opsi Pembuatan Laporan: Pakai AI vs Manual
    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (ctx) => SafeArea(
        child: Padding(
          padding: const EdgeInsets.fromLTRB(20, 16, 20, 24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Center(
                child: Container(
                  width: 40,
                  height: 4,
                  decoration: BoxDecoration(
                    color: Colors.grey.shade300,
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
              ),
              const SizedBox(height: 16),
              const Text(
                'Pilih Metode Pelaporan',
                style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
              ),
              const SizedBox(height: 4),
              const Text(
                'Pilih pelaporan dengan analisis cerdas AI atau pengisian mandiri.',
                style: TextStyle(fontSize: 12, color: AppColors.textSecondary),
              ),
              const SizedBox(height: 16),

              // Opsi 1: Pakai AI Cerdas
              InkWell(
                onTap: () {
                  Navigator.pop(ctx);
                  Navigator.of(context).push(
                    MaterialPageRoute(
                      builder: (_) => const CreateComplaintScreen(initialUseAi: true),
                    ),
                  ).then((_) {
                    if (mounted) setState(() {});
                  });
                },
                borderRadius: BorderRadius.circular(16),
                child: Container(
                  padding: const EdgeInsets.all(14),
                  decoration: BoxDecoration(
                    gradient: const LinearGradient(
                      colors: [Color(0xFF1E3A8A), Color(0xFF2563EB)],
                      begin: Alignment.topLeft,
                      end: Alignment.bottomRight,
                    ),
                    borderRadius: BorderRadius.circular(16),
                    boxShadow: [
                      BoxShadow(
                        color: const Color(0xFF2563EB).withValues(alpha: 0.25),
                        blurRadius: 10,
                        offset: const Offset(0, 4),
                      ),
                    ],
                  ),
                  child: Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(10),
                        decoration: BoxDecoration(
                          color: Colors.white.withValues(alpha: 0.18),
                          shape: BoxShape.circle,
                        ),
                        child: const Icon(LucideIcons.sparkles, color: Colors.white, size: 22),
                      ),
                      const SizedBox(width: 14),
                      const Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              children: [
                                Text(
                                  'Gunakan Analisis AI Cerdas',
                                  style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 13.5),
                                ),
                                SizedBox(width: 6),
                              ],
                            ),
                            SizedBox(height: 3),
                            Text(
                              'Deteksi otomatis jenis kerusakan jalan, jembatan, lampu, & narasi terisi instan.',
                              style: TextStyle(color: Colors.white70, fontSize: 11),
                            ),
                          ],
                        ),
                      ),
                      const Icon(LucideIcons.chevronRight, color: Colors.white70, size: 18),
                    ],
                  ),
                ),
              ),

              const SizedBox(height: 12),

              // Opsi 2: Lapor Manual (Tanpa AI)
              InkWell(
                onTap: () {
                  Navigator.pop(ctx);
                  Navigator.of(context).push(
                    MaterialPageRoute(
                      builder: (_) => const CreateComplaintScreen(initialUseAi: false),
                    ),
                  ).then((_) {
                    if (mounted) setState(() {});
                  });
                },
                borderRadius: BorderRadius.circular(16),
                child: Container(
                  padding: const EdgeInsets.all(14),
                  decoration: BoxDecoration(
                    color: const Color(0xFFF8FAFC),
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(color: const Color(0xFFE2E8F0)),
                  ),
                  child: Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(10),
                        decoration: const BoxDecoration(
                          color: Color(0xFFE2E8F0),
                          shape: BoxShape.circle,
                        ),
                        child: const Icon(LucideIcons.fileEdit, color: Color(0xFF475569), size: 22),
                      ),
                      const SizedBox(width: 14),
                      const Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              'Lapor Manual (Tanpa AI)',
                              style: TextStyle(color: Color(0xFF1E293B), fontWeight: FontWeight.bold, fontSize: 13.5),
                            ),
                            SizedBox(height: 3),
                            Text(
                              'Isi judul, deskripsi, dan lampirkan foto kerusakan secara manual.',
                              style: TextStyle(color: Color(0xFF64748B), fontSize: 11),
                            ),
                          ],
                        ),
                      ),
                      const Icon(LucideIcons.chevronRight, color: Color(0xFF94A3B8), size: 18),
                    ],
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    if (_isPetugas) {
      return _buildPetugasLayout();
    }
    return _buildWargaLayout();
  }

  // ─── Layout PETUGAS (5 tab) ──────────────────────────────────────────────

  Widget _buildPetugasLayout() {
    final screens = [
      const FieldTasksScreen(),
      const MapScreen(isPetugas: true),
      const FieldCompletedTasksScreen(),
      const AIAssistantScreen(),
      ProfileScreen(key: ValueKey('profile_petugas_$_refreshKey')),
    ];

    final navItems = [
      {'icon': LucideIcons.clipboardList, 'label': 'Tugas Aktif'},
      {'icon': LucideIcons.map, 'label': 'Peta GIS'},
      {'icon': LucideIcons.checkCircle2, 'label': 'Riwayat Selesai'},
      {'icon': LucideIcons.bot, 'label': 'Asisten SI-APPIK'},
      {'icon': LucideIcons.user, 'label': 'Profil'},
    ];

    return Scaffold(
      body: IndexedStack(index: _currentIndex, children: screens),
      bottomNavigationBar: Container(
        decoration: BoxDecoration(
          color: Colors.white,
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.06),
              blurRadius: 16,
              offset: const Offset(0, -4),
            ),
          ],
        ),
        child: SafeArea(
          top: false,
          child: SizedBox(
            height: 60,
            child: Row(
              children: List.generate(navItems.length, (index) {
                final isSelected = _currentIndex == index;
                final item = navItems[index];
                final color = isSelected ? AppColors.primary : AppColors.textSecondary;

                return Expanded(
                  child: InkWell(
                    onTap: () => _onTabTapped(index),
                    splashColor: Colors.transparent,
                    highlightColor: Colors.transparent,
                    child: Padding(
                      padding: const EdgeInsets.symmetric(vertical: 6, horizontal: 2),
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Icon(
                            item['icon'] as IconData,
                            size: 22,
                            color: color,
                          ),
                          const SizedBox(height: 3),
                          Padding(
                            padding: const EdgeInsets.symmetric(horizontal: 2),
                            child: FittedBox(
                              fit: BoxFit.scaleDown,
                              child: Text(
                                item['label'] as String,
                                maxLines: 1,
                                style: TextStyle(
                                  fontSize: 11,
                                  fontWeight: isSelected ? FontWeight.bold : FontWeight.w500,
                                  color: color,
                                  letterSpacing: -0.2,
                                ),
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                );
              }),
            ),
          ),
        ),
      ),
    );
  }

  // ─── Layout WARGA / ADMIN / BUPATI (5 tab) ──────────────────────────────

  Widget _buildWargaLayout() {
    final screens = [
      HomeScreen(
        key: ValueKey('home_$_refreshKey'),
        onNavigateToMap: () => setState(() => _currentIndex = 1),
        onNavigateToAi: () => setState(() => _currentIndex = 3),
      ),
      const MapScreen(),
      const SizedBox.shrink(), // Placeholder tombol lapor (tengah)
      const AIAssistantScreen(),
      ProfileScreen(key: ValueKey('profile_$_refreshKey')),
    ];

    return Scaffold(
      body: IndexedStack(index: _currentIndex, children: screens),
      bottomNavigationBar: Container(
        decoration: BoxDecoration(
          color: Colors.white,
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.06),
              blurRadius: 16,
              offset: const Offset(0, -4),
            ),
          ],
        ),
        child: BottomNavigationBar(
          currentIndex: _currentIndex,
          onTap: _onTabTapped,
          type: BottomNavigationBarType.fixed,
          backgroundColor: Colors.white,
          selectedItemColor: AppColors.primary,
          unselectedItemColor: AppColors.textSecondary,
          selectedFontSize: 11,
          unselectedFontSize: 11,
          elevation: 0,
          items: [
            const BottomNavigationBarItem(
              icon: Icon(LucideIcons.home, size: 22),
              label: 'Beranda',
            ),
            const BottomNavigationBarItem(
              icon: Icon(LucideIcons.map, size: 22),
              label: 'Peta GIS',
            ),
            BottomNavigationBarItem(
              icon: Container(
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                  color: _isWarga && _userRole == 'TAMU'
                      ? AppColors.textSecondary
                      : AppColors.primary,
                  shape: BoxShape.circle,
                ),
                child: const Icon(LucideIcons.plus, color: Colors.white, size: 20),
              ),
              label: 'Lapor',
            ),
            const BottomNavigationBarItem(
              icon: Icon(LucideIcons.bot, size: 22),
              label: 'Asisten SI-APPIK',
            ),
            const BottomNavigationBarItem(
              icon: Icon(LucideIcons.user, size: 22),
              label: 'Profil',
            ),
          ],
        ),
      ),
    );
  }
}
