import 'dart:io';
import 'package:flutter/foundation.dart' show kIsWeb, Uint8List;
import 'package:flutter/material.dart';
import 'package:geolocator/geolocator.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:lucide_icons_flutter/lucide_icons.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../../../core/auth/auth_service.dart';
import '../../../core/config/app_config.dart';
import '../../../core/media/media_service.dart';
import '../../../core/network/api_client.dart';
import '../../../core/utils/phone_validator.dart';
import '../../../shared/theme/app_colors.dart';
import '../../../shared/widgets/formatted_markdown_text.dart';
import '../../../shared/widgets/v2_components.dart';
import '../../../shared/widgets/v2_toast.dart';
import '../../auth/presentation/login_screen.dart';

class ProfileScreen extends StatefulWidget {
  const ProfileScreen({super.key});

  @override
  State<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen> {
  final AuthService _authService = AuthService();
  final MediaService _mediaService = MediaService();
  UserModel? _user;
  Map<String, dynamic>? _myTeam;
  String _currentBaseUrl = AppConfig.defaultBaseUrl;

  @override
  void initState() {
    super.initState();
    _loadProfile();
  }

  Future<void> _loadProfile() async {
    final user = await _authService.getCurrentUser();
    final url = await AppConfig.getBaseUrl();
    setState(() {
      _user = user;
      _currentBaseUrl = url;
    });

    if (user?.role == 'PETUGAS') {
      try {
        final res = await ApiClient().dio.get('/field-tasks', queryParameters: {'per_page': 1});
        if (res.statusCode == 200 && res.data['data'] != null) {
          final summary = res.data['data']['summary'];
          if (mounted && summary != null && summary['my_team'] != null) {
            setState(() {
              _myTeam = summary['my_team'];
            });
          }
        }
      } catch (_) {}
    }
  }

  // 1. Photo Picker (Camera / Gallery)
  Future<void> _showPhotoPickerSheet() async {
    final isGuest = _user?.role == 'TAMU' || _user?.isAnonymous == true;
    if (isGuest) {
      showV2Toast(
        context,
        message: 'Silakan login dengan akun terdaftar untuk mengubah foto profil.',
        type: V2ToastType.warning,
      );
      return;
    }

    await showModalBottomSheet(
      context: context,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (ctx) => SafeArea(
        child: Padding(
          padding: const EdgeInsets.symmetric(vertical: 20, horizontal: 16),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Padding(
                padding: EdgeInsets.symmetric(horizontal: 8),
                child: Text(
                  'Foto Profil Pengguna',
                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
                ),
              ),
              const SizedBox(height: 12),
              ListTile(
                leading: Container(
                  padding: const EdgeInsets.all(8),
                  decoration: const BoxDecoration(color: Color(0xFFEFF6FF), shape: BoxShape.circle),
                  child: const Icon(LucideIcons.camera, color: AppColors.primary, size: 20),
                ),
                title: const Text('Ambil Foto dari Kamera', style: TextStyle(fontWeight: FontWeight.w600, fontSize: 13)),
                onTap: () async {
                  Navigator.pop(ctx);
                  final file = await _mediaService.takePhoto();
                  if (file != null) {
                    final bytes = await file.readAsBytes();
                    await _handleUpdateAvatar(file.path, avatarBytes: bytes);
                  }
                },
              ),
              ListTile(
                leading: Container(
                  padding: const EdgeInsets.all(8),
                  decoration: const BoxDecoration(color: Color(0xFFEFF6FF), shape: BoxShape.circle),
                  child: const Icon(LucideIcons.image, color: AppColors.primary, size: 20),
                ),
                title: const Text('Pilih dari Galeri Foto', style: TextStyle(fontWeight: FontWeight.w600, fontSize: 13)),
                onTap: () async {
                  Navigator.pop(ctx);
                  final file = await _mediaService.pickFromGallery();
                  if (file != null) {
                    final bytes = await file.readAsBytes();
                    await _handleUpdateAvatar(file.path, avatarBytes: bytes);
                  }
                },
              ),
              if (_user?.avatar != null && _user!.avatar!.isNotEmpty)
                ListTile(
                  leading: Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(color: AppColors.statusRejected.withValues(alpha: 0.1), shape: BoxShape.circle),
                    child: const Icon(LucideIcons.trash2, color: AppColors.statusRejected, size: 20),
                  ),
                  title: const Text('Hapus Foto Profil', style: TextStyle(fontWeight: FontWeight.w600, fontSize: 13, color: AppColors.statusRejected)),
                  onTap: () async {
                    Navigator.pop(ctx);
                    await _handleUpdateAvatar('');
                  },
                ),
            ],
          ),
        ),
      ),
    );
  }

  void _showAvatarPreviewDialog() {
    final avatar = _user?.avatar;
    final hasAvatar = avatar != null && avatar.isNotEmpty;
    final isGuest = _user?.role == 'TAMU' || _user?.isAnonymous == true;

    showDialog(
      context: context,
      builder: (ctx) => Dialog(
        backgroundColor: Colors.transparent,
        insetPadding: const EdgeInsets.symmetric(horizontal: 20, vertical: 24),
        child: Container(
          padding: const EdgeInsets.all(20),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(24),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withValues(alpha: 0.2),
                blurRadius: 20,
                offset: const Offset(0, 10),
              ),
            ],
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    hasAvatar ? 'Foto Profil' : 'Preview Profil',
                    style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: AppColors.textPrimary),
                  ),
                  IconButton(
                    icon: const Icon(LucideIcons.x, size: 20, color: AppColors.textSecondary),
                    onPressed: () => Navigator.pop(ctx),
                  ),
                ],
              ),
              const SizedBox(height: 16),
              ClipRRect(
                borderRadius: BorderRadius.circular(100),
                child: Container(
                  width: 150,
                  height: 150,
                  decoration: BoxDecoration(
                    color: isGuest ? Colors.blueGrey.shade100 : AppColors.primary.withValues(alpha: 0.1),
                    shape: BoxShape.circle,
                    border: Border.all(color: AppColors.primary.withValues(alpha: 0.2), width: 3),
                  ),
                  child: () {
                    final netUrl = _resolveAvatarNetworkUrl(avatar);
                    if (netUrl != null) {
                      return Image.network(
                        netUrl,
                        fit: BoxFit.cover,
                        errorBuilder: (_, __, ___) => Icon(
                          _user?.role == 'PETUGAS' ? LucideIcons.hardHat : LucideIcons.user,
                          size: 70,
                          color: AppColors.primary,
                        ),
                      );
                    }
                    if (hasAvatar && _canReadLocalFile(avatar)) {
                      return Image.file(File(avatar), fit: BoxFit.cover);
                    }
                    return Icon(
                      isGuest
                          ? LucideIcons.userX
                          : (_user?.role == 'PETUGAS' ? LucideIcons.hardHat : LucideIcons.user),
                      size: 70,
                      color: isGuest ? Colors.blueGrey : AppColors.primary,
                    );
                  }(),
                ),
              ),
              const SizedBox(height: 16),
              Text(
                _user?.name ?? 'Pengguna',
                style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 17, color: AppColors.textPrimary),
              ),
              const SizedBox(height: 4),
              Text(
                _user?.role == 'PETUGAS' ? 'Pekerja Lapangan Dinas PUPR' : (isGuest ? 'Warga Tamu (Anonim)' : 'Warga Terdaftar'),
                style: const TextStyle(fontSize: 12, color: AppColors.textSecondary),
              ),
              const SizedBox(height: 20),
              if (!isGuest) ...[
                Row(
                  children: [
                    Expanded(
                      child: ElevatedButton.icon(
                        onPressed: () {
                          Navigator.pop(ctx);
                          _showPhotoPickerSheet();
                        },
                        icon: const Icon(LucideIcons.camera, size: 16),
                        label: Text(hasAvatar ? 'Ganti Foto' : 'Unggah Foto'),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: AppColors.primary,
                          foregroundColor: Colors.white,
                          padding: const EdgeInsets.symmetric(vertical: 12),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        ),
                      ),
                    ),
                    if (hasAvatar) ...[
                      const SizedBox(width: 10),
                      ElevatedButton.icon(
                        onPressed: () {
                          Navigator.pop(ctx);
                          _handleUpdateAvatar('');
                        },
                        icon: const Icon(LucideIcons.trash2, size: 16),
                        label: const Text('Hapus'),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: AppColors.statusRejected.withValues(alpha: 0.1),
                          foregroundColor: AppColors.statusRejected,
                          elevation: 0,
                          padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 16),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        ),
                      ),
                    ],
                  ],
                ),
              ] else
                SizedBox(
                  width: double.infinity,
                  child: OutlinedButton(
                    onPressed: () => Navigator.pop(ctx),
                    child: const Text('Tutup'),
                  ),
                ),
            ],
          ),
        ),
      ),
    );
  }

  Future<void> _handleUpdateAvatar(String avatarPath, {Uint8List? avatarBytes}) async {
    if (_user == null) return;
    final isDeleting = avatarPath.isEmpty || avatarPath == 'DELETE';
    final res = await _authService.updateProfile(
      name: _user!.name,
      email: _user!.email,
      avatar: isDeleting ? 'DELETE' : avatarPath,
      avatarBytes: isDeleting ? null : avatarBytes,
    );
    if (res['success'] == true) {
      setState(() {
        if (isDeleting) {
          _user = _user?.copyWith(clearAvatar: true);
        } else {
          _user = res['user'] ?? _user?.copyWith(avatar: avatarPath);
        }
      });
      if (mounted) {
        showV2Toast(
          context,
          message: isDeleting ? 'Foto profil telah dihapus.' : 'Foto profil berhasil diperbarui!',
          type: V2ToastType.success,
        );
      }
    } else {
      if (mounted) {
        showV2Toast(
          context,
          message: res['message'] ?? 'Gagal memperbarui foto profil.',
          type: V2ToastType.error,
        );
      }
    }
  }

  // 2. Edit Profile Modal (Username, Email, Password)
  Future<void> _showEditProfileModal() async {
    final isGuest = _user?.role == 'TAMU' || _user?.isAnonymous == true;
    if (isGuest) {
      showV2Toast(
        context,
        message: 'Silakan login dengan akun terdaftar untuk mengubah profil.',
        type: V2ToastType.warning,
      );
      return;
    }

    final nameCtrl = TextEditingController(text: _user?.name ?? '');
    final emailCtrl = TextEditingController(text: _user?.email ?? '');
    final phoneCtrl = TextEditingController(text: _user?.phone ?? '');
    final currentPassCtrl = TextEditingController();
    final passCtrl = TextEditingController();
    final confirmPassCtrl = TextEditingController();
    bool isSaving = false;
    bool obscureCurrent = true;
    bool obscurePass = true;
    bool obscureConfirm = true;
    String? phoneFeedback;
    bool? isPhoneValid;
    String? generalErrorBanner;
    String? currentPassError;
    String? passError;
    String? confirmPassError;
    String? nameError;
    String? emailError;

    if (phoneCtrl.text.isNotEmpty) {
      final initialCheck = PhoneValidator.validate(phoneCtrl.text, isRequired: false);
      phoneFeedback = initialCheck.message;
      isPhoneValid = initialCheck.isValid;
    } else {
      phoneFeedback = 'Nomor HP Indonesia 10 - 13 digit (contoh: 081234567890)';
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
        builder: (context, setModalState) => Padding(
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
                      child: const Icon(LucideIcons.userCog, color: AppColors.primary, size: 22),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text(
                            'Ubah Profil & Kata Sandi',
                            style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
                          ),
                          Text(
                            _user?.role == 'PETUGAS' ? 'Perbarui data akun Pekerja Lapangan' : 'Perbarui identitas Warga Terdaftar Anda',
                            style: const TextStyle(fontSize: 11, color: AppColors.textSecondary),
                          ),
                        ],
                      ),
                    ),
                    IconButton(
                      icon: const Icon(LucideIcons.x, size: 20),
                      onPressed: () => Navigator.pop(ctx),
                    ),
                  ],
                ),
                const Divider(height: 24),

                // Banner Peringatan Kesalahan Langsung di Dalam Sheet (Anti-Stuck)
                if (generalErrorBanner != null)
                  Container(
                    margin: const EdgeInsets.only(bottom: 16),
                    padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                    decoration: BoxDecoration(
                      color: const Color(0xFFFEF2F2),
                      borderRadius: BorderRadius.circular(14),
                      border: Border.all(color: const Color(0xFFFCA5A5)),
                    ),
                    child: Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Icon(LucideIcons.alertTriangle, color: Color(0xFFDC2626), size: 20),
                        const SizedBox(width: 10),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Text(
                                'Perhatian',
                                style: TextStyle(
                                  fontSize: 12,
                                  fontWeight: FontWeight.bold,
                                  color: Color(0xFF991B1B),
                                ),
                              ),
                              const SizedBox(height: 2),
                              Text(
                                generalErrorBanner!,
                                style: const TextStyle(
                                  fontSize: 12,
                                  color: Color(0xFFB91C1C),
                                  height: 1.3,
                                ),
                              ),
                            ],
                          ),
                        ),
                        GestureDetector(
                          onTap: () => setModalState(() => generalErrorBanner = null),
                          child: const Icon(LucideIcons.x, color: Color(0xFF991B1B), size: 16),
                        ),
                      ],
                    ),
                  ),

                Text('Username / Nama Lengkap *', style: GoogleFonts.inter(fontSize: 12.5, fontWeight: FontWeight.w600, color: AppColors.textPrimary)),
                const SizedBox(height: 6),
                TextField(
                  controller: nameCtrl,
                  style: GoogleFonts.inter(fontSize: 13.5, color: AppColors.textPrimary),
                  onChanged: (_) {
                    if (nameError != null) setModalState(() => nameError = null);
                  },
                  decoration: InputDecoration(
                    hintText: 'Nama lengkap Anda',
                    hintStyle: GoogleFonts.inter(fontSize: 13.5, color: AppColors.textSecondary.withValues(alpha: 0.7)),
                    prefixIcon: const Icon(LucideIcons.user, size: 18, color: AppColors.textSecondary),
                    filled: true,
                    fillColor: AppColors.cardSurface,
                    errorText: nameError,
                    contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 14),
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: AppColors.border)),
                    enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: AppColors.border)),
                    focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: AppColors.primary, width: 1.5)),
                  ),
                ),
                const SizedBox(height: 14),
                Text('Alamat Email *', style: GoogleFonts.inter(fontSize: 12.5, fontWeight: FontWeight.w600, color: AppColors.textPrimary)),
                const SizedBox(height: 6),
                TextField(
                  controller: emailCtrl,
                  keyboardType: TextInputType.emailAddress,
                  style: GoogleFonts.inter(fontSize: 13.5, color: AppColors.textPrimary),
                  onChanged: (_) {
                    if (emailError != null) setModalState(() => emailError = null);
                  },
                  decoration: InputDecoration(
                    hintText: 'contoh@email.com',
                    hintStyle: GoogleFonts.inter(fontSize: 13.5, color: AppColors.textSecondary.withValues(alpha: 0.7)),
                    prefixIcon: const Icon(LucideIcons.mail, size: 18, color: AppColors.textSecondary),
                    filled: true,
                    fillColor: AppColors.cardSurface,
                    errorText: emailError,
                    contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 14),
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: AppColors.border)),
                    enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: AppColors.border)),
                    focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: AppColors.primary, width: 1.5)),
                  ),
                ),
                const SizedBox(height: 14),
                Text('No. Handphone / WhatsApp (10 - 13 digit)', style: GoogleFonts.inter(fontSize: 12.5, fontWeight: FontWeight.w600, color: AppColors.textPrimary)),
                const SizedBox(height: 6),
                TextField(
                  controller: phoneCtrl,
                  keyboardType: TextInputType.phone,
                  style: GoogleFonts.inter(fontSize: 13.5, color: AppColors.textPrimary),
                  onChanged: (val) {
                    if (val.trim().isEmpty) {
                      setModalState(() {
                        phoneFeedback = 'Nomor HP Indonesia 10 - 13 digit (contoh: 081234567890)';
                        isPhoneValid = null;
                      });
                      return;
                    }
                    final res = PhoneValidator.validate(val, isRequired: false);
                    setModalState(() {
                      phoneFeedback = res.message;
                      isPhoneValid = res.isValid;
                    });
                  },
                  decoration: InputDecoration(
                    hintText: '08xxxxxxxxxx',
                    hintStyle: GoogleFonts.inter(fontSize: 13.5, color: AppColors.textSecondary.withValues(alpha: 0.7)),
                    prefixIcon: const Icon(LucideIcons.phone, size: 18, color: AppColors.textSecondary),
                    filled: true,
                    fillColor: AppColors.cardSurface,
                    contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 14),
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: AppColors.border)),
                    enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: AppColors.border)),
                    focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: AppColors.primary, width: 1.5)),
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
                Text('Kata Sandi Saat Ini (Wajib jika ganti kata sandi)', style: GoogleFonts.inter(fontSize: 12.5, fontWeight: FontWeight.w600, color: AppColors.textPrimary)),
                const SizedBox(height: 6),
                TextField(
                  controller: currentPassCtrl,
                  obscureText: obscureCurrent,
                  style: GoogleFonts.inter(fontSize: 13.5, color: AppColors.textPrimary),
                  onChanged: (_) {
                    if (currentPassError != null || generalErrorBanner != null) {
                      setModalState(() {
                        currentPassError = null;
                        generalErrorBanner = null;
                      });
                    }
                  },
                  decoration: InputDecoration(
                    hintText: 'Masukkan kata sandi lama/saat ini',
                    hintStyle: GoogleFonts.inter(fontSize: 13.5, color: AppColors.textSecondary.withValues(alpha: 0.7)),
                    prefixIcon: const Icon(LucideIcons.keyRound, size: 18, color: AppColors.textSecondary),
                    suffixIcon: IconButton(
                      icon: Icon(obscureCurrent ? LucideIcons.eyeOff : LucideIcons.eye, size: 18, color: AppColors.textSecondary),
                      onPressed: () => setModalState(() => obscureCurrent = !obscureCurrent),
                    ),
                    filled: true,
                    fillColor: AppColors.cardSurface,
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: AppColors.border)),
                    enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: AppColors.border)),
                    focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: AppColors.primary, width: 1.5)),
                    errorText: currentPassError,
                    contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 14),
                  ),
                ),
                const SizedBox(height: 14),
                Text('Kata Sandi Baru (Opsional)', style: GoogleFonts.inter(fontSize: 12.5, fontWeight: FontWeight.w600, color: AppColors.textPrimary)),
                const SizedBox(height: 2),
                Text(
                  'Ketentuan: Minimal 8 karakter, serta wajib memiliki setidaknya 1 huruf besar (A-Z) dan 1 angka (0-9).',
                  style: GoogleFonts.inter(fontSize: 11, color: AppColors.textSecondary, height: 1.3),
                ),
                const SizedBox(height: 6),
                TextField(
                  controller: passCtrl,
                  obscureText: obscurePass,
                  style: GoogleFonts.inter(fontSize: 13.5, color: AppColors.textPrimary),
                  onChanged: (_) {
                    if (passError != null || generalErrorBanner != null) {
                      setModalState(() {
                        passError = null;
                        generalErrorBanner = null;
                      });
                    }
                  },
                  decoration: InputDecoration(
                    hintText: 'Biarkan kosong jika tidak ingin mengubah',
                    hintStyle: GoogleFonts.inter(fontSize: 13.5, color: AppColors.textSecondary.withValues(alpha: 0.7)),
                    prefixIcon: const Icon(LucideIcons.lock, size: 18, color: AppColors.textSecondary),
                    suffixIcon: IconButton(
                      icon: Icon(obscurePass ? LucideIcons.eyeOff : LucideIcons.eye, size: 18, color: AppColors.textSecondary),
                      onPressed: () => setModalState(() => obscurePass = !obscurePass),
                    ),
                    filled: true,
                    fillColor: AppColors.cardSurface,
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: AppColors.border)),
                    enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: AppColors.border)),
                    focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: AppColors.primary, width: 1.5)),
                    errorText: passError,
                    contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 14),
                  ),
                ),
                const SizedBox(height: 14),
                Text('Konfirmasi Kata Sandi Baru', style: GoogleFonts.inter(fontSize: 12.5, fontWeight: FontWeight.w600, color: AppColors.textPrimary)),
                const SizedBox(height: 6),
                TextField(
                  controller: confirmPassCtrl,
                  obscureText: obscureConfirm,
                  style: GoogleFonts.inter(fontSize: 13.5, color: AppColors.textPrimary),
                  onChanged: (_) {
                    if (confirmPassError != null || generalErrorBanner != null) {
                      setModalState(() {
                        confirmPassError = null;
                        generalErrorBanner = null;
                      });
                    }
                  },
                  decoration: InputDecoration(
                    hintText: 'Ulangi kata sandi baru',
                    hintStyle: GoogleFonts.inter(fontSize: 13.5, color: AppColors.textSecondary.withValues(alpha: 0.7)),
                    prefixIcon: const Icon(LucideIcons.lockKeyhole, size: 18, color: AppColors.textSecondary),
                    suffixIcon: IconButton(
                      icon: Icon(obscureConfirm ? LucideIcons.eyeOff : LucideIcons.eye, size: 18, color: AppColors.textSecondary),
                      onPressed: () => setModalState(() => obscureConfirm = !obscureConfirm),
                    ),
                    filled: true,
                    fillColor: AppColors.cardSurface,
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: AppColors.border)),
                    enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: AppColors.border)),
                    focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: AppColors.primary, width: 1.5)),
                    errorText: confirmPassError,
                    contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 14),
                  ),
                ),
                const SizedBox(height: 20),
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton.icon(
                    onPressed: isSaving
                        ? null
                        : () async {
                            final name = nameCtrl.text.trim();
                            final email = emailCtrl.text.trim();
                            final phone = phoneCtrl.text.trim();
                            final currentPass = currentPassCtrl.text.trim();
                            final pass = passCtrl.text.trim();
                            final confirmPass = confirmPassCtrl.text.trim();

                            setModalState(() {
                              generalErrorBanner = null;
                              currentPassError = null;
                              passError = null;
                              confirmPassError = null;
                              nameError = null;
                              emailError = null;
                            });

                            if (name.isEmpty) {
                              setModalState(() {
                                nameError = 'Username / Nama lengkap tidak boleh kosong.';
                                generalErrorBanner = 'Harap isi nama lengkap Anda.';
                              });
                              return;
                            }
                            if (email.isEmpty || !email.contains('@')) {
                              setModalState(() {
                                emailError = 'Alamat email tidak valid.';
                                generalErrorBanner = 'Format alamat email tidak valid.';
                              });
                              return;
                            }

                            String? validatedPhone;
                            if (phone.isNotEmpty) {
                              final validation = PhoneValidator.validate(phone, isRequired: true);
                              if (!validation.isValid) {
                                setModalState(() {
                                  phoneFeedback = validation.message;
                                  isPhoneValid = false;
                                  generalErrorBanner = validation.message ?? 'Format nomor handphone tidak valid.';
                                });
                                return;
                              }
                              validatedPhone = validation.normalized;
                            }

                            if (pass.isNotEmpty) {
                              if (currentPass.isEmpty) {
                                setModalState(() {
                                  currentPassError = 'Kata sandi saat ini wajib diisi.';
                                  generalErrorBanner = 'Kata sandi saat ini wajib diisi untuk verifikasi keamanan perubahan kata sandi.';
                                });
                                return;
                              }
                              if (pass.length < 8) {
                                setModalState(() {
                                  passError = 'Kata sandi baru minimal 8 karakter.';
                                  generalErrorBanner = 'Kata sandi baru harus memiliki minimal 8 karakter.';
                                });
                                return;
                              }
                              if (!RegExp(r'[A-Z]').hasMatch(pass)) {
                                setModalState(() {
                                  passError = 'Wajib memiliki setidaknya 1 huruf besar (A-Z).';
                                  generalErrorBanner = 'Kata sandi baru wajib memiliki setidaknya 1 huruf besar (A-Z).';
                                });
                                return;
                              }
                              if (!RegExp(r'[0-9]').hasMatch(pass)) {
                                setModalState(() {
                                  passError = 'Wajib memiliki setidaknya 1 angka (0-9).';
                                  generalErrorBanner = 'Kata sandi baru wajib memiliki setidaknya 1 angka (0-9).';
                                });
                                return;
                              }
                              if (!RegExp(r'[a-z]').hasMatch(pass)) {
                                setModalState(() {
                                  passError = 'Wajib memiliki huruf kecil (a-z).';
                                  generalErrorBanner = 'Kata sandi baru wajib mengandung huruf kecil (a-z).';
                                });
                                return;
                              }
                              if (pass.toLowerCase().trim() == 'password') {
                                setModalState(() {
                                  passError = 'Tidak boleh menggunakan sandi default "password".';
                                  generalErrorBanner = 'Kata sandi baru tidak boleh menggunakan kata sandi default "password".';
                                });
                                return;
                              }
                              if (pass == currentPass) {
                                setModalState(() {
                                  passError = 'Tidak boleh sama dengan kata sandi saat ini.';
                                  generalErrorBanner = 'Kata sandi baru tidak boleh sama dengan kata sandi saat ini.';
                                });
                                return;
                              }
                              if (pass != confirmPass) {
                                setModalState(() {
                                  confirmPassError = 'Konfirmasi kata sandi baru tidak cocok.';
                                  generalErrorBanner = 'Konfirmasi kata sandi baru tidak cocok dengan kata sandi baru.';
                                });
                                return;
                              }
                            }

                            setModalState(() => isSaving = true);

                            // Pengecekan nomor telepon di database sebelum simpan
                            if (validatedPhone != null) {
                              final checkRes = await _authService.checkPhone(validatedPhone, userId: _user?.id);
                              if (checkRes['registered'] == true) {
                                setModalState(() {
                                  isSaving = false;
                                  phoneFeedback = checkRes['message'] ?? 'Nomor handphone sudah terdaftar oleh pengguna lain.';
                                  isPhoneValid = false;
                                  generalErrorBanner = checkRes['message'] ?? 'Nomor handphone ini sudah terdaftar oleh pengguna lain.';
                                });
                                return;
                              }
                            }

                            // Dapatkan koordinat perangkat secara dinamis jika tersedia
                            double? deviceLat;
                            double? deviceLon;
                            try {
                              final lastPos = await Geolocator.getLastKnownPosition();
                              if (lastPos != null) {
                                deviceLat = lastPos.latitude;
                                deviceLon = lastPos.longitude;
                              }
                            } catch (_) {}

                            final res = await _authService.updateProfile(
                              name: name,
                              email: email,
                              phone: validatedPhone,
                              currentPassword: currentPass.isNotEmpty ? currentPass : null,
                              password: pass.isNotEmpty ? pass : null,
                              latitude: deviceLat,
                              longitude: deviceLon,
                            );

                            setModalState(() => isSaving = false);

                            if (res['success'] == true) {
                              setState(() {
                                _user = res['user'] ?? _user?.copyWith(name: name, email: email, phone: phone);
                              });
                              if (ctx.mounted) Navigator.pop(ctx);
                              if (mounted) {
                                showV2Toast(
                                  this.context,
                                  message: 'Profil dan informasi akun Anda berhasil diperbarui!',
                                  type: V2ToastType.success,
                                );
                              }
                            } else {
                              final serverMsg = res['message'] as String? ?? 'Gagal memperbarui profil.';
                              setModalState(() {
                                generalErrorBanner = serverMsg;
                                if (serverMsg.toLowerCase().contains('saat ini') ||
                                    serverMsg.toLowerCase().contains('salah') ||
                                    serverMsg.toLowerCase().contains('tidak cocok')) {
                                  currentPassError = 'Kata sandi saat ini tidak cocok atau salah.';
                                  generalErrorBanner = 'Kata sandi saat ini yang Anda masukkan tidak cocok. Silakan periksa kembali kata sandi akun Anda.';
                                }
                                if (serverMsg.toLowerCase().contains('kata sandi baru') ||
                                    serverMsg.toLowerCase().contains('8 karakter') ||
                                    serverMsg.toLowerCase().contains('huruf besar') ||
                                    serverMsg.toLowerCase().contains('angka')) {
                                  passError = serverMsg;
                                }
                              });
                            }
                          },
                    icon: isSaving
                        ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                        : const Icon(LucideIcons.check, size: 18),
                    label: Text(isSaving ? 'Menyimpan...' : 'Simpan Perubahan', style: const TextStyle(fontWeight: FontWeight.bold)),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppColors.primary,
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(vertical: 14),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                    ),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  // 3. Preview Akun Modal
  void _showAccountPreviewModal() {
    final isGuest = _user?.role == 'TAMU' || _user?.isAnonymous == true;
    final isPetugas = _user?.role == 'PETUGAS';

    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (ctx) => SafeArea(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Container(
                width: 40,
                height: 4,
                decoration: BoxDecoration(
                  color: Colors.grey.shade300,
                  borderRadius: BorderRadius.circular(2),
                ),
              ),
              const SizedBox(height: 20),
              // Card ID Layout
              Container(
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(
                  gradient: LinearGradient(
                    colors: isPetugas
                        ? [AppColors.primary, AppColors.primaryLight]
                        : isGuest
                            ? [Colors.blueGrey.shade700, Colors.blueGrey.shade900]
                            : [AppColors.primary, AppColors.primaryLight],
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                  ),
                  borderRadius: BorderRadius.circular(20),
                  boxShadow: [
                    BoxShadow(
                      color: (isGuest ? Colors.blueGrey : AppColors.primary).withValues(alpha: 0.3),
                      blurRadius: 16,
                      offset: const Offset(0, 6),
                    ),
                  ],
                ),
                child: Column(
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Expanded(
                          child: Row(
                            children: [
                              Icon(isPetugas ? LucideIcons.hardHat : LucideIcons.shieldCheck, color: Colors.white, size: 20),
                              const SizedBox(width: 8),
                              Flexible(
                                child: Text(
                                  isPetugas
                                      ? 'KARTU IDENTITAS PEKERJA'
                                      : isGuest
                                          ? 'KARTU TAMU'
                                          : 'KARTU WARGA TERDAFTAR',
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                  style: const TextStyle(color: Colors.white70, fontSize: 11, fontWeight: FontWeight.bold, letterSpacing: 0.5),
                                ),
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(width: 8),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                          decoration: BoxDecoration(
                            color: isGuest ? Colors.white24 : AppColors.statusCompleted,
                            borderRadius: BorderRadius.circular(99),
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Icon(isGuest ? LucideIcons.userX : LucideIcons.badgeCheck, color: Colors.white, size: 12),
                              const SizedBox(width: 4),
                              Text(
                                isGuest ? 'Tamu' : 'Aktif',
                                style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 20),
                    Row(
                      children: [
                        _buildAvatarWidget(size: 32),
                        const SizedBox(width: 16),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                _user?.name ?? (isPetugas ? 'Pekerja Lapangan' : 'Warga Kabupaten'),
                                style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: Colors.white),
                              ),
                              const SizedBox(height: 4),
                              Text(
                                _user?.email ?? 'petugas@kabupaten.go.id',
                                style: const TextStyle(fontSize: 12, color: Colors.white70),
                              ),
                              const SizedBox(height: 3),
                              Row(
                                children: [
                                  const Icon(LucideIcons.phone, size: 11, color: Colors.white70),
                                  const SizedBox(width: 4),
                                  Text(
                                    _user?.phone != null && _user!.phone!.isNotEmpty ? _user!.phone! : 'No. Telp belum diatur',
                                    style: const TextStyle(fontSize: 11, color: Colors.white70),
                                  ),
                                ],
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 18),
                    const Divider(color: Colors.white24, height: 1),
                    const SizedBox(height: 12),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(isPetugas ? 'Instansi Dinas' : 'Wilayah Domisili', style: const TextStyle(color: Colors.white60, fontSize: 10)),
                              const SizedBox(height: 2),
                              Text(
                                isPetugas ? 'Dinas PUPR' : 'Kabupaten',
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                                style: const TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.w600),
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.end,
                            children: [
                              const Text('Peran Akun', style: TextStyle(color: Colors.white60, fontSize: 10)),
                              const SizedBox(height: 2),
                              Text(
                                isPetugas ? 'Pekerja Lapangan' : (isGuest ? 'Mode Tamu' : 'Warga Terdaftar'),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                                textAlign: TextAlign.end,
                                style: const TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.w600),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                    if (isPetugas) ...[
                      const SizedBox(height: 12),
                      const Divider(color: Colors.white24, height: 1),
                      const SizedBox(height: 10),
                      Row(
                        children: [
                          const Icon(LucideIcons.hardHat, size: 14, color: Colors.white70),
                          const SizedBox(width: 8),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                const Text('Regu Penugasan', style: TextStyle(color: Colors.white60, fontSize: 10)),
                                const SizedBox(height: 2),
                                Text(
                                  _myTeam != null
                                      ? '${_myTeam!['name']}${_myTeam!['code'] != null ? ' (${_myTeam!['code']})' : ''}'
                                      : 'Belum tergabung dalam regu',
                                  style: const TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.w600),
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                    ],
                  ],
                ),
              ),
              const SizedBox(height: 20),
              SizedBox(
                width: double.infinity,
                child: OutlinedButton(
                  onPressed: () => Navigator.pop(ctx),
                  style: OutlinedButton.styleFrom(
                    foregroundColor: AppColors.primary,
                    side: const BorderSide(color: Color(0xFFBFDBFE)),
                    padding: const EdgeInsets.symmetric(vertical: 12),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                  child: const Text('Tutup Preview', style: TextStyle(fontWeight: FontWeight.bold)),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  // 3b. Detail Regu Lapangan Modal
  void _showTeamDetailModal() {
    if (_myTeam == null) return;

    final teamName = _myTeam!['name'] ?? 'Regu Lapangan';
    final teamCode = _myTeam!['code'] ?? 'REGU';
    final district = _myTeam!['district'] ?? 'Semua Kecamatan Kabupaten';
    final leaderName = _myTeam!['leader_name'] ?? '-';
    final leaderPhone = _myTeam!['leader_phone'] ?? '-';
    final membersCount = _myTeam!['members_count'] ?? 0;
    final userRoleInTeam = _myTeam!['user_role_in_team'] ?? 'Anggota Regu';
    final List members = _myTeam!['members'] ?? [];

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (ctx) => DraggableScrollableSheet(
        initialChildSize: 0.78,
        minChildSize: 0.5,
        maxChildSize: 0.95,
        expand: false,
        builder: (context, scrollController) => Padding(
          padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
          child: ListView(
            controller: scrollController,
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

              Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(10),
                    decoration: BoxDecoration(
                      color: AppColors.primary.withValues(alpha: 0.1),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: const Icon(LucideIcons.users, color: AppColors.primary, size: 22),
                  ),
                  const SizedBox(width: 12),
                  const Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Detail Regu Lapangan',
                          style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
                        ),
                        Text(
                          'Struktur Tim & Personil Dinas PUPR',
                          style: TextStyle(fontSize: 11, color: AppColors.textSecondary),
                        ),
                      ],
                    ),
                  ),
                  IconButton(
                    icon: const Icon(LucideIcons.x, size: 20),
                    onPressed: () => Navigator.pop(ctx),
                  ),
                ],
              ),
              const SizedBox(height: 16),

              // Team Card Preview in Dominant Blue
              Container(
                padding: const EdgeInsets.all(18),
                decoration: BoxDecoration(
                  gradient: const LinearGradient(
                    colors: [AppColors.primary, AppColors.primaryLight],
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                  ),
                  borderRadius: BorderRadius.circular(18),
                  boxShadow: [
                    BoxShadow(
                      color: AppColors.primary.withValues(alpha: 0.3),
                      blurRadius: 14,
                      offset: const Offset(0, 5),
                    ),
                  ],
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 4),
                          decoration: BoxDecoration(
                            color: Colors.white.withValues(alpha: 0.2),
                            borderRadius: BorderRadius.circular(8),
                            border: Border.all(color: Colors.white.withValues(alpha: 0.3)),
                          ),
                          child: Text(
                            teamCode,
                            style: const TextStyle(
                              color: Colors.white,
                              fontSize: 12,
                              fontWeight: FontWeight.bold,
                              letterSpacing: 0.5,
                            ),
                          ),
                        ),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                          decoration: BoxDecoration(
                            color: Colors.white,
                            borderRadius: BorderRadius.circular(20),
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              const Icon(LucideIcons.shieldCheck, size: 12, color: AppColors.primary),
                              const SizedBox(width: 4),
                              Text(
                                userRoleInTeam,
                                style: const TextStyle(
                                  color: AppColors.primary,
                                  fontSize: 11,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 14),
                    Text(
                      teamName,
                      style: const TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.bold,
                        color: Colors.white,
                        letterSpacing: 0.3,
                      ),
                    ),
                    const SizedBox(height: 6),
                    Row(
                      children: [
                        const Icon(LucideIcons.mapPin, size: 13, color: Colors.white70),
                        const SizedBox(width: 4),
                        Expanded(
                          child: Text(
                            'Wilayah Operasional: $district',
                            style: const TextStyle(fontSize: 12, color: Colors.white70),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 14),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                      decoration: BoxDecoration(
                        color: Colors.black.withValues(alpha: 0.15),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Text('Ketua Regu', style: TextStyle(fontSize: 10, color: Colors.white70)),
                              const SizedBox(height: 2),
                              Text(
                                leaderName,
                                style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Colors.white),
                              ),
                            ],
                          ),
                          Column(
                            crossAxisAlignment: CrossAxisAlignment.end,
                            children: [
                              const Text('Kekuatan Regu', style: TextStyle(fontSize: 10, color: Colors.white70)),
                              const SizedBox(height: 2),
                              Text(
                                '$membersCount Personil',
                                style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Colors.white),
                              ),
                            ],
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 20),

              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text(
                    'Daftar Anggota & Personil',
                    style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
                  ),
                  Text(
                    '${members.isNotEmpty ? members.length : membersCount} Personil',
                    style: const TextStyle(fontSize: 12, color: AppColors.textSecondary, fontWeight: FontWeight.w600),
                  ),
                ],
              ),
              const SizedBox(height: 10),

              if (members.isNotEmpty)
                ...members.map((m) {
                  final name = m['name'] ?? 'Pekerja Lapangan';
                  final role = m['role'] ?? 'Anggota Regu';
                  final isLeader = m['is_leader'] == true || role == 'Ketua Regu';
                  final phone = m['phone'] ?? '-';
                  final isMe = _user != null && m['user_id'] == _user!.id;

                  return Container(
                    margin: const EdgeInsets.only(bottom: 8),
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: isMe ? const Color(0xFFEFF6FF) : Colors.white,
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(
                        color: isMe ? const Color(0xFFBFDBFE) : AppColors.border,
                        width: isMe ? 1.4 : 1.0,
                      ),
                    ),
                    child: Row(
                      children: [
                        CircleAvatar(
                          radius: 18,
                          backgroundColor: isLeader ? AppColors.primary : const Color(0xFFE2E8F0),
                          child: Icon(
                            isLeader ? LucideIcons.crown : LucideIcons.hardHat,
                            size: 16,
                            color: isLeader ? Colors.white : const Color(0xFF475569),
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Row(
                                children: [
                                  Flexible(
                                    child: Text(
                                      name,
                                      style: TextStyle(
                                        fontSize: 13,
                                        fontWeight: isMe ? FontWeight.bold : FontWeight.w600,
                                        color: AppColors.textPrimary,
                                      ),
                                      maxLines: 1,
                                      overflow: TextOverflow.ellipsis,
                                    ),
                                  ),
                                  if (isMe) ...[
                                    const SizedBox(width: 6),
                                    Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 1.5),
                                      decoration: BoxDecoration(
                                        color: AppColors.primary,
                                        borderRadius: BorderRadius.circular(4),
                                      ),
                                      child: const Text(
                                        'Anda',
                                        style: TextStyle(color: Colors.white, fontSize: 9, fontWeight: FontWeight.bold),
                                      ),
                                    ),
                                  ],
                                ],
                              ),
                              const SizedBox(height: 2),
                              Text(
                                'No. HP: $phone',
                                style: const TextStyle(fontSize: 11, color: AppColors.textSecondary),
                              ),
                            ],
                          ),
                        ),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                          decoration: BoxDecoration(
                            color: isLeader ? const Color(0xFFDBEAFE) : const Color(0xFFF1F5F9),
                            borderRadius: BorderRadius.circular(6),
                            border: Border.all(
                              color: isLeader ? const Color(0xFFBFDBFE) : const Color(0xFFE2E8F0),
                            ),
                          ),
                          child: Text(
                            role,
                            style: TextStyle(
                              fontSize: 10,
                              fontWeight: FontWeight.bold,
                              color: isLeader ? AppColors.primary : const Color(0xFF475569),
                            ),
                          ),
                        ),
                      ],
                    ),
                  );
                })
              else ...[
                Container(
                  padding: const EdgeInsets.all(14),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: AppColors.border),
                  ),
                  child: Row(
                    children: [
                      const CircleAvatar(
                        radius: 18,
                        backgroundColor: AppColors.primary,
                        child: Icon(LucideIcons.crown, size: 16, color: Colors.white),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(leaderName, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold)),
                            Text('No. HP: $leaderPhone', style: const TextStyle(fontSize: 11, color: AppColors.textSecondary)),
                          ],
                        ),
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                        decoration: BoxDecoration(
                          color: const Color(0xFFDBEAFE),
                          borderRadius: BorderRadius.circular(6),
                        ),
                        child: const Text('Ketua Regu', style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: AppColors.primary)),
                      ),
                    ],
                  ),
                ),
              ],

              const SizedBox(height: 16),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  onPressed: () => Navigator.pop(ctx),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppColors.primary,
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 12),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                  child: const Text('Tutup Detail Regu', style: TextStyle(fontWeight: FontWeight.bold)),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  // 4. Panduan Penggunaan Aplikasi Modal
  void _showAppGuideModal() {
    final isPetugas = _user?.role == 'PETUGAS';

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (ctx) => DraggableScrollableSheet(
        initialChildSize: 0.85,
        minChildSize: 0.5,
        maxChildSize: 0.95,
        expand: false,
        builder: (_, scrollController) => Padding(
          padding: const EdgeInsets.all(20),
          child: ListView(
            controller: scrollController,
            children: [
              Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(8),
                    decoration: const BoxDecoration(
                      color: Color(0xFFEFF6FF),
                      borderRadius: BorderRadius.all(Radius.circular(10)),
                    ),
                    child: Icon(isPetugas ? LucideIcons.hardHat : LucideIcons.bookOpenCheck, color: AppColors.primary, size: 22),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          isPetugas ? 'Panduan Teknis Pekerja Lapangan' : 'Panduan Penggunaan Aplikasi',
                          style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
                        ),
                        Text(
                          isPetugas ? 'Standar Operasional Penanganan & Validasi SPK' : 'Tata cara lapor fasilitas rusak di Kabupaten',
                          style: const TextStyle(fontSize: 11, color: AppColors.textSecondary),
                        ),
                      ],
                    ),
                  ),
                  IconButton(
                    icon: const Icon(LucideIcons.x, size: 20),
                    onPressed: () => Navigator.pop(ctx),
                  ),
                ],
              ),
              const Divider(height: 24),
              if (isPetugas) ...[
                _buildGuideCard(
                  step: '1',
                  title: 'Penerimaan SPK & Tinjauan Lokasi',
                  icon: LucideIcons.clipboardList,
                  color: AppColors.primary,
                  content:
                      '• Notifikasi tugas masuk otomatis ke tab **Tugas Aktif** setelah operator memverifikasi aduan warga.\n'
                      '• Tinjau klasifikasi kerusakan (**Kerusakan Ringan** vs **Kerusakan Berat**), estimasi volume, foto kondisi awal, dan batas waktu SLA.',
                ),
                const SizedBox(height: 12),
                _buildGuideCard(
                  step: '2',
                  title: 'Navigasi Rute Cepat GIS (OSRM)',
                  icon: LucideIcons.mapPin,
                  color: AppColors.primary,
                  content:
                      '• Buka tab **Peta GIS** atau gunakan navigasi pada kartu tugas untuk melihat rute jalan nyata menuju lokasi perbaikan.\n'
                      '• Koordinat GPS disinkronkan langsung dengan data Geofencing 15 kecamatan se-Kabupaten.',
                ),
                const SizedBox(height: 12),
                _buildGuideCard(
                  step: '3',
                  title: 'Mulai Pekerjaan, Batas SLA & Kuota 3 Tugas',
                  icon: LucideIcons.play,
                  color: AppColors.primary,
                  content:
                      '• Saat tiba di lokasi, tekan tombol **"Mulai Kerja Lapangan"** agar status aduan terupdate secara real-time.\n'
                      '• Batas Waktu Tindak Lanjut (SLA): Lubang jalan maksimal **48 Jam**, darurat jembatan maksimal **24 Jam**, lampu jalan PJU maksimal **72 Jam**.\n'
                      '• **Aturan Kuota:** Setiap regu dibatasi maksimal **3 tugas aktif** secara bersamaan demi menjaga mutu hasil fisik.',
                ),
                const SizedBox(height: 12),
                _buildGuideCard(
                  step: '4',
                  title: 'Isolasi Hak Regu & Unggah Bukti Selesai',
                  icon: LucideIcons.camera,
                  color: AppColors.primary,
                  content:
                      '• **Isolasi Hak Akses (Team Shield):** Hanya petugas dari regu yang ditugaskan resmi yang berhak membuka kamera pengerjaan dan mengirim bukti.\n'
                      '• Setelah pekerjaan fisik selesai, tekan **"Selesai + Kirim Bukti"**.\n'
                      '• Ambil foto hasil akhir yang jelas & rata di lokasi perbaikan, lalu sertakan catatan teknis material (misal: hotmix AC-WC tebal 4cm tuntas).',
                ),
                const SizedBox(height: 12),
                _buildGuideCard(
                  step: '5',
                  title: 'Validasi Mutu Operator & Riwayat Selesai',
                  icon: LucideIcons.checkCircle2,
                  color: AppColors.primary,
                  content:
                      '• Pekerjaan lapangan tidak langsung selesai otomatis, melainkan masuk tahap **Menunggu Validasi** untuk diverifikasi Operator Dinas PUPR.\n'
                      '• Setelah disetujui, status menjadi **Tuntas** dan tugas berpindah ke tab **Riwayat Selesai** sebagai portofolio resmi kinerja Anda.',
                ),
                const SizedBox(height: 12),
                _buildGuideCard(
                  step: '6',
                  title: 'Konsultasi dengan Asisten Teknis SI-APPIK',
                  icon: LucideIcons.bot,
                  color: AppColors.primary,
                  content:
                      '• Manfaatkan tab **Asisten Teknis SI-APPIK** kapan saja untuk panduan SOP teknis, kode OTP, ganti password mandiri, hingga pelacakan tiket SPK.\n'
                      '• Asisten cerdas siap siaga 24 jam mendukung kelancaran operasional lapangan Anda.',
                ),
              ] else ...[
                _buildGuideCard(
                  step: '1',
                  title: 'Pilih Metode & Tipe Pelaporan',
                  icon: LucideIcons.route,
                  color: AppColors.primary,
                  content:
                      '• Tekan **+ Buat Aduan Baru** lalu pilih **Analisis AI Cerdas** (deteksi otomatis) atau **Lapor Manual**.\n'
                      '• **Titik (Ringan < 500m):** Untuk lubang jalan tunggal, lampu jalan PJU mati, atau fasilitas umum (1 titik koordinat).\n'
                      '• **Ruas Jalan (Berat ≥ 500m):** Khusus jalan rusak parah berskala panjang. Tentukan **Titik Awal** dan **Titik Akhir** di peta.',
                ),
                const SizedBox(height: 12),
                _buildGuideCard(
                  step: '2',
                  title: 'Foto Kerusakan & AI Computer Vision',
                  icon: LucideIcons.scanLine,
                  color: AppColors.primary,
                  content:
                      '• Ambil foto di tempat terang langsung menghadap objek kerusakan jalan atau fasilitas umum.\n'
                      '• AI YOLOv8 otomatis memverifikasi lubang jalan (pothole), retakan jalan, lampu penerangan jalan umum, dan kondisi permukaan jalan.\n'
                      '• Hindari foto selfie, wajah orang, hewan, atau makanan agar foto tidak ditolak oleh validasi AI.',
                ),
                const SizedBox(height: 12),
                _buildGuideCard(
                  step: '3',
                  title: 'Pastikan di Wilayah Kabupaten',
                  icon: LucideIcons.mapPin,
                  color: AppColors.primary,
                  content:
                      '• Layanan ini mencakup seluruh 15 kecamatan di Kabupaten (Lasusua, Wawo, Ngapa, Kodeoha, Pakue, Katoi, Ranteangin, dll).\n'
                      '• Sistem Geofencing otomatis memvalidasi koordinat agar dinas teknis dapat langsung menindaklanjuti secara presisi.',
                ),
                const SizedBox(height: 12),
                _buildGuideCard(
                  step: '4',
                  title: 'Pantau Progres & Waktu Penanganan (SLA)',
                  icon: LucideIcons.clock,
                  color: AppColors.primary,
                  content:
                      '• Operator memverifikasi data laporan dalam **< 2 jam kerja**.\n'
                      '• Pekerja lapangan Dinas PUPR menyelesaikan lubang jalan maksimal **48 jam** dan jembatan kritis maksimal **24 jam**.\n'
                      '• Anda akan menerima notifikasi status secara berkala: **Dikerjakan**, **Menunggu Validasi**, hingga **Tuntas**.',
                ),
                const SizedBox(height: 12),
                _buildGuideCard(
                  step: '5',
                  title: 'Gunakan Asisten SI-APPIK',
                  icon: LucideIcons.bot,
                  color: AppColors.primary,
                  content:
                      '• Buka tab **Asisten SI-APPIK** kapan saja jika butuh panduan aplikasi, cara verifikasi kode OTP email, lupa kata sandi, atau tips foto AI.\n'
                      '• Ketik nomor tiket (misal: **0005/ringan/2026**) di chatbot untuk mengecek posisi penanganan laporan Anda secara real-time!',
                ),
              ],
              const SizedBox(height: 20),
              ElevatedButton(
                onPressed: () => Navigator.pop(ctx),
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppColors.primary,
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(vertical: 12),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                ),
                child: const Text('Mengerti Panduan'),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildGuideCard({
    required String step,
    required String title,
    required IconData icon,
    required Color color,
    required String content,
  }) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Colors.grey.shade50,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: AppColors.border),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                width: 24,
                height: 24,
                alignment: Alignment.center,
                decoration: BoxDecoration(color: color, shape: BoxShape.circle),
                child: Text(
                  step,
                  style: GoogleFonts.outfit(
                    color: Colors.white,
                    fontSize: 12,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Text(
                  title,
                  style: GoogleFonts.outfit(
                    fontWeight: FontWeight.w600,
                    fontSize: 13,
                    color: AppColors.textPrimary,
                  ),
                ),
              ),
              Icon(icon, color: color, size: 18),
            ],
          ),
          const SizedBox(height: 10),
          FormattedMarkdownText(
            content,
            style: GoogleFonts.inter(
              fontSize: 12,
              color: AppColors.textSecondary,
              height: 1.45,
            ),
            boldStyle: GoogleFonts.inter(
              fontSize: 12,
              fontWeight: FontWeight.w700,
              color: AppColors.textPrimary,
              height: 1.45,
            ),
          ),
        ],
      ),
    );
  }

  // 5. Pengaturan Notifikasi Modal
  Future<void> _showNotificationSettingsModal() async {
    final isPetugas = _user?.role == 'PETUGAS';
    final prefs = await SharedPreferences.getInstance();
    bool notifStatus = prefs.getBool('notif_complaint_status') ?? true;
    bool notifField = prefs.getBool('notif_field_assignment') ?? true;
    bool notifGov = prefs.getBool('notif_gov_announcements') ?? true;
    bool notifSound = prefs.getBool('notif_sound') ?? true;
    bool notifVibrate = prefs.getBool('notif_vibration') ?? true;

    if (!mounted) return;

    await showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (ctx) => StatefulBuilder(
        builder: (context, setSheetState) => SafeArea(
          child: Padding(
            padding: EdgeInsets.only(
              left: 20,
              right: 20,
              top: 20,
              bottom: MediaQuery.of(ctx).viewInsets.bottom + 24,
            ),
            child: SingleChildScrollView(
              physics: const BouncingScrollPhysics(),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(8),
                        decoration: const BoxDecoration(
                          color: Color(0xFFEFF6FF),
                          borderRadius: BorderRadius.all(Radius.circular(10)),
                        ),
                        child: Icon(isPetugas ? LucideIcons.bellRing : LucideIcons.bellRing, color: AppColors.primary, size: 22),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              isPetugas ? 'Pengaturan Notifikasi Petugas' : 'Pengaturan Notifikasi',
                              style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
                            ),
                            Text(
                              isPetugas ? 'Pemberitahuan penugasan SPK & instruksi dinas' : 'Atur notifikasi dan getar pada aplikasi',
                              style: const TextStyle(fontSize: 11, color: AppColors.textSecondary),
                            ),
                          ],
                        ),
                      ),
                      IconButton(
                        icon: const Icon(LucideIcons.x, size: 20),
                        onPressed: () => Navigator.pop(ctx),
                      ),
                    ],
                  ),
                  const Divider(height: 20),
                  if (isPetugas) ...[
                    SwitchListTile(
                      contentPadding: EdgeInsets.zero,
                      activeThumbColor: AppColors.primary,
                      activeTrackColor: AppColors.primaryLight.withValues(alpha: 0.3),
                      title: const Text('Perintah Kerja (SPK) Baru', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
                      subtitle: const Text('Pemberitahuan saat operator menugaskan perbaikan baru', style: TextStyle(fontSize: 11, color: AppColors.textSecondary)),
                      value: notifField,
                      onChanged: (val) async {
                        setSheetState(() => notifField = val);
                        await prefs.setBool('notif_field_assignment', val);
                      },
                    ),
                    SwitchListTile(
                      contentPadding: EdgeInsets.zero,
                      activeThumbColor: AppColors.primary,
                      activeTrackColor: AppColors.primaryLight.withValues(alpha: 0.3),
                      title: const Text('Validasi & Evaluasi Operator', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
                      subtitle: const Text('Pemberitahuan saat foto hasil kerja disetujui atau perlu revisi', style: TextStyle(fontSize: 11, color: AppColors.textSecondary)),
                      value: notifStatus,
                      onChanged: (val) async {
                        setSheetState(() => notifStatus = val);
                        await prefs.setBool('notif_complaint_status', val);
                      },
                    ),
                    SwitchListTile(
                      contentPadding: EdgeInsets.zero,
                      activeThumbColor: AppColors.primary,
                      activeTrackColor: AppColors.primaryLight.withValues(alpha: 0.3),
                      title: const Text('Instruksi Teknis Dinas', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
                      subtitle: const Text('Arahan keselamatan & pengumuman resmi pimpinan dinas', style: TextStyle(fontSize: 11, color: AppColors.textSecondary)),
                      value: notifGov,
                      onChanged: (val) async {
                        setSheetState(() => notifGov = val);
                        await prefs.setBool('notif_gov_announcements', val);
                      },
                    ),
                  ] else ...[
                    SwitchListTile(
                      contentPadding: EdgeInsets.zero,
                      activeThumbColor: AppColors.primary,
                      activeTrackColor: AppColors.primaryLight.withValues(alpha: 0.3),
                      title: const Text('Status Laporan & Tiket', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
                      subtitle: const Text('Pemberitahuan saat laporan diverifikasi atau selesai', style: TextStyle(fontSize: 11, color: AppColors.textSecondary)),
                      value: notifStatus,
                      onChanged: (val) async {
                        setSheetState(() => notifStatus = val);
                        await prefs.setBool('notif_complaint_status', val);
                      },
                    ),
                    SwitchListTile(
                      contentPadding: EdgeInsets.zero,
                      activeThumbColor: AppColors.primary,
                      activeTrackColor: AppColors.primaryLight.withValues(alpha: 0.3),
                      title: const Text('Tindak Lanjut Petugas', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
                      subtitle: const Text('Pemberitahuan saat petugas dinas mulai bekerja di lokasi', style: TextStyle(fontSize: 11, color: AppColors.textSecondary)),
                      value: notifField,
                      onChanged: (val) async {
                        setSheetState(() => notifField = val);
                        await prefs.setBool('notif_field_assignment', val);
                      },
                    ),
                    SwitchListTile(
                      contentPadding: EdgeInsets.zero,
                      activeThumbColor: AppColors.primary,
                      activeTrackColor: AppColors.primaryLight.withValues(alpha: 0.3),
                      title: const Text('Pengumuman Dinas & Sistem', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
                      subtitle: const Text('Informasi resmi dinas teknis Kabupaten', style: TextStyle(fontSize: 11, color: AppColors.textSecondary)),
                      value: notifGov,
                      onChanged: (val) async {
                        setSheetState(() => notifGov = val);
                        await prefs.setBool('notif_gov_announcements', val);
                      },
                    ),
                  ],
                  SwitchListTile(
                    contentPadding: EdgeInsets.zero,
                    activeThumbColor: AppColors.primary,
                    activeTrackColor: AppColors.primaryLight.withValues(alpha: 0.3),
                    title: const Text('Suara Notifikasi', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
                    subtitle: const Text('Bunyikan nada saat ada pesan baru', style: TextStyle(fontSize: 11, color: AppColors.textSecondary)),
                    value: notifSound,
                    onChanged: (val) async {
                      setSheetState(() => notifSound = val);
                      await prefs.setBool('notif_sound', val);
                    },
                  ),
                  SwitchListTile(
                    contentPadding: EdgeInsets.zero,
                    activeThumbColor: AppColors.primary,
                    activeTrackColor: AppColors.primaryLight.withValues(alpha: 0.3),
                    title: const Text('Getar Perangkat', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
                    subtitle: const Text('Getarkan perangkat saat notifikasi diterima', style: TextStyle(fontSize: 11, color: AppColors.textSecondary)),
                    value: notifVibrate,
                    onChanged: (val) async {
                      setSheetState(() => notifVibrate = val);
                      await prefs.setBool('notif_vibration', val);
                    },
                  ),
                  const SizedBox(height: 16),
                  SizedBox(
                    width: double.infinity,
                    child: ElevatedButton(
                      onPressed: () {
                        Navigator.pop(ctx);
                        showV2Toast(
                          context,
                          message: 'Pengaturan notifikasi berhasil disimpan.',
                          type: V2ToastType.success,
                        );
                      },
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppColors.primary,
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      ),
                      child: const Text('Simpan Pengaturan', style: TextStyle(fontWeight: FontWeight.bold)),
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

  // 6. Kontak Darurat Kabupaten
  void _showEmergencyContactsDialog() {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(8),
              decoration: const BoxDecoration(
                color: Color(0xFFEFF6FF),
                borderRadius: BorderRadius.all(Radius.circular(10)),
              ),
              child: const Icon(LucideIcons.phoneCall, color: AppColors.primary, size: 20),
            ),
            const SizedBox(width: 10),
            const Expanded(
              child: Text(
                'Kontak Darurat Kabupaten',
                style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
              ),
            ),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Layanan Cepat Tanggap Darurat Kabupaten:',
              style: TextStyle(fontSize: 12, color: AppColors.textSecondary),
            ),
            const SizedBox(height: 12),
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: const Color(0xFFEFF6FF),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: const Color(0xFFBFDBFE)),
              ),
              child: const Row(
                children: [
                  Icon(LucideIcons.phone, color: AppColors.primary, size: 18),
                  SizedBox(width: 10),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text('Call Center 112', style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: AppColors.primary)),
                        Text('24 Jam Bebas Pulsa (Darurat)', style: TextStyle(fontSize: 11, color: AppColors.textSecondary)),
                      ],
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 10),
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: const Color(0xFFF8FAFC),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: AppColors.border),
              ),
              child: const Row(
                children: [
                  Icon(LucideIcons.headset, color: AppColors.primary, size: 18),
                  SizedBox(width: 10),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text('Dinas PUPR (Operator)', style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: AppColors.textPrimary)),
                        Text('(0405) 2321100 - Kantor Dinas PUPR', style: TextStyle(fontSize: 11, color: AppColors.textSecondary)),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Tutup', style: TextStyle(color: AppColors.primary, fontWeight: FontWeight.bold)),
          ),
        ],
      ),
    );
  }

  // 7. Dialog Konfigurasi Server
  Future<void> _showServerConfigDialog() async {
    final controller = TextEditingController(text: _currentBaseUrl);
    await showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Text('Pengaturan Server API', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Sesuaikan alamat backend Laravel jika menggunakan Wi-Fi atau kabel USB:',
              style: TextStyle(fontSize: 12, color: AppColors.textSecondary),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: controller,
              decoration: InputDecoration(
                labelText: 'Base URL API',
                hintText: 'https://aduan.aispektra.com/api/v1',
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
              ),
            ),
            const SizedBox(height: 8),
            const Text(
              'Production Utama: https://aduan.aispektra.com/api/v1\nTailscale VPN: http://100.86.30.42:8000/api/v1\nUSB adb reverse: http://127.0.0.1:8000/api/v1',
              style: TextStyle(fontSize: 11, color: AppColors.primaryLight),
            ),
          ],
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Batal')),
          ElevatedButton(
            onPressed: () async {
              final newUrl = controller.text.trim();
              if (newUrl.isNotEmpty) {
                await AppConfig.setBaseUrl(newUrl);
                setState(() => _currentBaseUrl = newUrl);
                if (ctx.mounted) Navigator.pop(ctx);
                if (mounted) {
                  showV2Toast(
                    context,
                    message: 'Alamat server berhasil diperbarui!',
                    type: V2ToastType.success,
                  );
                }
              }
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.primary,
              foregroundColor: Colors.white,
            ),
            child: const Text('Simpan'),
          ),
        ],
      ),
    );
  }

  // 8. Handler Keluar Akun
  Future<void> _handleLogout() async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Text('Konfirmasi Keluar'),
        content: const Text('Apakah Anda yakin ingin keluar dari sesi akun ini?'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Batal')),
          ElevatedButton(
            onPressed: () => Navigator.pop(ctx, true),
            style: ElevatedButton.styleFrom(backgroundColor: AppColors.primary, foregroundColor: Colors.white),
            child: const Text('Keluar'),
          ),
        ],
      ),
    );

    if (confirm == true) {
      await _authService.logout();
      if (!mounted) return;
      Navigator.of(context).pushAndRemoveUntil(
        MaterialPageRoute(builder: (_) => const LoginScreen()),
        (route) => false,
      );
    }
  }

  String? _resolveAvatarNetworkUrl(String? raw) {
    if (raw == null || raw.isEmpty) return null;
    if (raw.startsWith('http://') || raw.startsWith('https://')) return raw;
    if (raw.startsWith('/storage/') || raw.startsWith('storage/')) {
      final origin = _currentBaseUrl.replaceAll(RegExp(r'/api/v1/?$'), '');
      final cleanPath = raw.startsWith('/') ? raw : '/$raw';
      return '$origin$cleanPath';
    }
    return null;
  }

  bool _canReadLocalFile(String? path) {
    if (kIsWeb || path == null || path.isEmpty) return false;
    try {
      return File(path).existsSync();
    } catch (_) {
      return false;
    }
  }

  Widget _buildAvatarWidget({double size = 32}) {
    final isGuest = _user?.role == 'TAMU' || _user?.isAnonymous == true;
    final avatar = _user?.avatar;

    if (avatar != null && avatar.isNotEmpty) {
      final netUrl = _resolveAvatarNetworkUrl(avatar);
      if (netUrl != null) {
        return CircleAvatar(
          radius: size,
          backgroundImage: NetworkImage(netUrl),
        );
      } else if (_canReadLocalFile(avatar)) {
        return CircleAvatar(
          radius: size,
          backgroundImage: FileImage(File(avatar)),
        );
      }
    }

    return CircleAvatar(
      radius: size,
      backgroundColor: isGuest ? Colors.blueGrey : AppColors.primary,
      child: Icon(
        isGuest ? LucideIcons.userX : LucideIcons.userCheck,
        color: Colors.white,
        size: size,
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final isGuest = _user?.role == 'TAMU' || _user?.isAnonymous == true;
    final isPetugas = _user?.role == 'PETUGAS';

    return Scaffold(
      backgroundColor: AppColors.background,
      body: SafeArea(
        bottom: false,
        child: Column(
          children: [
            V2FloatingHeader(
              title: 'Profil & Akun',
              subtitle: isPetugas ? 'Pekerja Lapangan Dinas PUPR' : 'Pengguna SI-APPIK Kabupaten',
              leading: Container(
                width: 36,
                height: 36,
                decoration: BoxDecoration(
                  color: AppColors.primaryContainer,
                  borderRadius: BorderRadius.circular(11),
                  border: Border.all(color: AppColors.primaryBorder),
                ),
                child: const Icon(LucideIcons.user, size: 18, color: AppColors.primary),
              ),
            ),
            Expanded(
              child: ListView(
                padding: const EdgeInsets.fromLTRB(16, 16, 16, 110),
        children: [
          // Profile Header Card
          Container(
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(20),
              border: Border.all(color: AppColors.border),
            ),
            child: Row(
              children: [
                Stack(
                  children: [
                    GestureDetector(
                      onTap: _showAvatarPreviewDialog,
                      child: _buildAvatarWidget(size: 34),
                    ),
                    Positioned(
                      bottom: 0,
                      right: 0,
                      child: GestureDetector(
                        onTap: _showPhotoPickerSheet,
                        child: Container(
                          padding: const EdgeInsets.all(4),
                          decoration: const BoxDecoration(
                            color: AppColors.primary,
                            shape: BoxShape.circle,
                          ),
                          child: const Icon(LucideIcons.camera, color: Colors.white, size: 12),
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        _user?.name ?? (isPetugas ? 'Pekerja Lapangan' : 'Warga Tamu'),
                        style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        _user?.email ?? (isPetugas ? 'petugas@kabupaten.go.id' : 'tamu@kabupaten.go.id'),
                        style: const TextStyle(fontSize: 12, color: AppColors.textSecondary),
                      ),
                      const SizedBox(height: 6),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                        decoration: BoxDecoration(
                          color: isGuest
                              ? Colors.blueGrey.withValues(alpha: 0.1)
                              : const Color(0xFFEFF6FF),
                          borderRadius: BorderRadius.circular(6),
                          border: Border.all(
                            color: isGuest ? Colors.blueGrey.shade200 : const Color(0xFFBFDBFE),
                          ),
                        ),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Icon(
                              isPetugas
                                  ? LucideIcons.hardHat
                                  : (isGuest ? LucideIcons.userX : LucideIcons.badgeCheck),
                              size: 13,
                              color: isGuest ? Colors.blueGrey : AppColors.primary,
                            ),
                            const SizedBox(width: 4),
                            Text(
                              isPetugas
                                  ? 'Pekerja Lapangan Terdaftar'
                                  : (isGuest ? 'Mode Tamu / Anonim' : 'Warga Terdaftar'),
                              style: TextStyle(
                                fontSize: 11,
                                fontWeight: FontWeight.bold,
                                color: isGuest ? Colors.blueGrey : AppColors.primary,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),

          const SizedBox(height: 20),

          // Akun & Keamanan Section
          const Text('Manajemen Akun & Pengaturan', style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: AppColors.textPrimary)),
          const SizedBox(height: 10),

          Material(
            color: Colors.white,
            borderRadius: BorderRadius.circular(16),
            clipBehavior: Clip.antiAlias,
            child: Container(
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(16),
                border: Border.all(color: AppColors.border),
              ),
              child: Column(
                children: [
                  ListTile(
                    leading: Container(
                      padding: const EdgeInsets.all(8),
                      decoration: const BoxDecoration(
                        color: AppColors.primaryContainer,
                        borderRadius: BorderRadius.all(Radius.circular(10)),
                      ),
                      child: Icon(isPetugas ? LucideIcons.idCard : LucideIcons.idCard, color: AppColors.primary, size: 18),
                    ),
                    title: const Text('Preview Akun', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
                    subtitle: Text(
                      isPetugas ? 'Lihat kartu identitas Pekerja Lapangan' : 'Lihat kartu identitas Warga Terdaftar',
                      style: const TextStyle(fontSize: 11, color: AppColors.textSecondary),
                    ),
                    trailing: const Icon(LucideIcons.chevronRight, size: 18),
                    onTap: _showAccountPreviewModal,
                  ),
                  if (isPetugas && _myTeam != null) ...[
                    const Divider(height: 1),
                    ListTile(
                      leading: Container(
                        padding: const EdgeInsets.all(8),
                        decoration: const BoxDecoration(
                          color: AppColors.primaryContainer,
                          borderRadius: BorderRadius.all(Radius.circular(10)),
                        ),
                        child: const Icon(LucideIcons.users, color: AppColors.primary, size: 18),
                      ),
                      title: const Text('Detail Regu Lapangan', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
                      subtitle: Text(
                        '${_myTeam!['code'] ?? 'REGU'} · ${_myTeam!['name'] ?? 'Regu Lapangan'}',
                        style: const TextStyle(fontSize: 11, color: AppColors.textSecondary),
                      ),
                      trailing: const Icon(LucideIcons.chevronRight, size: 18),
                      onTap: _showTeamDetailModal,
                    ),
                  ],
                  const Divider(height: 1),
                  ListTile(
                    leading: Container(
                      padding: const EdgeInsets.all(8),
                      decoration: const BoxDecoration(
                        color: AppColors.primaryContainer,
                        borderRadius: BorderRadius.all(Radius.circular(10)),
                      ),
                      child: const Icon(LucideIcons.userCog, color: AppColors.primary, size: 18),
                    ),
                    title: const Text('Ubah Profil & Kata Sandi', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
                    subtitle: const Text('Ganti username, email, dan kata sandi', style: TextStyle(fontSize: 11, color: AppColors.textSecondary)),
                    trailing: const Icon(LucideIcons.chevronRight, size: 18),
                    onTap: _showEditProfileModal,
                  ),
                  const Divider(height: 1),
                  ListTile(
                    leading: Container(
                      padding: const EdgeInsets.all(8),
                      decoration: const BoxDecoration(
                        color: AppColors.primaryContainer,
                        borderRadius: BorderRadius.all(Radius.circular(10)),
                      ),
                      child: Icon(isPetugas ? LucideIcons.bookOpenCheck : LucideIcons.bookOpenCheck, color: AppColors.primary, size: 18),
                    ),
                    title: const Text('Panduan Penggunaan Aplikasi', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
                    subtitle: Text(
                      isPetugas
                          ? 'SOP kerja lapangan, batas SLA, & foto bukti'
                          : 'Tips lapor kerusakan ringan vs berat & foto AI',
                      style: const TextStyle(fontSize: 11, color: AppColors.textSecondary),
                    ),
                    trailing: const Icon(LucideIcons.chevronRight, size: 18),
                    onTap: _showAppGuideModal,
                  ),
                  const Divider(height: 1),
                  ListTile(
                    leading: Container(
                      padding: const EdgeInsets.all(8),
                      decoration: const BoxDecoration(
                        color: AppColors.primaryContainer,
                        borderRadius: BorderRadius.all(Radius.circular(10)),
                      ),
                      child: const Icon(LucideIcons.bellRing, color: AppColors.primary, size: 18),
                    ),
                    title: const Text('Pengaturan Notifikasi', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
                    subtitle: Text(
                      isPetugas
                          ? 'Pemberitahuan SPK baru, evaluasi, suara, & getar'
                          : 'Pemberitahuan status laporan, suara, & getar',
                      style: const TextStyle(fontSize: 11, color: AppColors.textSecondary),
                    ),
                    trailing: const Icon(LucideIcons.chevronRight, size: 18),
                    onTap: _showNotificationSettingsModal,
                  ),
                ],
              ),
            ),
          ),
          const SizedBox(height: 20),

          // Layanan & Darurat Section (Kontak Darurat disembunyikan untuk role Petugas)
          const Text('Bantuan & Sistem', style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: AppColors.textPrimary)),
          const SizedBox(height: 10),

          Material(
            color: Colors.white,
            borderRadius: BorderRadius.circular(16),
            clipBehavior: Clip.antiAlias,
            child: Container(
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(16),
                border: Border.all(color: AppColors.border),
              ),
              child: Column(
                children: [
                  if (!isPetugas) ...[
                    ListTile(
                      leading: Container(
                        padding: const EdgeInsets.all(8),
                        decoration: const BoxDecoration(
                          color: AppColors.primaryContainer,
                          borderRadius: BorderRadius.all(Radius.circular(10)),
                        ),
                        child: const Icon(LucideIcons.phoneCall, color: AppColors.primary, size: 18),
                      ),
                      title: const Text('Kontak Darurat Kabupaten', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
                      subtitle: const Text('Call Center 112 & Dinas PUPR', style: TextStyle(fontSize: 11, color: AppColors.textSecondary)),
                      trailing: const Icon(LucideIcons.chevronRight, size: 18),
                      onTap: _showEmergencyContactsDialog,
                    ),
                    const Divider(height: 1),
                  ],
                  ListTile(
                    leading: Container(
                      padding: const EdgeInsets.all(8),
                      decoration: const BoxDecoration(
                        color: AppColors.primaryContainer,
                        borderRadius: BorderRadius.all(Radius.circular(10)),
                      ),
                      child: const Icon(LucideIcons.server, color: AppColors.primary, size: 18),
                    ),
                    title: const Text('Alamat Server API', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
                    subtitle: Text(_currentBaseUrl, style: const TextStyle(fontSize: 11, color: AppColors.textSecondary)),
                    trailing: const Icon(LucideIcons.chevronRight, size: 18),
                    onTap: _showServerConfigDialog,
                  ),
                ],
              ),
            ),
          ),
          const SizedBox(height: 24),

          // Logout Button
          ElevatedButton.icon(
            onPressed: _handleLogout,
            icon: const Icon(LucideIcons.logOut, size: 18),
            label: Text(isGuest ? 'Masuk dengan Akun Terdaftar' : 'Keluar dari Akun', style: const TextStyle(fontWeight: FontWeight.bold)),
            style: ElevatedButton.styleFrom(
              backgroundColor: isGuest ? AppColors.primary : const Color(0xFFFEF2F2),
              foregroundColor: isGuest ? Colors.white : AppColors.statusRejected,
              elevation: 0,
              padding: const EdgeInsets.symmetric(vertical: 14),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(14),
                side: BorderSide(color: isGuest ? AppColors.primary : const Color(0xFFFECACA)),
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


}
