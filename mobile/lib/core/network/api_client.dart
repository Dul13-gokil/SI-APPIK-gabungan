import 'package:dio/dio.dart';
import '../config/app_config.dart';
import '../storage/secure_storage_service.dart';

class ApiClient {
  late final Dio dio;
  final SecureStorageService _storageService;

  ApiClient({SecureStorageService? storageService})
      : _storageService = storageService ?? SecureStorageService() {
    dio = Dio(
      BaseOptions(
        baseUrl: AppConfig.baseUrl,
        connectTimeout: const Duration(milliseconds: AppConfig.connectTimeout),
        receiveTimeout: const Duration(milliseconds: AppConfig.receiveTimeout),
        headers: {
          'Accept': 'application/json',
        },
      ),
    );

    dio.interceptors.add(
      InterceptorsWrapper(
        onRequest: (options, handler) async {
          // Dynamic baseUrl support
          final currentBaseUrl = await AppConfig.getBaseUrl();
          if (options.baseUrl != currentBaseUrl) {
            options.baseUrl = currentBaseUrl;
          }

          final token = await _storageService.getToken();
          if (token != null && token.isNotEmpty) {
            options.headers['Authorization'] = 'Bearer $token';
          }

          return handler.next(options);
        },
        onError: (DioException error, handler) async {
          final statusCode = error.response?.statusCode;
          final respData = error.response?.data;
          final isDeactivated = statusCode == 403 &&
              respData is Map &&
              ((respData['data'] is Map && respData['data']['account_deactivated'] == true) ||
                  (respData['errors'] is Map && respData['errors']['account_deactivated'] == true) ||
                  (respData['message']?.toString().toLowerCase().contains('dinonaktifkan') ?? false));

          if (statusCode == 401 || isDeactivated) {
            await _storageService.clearAuth();
          }
          return handler.next(error);
        },
      ),
    );
  }

  void setBaseUrl(String url) {
    dio.options.baseUrl = AppConfig.normalizeUrl(url);
  }
}
