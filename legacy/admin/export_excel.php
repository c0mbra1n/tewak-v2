<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

requireRole(['super_admin']);

// Handle Filter
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');
$teacher_id = $_GET['teacher_id'] ?? '';

$where = "WHERE a.date BETWEEN ? AND ?";
$params = [$start_date, $end_date];

if ($teacher_id) {
    $where .= " AND a.user_id = ?";
    $params[] = $teacher_id;
}

// Fetch Attendance Data
$sql = "
    SELECT 
        a.date,
        u.full_name,
        c.class_name,
        a.subject as actual_subject,
        u.subject as default_subject,
        a.scan_time,
        a.status
    FROM attendance a
    JOIN users u ON a.user_id = u.id
    JOIN classes c ON a.class_id = c.id
    $where
    ORDER BY a.date DESC, a.scan_time DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$attendance_data = $stmt->fetchAll();

// Set headers for Excel download
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=laporan_absensi_" . $start_date . "_to_" . $end_date . ".xls");
header("Pragma: no-cache");
header("Expires: 0");

// Output HTML Table
?>
<table border="1">
    <thead>
        <tr>
            <th style="background-color: #f2f2f2;">Tanggal</th>
            <th style="background-color: #f2f2f2;">Nama Guru</th>
            <th style="background-color: #f2f2f2;">Kelas</th>
            <th style="background-color: #f2f2f2;">Mata Pelajaran</th>
            <th style="background-color: #f2f2f2;">Waktu Scan</th>
            <th style="background-color: #f2f2f2;">Status</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($attendance_data as $row): ?>
            <?php
            // Determine subject
            $subject = $row['actual_subject'];
            if (empty($subject)) {
                $subjects = explode(',', $row['default_subject'] ?? '');
                $subject = trim($subjects[0] ?? '-');
            }
            ?>
            <tr>
                <td><?= $row['date'] ?></td>
                <td><?= htmlspecialchars($row['full_name']) ?></td>
                <td><?= htmlspecialchars($row['class_name']) ?></td>
                <td><?= htmlspecialchars($subject) ?></td>
                <td><?= $row['scan_time'] ?></td>
                <td><?= strtoupper(str_replace('_', ' ', $row['status'])) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>