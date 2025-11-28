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
            <h2 class="text-center">Login Tewak Apps</h2>
            <?php if ($error): ?>
                <div class="alert alert-error"><?= $error ?></div>
            <?php endif; ?>
            <form method="POST" id="loginForm">
                <!-- Step 1: Username -->
                <div id="step-username">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" class="form-control" required>
                    </div>
                    <button type="button" class="btn btn-primary btn-block" onclick="showPasswordStep()">Lanjut</button>
                </div>

                <!-- Step 2: Password -->
                <div id="step-password" style="display: none;">
                    <div class="form-group">
                        <p class="text-center" style="margin-bottom: 10px;">
                            <span id="display-username" style="font-weight: bold;"></span>
                            <a href="#" onclick="showUsernameStep()"
                                style="font-size: 0.8rem; margin-left: 5px;">(Ubah)</a>
                        </p>
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Masuk</button>
                    <button type="button" class="btn btn-secondary btn-block"
                        style="margin-top: 10px; background: transparent; color: #666; border: none;"
                        onclick="showUsernameStep()">Kembali</button>
                </div>
            </form>
        </div>
    </div>
    <script src="assets/js/main.js"></script>
    <script>
        function showPasswordStep() {
            const username = document.getElementById('username').value;
            if (username.trim() === '') {
                alert('Silakan isi username terlebih dahulu');
                return;
            }
            document.getElementById('display-username').innerText = username;
            document.getElementById('step-username').style.display = 'none';
            document.getElementById('step-password').style.display = 'block';
            document.getElementById('password').focus();
        }

        function showUsernameStep() {
            document.getElementById('step-password').style.display = 'none';
            document.getElementById('step-username').style.display = 'block';
            document.getElementById('username').focus();
        }

        // Handle Enter key on username input
        document.getElementById('username').addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                showPasswordStep();
            }
        });
    </script>
</body>

</html>