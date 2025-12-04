<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

requireRole('guru');

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Handle Delete
if (isset($_POST['delete_id'])) {
    // Ensure the schedule belongs to the logged-in teacher
    $stmt = $pdo->prepare("DELETE FROM schedules WHERE id = ? AND user_id = ?");
    try {
        if ($stmt->execute([$_POST['delete_id'], $user_id])) {
            $count = $stmt->rowCount();
            $message = "Jadwal berhasil dihapus.";
            if ($count == 0) {
                $error = "Gagal menghapus. Jadwal tidak ditemukan atau bukan milik Anda.";
            }
        } else {
            $error = "Gagal menghapus jadwal.";
        }
    } catch (PDOException $e) {
        $error = "Exception: " . $e->getMessage();
    }
}

// Handle Add Schedule
if (isset($_POST['add_schedule'])) {
    $class_id = $_POST['class_id'];
    $day = $_POST['day'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $subject = $_POST['subject'];

    if ($class_id && $day && $start_time && $end_time && $subject) {
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

// Fetch Classes for Dropdown
$classes = $pdo->query("SELECT id, class_name FROM classes ORDER BY class_name ASC")->fetchAll();

// Fetch My Schedules
$stmt = $pdo->prepare("
    SELECT s.*, c.class_name 
    FROM schedules s 
    JOIN classes c ON s.class_id = c.id 
    WHERE s.user_id = ?
    ORDER BY FIELD(s.day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), s.start_time ASC
");
$stmt->execute([$user_id]);
$my_schedules = $stmt->fetchAll();

$days = [
    'Monday' => 'Senin',
    'Tuesday' => 'Selasa',
    'Wednesday' => 'Rabu',
    'Thursday' => 'Kamis',
    'Friday' => 'Jumat',
    'Saturday' => 'Sabtu',
    'Sunday' => 'Minggu'
];

// Fetch User Subjects
$stmt = $pdo->prepare("SELECT subjects FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user_subjects_str = $stmt->fetchColumn();
$user_subjects = $user_subjects_str ? array_map('trim', explode(',', $user_subjects_str)) : [];
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Jadwal Saya - Tewak Apps</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/themes.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="theme-flat">
    <nav class="navbar">
        <a href="#" class="navbar-brand">Tewak Apps Guru</a>
        <ul class="navbar-nav">
            <li class="nav-item"><a href="teacher_dashboard.php" class="nav-link">Dashboard</a></li>
            <li class="nav-item"><a href="teacher_schedules.php" class="nav-link active">Kelola Jadwal</a></li>
            <li class="nav-item"><a href="teacher_profile.php" class="nav-link">Profil Saya</a></li>
            <li class="nav-item"><a href="../monitor/monitor.php" class="nav-link" target="_blank">Monitoring</a></li>
            <li class="nav-item"><a href="../logout.php" class="nav-link">Logout</a></li>
        </ul>
    </nav>

    <div class="container">
        <div class="dashboard-grid">
            <!-- Form Input Jadwal -->
            <div class="card">
                <h3>Tambah Jadwal Mengajar</h3>
                <?php if ($message): ?>
                    <script>
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: '<?= $message ?>',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    </script>
                <?php endif; ?>
                <?php if ($error): ?>
                    <script>
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: '<?= $error ?>'
                        });
                    </script>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-group">
                        <label>Kelas</label>
                        <select name="class_id" required>
                            <option value="">-- Pilih Kelas --</option>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?= $class['id'] ?>"><?= htmlspecialchars($class['class_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Mata Pelajaran</label>
                        <?php if (!empty($user_subjects)): ?>
                            <select name="subject" required>
                                <option value="">-- Pilih Mata Pelajaran --</option>
                                <?php foreach ($user_subjects as $subj): ?>
                                    <option value="<?= htmlspecialchars($subj) ?>"><?= htmlspecialchars($subj) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <small style="display: block; color: #666; margin-top: 5px;">
                                <a href="teacher_profile.php">Tambah Mapel di Profil</a>
                            </small>
                        <?php else: ?>
                            <input type="text" name="subject" placeholder="Contoh: Matematika" required>
                            <small style="display: block; color: #dc3545; margin-top: 5px;">
                                Tips: <a href="teacher_profile.php">Atur daftar mapel di Profil</a> agar lebih mudah.
                            </small>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label>Hari</label>
                        <select name="day" required>
                            <option value="">-- Pilih Hari --</option>
                            <?php foreach ($days as $eng => $ind): ?>
                                <option value="<?= $eng ?>"><?= $ind ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Jam Mulai</label>
                        <input type="time" name="start_time" required>
                    </div>
                    <div class="form-group">
                        <label>Jam Selesai</label>
                        <input type="time" name="end_time" required>
                    </div>
                    <button type="submit" name="add_schedule" class="btn btn-primary" style="width: 100%;">Simpan Jadwal</button>
                </form>
            </div>

            <!-- List Jadwal -->
            <div class="card">
                <h3>Jadwal Mengajar Saya</h3>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Hari</th>
                                <th>Jam</th>
                                <th>Kelas</th>
                                <th>Mapel</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($my_schedules as $sch): ?>
                                <tr>
                                    <td><?= $days[$sch['day']] ?? $sch['day'] ?></td>
                                    <td><?= date('H:i', strtotime($sch['start_time'])) ?> - <?= date('H:i', strtotime($sch['end_time'])) ?></td>
                                    <td><?= htmlspecialchars($sch['class_name']) ?></td>
                                    <td><?= htmlspecialchars($sch['subject']) ?></td>
                                    <td>
                                        <form method="POST" onsubmit="return confirm('Hapus jadwal ini?');">
                                            <input type="hidden" name="delete_id" value="<?= $sch['id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($my_schedules)): ?>
                                <tr>
                                    <td colspan="5" class="text-center">Belum ada jadwal yang ditambahkan.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</body>
</html>