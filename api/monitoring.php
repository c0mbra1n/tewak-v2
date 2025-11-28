<?php
require_once '../config.php';

$date = date('Y-m-d');

// Get all teachers
$stmt = $pdo->prepare("SELECT id, full_name, subject FROM users WHERE role = 'guru' ORDER BY full_name ASC");
$stmt->execute();
$teachers = $stmt->fetchAll();
// Get all teachers and their latest attendance for today in a single query
$stmt = $pdo->prepare("
    SELECT
        u.full_name,
        u.subject as default_subject,
        a.status,
        a.scan_time,
        a.subject as actual_subject,
        c.class_name
    FROM users u
    LEFT JOIN attendance a ON u.id = a.user_id AND a.date = ?
    LEFT JOIN classes c ON a.class_id = c.id
    WHERE u.role = 'guru'
    ORDER BY u.full_name ASC
");
$stmt->execute([$date]);
$teachers_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

$data = [];
foreach ($teachers_data as $teacher) {
    // Determine subject to display
    // If they have scanned (actual_subject exists), use it.
    // Otherwise, use their default subject (first one if multiple).
    $display_subject = $teacher['actual_subject'];

    if (empty($display_subject)) {
        $subjects = explode(',', $teacher['default_subject'] ?? '');
        $display_subject = trim($subjects[0] ?? '-');
    }

    $data[] = [
        'name' => $teacher['full_name'],
        'subject' => $display_subject,
        'status' => $teacher['status'] ?? 'belum_hadir',
        'time' => $teacher['scan_time'] ? date('H:i', strtotime($teacher['scan_time'])) : '-',
        'location' => $teacher['class_name'] ?? '-'
    ];
}

header('Content-Type: application/json');
echo json_encode($data);
?>