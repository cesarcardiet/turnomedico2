import 'dart:convert';
import 'package:http/http.dart' as http;
import '../constants/api_constants.dart';
import '../storage/token_storage.dart';

class ApiClient {
  ApiClient._();

  static String _url(String path) {
    final base = ApiConstants.apiBaseUrl;
    final p = path.startsWith('/') ? path : '/$path';
    return '$base$p';
  }

  static Future<Map<String, String>> _headers({bool withToken = true}) async {
    final headers = <String, String>{
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    };
    if (withToken) {
      final token = await TokenStorage.getToken();
      if (token != null && token.isNotEmpty) {
        headers['Authorization'] = 'Bearer $token';
      }
    }
    return headers;
  }

  static Future<http.Response> get(
    String path, {
    Map<String, String>? queryParams,
    bool withToken = true,
  }) async {
    var uri = Uri.parse(_url(path));
    if (queryParams != null && queryParams.isNotEmpty) {
      uri = uri.replace(queryParameters: queryParams);
    }
    return http.get(uri, headers: await _headers(withToken: withToken));
  }

  static Future<http.Response> post(
    String path, {
    Map<String, dynamic>? body,
    bool withToken = true,
  }) async {
    return http.post(
      Uri.parse(_url(path)),
      headers: await _headers(withToken: withToken),
      body: body != null ? jsonEncode(body) : null,
    );
  }

  static Future<http.Response> patch(
    String path, {
    Map<String, dynamic>? body,
    bool withToken = true,
  }) async {
    return http.patch(
      Uri.parse(_url(path)),
      headers: await _headers(withToken: withToken),
      body: body != null ? jsonEncode(body) : null,
    );
  }

  static Future<http.Response> delete(
    String path, {
    bool withToken = true,
  }) async {
    return http.delete(
      Uri.parse(_url(path)),
      headers: await _headers(withToken: withToken),
    );
  }
}

class ApiException implements Exception {
  final int? statusCode;
  final String message;
  final Map<String, dynamic>? errors;

  ApiException({this.statusCode, required this.message, this.errors});

  @override
  String toString() => message;
}

/// Parsea respuesta JSON y lanza ApiException si no es 2xx.
Future<Map<String, dynamic>> handleResponse(http.Response response) async {
  final body = response.body.isEmpty ? <String, dynamic>{} : (jsonDecode(response.body) as Map<String, dynamic>? ?? {});
  if (response.statusCode >= 200 && response.statusCode < 300) {
    return body;
  }
  String message = body['message'] as String? ?? 'Error de conexión';
  if (body['errors'] != null && body['errors'] is Map) {
    final errors = body['errors'] as Map;
    final first = errors.values.isNotEmpty ? errors.values.first : null;
    if (first is List && first.isNotEmpty) message = first.first as String;
  }
  throw ApiException(statusCode: response.statusCode, message: message, errors: body['errors'] as Map<String, dynamic>?);
}
