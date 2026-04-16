import 'dart:convert';

import '../constants/api_constants.dart';
import '../network/api_client.dart';

class PatientRepository {
  /// Lista de doctores (aprobados). Paginado.
  /// [page] opcional, [q] búsqueda, [specialityId] opcional.
  static Future<Map<String, dynamic>> getDoctors({
    int page = 1,
    String? q,
    int? specialityId,
  }) async {
    final query = <String, String>{'page': page.toString()};
    if (q != null && q.isNotEmpty) query['q'] = q;
    if (specialityId != null) query['speciality_id'] = specialityId.toString();
    final response = await ApiClient.get(
      ApiConstants.patientDoctorsUri,
      queryParams: query,
      withToken: false,
    );
    return handleResponse(response);
  }

  /// Detalle de un doctor (público).
  static Future<Map<String, dynamic>> getDoctorDetail(int id) async {
    final response = await ApiClient.get(
      ApiConstants.patientDoctorDetailUri(id),
      withToken: false,
    );
    return handleResponse(response);
  }

  /// Especialidades (público). La API puede devolver array directo o { data: [] }.
  static Future<List<dynamic>> getSpecialities() async {
    final response = await ApiClient.get(
      ApiConstants.patientSpecialitiesUri,
      withToken: false,
    );
    if (response.statusCode < 200 || response.statusCode >= 300) return <dynamic>[];
    final decoded = jsonDecode(response.body);
    if (decoded is List) return decoded;
    if (decoded is Map && decoded['data'] is List) return decoded['data'] as List<dynamic>;
    return <dynamic>[];
  }

  /// Citas del paciente (requiere token).
  static Future<List<dynamic>> getAppointments() async {
    final response = await ApiClient.get(ApiConstants.patientAppointmentsUri);
    final data = await handleResponse(response);
    if (data is List) return data as List<dynamic>;
    if (data['data'] is List) return data['data'] as List<dynamic>;
    return <dynamic>[];
  }

  /// Reservar cita (requiere token).
  static Future<Map<String, dynamic>> bookAppointment({
    required int doctorProfileId,
    required String appointmentDate,
    required String startTime,
    String? reason,
  }) async {
    final response = await ApiClient.post(
      ApiConstants.patientBookUri,
      body: {
        'doctor_profile_id': doctorProfileId,
        'appointment_date': appointmentDate,
        'start_time': startTime,
        if (reason != null && reason.isNotEmpty) 'reason': reason,
      },
    );
    return handleResponse(response);
  }
}
