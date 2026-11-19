import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:google_sign_in/google_sign_in.dart';
import 'package:lucide_icons_flutter/lucide_icons.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../../core/auth/auth_service.dart';
import '../../../core/config/app_config.dart';
import '../../../shared/theme/app_colors.dart';
import '../../../shared/widgets/v2_toast.dart';
import '../../navigation/presentation/main_navigation_screen.dart';
import 'register_screen.dart';
import 'widgets/auth_ui_helpers.dart';

class LoginScreen extends StatefulWidget {
  final String? initialErrorMessage;

  const LoginScreen({super.key, this.initialErrorMessage});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _formKey = GlobalKey<FormState>();
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  final _authService = AuthService();

  static const String _googleWebClientId = '548526888002-iku6euuas29lupp0o9mcrbpnib0lcd48.apps.googleusercontent.com';

  bool _obscurePassword = true;
  bool _isLoading = false;
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    if (widget.initialErrorMessage != null && widget.initialErrorMessage!.isNotEmpty) {
      _errorMessage = widget.initialErrorMessage;
      WidgetsBinding.instance.addPostFrameCallback((_) {
        if (mounted) {
          _showDeactivatedDialog(widget.initialErrorMessage!);
        }
      });
    }
  }

  void _showDeactivatedDialog(String message) {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(18)),
        title: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: Colors.red.shade50,
                borderRadius: BorderRadius.circular(10),
              ),
              child: Icon(LucideIcons.shieldOff, color: Colors.red.shade700, size: 22),
            ),
            const SizedBox(width: 10),
            Expanded(
              child: Text(
                'Akun Dinonaktifkan',
                style: GoogleFonts.outfit(
                  fontSize: 16,
                  fontWeight: FontWeight.w700,
                  color: AppColors.textPrimary,
                ),
              ),
            ),
          ],
        ),
        content: Text(
          message,
          style: GoogleFonts.inter(fontSize: 13, color: AppColors.textSecondary, height: 1.5),
        ),
        actions: [
          ElevatedButton(
            onPressed: () => Navigator.of(ctx).pop(),
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.primary,
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
            ),
            child: Text('Mengerti', style: GoogleFonts.inter(fontWeight: FontWeight.w600)),
          ),
        ],
      ),
    );
  }

  @override
  void dispose() {
    _emailController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  Future<void> _handleLogin() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    final result = await _authService.login(
      email: _emailController.text.trim(),
      password: _passwordController.text,
    );

    if (!mounted) return;

    setState(() {
      _isLoading = false;
    });

    if (result['success'] == true) {
      final user = await _authService.getCurrentUser();
      final role = (user?.role ?? '').toUpperCase();

      // Peran khusus website tidak boleh login di aplikasi mobile
      if (role == 'ADMIN' || role == 'SUPERADMIN' || role == 'OPERATOR' || role == 'KEPALA_DAERAH' || role == 'BUPATI' || role == 'DINAS') {
        await _authService.logout();
        if (!mounted) return;
        _showWebOnlyDialog(role);
        return;
      }

      if (!mounted) return;
      Navigator.of(context).pushReplacement(
        MaterialPageRoute(builder: (_) => const MainNavigationScreen()),
      );
    } else {
      final msg = result['message']?.toString() ?? 'Login gagal. Periksa kembali email dan password.';
      setState(() {
        _errorMessage = msg;
      });
      if (result['account_deactivated'] == true && mounted) {
        _showDeactivatedDialog(msg);
      }
    }
  }

  Future<void> _handleGoogleSignIn() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final googleSignIn = GoogleSignIn(
        serverClientId: _googleWebClientId,
        scopes: ['email', 'profile'],
      );

      // Selalu munculkan dialog pemilihan akun Google (tidak auto login ke akun sebelumnya)
      try {
        await googleSignIn.signOut();
      } catch (_) {}

      final GoogleSignInAccount? account = await googleSignIn.signIn();
      if (account == null) {
        // Pengguna membatalkan dialog Google
        if (mounted) setState(() => _isLoading = false);
        return;
      }

      final GoogleSignInAuthentication auth = await account.authentication;
      final String? idToken = auth.idToken;

      if (idToken == null || idToken.isEmpty) {
        if (mounted) {
          setState(() {
            _isLoading = false;
            _errorMessage = 'Gagal memperoleh idToken dari Google. Pastikan OAuth Client ID Android sudah dibuat di Google Cloud Console.';
          });
        }
        return;
      }

      final result = await _authService.loginWithGoogle(idToken);

      if (!mounted) return;
      setState(() => _isLoading = false);

      if (result['success'] == true) {
        final isNewUser = result['is_new_user'] == true;

        if (isNewUser) {
          // Akun baru pertama kali daftar: munculkan izin akses akun Google terlebih dahulu
          final bool? agreed = await _showGoogleAccessConsentModal(account.email);
          if (!mounted) return;

          if (agreed == true) {
            // Setelah setuju, baru masuk ke halaman warga dengan status wajib lengkapi profil
            Navigator.of(context).pushReplacement(
              MaterialPageRoute(
                builder: (_) => const MainNavigationScreen(
                  showGoogleProfileNotice: true,
                ),
              ),
            );
          } else {
            // Jika membatalkan, batalkan sesi
            await _authService.logout();
          }
        } else {
          // Akun sudah pernah terdaftar: langsung masuk tanpa pemberitahuan izin
          Navigator.of(context).pushReplacement(
            MaterialPageRoute(
              builder: (_) => const MainNavigationScreen(
                showGoogleProfileNotice: false,
              ),
            ),
          );
        }
      } else {
        final msg = result['message']?.toString() ?? 'Login dengan Akun Google gagal diverifikasi oleh server.';
        setState(() {
          _errorMessage = msg;
        });
        if (result['account_deactivated'] == true && mounted) {
          _showDeactivatedDialog(msg);
        }
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _isLoading = false;
          _errorMessage = 'Terjadi kesalahan saat masuk dengan Google: $e';
        });
      }
    }
  }

  void _showWebOnlyDialog(String role) async {
    String roleLabel = 'Pimpinan / Operator';
    final roleUpper = role.toUpperCase();
    if (roleUpper == 'ADMIN' || roleUpper == 'SUPERADMIN') {
      roleLabel = 'Super Administrator';
    } else if (roleUpper == 'OPERATOR') {
      roleLabel = 'Operator Verifikasi';
    } else if (roleUpper == 'KEPALA_DAERAH' || roleUpper == 'BUPATI') {
      roleLabel = 'Kepala Daerah Kabupaten';
    }

    final baseUrl = await AppConfig.getBaseUrl();
    final cleanBase = baseUrl.replaceAll('/api/v1', '');
    final webUrl = '$cleanBase/login';

    if (!mounted) return;

    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: AppColors.primary.withValues(alpha: 0.1),
                borderRadius: BorderRadius.circular(10),
              ),
              child: const Icon(LucideIcons.monitor, color: AppColors.primary, size: 22),
            ),
            const SizedBox(width: 10),
            const Expanded(
              child: Text(
                'Akses Khusus Website',
                style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
              ),
            ),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Akun dengan peran $roleLabel dikhususkan untuk mengoperasikan Pusat Kendali melalui Web Browser.',
              style: const TextStyle(fontSize: 13, color: AppColors.textPrimary, height: 1.4),
            ),
            const SizedBox(height: 12),
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: Colors.blue.shade50,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: Colors.blue.shade200),
              ),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Icon(LucideIcons.info, size: 18, color: AppColors.primary),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      'Aplikasi mobile dikhususkan untuk Warga Masyarakat dan Pekerja Lapangan PUPR.',
                      style: TextStyle(fontSize: 12, color: Colors.blue.shade900),
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
            child: const Text('Tutup', style: TextStyle(color: AppColors.textSecondary)),
          ),
          ElevatedButton.icon(
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.primary,
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
            ),
            icon: const Icon(LucideIcons.externalLink, size: 16),
            label: const Text('Buka Website'),
            onPressed: () async {
              Navigator.pop(ctx);
              final uri = Uri.parse(webUrl);
              try {
                await launchUrl(uri, mode: LaunchMode.externalApplication);
              } catch (_) {
                if (mounted) {
                  showV2Toast(
                    context,
                    message: 'Silakan buka di browser: $webUrl',
                    type: V2ToastType.info,
                  );
                }
              }
            },
          ),
        ],
      ),
    );
  }



  Future<bool?> _showGoogleAccessConsentModal(String email) {
    return showModalBottomSheet<bool>(
      context: context,
      isDismissible: false,
      enableDrag: false,
      isScrollControlled: true,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (ctx) => PopScope(
        canPop: false,
        child: SafeArea(
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 20),
            child: SingleChildScrollView(
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
                  Row(
                    children: [
                      Image.asset(
                        'assets/images/google_logo.png',
                        width: 24,
                        height: 24,
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              'Persetujuan Akses Akun Google',
                              style: GoogleFonts.outfit(
                                fontSize: 16,
                                fontWeight: FontWeight.bold,
                                color: AppColors.textPrimary,
                              ),
                            ),
                            Text(
                              email,
                              style: GoogleFonts.inter(fontSize: 12, color: AppColors.textSecondary),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 14),
                  Text(
                    'Dengan mendaftarkan akun baru menggunakan Google, Anda menyetujui pemberian izin kepada sistem SI-APPIK untuk mengakses dan mengelola informasi berikut dari akun Google Anda:',
                    style: GoogleFonts.inter(fontSize: 12.5, color: AppColors.textSecondary, height: 1.45),
                  ),
                  const SizedBox(height: 14),
                  Container(
                    padding: const EdgeInsets.all(14),
                    decoration: BoxDecoration(
                      color: AppColors.background,
                      borderRadius: BorderRadius.circular(14),
                      border: Border.all(color: AppColors.border),
                    ),
                    child: Column(
                      children: [
                        Row(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Icon(LucideIcons.user, size: 16, color: AppColors.primary),
                            const SizedBox(width: 10),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    'Nama Lengkap Akun Google',
                                    style: GoogleFonts.inter(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.textPrimary),
                                  ),
                                  Text(
                                    'Digunakan sebagai identitas pendaftaran awal akun pengguna.',
                                    style: GoogleFonts.inter(fontSize: 11.5, color: AppColors.textSecondary),
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 12),
                        Row(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Icon(LucideIcons.mail, size: 16, color: AppColors.primary),
                            const SizedBox(width: 10),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    'Alamat Email Resmi',
                                    style: GoogleFonts.inter(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.textPrimary),
                                  ),
                                  Text(
                                    'Digunakan untuk otentikasi masuk, pengiriman notifikasi, dan verifikasi akun.',
                                    style: GoogleFonts.inter(fontSize: 11.5, color: AppColors.textSecondary),
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 12),
                        Row(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Icon(LucideIcons.shieldCheck, size: 16, color: AppColors.primary),
                            const SizedBox(width: 10),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    'Informasi Profil Dasar Lainnya',
                                    style: GoogleFonts.inter(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.textPrimary),
                                  ),
                                  Text(
                                    'Termasuk status verifikasi akun Google untuk memastikan keamanan pendaftaran akun.',
                                    style: GoogleFonts.inter(fontSize: 11.5, color: AppColors.textSecondary),
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 12),
                  Row(
                    crossAxisAlignment: CrossAxisAlignment.center,
                    children: [
                      const Icon(LucideIcons.lock, size: 13, color: AppColors.textSecondary),
                      const SizedBox(width: 6),
                      Expanded(
                        child: Text(
                          'Data Anda aman & dilindungi sesuai standar privasi Pemkab Kolaka Utara.',
                          style: GoogleFonts.inter(fontSize: 11, color: AppColors.textSecondary),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 18),
                  Row(
                    children: [
                      Expanded(
                        child: OutlinedButton(
                          onPressed: () => Navigator.pop(ctx, false),
                          style: OutlinedButton.styleFrom(
                            padding: const EdgeInsets.symmetric(vertical: 13),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                            side: const BorderSide(color: AppColors.border),
                          ),
                          child: Text('Batal', style: GoogleFonts.inter(color: AppColors.textSecondary, fontWeight: FontWeight.w600)),
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        flex: 2,
                        child: ElevatedButton(
                          onPressed: () => Navigator.pop(ctx, true),
                          style: ElevatedButton.styleFrom(
                            backgroundColor: AppColors.primary,
                            foregroundColor: Colors.white,
                            padding: const EdgeInsets.symmetric(vertical: 13),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                          ),
                          child: Text('Setujui & Lanjutkan', style: GoogleFonts.inter(fontWeight: FontWeight.bold, fontSize: 13)),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }

  void _showForgotPasswordModal() {
    final emailCtrl = TextEditingController(text: _emailController.text.trim());
    final otpCtrl = TextEditingController();
    final newPassCtrl = TextEditingController();
    final confirmPassCtrl = TextEditingController();
    final modalFormKey = GlobalKey<FormState>();
    int currentStep = 1;
    bool isSubmitting = false;
    String? modalError;
    String? modalSuccess;
    bool obscureNew = true;
    bool obscureConfirm = true;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (bCtx) => StatefulBuilder(
        builder: (context, setModalState) => Padding(
          padding: EdgeInsets.only(
            top: 20,
            left: 24,
            right: 24,
            bottom: MediaQuery.of(bCtx).viewInsets.bottom + 24,
          ),
          child: SingleChildScrollView(
            physics: const ClampingScrollPhysics(),
            child: Form(
              key: modalFormKey,
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  Center(
                    child: Container(
                      width: 40,
                      height: 4,
                      decoration: BoxDecoration(
                        color: AppColors.border,
                        borderRadius: BorderRadius.circular(4),
                      ),
                    ),
                  ),
                  const SizedBox(height: 16),

                  // Header Modal (Ikon + Judul + Tombol Tutup X)
                  Row(
                    children: [
                      Container(
                        width: 42,
                        height: 42,
                        decoration: BoxDecoration(
                          color: AppColors.primaryContainer,
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(color: AppColors.primaryBorder),
                        ),
                        child: const Icon(LucideIcons.keyRound, color: AppColors.primary, size: 20),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              'Atur Ulang Kata Sandi',
                              style: GoogleFonts.outfit(
                                fontSize: 17,
                                fontWeight: FontWeight.bold,
                                color: AppColors.textPrimary,
                              ),
                            ),
                            Text(
                              'Verifikasi OTP Resmi SI-APPIK',
                              style: GoogleFonts.inter(
                                fontSize: 11.5,
                                color: AppColors.textSecondary,
                              ),
                            ),
                          ],
                        ),
                      ),
                      InkWell(
                        onTap: () => Navigator.of(bCtx).pop(),
                        borderRadius: BorderRadius.circular(10),
                        child: Container(
                          width: 36,
                          height: 36,
                          decoration: BoxDecoration(
                            color: AppColors.cardSurface,
                            borderRadius: BorderRadius.circular(10),
                            border: Border.all(color: AppColors.border),
                          ),
                          child: const Icon(LucideIcons.x, size: 16, color: AppColors.textSecondary),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 14),

                  // Indikator Langkah 1 / 2
                  Align(
                    alignment: Alignment.centerLeft,
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                      decoration: BoxDecoration(
                        color: currentStep == 1
                            ? AppColors.primaryContainer
                            : AppColors.statusCompleted.withValues(alpha: 0.1),
                        borderRadius: BorderRadius.circular(99),
                        border: Border.all(
                          color: currentStep == 1
                              ? AppColors.primaryBorder
                              : AppColors.statusCompleted.withValues(alpha: 0.3),
                        ),
                      ),
                      child: Text(
                        currentStep == 1
                            ? 'Langkah 1 dari 2 • Verifikasi Email'
                            : 'Langkah 2 dari 2 • Buat Sandi Baru',
                        style: GoogleFonts.inter(
                          fontSize: 11,
                          fontWeight: FontWeight.w700,
                          color: currentStep == 1 ? AppColors.primary : AppColors.statusCompleted,
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(height: 12),

                  if (currentStep == 1)
                    Text(
                      'Masukkan alamat email terdaftar Anda untuk menerima kode OTP 6-digit.',
                      style: GoogleFonts.inter(
                        fontSize: 12.5,
                        color: AppColors.textSecondary,
                        height: 1.45,
                      ),
                    )
                  else
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                      decoration: BoxDecoration(
                        color: AppColors.cardSurface,
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: AppColors.border),
                      ),
                      child: Row(
                        children: [
                          Expanded(
                            child: Text(
                              'OTP dikirim ke ${emailCtrl.text.trim()}',
                              style: GoogleFonts.inter(fontSize: 12, color: AppColors.textSecondary),
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                          const SizedBox(width: 8),
                          GestureDetector(
                            onTap: isSubmitting
                                ? null
                                : () {
                                    setModalState(() {
                                      currentStep = 1;
                                      modalError = null;
                                      modalSuccess = null;
                                    });
                                  },
                            child: Text(
                              'Ganti',
                              style: GoogleFonts.inter(
                                fontSize: 12,
                                fontWeight: FontWeight.w700,
                                color: AppColors.primary,
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                  const SizedBox(height: 14),

                  if (modalError != null)
                    AuthStatusBanner(
                      message: modalError!,
                      icon: LucideIcons.alertCircle,
                      color: AppColors.statusRejected,
                    ),

                  if (modalSuccess != null)
                    AuthStatusBanner(
                      message: modalSuccess!,
                      icon: LucideIcons.checkCircle,
                      color: AppColors.statusCompleted,
                    ),

                  if (currentStep == 1) ...[
                    Text(
                      'Email Akun Terdaftar',
                      style: GoogleFonts.inter(
                        fontSize: 12.5,
                        fontWeight: FontWeight.w600,
                        color: AppColors.textPrimary,
                      ),
                    ),
                    const SizedBox(height: 6),
                    TextFormField(
                      controller: emailCtrl,
                      keyboardType: TextInputType.emailAddress,
                      style: GoogleFonts.inter(fontSize: 13.5, color: AppColors.textPrimary),
                      decoration: buildAuthInputDecoration(
                        hintText: 'nama@email.com',
                        prefixIcon: LucideIcons.mail,
                      ),
                      validator: (v) => (v == null || v.trim().isEmpty) ? 'Email wajib diisi' : null,
                    ),
                    const SizedBox(height: 18),
                    SizedBox(
                      height: 50,
                      child: ElevatedButton(
                        onPressed: isSubmitting
                            ? null
                            : () async {
                                if (!modalFormKey.currentState!.validate()) return;
                                setModalState(() {
                                  isSubmitting = true;
                                  modalError = null;
                                  modalSuccess = null;
                                });

                                final res = await _authService.sendForgotPasswordOtp(emailCtrl.text.trim());

                                if (!mounted) return;
                                setModalState(() {
                                  isSubmitting = false;
                                  if (res['success'] == true) {
                                    currentStep = 2;
                                    modalSuccess = res['message'] ?? 'Kode OTP telah dikirimkan ke email.';
                                  } else {
                                    modalError = res['message'] ?? 'Gagal mengirim kode OTP.';
                                  }
                                });
                              },
                        style: ElevatedButton.styleFrom(
                          backgroundColor: AppColors.primary,
                          foregroundColor: Colors.white,
                          elevation: 0,
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                        ),
                        child: isSubmitting
                            ? const SizedBox(
                                width: 20,
                                height: 20,
                                child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                              )
                            : Text(
                                'Kirim Kode OTP ke Email',
                                style: GoogleFonts.outfit(fontSize: 15, fontWeight: FontWeight.bold),
                              ),
                      ),
                    ),
                  ] else ...[
                    Text(
                      'Kode OTP 6 Digit',
                      style: GoogleFonts.inter(
                        fontSize: 12.5,
                        fontWeight: FontWeight.w600,
                        color: AppColors.textPrimary,
                      ),
                    ),
                    const SizedBox(height: 6),
                    TextFormField(
                      controller: otpCtrl,
                      keyboardType: TextInputType.number,
                      maxLength: 6,
                      style: GoogleFonts.inter(fontSize: 13.5, color: AppColors.textPrimary),
                      decoration: buildAuthInputDecoration(
                        hintText: 'Masukkan 6 digit kode OTP',
                        prefixIcon: LucideIcons.shieldCheck,
                      ).copyWith(counterText: ''),
                      validator: (v) => (v == null || v.trim().length != 6) ? 'Kode OTP harus 6 digit' : null,
                    ),
                    const SizedBox(height: 12),

                    Text(
                      'Kata Sandi Baru',
                      style: GoogleFonts.inter(
                        fontSize: 12.5,
                        fontWeight: FontWeight.w600,
                        color: AppColors.textPrimary,
                      ),
                    ),
                    const SizedBox(height: 6),
                    TextFormField(
                      controller: newPassCtrl,
                      obscureText: obscureNew,
                      style: GoogleFonts.inter(fontSize: 13.5, color: AppColors.textPrimary),
                      decoration: buildAuthInputDecoration(
                        hintText: 'Min. 8 karakter (huruf besar, kecil, angka)',
                        prefixIcon: LucideIcons.lock,
                        suffixIcon: IconButton(
                          icon: Icon(
                            obscureNew ? LucideIcons.eyeOff : LucideIcons.eye,
                            size: 18,
                            color: AppColors.textSecondary,
                          ),
                          onPressed: () => setModalState(() => obscureNew = !obscureNew),
                        ),
                      ),
                      validator: (v) {
                        if (v == null || v.length < 8) return 'Minimal 8 karakter';
                        if (!RegExp(r'[A-Z]').hasMatch(v)) return 'Wajib mengandung huruf besar (A-Z)';
                        if (!RegExp(r'[a-z]').hasMatch(v)) return 'Wajib mengandung huruf kecil (a-z)';
                        if (!RegExp(r'[0-9]').hasMatch(v)) return 'Wajib mengandung angka (0-9)';
                        return null;
                      },
                    ),
                    const SizedBox(height: 12),

                    Text(
                      'Konfirmasi Kata Sandi Baru',
                      style: GoogleFonts.inter(
                        fontSize: 12.5,
                        fontWeight: FontWeight.w600,
                        color: AppColors.textPrimary,
                      ),
                    ),
                    const SizedBox(height: 6),
                    TextFormField(
                      controller: confirmPassCtrl,
                      obscureText: obscureConfirm,
                      style: GoogleFonts.inter(fontSize: 13.5, color: AppColors.textPrimary),
                      decoration: buildAuthInputDecoration(
                        hintText: 'Ulangi kata sandi baru',
                        prefixIcon: LucideIcons.lock,
                        suffixIcon: IconButton(
                          icon: Icon(
                            obscureConfirm ? LucideIcons.eyeOff : LucideIcons.eye,
                            size: 18,
                            color: AppColors.textSecondary,
                          ),
                          onPressed: () => setModalState(() => obscureConfirm = !obscureConfirm),
                        ),
                      ),
                      validator: (v) => v != newPassCtrl.text ? 'Konfirmasi kata sandi tidak cocok' : null,
                    ),
                    const SizedBox(height: 18),

                    SizedBox(
                      height: 50,
                      child: ElevatedButton(
                        onPressed: isSubmitting
                            ? null
                            : () async {
                                if (!modalFormKey.currentState!.validate()) return;
                                setModalState(() {
                                  isSubmitting = true;
                                  modalError = null;
                                });

                                final nav = Navigator.of(bCtx);

                                final res = await _authService.forgotPassword(
                                  email: emailCtrl.text.trim(),
                                  otp: otpCtrl.text.trim(),
                                  password: newPassCtrl.text,
                                  passwordConfirmation: confirmPassCtrl.text,
                                );

                                if (!mounted) return;

                                if (res['success'] == true) {
                                  nav.pop();
                                  showV2Toast(
                                    this.context,
                                    message: res['message'] ?? 'Kata sandi berhasil diatur ulang! Silakan masuk.',
                                    type: V2ToastType.success,
                                  );
                                } else {
                                  setModalState(() {
                                    isSubmitting = false;
                                    modalError = res['message'] ?? 'Gagal memperbarui kata sandi. Pastikan kode OTP benar.';
                                  });
                                }
                              },
                        style: ElevatedButton.styleFrom(
                          backgroundColor: AppColors.primary,
                          foregroundColor: Colors.white,
                          elevation: 0,
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                        ),
                        child: isSubmitting
                            ? const SizedBox(
                                width: 20,
                                height: 20,
                                child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                              )
                            : Text(
                                'Simpan Kata Sandi Baru',
                                style: GoogleFonts.outfit(fontSize: 15, fontWeight: FontWeight.bold),
                              ),
                      ),
                    ),
                    const SizedBox(height: 8),
                    Center(
                      child: TextButton(
                        onPressed: isSubmitting
                            ? null
                            : () {
                                setModalState(() {
                                  currentStep = 1;
                                  modalError = null;
                                  modalSuccess = null;
                                });
                              },
                        child: Text(
                          'Kirim Ulang Kode / Ganti Email',
                          style: GoogleFonts.inter(
                            color: AppColors.textSecondary,
                            fontSize: 12.5,
                            fontWeight: FontWeight.w500,
                          ),
                        ),
                      ),
                    ),
                  ],
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      body: SafeArea(
        child: Center(
          child: SingleChildScrollView(
            padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 20),
            child: Form(
              key: _formKey,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  // Header Brand Ringkas (Selaras Web Mobile V2)
                  Center(
                    child: Container(
                      width: 56,
                      height: 56,
                      padding: const EdgeInsets.all(10),
                      decoration: BoxDecoration(
                        color: AppColors.primaryContainer,
                        borderRadius: BorderRadius.circular(16),
                        border: Border.all(color: AppColors.primaryBorder),
                      ),
                      child: Image.asset(
                        'assets/images/logo_pin.png',
                        fit: BoxFit.contain,
                      ),
                    ),
                  ),
                  const SizedBox(height: 10),
                  Center(
                    child: Text(
                      'SI-APPIK',
                      style: GoogleFonts.outfit(
                        fontSize: 22,
                        fontWeight: FontWeight.w800,
                        color: AppColors.textPrimary,
                        letterSpacing: -0.3,
                      ),
                    ),
                  ),
                  const SizedBox(height: 2),
                  Center(
                    child: Text(
                      'Kabupaten Kolaka Utara • Sulawesi Tenggara',
                      textAlign: TextAlign.center,
                      style: GoogleFonts.inter(
                        fontSize: 12,
                        fontWeight: FontWeight.w500,
                        color: AppColors.textSecondary,
                      ),
                    ),
                  ),
                  const SizedBox(height: 20),

                  // Kartu Login Utama (rounded 24px + border #E2E8F0)
                  Container(
                    padding: const EdgeInsets.all(22),
                    decoration: BoxDecoration(
                      color: AppColors.surface,
                      borderRadius: BorderRadius.circular(24),
                      border: Border.all(color: AppColors.border),
                      boxShadow: [
                        BoxShadow(
                          color: AppColors.textPrimary.withValues(alpha: 0.04),
                          blurRadius: 24,
                          offset: const Offset(0, 8),
                        ),
                      ],
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.stretch,
                      children: [
                        Text(
                          'Masuk ke Akun',
                          style: GoogleFonts.outfit(
                            fontSize: 22,
                            fontWeight: FontWeight.bold,
                            color: AppColors.textPrimary,
                            letterSpacing: -0.3,
                          ),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          'Masuk untuk melaporkan kerusakan atau memantau progres penanganan infrastruktur.',
                          style: GoogleFonts.inter(
                            fontSize: 12.5,
                            color: AppColors.textSecondary,
                            height: 1.45,
                          ),
                        ),
                        const SizedBox(height: 18),

                        if (_errorMessage != null)
                          AuthStatusBanner(
                            message: _errorMessage!,
                            icon: LucideIcons.alertCircle,
                            color: AppColors.statusRejected,
                          ),

                        Text(
                          'Email atau Nomor HP',
                          style: GoogleFonts.inter(
                            fontSize: 12.5,
                            fontWeight: FontWeight.w600,
                            color: AppColors.textPrimary,
                          ),
                        ),
                        const SizedBox(height: 6),
                        TextFormField(
                          controller: _emailController,
                          keyboardType: TextInputType.text,
                          style: GoogleFonts.inter(fontSize: 13.5, color: AppColors.textPrimary),
                          decoration: buildAuthInputDecoration(
                            hintText: 'nama@email.com / 0812...',
                            prefixIcon: LucideIcons.mail,
                          ),
                          validator: (v) {
                            if (v == null || v.trim().isEmpty) return 'Email atau Nomor HP wajib diisi';
                            return null;
                          },
                        ),
                        const SizedBox(height: 14),

                        Text(
                          'Kata Sandi',
                          style: GoogleFonts.inter(
                            fontSize: 12.5,
                            fontWeight: FontWeight.w600,
                            color: AppColors.textPrimary,
                          ),
                        ),
                        const SizedBox(height: 6),
                        TextFormField(
                          controller: _passwordController,
                          obscureText: _obscurePassword,
                          style: GoogleFonts.inter(fontSize: 13.5, color: AppColors.textPrimary),
                          decoration: buildAuthInputDecoration(
                            hintText: 'Masukkan kata sandi',
                            prefixIcon: LucideIcons.lock,
                            suffixIcon: IconButton(
                              icon: Icon(
                                _obscurePassword ? LucideIcons.eyeOff : LucideIcons.eye,
                                size: 18,
                                color: AppColors.textSecondary,
                              ),
                              onPressed: () => setState(() => _obscurePassword = !_obscurePassword),
                            ),
                          ),
                          validator: (v) {
                            if (v == null || v.isEmpty) return 'Kata sandi wajib diisi';
                            return null;
                          },
                        ),
                        const SizedBox(height: 8),

                        Align(
                          alignment: Alignment.centerRight,
                          child: TextButton(
                            onPressed: _showForgotPasswordModal,
                            style: TextButton.styleFrom(
                              padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 2),
                              minimumSize: Size.zero,
                              tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                            ),
                            child: Text(
                              'Lupa kata sandi?',
                              style: GoogleFonts.inter(
                                color: AppColors.primary,
                                fontSize: 12.5,
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                          ),
                        ),
                        const SizedBox(height: 16),

                        SizedBox(
                          height: 50,
                          child: ElevatedButton(
                            onPressed: _isLoading ? null : _handleLogin,
                            style: ElevatedButton.styleFrom(
                              backgroundColor: AppColors.primary,
                              foregroundColor: Colors.white,
                              elevation: 0,
                              shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(14),
                              ),
                            ),
                            child: _isLoading
                                ? const SizedBox(
                                    width: 20,
                                    height: 20,
                                    child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                                  )
                                : Text(
                                    'Masuk ke Akun',
                                    style: GoogleFonts.outfit(fontSize: 15, fontWeight: FontWeight.bold),
                                  ),
                          ),
                        ),
                        const SizedBox(height: 16),

                        Row(
                          children: [
                            const Expanded(child: Divider(color: AppColors.border)),
                            Padding(
                              padding: const EdgeInsets.symmetric(horizontal: 12),
                              child: Text(
                                'atau masuk dengan',
                                style: GoogleFonts.inter(color: AppColors.textSecondary, fontSize: 11.5),
                              ),
                            ),
                            const Expanded(child: Divider(color: AppColors.border)),
                          ],
                        ),
                        const SizedBox(height: 14),

                        SizedBox(
                          height: 48,
                          child: OutlinedButton(
                            onPressed: _isLoading ? null : _handleGoogleSignIn,
                            style: OutlinedButton.styleFrom(
                              backgroundColor: AppColors.surface,
                              foregroundColor: AppColors.textPrimary,
                              side: const BorderSide(color: AppColors.border),
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                            ),
                            child: Row(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                Image.asset(
                                  'assets/images/google_logo.png',
                                  width: 18,
                                  height: 18,
                                ),
                                const SizedBox(width: 10),
                                Text(
                                  'Masuk dengan Akun Google',
                                  style: GoogleFonts.inter(fontSize: 13.5, fontWeight: FontWeight.w600),
                                ),
                              ],
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 20),

                  // Link Daftar Akun
                  Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Text(
                        'Belum punya akun? ',
                        style: GoogleFonts.inter(color: AppColors.textSecondary, fontSize: 13),
                      ),
                      GestureDetector(
                        onTap: () {
                          Navigator.of(context).push(
                            MaterialPageRoute(builder: (_) => const RegisterScreen()),
                          );
                        },
                        child: Text(
                          'Daftar Sekarang',
                          style: GoogleFonts.inter(
                            color: AppColors.primary,
                            fontWeight: FontWeight.bold,
                            fontSize: 13,
                          ),
                        ),
                      ),
                    ],
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
