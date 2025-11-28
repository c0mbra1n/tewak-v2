<?php
require_once 'config.php';
require_once 'auth.php';

requireRole(['super_admin']);

$message = '';

// Handle Class Addition
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_class') {
    $class_name = $_POST['class_name'];
    $qr_code = 'CLASS_' . strtoupper(preg_replace('/[^a-zA-Z0-9]/', '_', $class_name));

    try {
        $stmt = $pdo->prepare("INSERT INTO classes (class_name, qr_code) VALUES (?, ?)");
        $stmt->execute([$class_name, $qr_code]);
        $message = "Kelas berhasil ditambahkan!";
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
    }
}

// Fetch Classes
$stmt = $pdo->query("SELECT * FROM classes ORDER BY class_name ASC");
$classes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Kelas - Mogu</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/themes.css">
</head>

<body class="theme-material">
    <nav class="navbar">
        <a href="#" class="navbar-brand">Mogu Admin</a>
        <ul class="navbar-nav">
            <li class="nav-item"><a href="admin_dashboard.php" class="nav-link">Users</a></li>
            <li class="nav-item"><a href="classes.php" class="nav-link">Kelas</a></li>
            <li class="nav-item"><a href="index.php" class="nav-link" target="_blank">Monitoring</a></li>
            <li class="nav-item"><a href="logout.php" class="nav-link">Logout</a></li>
        </ul>
    </nav>

    <div class="container">
        <div class="card">
            <h3>Tambah Kelas Baru</h3>
            <?php if ($message): ?>
                <div class="alert alert-info"><?= $message ?></div>
            <?php endif; ?>
            <form method="POST">
                <input type="hidden" name="action" value="add_class">
                <div class="form-group">
                    <label>Nama Kelas</label>
                    <input type="text" name="class_name" class="form-control" required placeholder="Contoh: X IPA 1">
                </div>
                <button type="submit" class="btn btn-primary">Tambah Kelas</button>
            </form>
        </div>

        <div class="card">
            <h3>Daftar Kelas</h3>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama Kelas</th>
                            <th>Kode QR</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($classes as $class): ?>
                            <tr>
                                <td><?= $class['id'] ?></td>
                                <td><?= htmlspecialchars($class['class_name']) ?></td>
                                <td><?= $class['qr_code'] ?></td>
                                <td>
                                    <a href="view_qr.php?id=<?= $class['id'] ?>" target="_blank"
                                        class="btn btn-primary">Lihat QR</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script src="assets/js/main.js"></script>
</body>

</html>