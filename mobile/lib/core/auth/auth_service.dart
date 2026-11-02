import 'dart:convert';
import 'package:dio/dio.dart';
import '../network/api_client.dart';
import '../storage/secure_storage_service.dart';

class UserModel {
  final int id;
  final String name;
  final String email;
  final String? phone;
  final String? avatar;
  final String role;
  final bool isAnonymous;
  final bool needsFirstLoginSetup;

  UserModel({
    required this.id,
    required this.name,
    required this.email,
    this.phone,
    this.avatar,
    required this.role,
    this.isAnonymous = false,
    this.needsFirstLoginSetup = false,
  });

  factory UserModel.fromJson(Map<String, dynamic> json) {
    return UserModel(
      id: json['id'] ?? 0,
      name: json['name'] ?? 'Warga',
      email: json['email'] ?? '',
      phone: json['phone'],
      avatar: json['avatar'] ?? json['avatar_url'],
      role: json['role'] ?? 'WARGA',
      isAnonymous: json['is_anonymous'] ?? false,
      needsFirstLoginSetup: json['needs_first_login_setup'] ?? false,
    );
  }

  Map<String, dynamic> toJson() => {
        'id': id,
        'name': name,
        'email': email,
        'phone': phone,
        'avatar': avatar,
        'role': role,
        'is_anonymous': isAnonymous,
        'needs_first_login_setup': needsFirstLoginSetup,
      };

  UserModel copyWith({
    int? id,
    String? name,
    String? email,
    String? phone,
    String? avatar,
    String? role,
    bool? isAnonymous,
    bool? needsFirstLoginSetup,
    bool clearAvatar = false,
  }) {
    return UserModel(
      id: id ?? this.id,
      name: name ?? this.name,
      email: email ?? this.email,
      phone: phone ?? this.phone,
      avatar: clearAvatar ? null : (avatar ?? this.avatar),
      role: role ?? this.role,
      isAnonymous: isAnonymous ?? this.isAnonymous,
      needsFirstLoginSetup: needsFirstLoginSetup ?? this.needsFirstLoginSetup,
    );
  }
}

class AuthService {
  final ApiClient _apiClient;
  final SecureStorageService _storage;

  AuthService({ApiClient? apiClient, SecureStorageService? storage})
      : _apiClient = apiClient ?? ApiClient(),
        _storage = storage ?? SecureStorageService();


  Future<bool> isLoggedIn() async {
    final token = await _storage.getToken();
    final isGuest = await isGuestMode();
    return (token != null && token.isNotEmpty) || isGuest;
  }

  Future<bool> isGuestMode() async {
    final data = await _storage.getUserData();
    return data == 'GUEST_USER';
  }

  Future<UserModel?> getCurrentUser() async {
    if (await isGuestMode()) {
      return UserModel(
        id: 0,
        name: 'Warga Tamu (Anonim)',
        email: 'tamu@kabupaten.go.id',
        role: 'TAMU',
        isAnonymous: true,
      );
    }

    final data = await _storage.getUserData();
    if (data != null && data.isNotEmpty) {
      try {
        return UserModel.fromJson(jsonDecode(data));
      } catch (_) {}
    }
    return null;
  }

  Future<Map<String, dynamic>> login({
    required String email,
    required String password,
  }) async {
    try {
      final response = await _apiClient.dio.post('/auth/login', data: {
        'email': email,
        'password': password,
      });

      if (response.statusCode == 200 && response.data['data'] != null) {
        final data = response.data['data'];
        final token = data['token'];
        final user = UserModel.fromJson(data['user']);

        await _storage.saveToken(token);
        await _storage.saveUserData(jsonEncode(user.toJson()));

        return {'success': true, 'user': user};
      }
      return {'success': false, 'message': response.data['message'] ?? 'Login gagal'};
    } on DioException catch (e) {
      final msg = e.response?.data?['message'] ?? 'Koneksi gagal atau kredensial salah';
      return {'success': false, 'message': msg};
    } catch (e) {
      return {'success': false, 'message': e.toString()};
    }
  }

  Future<Map<String, dynamic>> loginWithGoogle(String idToken) async {
    try {
      final response = await _apiClient.dio.post('/auth/google', data: {
        'id_token': idToken,
      });

      if (response.statusCode == 200 && response.data['data'] != null) {
        final data = response.data['data'];
        final token = data['token'];
        final user = UserModel.fromJson(data['user']);
        final isNewUser = data['is_new_user'] == true;

        await _storage.saveToken(token);
        await _storage.saveUserData(jsonEncode(user.toJson()));

        return {'success': true, 'user': user, 'is_new_user': isNewUser};
      }
      return {'success': false, 'message': response.data['message'] ?? 'Login Google gagal'};
    } on DioException catch (e) {
      final msg = e.response?.data?['message'] ?? 'Gagal memverifikasi akun Google ke server';
      return {'success': false, 'message': msg};
    } catch (e) {
      return {'success': false, 'message': e.toString()};
    }
  }

