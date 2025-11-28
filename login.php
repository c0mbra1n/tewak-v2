<?php
require_once 'config.php';
require_once 'auth.php';

if (isLoggedIn()) {
    if ($_SESSION['role'] == 'guru') {
        header('Location: teacher_dashboard.php');
    } elseif ($_SESSION['role'] == 'admin_kelas') {
        header('Location: class_admin_dashboard.php');
    } else {
        header('Location: admin_dashboard.php');
    }
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['subject'] = $user['subject']; // Store subject in session

        if ($user['role'] == 'guru') {
            header('Location: teacher_dashboard.php');
        } elseif ($user['role'] == 'admin_kelas') {
            header('Location: class_admin_dashboard.php');
        } else {
            header('Location: admin_dashboard.php');
        }
        exit;
    } else {
        $error = 'Username atau password salah';
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Monitoring Kelas</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/themes.css">
</head>

<body class="theme-material">
    <div class="login-container">
        <div class="card login-card">
            <h2 class="text-center">Login Mogu</h2>
            <?php if ($error): ?>
                <div class="alert alert-error"><?= $error ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Masuk</button>
            </form>
        </div>
    </div>
    <script src="assets/js/main.js"></script>
</body>

</html>