<?php
require_once 'config.php';
require_once 'auth.php';

requireRole(['super_admin']);

if (!isset($_GET['id'])) {
    die("ID Kelas tidak ditemukan");
}

$stmt = $pdo->prepare("SELECT * FROM classes WHERE id = ?");
$stmt->execute([$_GET['id']]);
$class = $stmt->fetch();

if (!$class) {
    die("Kelas tidak ditemukan");
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code - <?= htmlspecialchars($class['class_name']) ?></title>
    <script src="assets/js/qrcode.min.js"></script>
    <style>
        body {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            font-family: sans-serif;
        }

        #qrcode {
            margin: 20px;
        }
    </style>
</head>

<body>
    <h1><?= htmlspecialchars($class['class_name']) ?></h1>
    <div id="qrcode"></div>
    <p>Scan untuk Absen</p>
    <script>
        // Append today's date to the QR code content
        // Format: STATIC_CODE|YYYY-MM-DD
        // Use local date (Indonesia/Server time) instead of UTC
        var date = new Date().toLocaleDateString('en-CA');
        var qrContent = "<?= $class['qr_code'] ?>|" + date;

        new QRCode(document.getElementById("qrcode"), {
            text: qrContent,
            width: 256,
            height: 256
        });
    </script>
</body>

</html>