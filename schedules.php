<?php
require_once 'config.php';
require_once 'auth.php';

requireRole(['super_admin']);


$message = '';
$error = '';

// Handle Delete
if (isset($_POST['delete_id'])) {
    $stmt = $pdo->prepare("DELETE FROM schedules WHERE id = ?");
    try {
        if ($stmt->execute([$_POST['delete_id']])) {
            $count = $stmt->rowCount();
            $message = "Jadwal berhasil dihapus. Rows affected: " . $count;
            if ($count == 0) {
                $error = "ID tidak ditemukan atau sudah terhapus.";
            }
        } else {
            $error = "Gagal menghapus jadwal. Error: " . implode(" ", $stmt->errorInfo());
        }
    } catch (PDOException $e) {
        $error = "Exception: " . $e->getMessage();
    }
}

// Handle Add Schedule
if (isset($_POST['add_schedule'])) {
    $user_id = $_POST['user_id'];
    $class_id = $_POST['class_id'];
    $day = $_POST['day'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $subject = $_POST['subject'];

    if ($user_id && $class_id && $day && $start_time && $end_time && $subject) {
        try {
            $stmt = $pdo->prepare("INSERT INTO schedules (user_id, class_id, day, start_time, end_time, subject) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $class_id, $day, $start_time, $end_time, $subject]);
            $message = "Jadwal berhasil ditambahkan.";
        } catch (PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    } else {
        $error = "Mohon lengkapi semua field.";
    }
}

// Fetch Data for Dropdowns
$teachers = $pdo->query("SELECT id, full_name FROM users WHERE role = 'guru' ORDER BY full_name ASC")->fetchAll();
$classes = $pdo->query("SELECT id, class_name FROM classes ORDER BY class_name ASC")->fetchAll();

// Fetch Schedules
$schedules = $pdo->query("
    SELECT s.*, u.full_name, c.class_name 
    FROM schedules s 
    JOIN users u ON s.user_id = u.id 
    JOIN classes c ON s.class_id = c.id 
    ORDER BY FIELD(s.day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), s.start_time ASC
")->fetchAll();

$days = [
    'Monday' => 'Senin',
    'Tuesday' => 'Selasa',
    'Wednesday' => 'Rabu',
    'Thursday' => 'Kamis',
    'Friday' => 'Jumat',
    'Saturday' => 'Sabtu',
    'Sunday' => 'Minggu'
];
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Jadwal - Mogu</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/themes.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="theme-material">
    <nav class="navbar">
        <a href="#" class="navbar-brand">Mogu Admin</a>
        <ul class="navbar-nav">
            <li class="nav-item"><a href="admin_dashboard.php" class="nav-link">Users</a></li>
            <li class="nav-item"><a href="classes.php" class="nav-link">Kelas</a></li>
            <li class="nav-item"><a href="schedules.php" class="nav-link active">Jadwal</a></li>
            <li class="nav-item"><a href="reports.php" class="nav-link">Laporan</a></li>
            <li class="nav-item"><a href="permissions.php" class="nav-link">Izin/Sakit</a></li>
            <li class="nav-item"><a href="monitor.php" class="nav-link" target="_blank">Monitoring</a></li>
            <li class="nav-item"><a href="logout.php" class="nav-link">Logout</a></li>
        </ul>
    </nav>

    <div class="container dashboard-container">
        <div class="card form-card">
            <h3>Tambah Jadwal Pelajaran</h3>

            <?php if ($message): ?>
                <script>
                    Swal.fire({ icon: 'success', title: 'Berhasil', text: '<?= $message ?>', timer: 2000, showConfirmButton: false });
                </script>
            <?php endif; ?>

            <?php if ($error): ?>
                <script>
                    Swal.fire({ icon: 'error', title: 'Gagal', text: '<?= $error ?>' });
                </script>
            <?php endif; ?>

            <form method="POST" style="display: grid; grid-template-columns: 1fr; gap: 15px;">
                <div class="form-group">
                    <label>Guru</label>
                    <select name="user_id" class="form-control" required>
                        <option value="">-- Pilih Guru --</option>
                        <?php foreach ($teachers as $t): ?>
                            <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['full_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Kelas</label>
                    <select name="class_id" class="form-control" required>
                        <option value="">-- Pilih Kelas --</option>
                        <?php foreach ($classes as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['class_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Hari</label>
                    <select name="day" class="form-control" required>
                        <?php foreach ($days as $eng => $ind): ?>
                            <option value="<?= $eng ?>"><?= $ind ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Mata Pelajaran</label>
                    <input type="text" name="subject" class="form-control" placeholder="Contoh: Matematika" required>
                </div>

                <div class="form-group">
                    <label>Jam Mulai</label>
                    <input type="time" name="start_time" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Jam Selesai</label>
                    <input type="time" name="end_time" class="form-control" required>
                </div>

                <div class="form-group">
                    <button type="submit" name="add_schedule" class="btn btn-primary btn-block">Simpan Jadwal</button>
                </div>
            </form>
        </div>

        <div class="card table-card">
            <h3>Daftar Jadwal</h3>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Hari</th>
                            <th>Jam</th>
                            <th>Guru</th>
                            <th>Kelas</th>
                            <th>Mata Pelajaran</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($schedules as $row): ?>
                            <tr>
                                <td><?= $days[$row['day']] ?></td>
                                <td><?= date('H:i', strtotime($row['start_time'])) ?> -
                                    <?= date('H:i', strtotime($row['end_time'])) ?>
                                </td>
                                <td><?= htmlspecialchars($row['full_name']) ?></td>
                                <td><?= htmlspecialchars($row['class_name']) ?></td>
                                <td><?= htmlspecialchars($row['subject']) ?></td>
                                <td>
                                    <form method="POST" style="display:inline;"
                                        onsubmit="return confirm('Yakin ingin menghapus jadwal ini?');">
                                        <input type="hidden" name="delete_id" value="<?= $row['id'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($schedules)): ?>
                            <tr>
                                <td colspan="6" class="text-center">Belum ada jadwal pelajaran.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
</body>

</html>