<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

requireRole('admin_kelas');

$user_id = $_SESSION['user_id'];

// Get assigned class
$stmt = $pdo->prepare("SELECT u.*, c.class_name, c.qr_code, c.id as class_id FROM users u JOIN classes c ON u.class_id = c.id WHERE u.id = ?");
$stmt->execute([$user_id]);
$user_data = $stmt->fetch();

if (!$user_data || !$user_data['class_id']) {
    die("Anda belum ditugaskan ke kelas manapun. Hubungi Super Admin.");
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code Kelas - <?= htmlspecialchars($user_data['class_name']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/themes.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        body {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            text-align: center;
        }

        .qr-container {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        #qrcode {
            margin: 20px auto;
        }
    </style>
</head>

<body class="theme-flat">
    <nav class="navbar" style="position: fixed; top: 0; width: 100%; box-sizing: border-box;">
        <a href="#" class="navbar-brand">Admin Kelas</a>
        <ul class="navbar-nav">
            <li class="nav-item"><a href="../monitor/monitor.php" class="nav-link" target="_blank">Monitoring</a></li>
            <li class="nav-item"><a href="../logout.php" class="nav-link">Logout</a></li>
        </ul>
    </nav>

    <div class="container">
        <div class="qr-container">
            <h1><?= htmlspecialchars($user_data['class_name']) ?></h1>
            <div id="qrcode"></div>
            <p>Scan untuk Absen (Berlaku Hari Ini)</p>
            <p><small><?= date('d F Y') ?></small></p>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
    <script>
        // Append today's date to the QR code content
        // Format: STATIC_CODE|YYYY-MM-DD
        // Use local date (Indonesia/Server time) instead of UTC
        var date = new Date().toLocaleDateString('en-CA');
        var qrContent = "<?= $user_data['qr_code'] ?>|" + date;

        new QRCode(document.getElementById("qrcode"), {
            text: qrContent,
            width: 300,
            height: 300
        });

        // Auto reload page at midnight to update date
        setInterval(function () {
            var now = new Date();
            if (now.getHours() === 0 && now.getMinutes() === 0 && now.getSeconds() === 0) {
                location.reload();
            }
        }, 1000);
    </script>
</body>

</html>