<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse('error', 'Method not allowed');
}

if (!isLoggedIn() || $_SESSION['role'] !== 'guru') {
    jsonResponse('error', 'Unauthorized');
}

$input = json_decode(file_get_contents('php://input'), true);
$qr_code = $input['qr_code'] ?? '';

if (empty($qr_code)) {
    jsonResponse('error', 'QR Code is required');
}

// DEBUG LOGGING
file_put_contents('../debug_scan.log', date('Y-m-d H:i:s') . " - Received QR: " . $qr_code . "\n", FILE_APPEND);

// Parse dynamic QR code (Format: CODE|YYYY-MM-DD)
$parts = explode('|', $qr_code);
$static_code = trim($parts[0]);
$qr_date = trim($parts[1] ?? '');

file_put_contents('../debug_scan.log', date('Y-m-d H:i:s') . " - Parsed Static: '$static_code', Date: '$qr_date'\n", FILE_APPEND);

if ($qr_date !== date('Y-m-d')) {
    jsonResponse('error', 'QR Code kadaluarsa atau tidak valid. Pastikan scan QR Code hari ini.');
}

// Find class by static QR code
$stmt = $pdo->prepare("SELECT * FROM classes WHERE qr_code = ?");
$stmt->execute([$static_code]);
$class = $stmt->fetch();

if (!$class) {
    jsonResponse('error', "Kelas tidak ditemukan. Code: '$static_code', Date: '$qr_date'");
}

// Location Validation
$user_lat = $input['latitude'] ?? null;
$user_lng = $input['longitude'] ?? null;

// Only validate if class has location set
if (!empty($class['latitude']) && !empty($class['longitude'])) {
    if (empty($user_lat) || empty($user_lng)) {
        jsonResponse('error', 'Lokasi tidak terdeteksi. Pastikan GPS aktif dan izin lokasi diberikan.');
    }

    $class_lat = $class['latitude'];
    $class_lng = $class['longitude'];
    $radius_meters = $class['radius'] ?? 50;

    // Haversine Formula
    $earth_radius = 6371000; // Meters
    $dLat = deg2rad($user_lat - $class_lat);
    $dLng = deg2rad($user_lng - $class_lng);
    $a = sin($dLat / 2) * sin($dLat / 2) +
        cos(deg2rad($class_lat)) * cos(deg2rad($user_lat)) *
        sin($dLng / 2) * sin($dLng / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    $distance = $earth_radius * $c;

    // DEBUG LOGGING
    file_put_contents('../debug_scan.log', date('Y-m-d H:i:s') . " - Location Check: Class($class_lat, $class_lng) vs User($user_lat, $user_lng) -> Dist: " . round($distance) . "m / Radius: $radius_meters\n", FILE_APPEND);

    if ($distance > $radius_meters) {
        jsonResponse('error', "Anda berada di luar radius kelas. Jarak: " . round($distance) . "m (Maks: $radius_meters m). Silakan mendekat ke kelas.");
    }
} else {
    file_put_contents('../debug_scan.log', date('Y-m-d H:i:s') . " - Location Check Skipped: Class location not set.\n", FILE_APPEND);
}

$user_id = $_SESSION['user_id'];
$class_id = $class['id'];
$date = date('Y-m-d');
$time = date('H:i:s');

// Check if already scanned today for this class AND this subject
$subject = $input['subject'] ?? '';
$stmt = $pdo->prepare("SELECT * FROM attendance WHERE user_id = ? AND class_id = ? AND date = ? AND subject = ?");
$stmt->execute([$user_id, $class_id, $date, $subject]);
$existing = $stmt->fetch();

if ($existing) {
    jsonResponse('error', "Anda sudah melakukan absensi untuk mata pelajaran '$subject' di kelas ini hari ini");
}

// Check for specific schedule
$day_name = date('l'); // e.g., "Monday"
$stmt = $pdo->prepare("SELECT start_time FROM schedules WHERE user_id = ? AND class_id = ? AND day = ?");
$stmt->execute([$user_id, $class_id, $day_name]);
$schedule = $stmt->fetch();

if ($schedule) {
    // Tolerance 15 mins from schedule start time
    $late_threshold = date('H:i:s', strtotime($schedule['start_time'] . ' +15 minutes'));
} else {
    // Default logic (Dynamic from Settings)
    $late_threshold = $DEFAULT_START_TIME;
}

$current_time = date('H:i:s');
$status = ($current_time > $late_threshold) ? 'telat' : 'hadir';

// Get subject from request or default to user's first subject
$subject = $input['subject'] ?? null;
if (!$subject) {
    // Fallback: use the first subject from user profile
    $stmt = $pdo->prepare("SELECT subject FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if ($user && !empty($user['subject'])) {
        $user_subjects = explode(',', $user['subject']);
        $subject = trim($user_subjects[0]);
    } else {
        // Default subject if user has none defined
        $subject = 'Umum';
    }
}

// Insert attendance
try {
    $stmt = $pdo->prepare("INSERT INTO attendance (user_id, class_id, status, date, subject) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $class_id, $status, $date, $subject]);
    jsonResponse('success', 'Berhasil absen di kelas ' . $class['class_name'], ['status' => $status, 'subject' => $subject, 'class_lat' => $class['latitude'], 'class_lng' => $class['longitude'], 'radius' => $class['radius'] ?? 50]);
} catch (PDOException $e) {
    jsonResponse('error', 'Database error: ' . $e->getMessage());
}
?>