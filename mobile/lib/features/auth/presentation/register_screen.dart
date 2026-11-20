import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:google_sign_in/google_sign_in.dart';
import 'package:lucide_icons_flutter/lucide_icons.dart';
import '../../../core/auth/auth_service.dart';
import '../../../core/utils/phone_validator.dart';
import '../../../shared/theme/app_colors.dart';
import '../../../shared/widgets/v2_toast.dart';
import '../../navigation/presentation/main_navigation_screen.dart';
import 'widgets/auth_ui_helpers.dart';

class RegisterScreen extends StatefulWidget {
  const RegisterScreen({super.key});

  @override
  State<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends State<RegisterScreen> {
  final _formKey = GlobalKey<FormState>();
  final _nameController = TextEditingController();
  final _emailController = TextEditingController();
  final _phoneController = TextEditingController();
  final _otpController = TextEditingController();
  final _passwordController = TextEditingController();
  final _confirmPasswordController = TextEditingController();
  final _authService = AuthService();

  static const String _googleWebClientId = '548526888002-iku6euuas29lupp0o9mcrbpnib0lcd48.apps.googleusercontent.com';

  bool _obscurePassword = true;
  bool _isLoading = false;
  bool _isSendingOtp = false;
  bool _otpSent = false;
  String? _errorMessage;
  String? _successMessage;
  String? _phoneFeedback;
  bool? _isPhoneValid;

  @override
  void dispose() {
    _nameController.dispose();
    _emailController.dispose();
    _phoneController.dispose();
    _otpController.dispose();
    _passwordController.dispose();
    _confirmPasswordController.dispose();
    super.dispose();
  }

  Future<void> _handleSendOtp() async {
    final email = _emailController.text.trim();
    if (email.isEmpty || !email.contains('@')) {
      setState(() {
        _errorMessage = 'Masukkan alamat email yang valid terlebih dahulu untuk meminta kode OTP.';
      });
      return;
    }

    setState(() {
      _isSendingOtp = true;
      _errorMessage = null;
      _successMessage = null;
    });

    final res = await _authService.sendRegisterOtp(email);

    if (!mounted) return;
    setState(() {
      _isSendingOtp = false;
      if (res['success'] == true) {
        _otpSent = true;
        _successMessage = 'Kode OTP 6-digit prioritas tinggi telah dikirim ke $email.\nPeriksa notifikasi HP atau kotak masuk email Anda.';
      } else {
        _errorMessage = res['message'] ?? 'Gagal mengirim kode OTP.';
      }
    });
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
                            const Text(
                              'Persetujuan Akses Akun Google',
                              style: TextStyle(
                                fontSize: 16,
                                fontWeight: FontWeight.bold,
                                color: AppColors.textPrimary,
                              ),
                            ),
                            Text(
                              email,
                              style: const TextStyle(fontSize: 12, color: AppColors.textSecondary),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 14),
                  const Text(
                    'Dengan mendaftarkan akun baru menggunakan Google, Anda menyetujui pemberian izin kepada sistem SI-APPIK untuk mengakses dan mengelola informasi berikut dari akun Google Anda:',
                    style: TextStyle(fontSize: 12.5, color: AppColors.textSecondary, height: 1.45),
                  ),
                  const SizedBox(height: 14),
                  Container(
                    padding: const EdgeInsets.all(14),
                    decoration: BoxDecoration(
                      color: const Color(0xFFF8FAFC),
                      borderRadius: BorderRadius.circular(14),
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
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    'Nama Lengkap Akun Google',
                                    style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.textPrimary),
                                  ),
                                  Text(
                                    'Digunakan sebagai identitas pendaftaran awal akun pengguna.',
                                    style: TextStyle(fontSize: 11.5, color: AppColors.textSecondary),
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ),
                        SizedBox(height: 12),
                        Row(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Icon(LucideIcons.mail, size: 16, color: AppColors.primary),
                            SizedBox(width: 10),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    'Alamat Email Resmi',
                                    style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.textPrimary),
                                  ),
                                  Text(
                                    'Digunakan untuk otentikasi masuk, pengiriman notifikasi, dan verifikasi akun.',
                                    style: TextStyle(fontSize: 11.5, color: AppColors.textSecondary),
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ),
                        SizedBox(height: 12),
                        Row(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Icon(LucideIcons.shieldCheck, size: 16, color: AppColors.primary),
                            SizedBox(width: 10),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    'Informasi Profil Dasar Lainnya',
                                    style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.textPrimary),
                                  ),
                                  Text(
                                    'Termasuk status verifikasi akun Google untuk memastikan keamanan pendaftaran akun.',
                                    style: TextStyle(fontSize: 11.5, color: AppColors.textSecondary),
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
                  const Row(
                    crossAxisAlignment: CrossAxisAlignment.center,
                    children: [
                      Icon(LucideIcons.lock, size: 13, color: AppColors.textSecondary),
                      SizedBox(width: 6),
                      Expanded(
                        child: Text(
                          'Data Anda aman & dilindungi sesuai standar privasi Pemerintah Kabupaten.',
                          style: TextStyle(fontSize: 11, color: AppColors.textSecondary),
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
                          child: const Text('Batal', style: TextStyle(color: AppColors.textSecondary, fontWeight: FontWeight.w600)),
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
                          child: const Text('Setujui & Lanjutkan', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
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

  Future<void> _handleRegister() async {
    if (!_formKey.currentState!.validate()) return;

    if (_otpController.text.trim().length != 6) {
      setState(() {
        _errorMessage = 'Silakan minta dan masukkan 6 digit kode OTP verifikasi email.';
      });
      return;
    }

    if (_passwordController.text != _confirmPasswordController.text) {
      setState(() {
        _errorMessage = 'Konfirmasi password tidak cocok.';
      });
      return;
    }

    final phoneRaw = _phoneController.text.trim();
    if (phoneRaw.isEmpty) {
      setState(() {
        _errorMessage = 'Nomor HP / WhatsApp wajib diisi (10 - 13 digit).';
      });
      return;
    }

    final validation = PhoneValidator.validate(phoneRaw, isRequired: true);
    if (!validation.isValid) {
      setState(() {
        _errorMessage = validation.message;
      });
      return;
    }
    final validatedPhone = validation.normalized;

    final checkRes = await _authService.checkPhone(validatedPhone);
    if (checkRes['registered'] == true) {
      setState(() {
        _errorMessage = checkRes['message'] ?? 'Nomor handphone sudah terdaftar oleh pengguna lain.';
        _phoneFeedback = checkRes['message'];
        _isPhoneValid = false;
      });
      return;
    }

    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    final result = await _authService.register(
      name: _nameController.text.trim(),
      email: _emailController.text.trim(),
      phone: validatedPhone,
      password: _passwordController.text,
      otp: _otpController.text.trim(),
    );

    if (!mounted) return;

    setState(() {
      _isLoading = false;
    });

    if (result['success'] == true) {
      showV2Toast(
        context,
        message: 'Akun warga berhasil didaftarkan! Selamat datang.',
        type: V2ToastType.success,
      );
      Navigator.of(context).pushAndRemoveUntil(
        MaterialPageRoute(builder: (_) => const MainNavigationScreen()),
        (route) => false,
      );
    } else {
      setState(() {
        _errorMessage = result['message'] ?? 'Registrasi gagal. Silakan coba lagi.';
      });
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
        if (mounted) setState(() => _isLoading = false);
        return;
      }

      final GoogleSignInAuthentication auth = await account.authentication;
      final String? idToken = auth.idToken;

      if (idToken == null || idToken.isEmpty) {
        if (mounted) {
          setState(() {
            _isLoading = false;
            _errorMessage = 'Gagal memperoleh idToken dari Google. Pastikan OAuth Client ID Android sudah dibuat.';
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
            Navigator.of(context).pushAndRemoveUntil(
              MaterialPageRoute(
                builder: (_) => const MainNavigationScreen(
                  showGoogleProfileNotice: true,
                ),
              ),
              (route) => false,
            );
          } else {
            await _authService.logout();
          }
        } else {
          // Akun sudah pernah terdaftar: langsung masuk
          Navigator.of(context).pushAndRemoveUntil(
            MaterialPageRoute(
              builder: (_) => const MainNavigationScreen(
                showGoogleProfileNotice: false,
              ),
            ),
            (route) => false,
          );
        }
      } else {
        setState(() {
          _errorMessage = result['message'] ?? 'Daftar dengan Akun Google gagal diverifikasi oleh server.';
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _isLoading = false;
          _errorMessage = 'Terjadi kesalahan saat daftar dengan Google: $e';
        });
      }
    }
  }

  Widget _buildFieldLabel(String label) {
    return RichText(
      text: TextSpan(
        text: label,
        style: GoogleFonts.inter(
          fontSize: 12.5,
          fontWeight: FontWeight.w600,
          color: AppColors.textPrimary,
        ),
        children: const [
          TextSpan(
            text: ' *',
            style: TextStyle(
              color: AppColors.statusSubmitted,
              fontWeight: FontWeight.bold,
            ),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
          child: Form(
            key: _formKey,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                // Top Bar Ringkas (Back button + Judul)
                Row(
                  children: [
                    InkWell(
                      onTap: () => Navigator.of(context).pop(),
                      borderRadius: BorderRadius.circular(12),
                      child: Container(
                        width: 40,
                        height: 40,
                        decoration: BoxDecoration(
                          color: AppColors.surface,
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(color: AppColors.border),
                        ),
                        child: const Icon(LucideIcons.arrowLeft, size: 18, color: AppColors.textPrimary),
                      ),
                    ),
                    const SizedBox(width: 12),
                    Text(
                      'Daftar Akun Baru',
                      style: GoogleFonts.outfit(
                        fontSize: 18,
                        fontWeight: FontWeight.bold,
                        color: AppColors.textPrimary,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 16),

                // Kartu Form Utama (rounded 24px + border #E2E8F0)
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
                        'Lengkapi data diri Anda untuk mulai melaporkan kerusakan infrastruktur.',
                        style: GoogleFonts.inter(
                          fontSize: 12.5,
                          color: AppColors.textSecondary,
                          height: 1.45,
                        ),
                      ),
                      const SizedBox(height: 16),

                      if (_errorMessage != null)
                        AuthStatusBanner(
                          message: _errorMessage!,
                          icon: LucideIcons.alertCircle,
                          color: AppColors.statusRejected,
                        ),

                      if (_successMessage != null)
                        AuthStatusBanner(
                          message: _successMessage!,
                          icon: LucideIcons.checkCircle,
                          color: AppColors.statusCompleted,
                        ),

                      // Nama Lengkap
                      _buildFieldLabel('Nama Lengkap'),
                      const SizedBox(height: 6),
                      TextFormField(
                        controller: _nameController,
                        style: GoogleFonts.inter(fontSize: 13.5, color: AppColors.textPrimary),
                        decoration: buildAuthInputDecoration(
                          hintText: 'Contoh: Ahmad Fauzi',
                          prefixIcon: LucideIcons.user,
                        ),
                        validator: (v) => (v == null || v.isEmpty) ? 'Nama wajib diisi' : null,
                      ),
                      const SizedBox(height: 14),

                      // Nomor HP / WhatsApp
                      _buildFieldLabel('Nomor HP / WhatsApp'),
                      const SizedBox(height: 6),
                      TextFormField(
                        controller: _phoneController,
                        keyboardType: TextInputType.phone,
                        style: GoogleFonts.inter(fontSize: 13.5, color: AppColors.textPrimary),
                        onChanged: (val) {
                          if (val.trim().isEmpty) {
                            setState(() {
                              _phoneFeedback = 'Nomor HP / WhatsApp wajib diisi (10 - 13 digit).';
                              _isPhoneValid = false;
                            });
                            return;
                          }
                          final res = PhoneValidator.validate(val, isRequired: true);
                          setState(() {
                            _phoneFeedback = res.message;
                            _isPhoneValid = res.isValid;
                          });
                        },
                        decoration: buildAuthInputDecoration(
                          hintText: '0812xxxxxxxx',
                          prefixIcon: LucideIcons.phone,
                        ),
                        validator: (v) {
                          if (v == null || v.trim().isEmpty) {
                            return 'Nomor HP / WhatsApp wajib diisi';
                          }
                          final res = PhoneValidator.validate(v, isRequired: true);
                          if (!res.isValid) {
                            return res.message;
                          }
                          return null;
                        },
                      ),
                      if (_phoneFeedback != null)
                        Padding(
                          padding: const EdgeInsets.only(top: 6, left: 4),
                          child: Text(
                            _phoneFeedback!,
                            style: GoogleFonts.inter(
                              fontSize: 11.5,
                              fontWeight: FontWeight.w500,
                              color: _isPhoneValid == true
                                  ? AppColors.statusCompleted
                                  : (_isPhoneValid == false ? AppColors.statusSubmitted : AppColors.textSecondary),
                            ),
                          ),
                        ),
                      const SizedBox(height: 14),

                      // Email + Tombol Kirim OTP sejajar (sesuai FigCraft)
                      _buildFieldLabel('Alamat Email'),
                      const SizedBox(height: 6),
                      Row(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Expanded(
                            child: TextFormField(
                              controller: _emailController,
                              keyboardType: TextInputType.emailAddress,
                              style: GoogleFonts.inter(fontSize: 13.5, color: AppColors.textPrimary),
                              decoration: buildAuthInputDecoration(
                                hintText: 'nama@email.com',
                                prefixIcon: LucideIcons.mail,
                              ),
                              validator: (v) {
                                if (v == null || v.isEmpty) return 'Email wajib diisi';
                                if (!v.contains('@')) return 'Format email tidak valid';
                                return null;
                              },
                            ),
                          ),
                          const SizedBox(width: 8),
                          SizedBox(
                            height: 48,
                            child: OutlinedButton(
                              onPressed: _isSendingOtp ? null : _handleSendOtp,
                              style: OutlinedButton.styleFrom(
                                backgroundColor: AppColors.primaryContainer,
                                foregroundColor: AppColors.primary,
                                side: const BorderSide(color: AppColors.primaryBorder),
                                padding: const EdgeInsets.symmetric(horizontal: 14),
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                              ),
                              child: _isSendingOtp
                                  ? const SizedBox(
                                      width: 16,
                                      height: 16,
                                      child: CircularProgressIndicator(strokeWidth: 2, color: AppColors.primary),
                                    )
                                  : Text(
                                      _otpSent ? 'Kirim Ulang' : 'Kirim OTP',
                                      style: GoogleFonts.inter(
                                        fontSize: 12,
                                        fontWeight: FontWeight.bold,
                                      ),
                                    ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 14),

                      // Kode OTP
                      _buildFieldLabel('Kode Verifikasi OTP (6 Digit)'),
                      const SizedBox(height: 6),
                      TextFormField(
                        controller: _otpController,
                        keyboardType: TextInputType.number,
                        maxLength: 6,
                        style: GoogleFonts.inter(fontSize: 13.5, color: AppColors.textPrimary),
                        decoration: buildAuthInputDecoration(
                          hintText: 'Masukkan 6 digit kode OTP',
                          prefixIcon: LucideIcons.shieldCheck,
                        ).copyWith(counterText: ''),
                        validator: (v) => (v == null || v.trim().length != 6) ? 'Kode OTP 6 digit wajib diisi' : null,
                      ),
                      const SizedBox(height: 14),

                      // Password
                      _buildFieldLabel('Kata Sandi'),
                      const SizedBox(height: 6),
                      TextFormField(
                        controller: _passwordController,
                        obscureText: _obscurePassword,
                        style: GoogleFonts.inter(fontSize: 13.5, color: AppColors.textPrimary),
                        decoration: buildAuthInputDecoration(
                          hintText: 'Min. 8 karakter (huruf besar, kecil, angka)',
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
                          if (v == null || v.length < 8) return 'Kata sandi minimal 8 karakter';
                          if (!RegExp(r'[A-Z]').hasMatch(v)) return 'Wajib mengandung huruf besar (A-Z)';
                          if (!RegExp(r'[a-z]').hasMatch(v)) return 'Wajib mengandung huruf kecil (a-z)';
                          if (!RegExp(r'[0-9]').hasMatch(v)) return 'Wajib mengandung angka (0-9)';
                          return null;
                        },
                      ),
                      const SizedBox(height: 14),

                      // Konfirmasi Password
                      _buildFieldLabel('Konfirmasi Kata Sandi'),
                      const SizedBox(height: 6),
                      TextFormField(
                        controller: _confirmPasswordController,
                        obscureText: _obscurePassword,
                        style: GoogleFonts.inter(fontSize: 13.5, color: AppColors.textPrimary),
                        decoration: buildAuthInputDecoration(
                          hintText: 'Ulangi kata sandi',
                          prefixIcon: LucideIcons.lock,
                        ),
                        validator: (v) {
                          if (v == null || v.isEmpty) return 'Konfirmasi kata sandi wajib diisi';
                          if (v != _passwordController.text) return 'Konfirmasi kata sandi tidak cocok';
                          return null;
                        },
                      ),
                      const SizedBox(height: 20),

                      // Tombol Daftar Akun
                      SizedBox(
                        height: 50,
                        child: ElevatedButton(
                          onPressed: _isLoading ? null : _handleRegister,
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
                                  'Daftar Akun',
                                  style: GoogleFonts.outfit(fontSize: 15, fontWeight: FontWeight.bold),
                                ),
                        ),
                      ),
                      const SizedBox(height: 16),

                      // Divider
                      Row(
                        children: [
                          const Expanded(child: Divider(color: AppColors.border)),
                          Padding(
                            padding: const EdgeInsets.symmetric(horizontal: 12),
                            child: Text(
                              'atau daftar dengan',
                              style: GoogleFonts.inter(color: AppColors.textSecondary, fontSize: 11.5),
                            ),
                          ),
                          const Expanded(child: Divider(color: AppColors.border)),
                        ],
                      ),
                      const SizedBox(height: 14),

                      // Google Sign-In Button
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
                                'Daftar dengan Akun Google',
                                style: GoogleFonts.inter(fontSize: 13.5, fontWeight: FontWeight.w600),
                              ),
                            ],
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 18),

                // Link ke Login
                Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Text(
                      'Sudah punya akun? ',
                      style: GoogleFonts.inter(color: AppColors.textSecondary, fontSize: 13),
                    ),
                    GestureDetector(
                      onTap: () => Navigator.pop(context),
                      child: Text(
                        'Masuk di sini',
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
    );
  }
}
