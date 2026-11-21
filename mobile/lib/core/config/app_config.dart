import 'package:shared_preferences/shared_preferences.dart';

class AppConfig {
  static const String appName = 'Aduan Jalan';
  static const String appVersion = '1.0.0';

  // Default Server API Production Resmi:
  // https://aduan.aispektra.com/api/v1
  static String get defaultBaseUrl => 'https://aduan.aispektra.com';
  static String activeOrigin = 'https://aduan.aispektra.com';
  static String get baseUrl => normalizeUrl(defaultBaseUrl);
  
  static const int connectTimeout = 15000;
  static const int receiveTimeout = 15000;

  static const double defaultLatitude = -3.50601;
  static const double defaultLongitude = 120.88780;

  static const String _keyCustomBaseUrl = 'custom_base_url';

  static String normalizeUrl(String url) {
    var trimmed = url.trim();
    if (trimmed.endsWith('/')) {
      trimmed = trimmed.substring(0, trimmed.length - 1);
    }
    if (!trimmed.endsWith('/api/v1')) {
      trimmed = '$trimmed/api/v1';
    }
    return trimmed;
  }

  static String _extractOrigin(String url) {
    var u = url.trim();
    if (u.endsWith('/api/v1')) {
      u = u.substring(0, u.length - 7);
    }
    if (u.endsWith('/')) {
      u = u.substring(0, u.length - 1);
    }
    return u;
  }

  static String fixMediaUrl(String url) {
    var fixed = url.trim();
    final origin = activeOrigin.isNotEmpty ? activeOrigin : defaultBaseUrl;
    if (fixed.startsWith('http://localhost:8000') ||
        fixed.startsWith('http://127.0.0.1:8000') ||
        fixed.startsWith('http://100.86.30.42:8000') ||
        fixed.startsWith('https://si-appik-production.up.railway.app') ||
        fixed.startsWith('http://si-appik-production.up.railway.app')) {
      fixed = fixed.replaceFirst('http://localhost:8000', origin);
      fixed = fixed.replaceFirst('http://127.0.0.1:8000', origin);
      fixed = fixed.replaceFirst('http://100.86.30.42:8000', origin);
      fixed = fixed.replaceFirst('https://si-appik-production.up.railway.app', origin);
      fixed = fixed.replaceFirst('http://si-appik-production.up.railway.app', origin);
    }
    if (fixed.startsWith('http://aduan.aispektra.com')) {
      fixed = fixed.replaceFirst('http://aduan.aispektra.com', 'https://aduan.aispektra.com');
    }
    if (!fixed.startsWith('http://') && !fixed.startsWith('https://')) {
      if (fixed.startsWith('/')) {
        fixed = '$origin$fixed';
      } else {
        fixed = '$origin/$fixed';
      }
    }
    return fixed;
  }

  static Future<String> getBaseUrl() async {
    final prefs = await SharedPreferences.getInstance();
    var custom = prefs.getString(_keyCustomBaseUrl);
    // Bersihkan cache lama (Railway / IP lokal / Tailscale) agar perangkat otomatis memakai server utama
    if (custom != null &&
        (custom.contains('si-appik-production.up.railway.app') ||
            custom.contains('100.86.30.42') ||
            custom.contains('127.0.0.1') ||
            custom.contains('localhost'))) {
      await prefs.remove(_keyCustomBaseUrl);
      custom = null;
    }
    final selected = (custom != null && custom.isNotEmpty) ? custom : defaultBaseUrl;
    activeOrigin = _extractOrigin(selected);
    return normalizeUrl(selected);
  }

  static Future<void> setBaseUrl(String url) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_keyCustomBaseUrl, normalizeUrl(url));
    activeOrigin = _extractOrigin(url);
  }
}


