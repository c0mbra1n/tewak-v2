<?php
require_once '../includes/config.php';

// Set timezone to Indonesia/Jakarta
date_default_timezone_set('Asia/Jakarta');

$current_day = date('l'); // e.g., "Monday"
$current_time = date('H:i:s');
$current_date = date('Y-m-d');

// Query to get ACTIVE schedules for right now
$sql = "
    SELECT 
        c.class_name,
        s.subject,
        s.start_time,
        s.end_time,
        u.full_name as teacher_name,
        a.scan_time,
        a.status as attendance_status,
        p.status as permission_status
    FROM schedules s
    JOIN classes c ON s.class_id = c.id
    JOIN users u ON s.user_id = u.id
    LEFT JOIN attendance a ON 
        s.user_id = a.user_id AND 
        s.class_id = a.class_id AND 
        a.date = :current_date AND
        a.subject = s.subject
    LEFT JOIN attendance p ON
        s.user_id = p.user_id AND
        p.date = :current_date AND
        p.status IN ('izin', 'sakit', 'dinas')
    WHERE s.day = :current_day
    AND :current_time BETWEEN s.start_time AND s.end_time
    ORDER BY c.class_name ASC
";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'current_date' => $current_date,
        'current_day' => $current_day,
        'current_time' => $current_time
    ]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode(['status' => 'success', 'data' => $data, 'timestamp' => date('d F Y H:i:s')]);
} catch (PDOException $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>