  Future<Map<String, dynamic>> sendRegisterOtp(String email) async {
    try {
      final response = await _apiClient.dio.post('/auth/register/send-otp', data: {
        'email': email,
      });
      if (response.statusCode == 200) {
        return {
          'success': true,
          'message': response.data['message'] ?? 'Kode OTP berhasil dikirim ke email.',
          'dev_otp': response.data['data']?['dev_otp'],
        };
      }
      return {
        'success': false,
        'message': response.data['message'] ?? 'Gagal mengirim kode OTP.',
      };
    } on DioException catch (e) {
      final msg = e.response?.data?['message'] ?? 'Gagal mengirim kode OTP. Periksa email Anda.';
      return {'success': false, 'message': msg};
    } catch (e) {
      return {'success': false, 'message': e.toString()};
    }
  }

  Future<Map<String, dynamic>> sendForgotPasswordOtp(String email) async {
    try {
      final response = await _apiClient.dio.post('/auth/forgot-password/send-otp', data: {
        'email': email,
      });
      if (response.statusCode == 200) {
        return {
          'success': true,
          'message': response.data['message'] ?? 'Kode OTP berhasil dikirim ke email.',
          'dev_otp': response.data['data']?['dev_otp'],
        };
      }
      return {
        'success': false,
        'message': response.data['message'] ?? 'Gagal mengirim kode OTP.',
      };
    } on DioException catch (e) {
      final msg = e.response?.data?['message'] ?? 'Email tidak terdaftar atau terjadi kesalahan server.';
      return {'success': false, 'message': msg};
    } catch (e) {
      return {'success': false, 'message': e.toString()};
    }
  }

  Future<Map<String, dynamic>> register({
    required String name,
    required String email,
    required String password,
    required String otp,
    String? phone,
    bool isAnonymous = false,
  }) async {
    try {
      final response = await _apiClient.dio.post('/auth/register', data: {
        'name': name,
        'email': email,
        'phone': phone,
        'password': password,
        'password_confirmation': password,
        'otp': otp,
        'is_anonymous': isAnonymous,
      });

      if (response.statusCode == 201 && response.data['data'] != null) {
        final data = response.data['data'];
        final token = data['token'];
        final user = UserModel.fromJson(data['user']);

        await _storage.saveToken(token);
        await _storage.saveUserData(jsonEncode(user.toJson()));

        return {'success': true, 'user': user};
      }
      return {'success': false, 'message': response.data['message'] ?? 'Registrasi gagal'};
    } on DioException catch (e) {
      final msg = e.response?.data?['message'] ?? 'Gagal melakukan registrasi';
      return {'success': false, 'message': msg};
    } catch (e) {
      return {'success': false, 'message': e.toString()};
    }
  }

  Future<Map<String, dynamic>> forgotPassword({
    required String email,
    required String otp,
    required String password,
    required String passwordConfirmation,
  }) async {
    try {
      final response = await _apiClient.dio.post('/auth/forgot-password', data: {
        'email': email,
        'otp': otp,
        'password': password,
        'password_confirmation': passwordConfirmation,
      });

      if (response.statusCode == 200) {
        return {
          'success': true,
          'message': response.data['message'] ?? 'Kata sandi berhasil diperbarui',
        };
      }
      return {
        'success': false,
        'message': response.data['message'] ?? 'Gagal memperbarui kata sandi',
      };
    } on DioException catch (e) {
      final msg = e.response?.data?['message'] ?? 'Email tidak terdaftar atau kode OTP salah';
      return {'success': false, 'message': msg};
    } catch (e) {
      return {'success': false, 'message': e.toString()};
    }
  }

  Future<void> loginAsGuest() async {
    await _storage.clearAuth();
    await _storage.saveUserData('GUEST_USER');
  }

  Future<void> logout() async {
    try {
      if (!await isGuestMode()) {
        await _apiClient.dio.post('/auth/logout');
      }
    } catch (_) {}
    await _storage.clearAuth();
  }

