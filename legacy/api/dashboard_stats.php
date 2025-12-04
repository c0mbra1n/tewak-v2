<?php
require_once '../includes/config.php';

// Set timezone
date_default_timezone_set('Asia/Jakarta');

$today = date('Y-m-d');

try {
    // 1. Total Teachers
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'guru'");
    $total_teachers = $stmt->fetchColumn();

    // 2. Present Today (Unique teachers who scanned today)
    $stmt = $pdo->prepare("SELECT COUNT(DISTINCT user_id) FROM attendance WHERE date = ? AND status = 'hadir'");
    $stmt->execute([$today]);
    $present_today = $stmt->fetchColumn();

    // 3. Absent Today (Total Teachers - Present Today)
    // Note: This is a simple calculation. For more accuracy, we might check permissions.
    $absent_today = $total_teachers - $present_today;

    // 4. Permission Today (Izin/Sakit/Dinas)
    $stmt = $pdo->prepare("SELECT COUNT(DISTINCT user_id) FROM attendance WHERE date = ? AND status IN ('izin', 'sakit', 'dinas')");
    $stmt->execute([$today]);
    $permission_today = $stmt->fetchColumn();

    // 5. Attendance per Day (Last 7 Days)
    $labels = [];
    $data_present = [];
    $data_absent = [];

    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $labels[] = date('d M', strtotime($date));

        $stmt = $pdo->prepare("SELECT COUNT(DISTINCT user_id) FROM attendance WHERE date = ? AND status = 'hadir'");
        $stmt->execute([$date]);
        $present = $stmt->fetchColumn();

        $data_present[] = $present;
        $data_absent[] = $total_teachers - $present;
    }

    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'success',
        'total_teachers' => $total_teachers,
        'present_today' => $present_today,
        'absent_today' => $absent_today,
        'permission_today' => $permission_today,
        'chart_labels' => $labels,
        'chart_data_present' => $data_present,
        'chart_data_absent' => $data_absent
    ]);

} catch (PDOException $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>