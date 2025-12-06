import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter/foundation.dart' show kDebugMode;
import 'package:flutter_background_service/flutter_background_service.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../services/api_service.dart';
import '../utils/constants.dart';
import 'login_screen.dart';
import 'qr_scanner_screen.dart';

class HomeScreen extends StatefulWidget {
  final String token;
  const HomeScreen({super.key, required this.token});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  final _apiService = ApiService();
  Map<String, dynamic>? _userProfile;
  Map<String, dynamic>? _activeSchedule;
  List<dynamic> _attendanceHistory = [];
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    // Skip background service check in debug mode (emulator)
    if (!kDebugMode) {
      _checkServiceStatus();
    }
    _loadData();
  }

  Future<void> _checkServiceStatus() async {
    try {
      final service = FlutterBackgroundService();
      var isRunning = await service.isRunning();
      if (!isRunning) {
        await service.startService();
      }
    } catch (e) {
      print("Background service error: $e");
    }
  }

  Future<void> _loadData() async {
    setState(() => _isLoading = true);
    try {
      final scheduleData = await _apiService.getActiveSchedule();
      final historyData = await _apiService.getAttendanceHistory();
      final userData = await _apiService.getUserProfile();

      setState(() {
        if (scheduleData['status'] == 'active_schedule') {
          _activeSchedule = scheduleData['data'];
        } else {
          _activeSchedule = null;
        }
        _attendanceHistory = historyData;
        _userProfile = userData;
      });
    } catch (e) {
      print("Error loading data: $e");
    } finally {
      setState(() => _isLoading = false);
    }
  }

  Future<void> _logout() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(Constants.tokenKey);
    
    final service = FlutterBackgroundService();
    service.invoke("stopService");

    if (mounted) {
      Navigator.pushReplacement(
        context,
        MaterialPageRoute(builder: (context) => const LoginScreen()),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Dashboard Tewak'),
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadData,
          ),
          IconButton(
            icon: const Icon(Icons.logout),
            onPressed: _logout,
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: _loadData,
        child: SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.all(16.0),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // 1. Profile Header
              if (_userProfile != null) _buildProfileHeader(),
              const SizedBox(height: 24),

              // 2. Active Schedule Card
              _buildScheduleCard(),
              const SizedBox(height: 24),

              // 3. Attendance History Header
              const Text(
                'Riwayat Absensi Hari Ini',
                style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
              ),
              const SizedBox(height: 12),

              // 4. Attendance List
              _buildAttendanceList(),
            ],
          ),
        ),
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () async {
          final result = await Navigator.push(
            context,
            MaterialPageRoute(builder: (context) => const QRScannerScreen()),
          );
          if (result == true) {
            _loadData();
          }
        },
        label: const Text('Scan Absen'),
        icon: const Icon(Icons.qr_code_scanner),
      ),
    );
  }

  Widget _buildProfileHeader() {
    return Row(
      children: [
        CircleAvatar(
          radius: 30,
          backgroundImage: _userProfile!['photo_url'] != null
              ? NetworkImage(_userProfile!['photo_url'])
              : null,
          child: _userProfile!['photo_url'] == null
              ? const Icon(Icons.person, size: 30)
              : null,
        ),
        const SizedBox(width: 16),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text(
                'Selamat Datang,',
                style: TextStyle(color: Colors.grey, fontSize: 14),
              ),
              Text(
                _userProfile!['full_name'] ?? 'Guru',
                style: const TextStyle(
                  fontSize: 20,
                  fontWeight: FontWeight.bold,
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }

  Widget _buildScanButton() {
    return SizedBox(
      height: 55,
      child: ElevatedButton.icon(
        onPressed: () async {
          final result = await Navigator.push(
            context,
            MaterialPageRoute(builder: (context) => const QRScannerScreen()),
          );
          if (result == true) {
            _loadData();
          }
        },
        icon: const Icon(Icons.qr_code_scanner, size: 28),
        label: const Text(
          'SCAN QR CODE',
          style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
        ),
        style: ElevatedButton.styleFrom(
          backgroundColor: Colors.blue,
          foregroundColor: Colors.white,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(12),
          ),
          elevation: 4,
        ),
      ),
    );
  }

  Widget _buildScheduleCard() {
    return Card(
      elevation: 4,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      color: _activeSchedule != null ? Colors.blue.shade50 : Colors.grey.shade100,
      child: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          children: [
            Row(
              children: [
                Icon(
                  Icons.calendar_today,
                  color: _activeSchedule != null ? Colors.blue : Colors.grey,
                ),
                const SizedBox(width: 8),
                Text(
                  _activeSchedule != null ? 'Jadwal Aktif' : 'Tidak Ada Jadwal',
                  style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                ),
              ],
            ),
            if (_activeSchedule != null) ...[
              const Divider(height: 24),
              _buildInfoRow(Icons.class_, 'Kelas', _activeSchedule!['class_name']),
              const SizedBox(height: 8),
              _buildInfoRow(Icons.book, 'Mapel', _activeSchedule!['subject']),
              const SizedBox(height: 8),
              _buildInfoRow(Icons.access_time, 'Waktu', 
                  '${_activeSchedule!['start_time']} - ${_activeSchedule!['end_time']}'),
            ] else ...[
              const SizedBox(height: 16),
              const Text('Belum ada jadwal mengajar saat ini.'),
            ]
          ],
        ),
      ),
    );
  }

  Widget _buildInfoRow(IconData icon, String label, String value) {
    return Row(
      children: [
        Icon(icon, size: 16, color: Colors.grey.shade600),
        const SizedBox(width: 8),
        Text('$label: ', style: TextStyle(color: Colors.grey.shade600)),
        Expanded(
          child: Text(
            value,
            style: const TextStyle(fontWeight: FontWeight.bold),
            overflow: TextOverflow.ellipsis,
          ),
        ),
      ],
    );
  }

  Widget _buildAttendanceList() {
    if (_isLoading) {
      return const Center(child: CircularProgressIndicator());
    }

    if (_attendanceHistory.isEmpty) {
      return Container(
        padding: const EdgeInsets.all(32),
        alignment: Alignment.center,
        child: Column(
          children: [
            Icon(Icons.history, size: 48, color: Colors.grey.shade300),
            const SizedBox(height: 8),
            const Text('Belum ada riwayat absensi hari ini'),
          ],
        ),
      );
    }

    return ListView.builder(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      itemCount: _attendanceHistory.length,
      itemBuilder: (context, index) {
        final item = _attendanceHistory[index];
        return Card(
          margin: const EdgeInsets.only(bottom: 8),
          child: ListTile(
            leading: CircleAvatar(
              backgroundColor: item['status'] == 'hadir' 
                  ? Colors.green.shade100 
                  : (item['status'] == 'telat' ? Colors.orange.shade100 : Colors.red.shade100),
              child: Icon(
                item['status'] == 'hadir' 
                    ? Icons.check 
                    : (item['status'] == 'telat' ? Icons.access_time : Icons.close),
                color: item['status'] == 'hadir' 
                    ? Colors.green 
                    : (item['status'] == 'telat' ? Colors.orange : Colors.red),
              ),
            ),
            title: Text(item['class_name'], style: const TextStyle(fontWeight: FontWeight.bold)),
            subtitle: Text('${item['subject']} • ${item['time']}'),
            trailing: Chip(
              label: Text(
                item['status'].toString().toUpperCase(),
                style: const TextStyle(color: Colors.white, fontSize: 10),
              ),
              backgroundColor: item['status'] == 'hadir' 
                  ? Colors.green 
                  : (item['status'] == 'telat' ? Colors.orange : Colors.red),
              padding: EdgeInsets.zero,
              visualDensity: VisualDensity.compact,
            ),
          ),
        );
      },
    );
  }
}
