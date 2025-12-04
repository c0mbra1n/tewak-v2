<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

requireRole('guru');

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = $_POST['full_name'];
    $password = $_POST['password'];
    $subjects = $_POST['subjects']; // Comma separated string

    if ($full_name) {
        try {
            if (!empty($password)) {
                // Update with password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET full_name = ?, password = ?, subjects = ? WHERE id = ?");
                $stmt->execute([$full_name, $hashed_password, $subjects, $user_id]);
            } else {
                // Update without password
                $stmt = $pdo->prepare("UPDATE users SET full_name = ?, subjects = ? WHERE id = ?");
                $stmt->execute([$full_name, $subjects, $user_id]);
            }
            $message = "Profil berhasil diperbarui.";

            // Update session name if changed
            $_SESSION['full_name'] = $full_name;
        } catch (PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    } else {
        $error = "Nama Lengkap tidak boleh kosong.";
    }
}

// Fetch Current Data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - Tewak Apps</title>
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
            <li class="nav-item"><a href="teacher_schedules.php" class="nav-link">Kelola Jadwal</a></li>
            <li class="nav-item"><a href="teacher_profile.php" class="nav-link active">Profil Saya</a></li>
            <li class="nav-item"><a href="../monitor/monitor.php" class="nav-link" target="_blank">Monitoring</a></li>
            <li class="nav-item"><a href="../logout.php" class="nav-link">Logout</a></li>
        </ul>
    </nav>

    <div class="container">
        <div class="card" style="max-width: 600px; margin: 0 auto;">
            <h3>Edit Profil Saya</h3>

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
                    <label>Nama Lengkap</label>
                    <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" required>
                </div>

                <div class="form-group">
                    <label>Mata Pelajaran yang Diampu</label>
                    <small style="display: block; color: #666; margin-bottom: 5px;">Pisahkan dengan koma (contoh:
                        Matematika, Fisika, Kimia)</small>
                    <textarea name="subjects" rows="3"
                        placeholder="Masukkan daftar mata pelajaran..."><?= htmlspecialchars($user['subjects'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label>Password Baru (Opsional)</label>
                    <small style="display: block; color: #666; margin-bottom: 5px;">Kosongkan jika tidak ingin mengubah
                        password</small>
                    <input type="password" name="password" placeholder="Password Baru">
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">Simpan Perubahan</button>
            </form>
        </div>
    </div>

</body>

</html>