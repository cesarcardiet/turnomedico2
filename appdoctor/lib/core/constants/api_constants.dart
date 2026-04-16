/// Constantes de la API Turno Médico.
/// Una sola URL base; todos los endpoints referencian esta base.
class ApiConstants {
  ApiConstants._();

  // ——— URL base (servidor donde está montada la web) ———
  static const String baseUrl = 'http://test.turnomedicord.com';
  static const String apiPath = '/api';
  static String get apiBaseUrl => '$baseUrl$apiPath';

  // ——— Auth (públicos) ———
  static const String loginUri = '/login';
  static const String registerUri = '/register';

  // ——— Auth (con token) ———
  static const String logoutUri = '/logout';
  static const String userUri = '/user';

  // ——— Paciente (públicos, sin token) ———
  static const String patientSpecialitiesUri = '/patient/specialities';
  static const String patientDoctorsUri = '/patient/doctors';
  static String patientDoctorDetailUri(int id) => '/patient/doctors/$id';

  // ——— Paciente (con token) ———
  static const String patientAppointmentsUri = '/patient/appointments';
  static const String patientBookUri = '/patient/appointments';

  // ——— Doctor (con token) ———
  static const String doctorDashboardUri = '/doctor/dashboard';
  static const String doctorAppointmentsUri = '/doctor/appointments';
  static String doctorAppointmentStatusUri(int id) => '/doctor/appointments/$id/status';

  // ——— Chat (con token) ———
  static const String chatRoomsUri = '/chat/rooms';
  static String chatRoomMessagesUri(String roomId) => '/chat/rooms/$roomId/messages';
  static const String chatSendMessageUri = '/chat/messages';

  // ——— Claves de almacenamiento local ———
  static const String storageToken = 'turnomedico_token';
  static const String storageUser = 'turnomedico_user';
  static const String storageRole = 'turnomedico_role';

  /// Convierte ruta relativa de imagen en URL completa (para fotos de perfil, etc.).
  static String imageUrl(String? path) {
    if (path == null || path.isEmpty) return '';
    if (path.startsWith('http://') || path.startsWith('https://')) return path;
    final base = baseUrl.endsWith('/') ? baseUrl : '$baseUrl/';
    final p = path.startsWith('/') ? path.substring(1) : path;
    return '$base$p';
  }
}
