<?php
require_once 'config.php';
require_once 'auth.php';

requireRole(['super_admin']);

$message = '';
$error = '';

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $school_name = $_POST['school_name'];
    $default_start_time = $_POST['default_start_time'];

    // Handle Logo Upload
    $logo_path = $SCHOOL_LOGO; // Keep existing by default
    if (isset($_FILES['school_logo']) && $_FILES['school_logo']['error'] == 0) {
        $target_dir = "assets/uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_ext = strtolower(pathinfo($_FILES["school_logo"]["name"], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($file_ext, $allowed)) {
            $new_filename = "school_logo." . $file_ext;
            $target_file = $target_dir . $new_filename;

            if (move_uploaded_file($_FILES["school_logo"]["tmp_name"], $target_file)) {
                $logo_path = $target_file;
            } else {
                $error = "Gagal mengupload logo.";
            }
        } else {
            $error = "Format file tidak valid. Gunakan JPG, PNG, atau GIF.";
        }
    }

    if (!$error) {
        try {
            $stmt = $pdo->prepare("UPDATE settings SET school_name = ?, school_logo = ?, default_start_time = ? WHERE id = 1");
            $stmt->execute([$school_name, $logo_path, $default_start_time]);
            $message = "Pengaturan berhasil disimpan.";

            // Refresh variables for current page load
            $SCHOOL_NAME = $school_name;
            $SCHOOL_LOGO = $logo_path;
            $DEFAULT_START_TIME = $default_start_time;
        } catch (PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}

// Fetch Current Settings (already loaded in config.php, but let's be explicit for form values)
$stmt = $pdo->query("SELECT * FROM settings WHERE id = 1");
$settings = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Sekolah - <?= htmlspecialchars($SCHOOL_NAME) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/themes.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="theme-material">
    <nav class="navbar">
        <a href="#" class="navbar-brand"><?= htmlspecialchars($SCHOOL_NAME) ?> Admin</a>
        <ul class="navbar-nav">
            <li class="nav-item"><a href="admin_dashboard.php" class="nav-link">Users</a></li>
            <li class="nav-item"><a href="classes.php" class="nav-link">Kelas</a></li>
            <li class="nav-item"><a href="schedules.php" class="nav-link">Jadwal</a></li>
            <li class="nav-item"><a href="reports.php" class="nav-link">Laporan</a></li>
            <li class="nav-item"><a href="permissions.php" class="nav-link">Izin/Sakit</a></li>
            <li class="nav-item"><a href="settings.php" class="nav-link active">Settings</a></li>
            <li class="nav-item"><a href="monitor.php" class="nav-link" target="_blank">Monitoring</a></li>
            <li class="nav-item"><a href="logout.php" class="nav-link">Logout</a></li>
        </ul>
    </nav>

    <div class="container">
        <div class="card" style="max-width: 600px; margin: 0 auto;">
            <h3>Pengaturan Sekolah</h3>

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

            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Nama Sekolah</label>
                    <input type="text" name="school_name" class="form-control"
                        value="<?= htmlspecialchars($settings['school_name']) ?>" required>
                </div>

                <div class="form-group">
                    <label>Logo Sekolah</label>
                    <?php if ($settings['school_logo']): ?>
                        <div style="margin-bottom: 10px;">
                            <img src="<?= htmlspecialchars($settings['school_logo']) ?>" alt="Logo"
                                style="max-height: 100px;">
                        </div>
                    <?php endif; ?>
                    <input type="file" name="school_logo" class="form-control">
                    <small>Biarkan kosong jika tidak ingin mengubah logo.</small>
                </div>

                <div class="form-group">
                    <label>Jam Masuk Default (Batas Telat)</label>
                    <input type="time" name="default_start_time" class="form-control"
                        value="<?= htmlspecialchars($settings['default_start_time']) ?>" required>
                    <small>Digunakan jika tidak ada jadwal khusus untuk kelas tersebut.</small>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Simpan Pengaturan</button>
            </form>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
</body>

</html>