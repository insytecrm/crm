import 'package:flutter_dotenv/flutter_dotenv.dart';

class ApiConfig {
  ApiConfig._();

  static String get baseUrl =>
      dotenv.env['API_BASE_URL']?.trim().replaceAll(RegExp(r'/+$'), '') ??
      'http://localhost:8000';

  static String get tenantSlug =>
      dotenv.env['TENANT_SLUG']?.trim().isNotEmpty == true
          ? dotenv.env['TENANT_SLUG']!.trim()
          : 'acme';

  /// Tenant path prefix, e.g. /acme
  static String get tenantPath => '/$tenantSlug';

  static Uri tenantUri(String path) {
    final normalized = path.startsWith('/') ? path : '/$path';

    return Uri.parse('$baseUrl$tenantPath$normalized');
  }
}
