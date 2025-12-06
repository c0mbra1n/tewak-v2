import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../utils/constants.dart';

class ApiService {
  Future<Map<String, dynamic>> login(String username, String password) async {
    final response = await http.post(
      Uri.parse('${Constants.baseUrl}/login'),
      body: {
        'username': username,
        'password': password,
      },
      headers: {'Accept': 'application/json'},
    );

    if (response.statusCode == 200) {
      final data = json.decode(response.body);
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString(Constants.tokenKey, data['access_token']);
      return data;
    } else {
      throw Exception('Failed to login: ${response.body}');
    }
  }

  Future<Map<String, dynamic>> getActiveSchedule() async {
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString(Constants.tokenKey);

    final response = await http.get(
      Uri.parse('${Constants.baseUrl}/schedule/active'),
      headers: {
        'Authorization': 'Bearer $token',
        'Accept': 'application/json',
      },
    );

    if (response.statusCode == 200) {
      return json.decode(response.body);
    } else {
      throw Exception('Failed to get schedule');
    }
  }

  Future<void> updateLocation(double lat, double lng, String status) async {
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString(Constants.tokenKey);

    await http.post(
      Uri.parse('${Constants.baseUrl}/location/update'),
      body: {
        'latitude': lat.toString(),
        'longitude': lng.toString(),
        'status': status,
      },
      headers: {
        'Authorization': 'Bearer $token',
        'Accept': 'application/json',
      },
    );
  }
  Future<List<dynamic>> getAttendanceHistory() async {
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString(Constants.tokenKey);

    final response = await http.get(
      Uri.parse('${Constants.baseUrl}/attendance/history'),
      headers: {
        'Authorization': 'Bearer $token',
        'Accept': 'application/json',
      },
    );

    if (response.statusCode == 200) {
      final data = json.decode(response.body);
      return data['data'];
    } else {
      throw Exception('Failed to get attendance history');
    }
  }

  Future<Map<String, dynamic>> scanQr(String qrCode, double? lat, double? lng) async {
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString(Constants.tokenKey);

    final response = await http.post(
      Uri.parse('${Constants.baseUrl}/scan'),
      body: {
        'qr_code': qrCode,
        'latitude': lat?.toString() ?? '',
        'longitude': lng?.toString() ?? '',
      },
      headers: {
        'Authorization': 'Bearer $token',
        'Accept': 'application/json',
      },
    );

    final data = json.decode(response.body);
    if (response.statusCode == 200) {
      return data;
    } else {
      throw Exception(data['message'] ?? 'Failed to scan QR');
    }
  }

  Future<Map<String, dynamic>> getUserProfile() async {
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString(Constants.tokenKey);

    final response = await http.get(
      Uri.parse('${Constants.baseUrl}/user'),
      headers: {
        'Authorization': 'Bearer $token',
        'Accept': 'application/json',
      },
    );

    if (response.statusCode == 200) {
      return json.decode(response.body);
    } else {
      throw Exception('Failed to get user profile');
    }
  }

  Future<void> reportGeofenceViolation({
    int? scheduleId,
    required String className,
    required double teacherLat,
    required double teacherLng,
    required double classLat,
    required double classLng,
    required double distance,
    required double radius,
  }) async {
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString(Constants.tokenKey);

    await http.post(
      Uri.parse('${Constants.baseUrl}/geofence/violation'),
      body: {
        'schedule_id': scheduleId?.toString() ?? '',
        'class_name': className,
        'teacher_lat': teacherLat.toString(),
        'teacher_lng': teacherLng.toString(),
        'class_lat': classLat.toString(),
        'class_lng': classLng.toString(),
        'distance': distance.toString(),
        'radius': radius.toString(),
      },
      headers: {
        'Authorization': 'Bearer $token',
        'Accept': 'application/json',
      },
    );
  }
}

