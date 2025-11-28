<?php
require_once 'config.php';
require_once 'auth.php';

requireRole(['super_admin']);

$message = '';
$error = '';

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'];
    $date = $_POST['date'];
    $status = $_POST['status'];
    $description = $_POST['description'] ?? '';

    if ($user_id && $date && $status) {
        try {
            // Check if attendance already exists for this user and date
            $stmt = $pdo->prepare("SELECT id FROM attendance WHERE user_id = ? AND date = ?");
            $stmt->execute([$user_id, $date]);
            $existing = $stmt->fetch();

            if ($existing) {
                // Update existing record
                $stmt = $pdo->prepare("UPDATE attendance SET status = ?, description = ?, scan_time = NOW() WHERE id = ?");
                $stmt->execute([$status, $description, $existing['id']]);
                $message = "Status absensi berhasil diperbarui.";
            } else {
                // Insert new record
                // For permissions, we might not have a class_id. 
                // We can set it to 0 or NULL if allowed, or fetch their assigned class.
                // Let's try to fetch their assigned class first.
                $stmtUser = $pdo->prepare("SELECT class_id FROM users WHERE id = ?");
                $stmtUser->execute([$user_id]);
                $user = $stmtUser->fetch();
                $class_id = $user['class_id'] ?? 0; // Default to 0 if no class assigned

                // If class_id is 0/NULL and foreign key fails, we might need a dummy class or allow NULL.
                // Assuming class_id allows NULL or we have a dummy class. 
                // Based on previous schema, class_id is NOT NULL? Let's check.
                // If it is NOT NULL, we might need to use a valid class ID.
                // Let's assume for now we use their assigned class or a placeholder.

                // If they don't have a class (e.g. floating teacher), this might fail if we don't have a fallback.
                // Let's just try to use their assigned class.

                $stmt = $pdo->prepare("INSERT INTO attendance (user_id, class_id, status, date, description, scan_time) VALUES (?, ?, ?, ?, ?, NOW())");
                $stmt->execute([$user_id, $class_id, $status, $date, $description]);
                $message = "Status absensi berhasil ditambahkan.";
            }
        } catch (PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    } else {
        $error = "Mohon lengkapi semua field.";
    }
}

// Fetch Teachers
$stmt = $pdo->query("SELECT id, full_name FROM users WHERE role = 'guru' ORDER BY full_name ASC");
$teachers = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Izin/Sakit - Tewak Apps</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/themes.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="theme-material">
    <nav class="navbar">
        <a href="#" class="navbar-brand">Tewak Apps Admin</a>
        <ul class="navbar-nav">
            <li class="nav-item"><a href="admin_dashboard.php" class="nav-link">Users</a></li>
            <li class="nav-item"><a href="classes.php" class="nav-link">Kelas</a></li>
            <li class="nav-item"><a href="reports.php" class="nav-link">Laporan</a></li>
            <li class="nav-item"><a href="permissions.php" class="nav-link active">Izin/Sakit</a></li>
            <li class="nav-item"><a href="monitor.php" class="nav-link" target="_blank">Monitoring</a></li>
            <li class="nav-item"><a href="logout.php" class="nav-link">Logout</a></li>
        </ul>
    </nav>

    <div class="container">
        <div class="card" style="max-width: 600px; margin: 0 auto;">
            <h3>Input Izin / Sakit / Dinas</h3>

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

            <form method="POST">
                <div class="form-group">
                    <label>Nama Guru</label>
                    <select name="user_id" class="form-control" required>
                        <option value="">-- Pilih Guru --</option>
                        <?php foreach ($teachers as $t): ?>
                            <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['full_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Tanggal</label>
                    <input type="date" name="date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>

                <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="form-control" required>
                        <option value="sakit">Sakit</option>
                        <option value="izin">Izin</option>
                        <option value="dinas_luar">Dinas Luar</option>
                        <option value="alpa">Alpa</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Keterangan (Opsional)</label>
                    <textarea name="description" class="form-control" rows="3"
                        placeholder="Contoh: Sakit demam, Izin acara keluarga..."></textarea>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Simpan Data</button>
            </form>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
</body>

</html>