  Future<Map<String, dynamic>> updateProfile({
    required String name,
    required String email,
    String? currentPassword,
    String? password,
    String? avatar,
    String? phone,
    double? latitude,
    double? longitude,
  }) async {
    try {
      final body = <String, dynamic>{
        'name': name,
        'email': email,
      };
      if (currentPassword != null && currentPassword.isNotEmpty) {
        body['current_password'] = currentPassword;
      }
      if (password != null && password.isNotEmpty) {
        body['password'] = password;
      }
      final shouldClearAvatar = avatar == '' || avatar == 'DELETE';
      if (avatar != null) {
        body['avatar'] = shouldClearAvatar ? 'DELETE' : avatar;
      }
      if (phone != null) {
        body['phone'] = phone;
      }
      if (latitude != null && longitude != null) {
        body['latitude'] = latitude;
        body['longitude'] = longitude;
      }

      final response = await _apiClient.dio.post('/profile', data: body);
      if (response.statusCode == 200 && response.data['data'] != null) {
        final userData = response.data['data'];
        final updatedUser = UserModel.fromJson(userData);
        await _storage.saveUserData(jsonEncode(updatedUser.toJson()));
        return {'success': true, 'user': updatedUser};
      }

      final serverMsg = response.data is Map ? response.data['message'] : null;
      return {'success': false, 'message': serverMsg ?? 'Gagal memperbarui profil'};
    } on DioException catch (e) {
      // Jika server memberikan respon error (misal 422 Password Salah, 400, 401, 403),
      // WAJIB kembalikan pesan error server, JANGAN dianggap sukses!
      if (e.response != null) {
        final serverData = e.response?.data;
        String? msg;
        if (serverData is Map) {
          msg = serverData['message'] as String?;
          if ((msg == null || msg.isEmpty) && serverData['errors'] is Map) {
            final errors = serverData['errors'] as Map;
            final firstKey = errors.keys.firstOrNull;
            if (firstKey != null && errors[firstKey] is List && (errors[firstKey] as List).isNotEmpty) {
              msg = (errors[firstKey] as List).first.toString();
            }
          }
        }
        return {'success': false, 'message': msg ?? 'Permintaan ditolak oleh server (${e.response?.statusCode})'};
      }

      // Jika jaringan benar-benar offline (tidak ada respon server):
      // Jika pengguna sedang mencoba ganti kata sandi, dilarang fallback offline!
      if (password != null && password.isNotEmpty) {
        return {
          'success': false,
          'message': 'Gagal menghubungi server. Perubahan kata sandi memerlukan koneksi internet aktif.',
        };
      }

      // Fallback offline HANYA untuk edit nama/kontak lokal saat server tidak terjangkau
      final currentUser = await getCurrentUser();
      if (currentUser != null) {
        final shouldClearAvatar = avatar == '' || avatar == 'DELETE';
        final updatedUser = currentUser.copyWith(
          name: name,
          email: email,
          avatar: shouldClearAvatar ? null : (avatar ?? currentUser.avatar),
          clearAvatar: shouldClearAvatar,
          phone: phone ?? currentUser.phone,
        );
        await _storage.saveUserData(jsonEncode(updatedUser.toJson()));
        return {'success': true, 'user': updatedUser, 'isOffline': true};
      }

      return {'success': false, 'message': 'Gagal terhubung ke server aplikasi'};
    } catch (e) {
      return {'success': false, 'message': e.toString()};
    }
  }

  Future<void> updateLocalAvatar(String avatarPath) async {
    final currentUser = await getCurrentUser();
    if (currentUser != null) {
      final updatedUser = currentUser.copyWith(avatar: avatarPath);
      await _storage.saveUserData(jsonEncode(updatedUser.toJson()));
    }
  }

  Future<Map<String, dynamic>> checkPhone(String phone, {int? userId}) async {
    try {
      final params = <String, dynamic>{'phone': phone};
      if (userId != null) params['user_id'] = userId;
      final response = await _apiClient.dio.get('/auth/check-phone', queryParameters: params);
      if (response.statusCode == 200 && response.data != null) {
        final data = response.data['data'];
        return {
          'success': true,
          'valid_format': data?['valid_format'] ?? false,
          'registered': data?['registered'] ?? false,
          'normalized': data?['normalized'] ?? phone,
          'digit_count': data?['digit_count'] ?? 0,
          'message': data?['message'] ?? response.data['message'] ?? '',
        };
      }
      return {'success': false, 'message': response.data?['message'] ?? 'Gagal memeriksa nomor.'};
    } catch (e) {
      return {'success': false, 'message': 'Gagal terhubung untuk memeriksa nomor.'};
    }
  }
}
