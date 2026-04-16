import '../constants/api_constants.dart';
import '../network/api_client.dart';
import '../storage/token_storage.dart';

class AuthRepository {
  static Future<Map<String, dynamic>> login({
    required String email,
    required String password,
  }) async {
    final response = await ApiClient.post(
      ApiConstants.loginUri,
      body: {
        'email': email,
        'password': password,
        'device_name': 'appdoctor_mobile',
      },
      withToken: false,
    );
    final data = await handleResponse(response);
    final token = data['token'] as String?;
    final user = data['user'] as Map<String, dynamic>?;
    if (token == null || user == null) throw ApiException(message: 'Respuesta inválida');
    await TokenStorage.setToken(token);
    await TokenStorage.setUser(user);
    final roles = user['roles'];
    final role = (roles is List && roles.isNotEmpty) ? roles.first.toString() : 'patient';
    await TokenStorage.setRole(role);
    return data;
  }

  static Future<Map<String, dynamic>> register({
    required String name,
    required String email,
    required String password,
    required String passwordConfirmation,
    required String role,
  }) async {
    final response = await ApiClient.post(
      ApiConstants.registerUri,
      body: {
        'name': name,
        'email': email,
        'password': password,
        'password_confirmation': passwordConfirmation,
        'role': role,
      },
      withToken: false,
    );
    final data = await handleResponse(response);
    final token = data['token'] as String?;
    final user = data['user'] as Map<String, dynamic>?;
    if (token == null || user == null) throw ApiException(message: 'Respuesta inválida');
    await TokenStorage.setToken(token);
    await TokenStorage.setUser(user);
    await TokenStorage.setRole(role);
    return data;
  }

  static Future<void> logout() async {
    try {
      await ApiClient.post(ApiConstants.logoutUri);
    } catch (_) {}
    await TokenStorage.clear();
  }

  static Future<bool> isLoggedIn() async {
    final token = await TokenStorage.getToken();
    return token != null && token.isNotEmpty;
  }
}
