<?php
require_once 'config.php';
require_once 'auth.php';

requireRole(['super_admin']);

// Fetch Teachers for Dropdown
$stmt = $pdo->query("SELECT id, full_name FROM users WHERE role = 'guru' ORDER BY full_name ASC");
$teachers = $stmt->fetchAll();

// Handle Filter
$start_date = $_GET['start_date'] ?? date('Y-m-01'); // Default to first day of current month
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
        a.scan_time,
        a.status,
        a.subject as actual_subject,
        u.full_name,
        u.subject as default_subject,
        c.class_name
    FROM attendance a
    JOIN users u ON a.user_id = u.id
    JOIN classes c ON a.class_id = c.id
    $where
    ORDER BY a.date DESC, a.scan_time DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$attendance_data = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Absensi - Tewak Apps</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/themes.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
</head>

<body class="theme-material">
    <nav class="navbar">
        <a href="#" class="navbar-brand">Tewak Apps Admin</a>
        <ul class="navbar-nav">
            <li class="nav-item"><a href="admin_dashboard.php" class="nav-link">Users</a></li>
            <li class="nav-item"><a href="classes.php" class="nav-link">Kelas</a></li>
            <li class="nav-item"><a href="reports.php" class="nav-link active">Laporan</a></li>
            <li class="nav-item"><a href="monitor.php" class="nav-link" target="_blank">Monitoring</a></li>
            <li class="nav-item"><a href="logout.php" class="nav-link">Logout</a></li>
        </ul>
    </nav>

    <div class="container">
        <div class="card">
            <h3>Filter Laporan</h3>
            <form method="GET" action="reports.php"
                style="display: flex; gap: 15px; flex-wrap: wrap; align-items: flex-end;">
                <div class="form-group" style="flex: 1; min-width: 150px;">
                    <label>Dari Tanggal</label>
                    <input type="date" name="start_date" class="form-control" value="<?= $start_date ?>">
                </div>
                <div class="form-group" style="flex: 1; min-width: 150px;">
                    <label>Sampai Tanggal</label>
                    <input type="date" name="end_date" class="form-control" value="<?= $end_date ?>">
                </div>
                <div class="form-group" style="flex: 1; min-width: 200px;">
                    <label>Guru</label>
                    <select name="teacher_id" class="form-control">
                        <option value="">Semua Guru</option>
                        <?php foreach ($teachers as $t): ?>
                            <option value="<?= $t['id'] ?>" <?= $teacher_id == $t['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($t['full_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Tampilkan</button>
                    <a href="export_excel.php?start_date=<?= $start_date ?>&end_date=<?= $end_date ?>&teacher_id=<?= $teacher_id ?>"
                        target="_blank" class="btn btn-success" style="background-color: #217346;">Export Excel</a>
                    <button type="button" onclick="exportPDF()" class="btn btn-danger"
                        style="background-color: #dc3545;">Export PDF</button>
                </div>
            </form>
        </div>

        <div class="card" id="report-content">
            <h3>Data Absensi</h3>
            <p>Periode: <?= date('d/m/Y', strtotime($start_date)) ?> - <?= date('d/m/Y', strtotime($end_date)) ?></p>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Nama Guru</th>
                            <th>Kelas</th>
                            <th>Mata Pelajaran</th>
                            <th>Waktu Scan</th>
                            <th>Status</th>
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
                                <td><?= date('d/m/Y', strtotime($row['date'])) ?></td>
                                <td><?= htmlspecialchars($row['full_name']) ?></td>
                                <td><?= htmlspecialchars($row['class_name']) ?></td>
                                <td><?= htmlspecialchars($subject) ?></td>
                                <td><?= date('H:i', strtotime($row['scan_time'])) ?></td>
                                <td>
                                    <span class="status-badge status-<?= $row['status'] ?>">
                                        <?= strtoupper(str_replace('_', ' ', $row['status'])) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($attendance_data)): ?>
                            <tr>
                                <td colspan="6" class="text-center">Tidak ada data absensi pada periode ini.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
    <script>
        function exportPDF() {
            const element = document.getElementById('report-content');
            const opt = {
                margin: 1,
                filename: 'laporan_absensi.pdf',
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2 },
                jsPDF: { unit: 'in', format: 'letter', orientation: 'landscape' }
            };

            // Choose the element that our invoice is rendered in.
            html2pdf().set(opt).from(element).save();
        }
    </script>
</body>

</html>