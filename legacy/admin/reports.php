<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

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

// Handle Delete Attendance
if (isset($_POST['action']) && $_POST['action'] === 'delete_attendance') {
    $delete_id = $_POST['id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM attendance WHERE id = ?");
        if ($stmt->execute([$delete_id])) {
            $message = "Data absensi berhasil dihapus.";
        } else {
            $error = "Gagal menghapus data absensi.";
        }
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Fetch Attendance Data
$sql = "
    SELECT 
        a.id,
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
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/themes.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="theme-flat">
    <nav class="navbar">
        <a href="#" class="navbar-brand">Tewak Apps Admin</a>
        <ul class="navbar-nav">
            <li class="nav-item"><a href="users.php" class="nav-link">Users</a></li>
            <li class="nav-item"><a href="classes.php" class="nav-link">Kelas</a></li>
            <li class="nav-item"><a href="reports.php" class="nav-link active">Laporan</a></li>
            <li class="nav-item"><a href="../monitor/monitor.php" class="nav-link" target="_blank">Monitoring</a></li>
            <li class="nav-item"><a href="../logout.php" class="nav-link">Logout</a></li>
        </ul>
    </nav>

    <div class="container dashboard-container">
        <div class="card dashboard-left">
            <h3>Filter Laporan</h3>
            <?php if (isset($message)): ?>
                <script>Swal.fire({ icon: 'success', title: 'Berhasil', text: '<?= $message ?>', timer: 2000, showConfirmButton: false });</script>
            <?php endif; ?>
            <?php if (isset($error)): ?>
                <script>Swal.fire({ icon: 'error', title: 'Gagal', text: '<?= $error ?>' });</script>
            <?php endif; ?>

            <form method="GET" action="reports.php" style="display: flex; flex-direction: column; gap: 15px;">
                <div class="form-group">
                    <label>Dari Tanggal</label>
                    <input type="date" name="start_date" class="form-control" value="<?= $start_date ?>">
                </div>
                <div class="form-group">
                    <label>Sampai Tanggal</label>
                    <input type="date" name="end_date" class="form-control" value="<?= $end_date ?>">
                </div>
                <div class="form-group">
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
                    <button type="submit" class="btn btn-primary btn-block">Tampilkan</button>
                    <div style="margin-top: 10px; display: flex; gap: 5px;">
                        <a href="export_excel.php?start_date=<?= $start_date ?>&end_date=<?= $end_date ?>&teacher_id=<?= $teacher_id ?>"
                            target="_blank" class="btn btn-success"
                            style="background-color: #217346; flex: 1; text-align: center; font-size: 0.8rem;">Excel</a>
                        <button type="button" onclick="exportPDF()" class="btn btn-danger"
                            style="background-color: #dc3545; flex: 1; font-size: 0.8rem;">PDF</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="card dashboard-right" id="report-content">
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
                            <th class="no-print">Aksi</th>
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
                                <td class="no-print">
                                    <button class="btn btn-sm btn-danger"
                                        onclick="deleteAttendance(<?= $row['id'] ?>)">Hapus</button>
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

    <script src="../assets/js/main.js"></script>
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

        function deleteAttendance(id) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data absensi ini akan dihapus permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = 'reports.php';

                    const actionInput = document.createElement('input');
                    actionInput.type = 'hidden';
                    actionInput.name = 'action';
                    actionInput.value = 'delete_attendance';
                    form.appendChild(actionInput);

                    const idInput = document.createElement('input');
                    idInput.type = 'hidden';
                    idInput.name = 'id';
                    idInput.value = id;
                    form.appendChild(idInput);

                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }
    </script>
</body>

</html>