import 'dart:async';
import 'dart:ui';
import 'package:flutter/material.dart';
import 'package:flutter_background_service/flutter_background_service.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'package:geolocator/geolocator.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'api_service.dart';

class BackgroundService {
  static Future<void> initializeService() async {
    final service = FlutterBackgroundService();

    const AndroidNotificationChannel channel = AndroidNotificationChannel(
      'my_foreground', // id
      'MY FOREGROUND SERVICE', // title
      description: 'This channel is used for important notifications.', // description
      importance: Importance.low,
    );

    final FlutterLocalNotificationsPlugin flutterLocalNotificationsPlugin =
        FlutterLocalNotificationsPlugin();

    await flutterLocalNotificationsPlugin
        .resolvePlatformSpecificImplementation<
            AndroidFlutterLocalNotificationsPlugin>()
        ?.createNotificationChannel(channel);

    await service.configure(
      androidConfiguration: AndroidConfiguration(
        onStart: onStart,
        autoStart: true,
        isForegroundMode: true,
        notificationChannelId: 'my_foreground',
        initialNotificationTitle: 'Tewak Service',
        initialNotificationContent: 'Aplikasi Berjalan',
        foregroundServiceNotificationId: 888,
      ),
      iosConfiguration: IosConfiguration(
        autoStart: true,
        onForeground: onStart,
        onBackground: onIosBackground,
      ),
    );
  }

  @pragma('vm:entry-point')
  static Future<bool> onIosBackground(ServiceInstance service) async {
    return true;
  }

  @pragma('vm:entry-point')
  static void onStart(ServiceInstance service) async {
    DartPluginRegistrant.ensureInitialized();
    
    final apiService = ApiService();
    final FlutterLocalNotificationsPlugin flutterLocalNotificationsPlugin =
        FlutterLocalNotificationsPlugin();

    service.on('stopService').listen((event) {
      service.stopSelf();
    });

    Timer.periodic(const Duration(seconds: 15), (timer) async {
      if (service is AndroidServiceInstance) {
        if (await service.isForegroundService()) {
          service.setForegroundNotificationInfo(
            title: "Tewak Monitoring",
            content: "Memantau lokasi anda secara real-time",
          );
        }
      }

      try {
        // 1. Get Location
        Position position = await Geolocator.getCurrentPosition(
            desiredAccuracy: LocationAccuracy.high);

        // 2. Update Location to Server
        await apiService.updateLocation(
            position.latitude, position.longitude, 'active');

        // 3. Check Schedule and Geofence
        final scheduleData = await apiService.getActiveSchedule();
        
        if (scheduleData['status'] == 'active_schedule') {
          final data = scheduleData['data'];
          int? scheduleId = data['schedule_id'];
          String className = data['class_name'];
          double classLat = double.parse(data['class_lat'].toString());
          double classLng = double.parse(data['class_lng'].toString());
          double radius = double.parse(data['radius'].toString());
          
          double distance = Geolocator.distanceBetween(
              position.latitude, position.longitude, classLat, classLng);

          if (distance > radius) {
            // Show local notification to teacher
            _showNotification(flutterLocalNotificationsPlugin, 
                "Peringatan!", 
                "Anda berada ${distance.toStringAsFixed(0)}m dari kelas. Harap kembali mengajar!");
            
            // Report violation to server (for admin notification)
            await apiService.reportGeofenceViolation(
              scheduleId: scheduleId,
              className: className,
              teacherLat: position.latitude,
              teacherLng: position.longitude,
              classLat: classLat,
              classLng: classLng,
              distance: distance,
              radius: radius,
            );
          }
        }
      } catch (e) {
        print("Background service error: $e");
      }
    });
  }

  static Future<void> _showNotification(
      FlutterLocalNotificationsPlugin fln, String title, String body) async {
    const AndroidNotificationDetails androidPlatformChannelSpecifics =
        AndroidNotificationDetails(
      'geofence_alerts', 
      'Geofence Alerts',
      channelDescription: 'Notifications for geofence alerts',
      importance: Importance.max,
      priority: Priority.high,
      ticker: 'ticker',
    );
    const NotificationDetails platformChannelSpecifics =
        NotificationDetails(android: androidPlatformChannelSpecifics);
    await fln.show(0, title, body, platformChannelSpecifics);
  }
}
