import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';
import '../constants/api_constants.dart';

class TokenStorage {
  static SharedPreferences? _prefs;

  static Future<void> init() async {
    _prefs ??= await SharedPreferences.getInstance();
  }

  static Future<void> setToken(String token) async {
    await init();
    await _prefs!.setString(ApiConstants.storageToken, token);
  }

  static Future<String?> getToken() async {
    await init();
    return _prefs!.getString(ApiConstants.storageToken);
  }

  static Future<void> setUser(Map<String, dynamic> user) async {
    await init();
    await _prefs!.setString(ApiConstants.storageUser, jsonEncode(user));
  }

  static Future<Map<String, dynamic>?> getUser() async {
    await init();
    final s = _prefs!.getString(ApiConstants.storageUser);
    if (s == null) return null;
    return Map<String, dynamic>.from(jsonDecode(s) as Map);
  }

  static Future<void> setRole(String role) async {
    await init();
    await _prefs!.setString(ApiConstants.storageRole, role);
  }

  static Future<String?> getRole() async {
    await init();
    return _prefs!.getString(ApiConstants.storageRole);
  }

  static Future<void> clear() async {
    await init();
    await _prefs!.remove(ApiConstants.storageToken);
    await _prefs!.remove(ApiConstants.storageUser);
    await _prefs!.remove(ApiConstants.storageRole);
  }
}
