import 'package:flutter/material.dart';
import 'package:flutter/foundation.dart' show kDebugMode;
import 'package:permission_handler/permission_handler.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'screens/login_screen.dart';
import 'screens/home_screen.dart';
import 'services/background_service.dart';
import 'utils/constants.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  
  // Only initialize background service in release mode or physical device
  // Skip on debug mode to prevent emulator crashes
  if (!kDebugMode) {
    try {
      await BackgroundService.initializeService();
    } catch (e) {
      print("Background service init failed: $e");
    }
  } else {
    print("DEBUG MODE: Skipping background service initialization");
  }
  
  // Request permissions
  try {
    await [
      Permission.location,
      Permission.locationAlways,
      Permission.notification,
    ].request();
  } catch (e) {
    print("Permission request failed: $e");
  }

  final prefs = await SharedPreferences.getInstance();
  final token = prefs.getString(Constants.tokenKey);
  final isLoggedIn = token != null;

  runApp(MyApp(isLoggedIn: isLoggedIn, token: token));
}

class MyApp extends StatelessWidget {
  final bool isLoggedIn;
  final String? token;

  const MyApp({super.key, required this.isLoggedIn, this.token});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Tewak Mobile',
      theme: ThemeData(
        primarySwatch: Colors.blue,
        useMaterial3: true,
      ),
      home: isLoggedIn ? HomeScreen(token: token!) : const LoginScreen(),
    );
  }
}
