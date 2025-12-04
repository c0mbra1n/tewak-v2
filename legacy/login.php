<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

if (isLoggedIn()) {
    if ($_SESSION['role'] == 'guru') {
        header('Location: teacher/teacher_dashboard.php');
    } elseif ($_SESSION['role'] == 'admin_kelas') {
        header('Location: admin/class_admin_dashboard.php');
    } else {
        header('Location: admin/admin_dashboard.php');
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
            header('Location: teacher/teacher_dashboard.php');
        } elseif ($user['role'] == 'admin_kelas') {
            header('Location: admin/class_admin_dashboard.php');
        } else {
            header('Location: admin/admin_dashboard.php');
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
    <title>Login - <?= htmlspecialchars($SCHOOL_NAME) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/themes.css">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-card {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        .school-logo {
            width: 100px;
            height: 100px;
            object-fit: contain;
            margin-bottom: 15px;
        }

        .school-name {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 5px;
        }

        .app-name {
            font-size: 1rem;
            color: #666;
            margin-bottom: 30px;
        }

        .form-control {
            background: #f8f9fa;
            border: 1px solid #eee;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            background: white;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(98, 0, 238, 0.1);
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
            padding: 12px;
            border-radius: 8px;
            font-weight: 600;
            width: 100%;
            margin-top: 10px;
            transition: transform 0.2s;
        }

        .btn-primary:active {
            transform: scale(0.98);
        }

        .back-link {
            display: inline-block;
            margin-top: 15px;
            color: #666;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .back-link:hover {
            color: var(--primary-color);
        }
    </style>
</head>

<body>
    <div class="login-card">
        <?php if ($SCHOOL_LOGO): ?>
            <img src="assets/uploads/settings/<?= htmlspecialchars($SCHOOL_LOGO) ?>" alt="Logo Sekolah" class="school-logo">
        <?php else: ?>
            <div style="font-size: 3rem; margin-bottom: 15px;">🏫</div>
        <?php endif; ?>

        <div class="school-name"><?= htmlspecialchars($SCHOOL_NAME) ?></div>
        <div class="app-name">Sistem Monitoring Kehadiran</div>

        <?php if ($error): ?>
            <div class="alert alert-error"
                style="background: #ffebee; color: #c62828; padding: 10px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem;">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST" id="loginForm">
            <!-- Step 1: Username -->
            <div id="step-username">
                <div class="form-group" style="text-align: left;">
                    <label for="username"
                        style="font-weight: 500; color: #444; margin-bottom: 5px; display: block;">Username</label>
                    <input type="text" id="username" name="username" class="form-control"
                        placeholder="Masukkan username Anda" required>
                </div>
                <button type="button" class="btn btn-primary" onclick="showPasswordStep()">Lanjut</button>
            </div>

            <!-- Step 2: Password -->
            <div id="step-password" style="display: none;">
                <div style="margin-bottom: 20px;">
                    <div style="font-size: 0.9rem; color: #666;">Login sebagai:</div>
                    <div id="display-username" style="font-weight: bold; font-size: 1.1rem; color: #333;"></div>
                </div>

                <div class="form-group" style="text-align: left;">
                    <label for="password"
                        style="font-weight: 500; color: #444; margin-bottom: 5px; display: block;">Password</label>
                    <input type="password" id="password" name="password" class="form-control"
                        placeholder="Masukkan password Anda">
                </div>

                <button type="submit" class="btn btn-primary">Masuk</button>
                <a href="#" class="back-link" onclick="showUsernameStep()">← Ganti Username</a>
            </div>
        </form>
    </div>

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