<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

requireRole(['super_admin']);

$message = '';
$error = '';
$success_count = 0;
$fail_count = 0;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['csv_file'])) {
    if ($_FILES['csv_file']['error'] == 0) {
        $file_ext = strtolower(pathinfo($_FILES['csv_file']['name'], PATHINFO_EXTENSION));

        if ($file_ext === 'csv') {
            $handle = fopen($_FILES['csv_file']['tmp_name'], "r");

            // Skip header row if exists (optional, but good practice to assume header)
            // Let's assume no header for simplicity, or check first row.
            // Better: Assume NO header based on plan: Name, Username, Password, Subject

            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                // Expected format: Full Name, Username, Password, Subject (optional)
                if (count($data) < 3) {
                    $fail_count++;
                    $errors[] = "Row invalid format: " . implode(",", $data);
                    continue;
                }

                $full_name = trim($data[0]);
                $username = trim($data[1]);
                $password = trim($data[2]);
                $subject = isset($data[3]) ? trim($data[3]) : '';
                $role = 'guru';

                if (empty($full_name) || empty($username) || empty($password)) {
                    $fail_count++;
                    $errors[] = "Missing required fields for: $username";
                    continue;
                }

                // Check if username exists
                $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
                $stmt->execute([$username]);
                if ($stmt->fetch()) {
                    $fail_count++;
                    $errors[] = "Username already exists: $username";
                    continue;
                }

                // Insert User
                try {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO users (username, password, full_name, role, subject) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$username, $hashed_password, $full_name, $role, $subject]);
                    $success_count++;
                } catch (PDOException $e) {
                    $fail_count++;
                    $errors[] = "Database error for $username: " . $e->getMessage();
                }
            }
            fclose($handle);

            $message = "Import selesai. Berhasil: $success_count, Gagal: $fail_count.";
        } else {
            $error = "Mohon upload file CSV.";
        }
    } else {
        $error = "Terjadi error saat upload file.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Guru - <?= htmlspecialchars($SCHOOL_NAME) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/themes.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="theme-flat">
    <nav class="navbar">
        <a href="#" class="navbar-brand"><?= htmlspecialchars($SCHOOL_NAME) ?> Admin</a>
        <ul class="navbar-nav">
            <li class="nav-item"><a href="admin_dashboard.php" class="nav-link">Users</a></li>
            <li class="nav-item"><a href="classes.php" class="nav-link">Kelas</a></li>
            <li class="nav-item"><a href="schedules.php" class="nav-link">Jadwal</a></li>
            <li class="nav-item"><a href="reports.php" class="nav-link">Laporan</a></li>
            <li class="nav-item"><a href="permissions.php" class="nav-link">Izin/Sakit</a></li>
            <li class="nav-item"><a href="settings.php" class="nav-link">Settings</a></li>
            <li class="nav-item"><a href="../monitor/monitor.php" class="nav-link" target="_blank">Monitoring</a></li>
            <li class="nav-item"><a href="../logout.php" class="nav-link">Logout</a></li>
        </ul>
    </nav>

    <div class="container">
        <div class="card" style="max-width: 600px; margin: 0 auto;">
            <h3>Import Data Guru (CSV)</h3>

            <?php if ($message): ?>
                <script>
                    Swal.fire({ icon: 'info', title: 'Hasil Import', text: '<?= $message ?>' });
                </script>
            <?php endif; ?>

            <?php if ($error): ?>
                <script>
                    Swal.fire({ icon: 'error', title: 'Gagal', text: '<?= $error ?>' });
                </script>
            <?php endif; ?>

            <div class="alert alert-info"
                style="background: #e3f2fd; color: #0d47a1; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
                <strong>Format CSV (Tanpa Header):</strong><br>
                Nama Lengkap, Username, Password, Mata Pelajaran (Opsional)<br>
                <br>
                <em>Contoh:</em><br>
                Budi Santoso, budi123, pass123, Matematika<br>
                Siti Aminah, siti_a, rahasia, Biologi
            </div>

            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Pilih File CSV</label>
                    <input type="file" name="csv_file" class="form-control" accept=".csv" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Import Data</button>
            </form>

            <?php if (!empty($errors)): ?>
                <div style="margin-top: 20px; color: red;">
                    <h4>Detail Error:</h4>
                    <ul>
                        <?php foreach ($errors as $err): ?>
                            <li><?= htmlspecialchars($err) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
</body>

</html>