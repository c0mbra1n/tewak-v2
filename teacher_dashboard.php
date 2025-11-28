<?php
require_once 'config.php';
require_once 'auth.php';

requireRole('guru');

$user_id = $_SESSION['user_id'];
$date = date('Y-m-d');

// Get today's attendance
$stmt = $pdo->prepare("
    SELECT a.*, c.class_name 
    FROM attendance a 
    JOIN classes c ON a.class_id = c.id 
    WHERE a.user_id = ? AND a.date = ?
    ORDER BY a.scan_time DESC
");
$stmt->execute([$user_id, $date]);
$attendance_history = $stmt->fetchAll();
// Handle Photo Upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['photo'])) {
    if ($_FILES['photo']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['photo']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $new_filename = 'teacher_' . $user_id . '_' . time() . '.' . $ext;
            $upload_dir = 'assets/uploads/teachers/';

            // Ensure directory exists
            if (!is_dir($upload_dir)) {
                if (!mkdir($upload_dir, 0777, true)) {
                    echo "<script>alert('Gagal membuat direktori upload.');</script>";
                    exit;
                }
            }

            if (move_uploaded_file($_FILES['photo']['tmp_name'], $upload_dir . $new_filename)) {
                // Update DB
                $stmt = $pdo->prepare("UPDATE users SET photo = ? WHERE id = ?");
                $stmt->execute([$new_filename, $user_id]);
                // Update session
                $_SESSION['photo'] = $new_filename;
                echo "<script>alert('Foto berhasil diupload!'); window.location.href='teacher_dashboard.php';</script>";
            } else {
                echo "<script>alert('Gagal mengupload foto. Cek permission folder.');</script>";
            }
        } else {
            echo "<script>alert('Format file tidak valid. Gunakan JPG, PNG, atau GIF.');</script>";
        }
    } else {
        $error_code = $_FILES['photo']['error'];
        echo "<script>alert('Error upload file. Code: $error_code');</script>";
    }
}

// Get current photo
$stmt = $pdo->prepare("SELECT photo FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user_photo = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Guru - Tewak Apps</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/themes.css">
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <style>
        #reader {
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
            display: none;
        }
    </style>
</head>

<body class="theme-material">
    <nav class="navbar">
        <a href="#" class="navbar-brand">Tewak Apps Guru</a>
        <ul class="navbar-nav">
            <li class="nav-item"><a href="#" class="nav-link">Dashboard</a></li>
            <li class="nav-item"><a href="monitor.php" class="nav-link" target="_blank">Monitoring</a></li>
            <li class="nav-item"><a href="logout.php" class="nav-link">Logout</a></li>
        </ul>
    </nav>

    <div class="container">
        <div class="card">
            <h3>Absensi Guru</h3>
            <div style="text-align: center; margin-bottom: 20px;">
                <?php if ($user_photo): ?>
                    <img src="assets/uploads/teachers/<?= htmlspecialchars($user_photo) ?>" alt="Foto Profil"
                        style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 3px solid var(--primary-color);">
                <?php else: ?>
                    <div
                        style="width: 100px; height: 100px; border-radius: 50%; background: #ccc; display: inline-flex; align-items: center; justify-content: center; color: #fff; font-size: 40px;">
                        <?= strtoupper(substr($_SESSION['full_name'], 0, 1)) ?>
                    </div>
                <?php endif; ?>
                <br>
                <button class="btn btn-sm btn-secondary" onclick="document.getElementById('photoInput').click()"
                    style="margin-top: 10px; font-size: 0.8rem;">Ubah Foto</button>
                <form method="POST" enctype="multipart/form-data" style="display: none;">
                    <input type="file" name="photo" id="photoInput" accept="image/*" onchange="this.form.submit()">
                </form>
            </div>
            <p>Halo, <strong><?= htmlspecialchars($_SESSION['full_name']) ?></strong></p>

            <?php
            // Parse subjects
            $user_subjects = explode(',', $_SESSION['subject']);
            $user_subjects = array_map('trim', $user_subjects);
            ?>

            <?php if (count($user_subjects) > 1): ?>
                <div class="form-group" style="margin-bottom: 20px; text-align: left;">
                    <label>Pilih Mata Pelajaran:</label>
                    <select id="subjectSelect" class="form-control">
                        <?php foreach ($user_subjects as $subj): ?>
                            <option value="<?= htmlspecialchars($subj) ?>"><?= htmlspecialchars($subj) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php else: ?>
                <input type="hidden" id="subjectSelect" value="<?= htmlspecialchars($user_subjects[0] ?? '') ?>">
                <p>Mata Pelajaran: <strong><?= htmlspecialchars($user_subjects[0] ?? '-') ?></strong></p>
            <?php endif; ?>

            <div id="reader"></div>
            <div id="scanResult" class="alert" style="display:none; margin-top: 20px;"></div>

            <button id="startScan" class="btn btn-primary">Mulai Scan QR</button>
            <button id="stopScan" class="btn btn-danger" style="display:none;">Stop Scan</button>
        </div>

        <div class="card">
            <h3>Riwayat Absensi Hari Ini</h3>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Kelas</th>
                            <th>Waktu</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($attendance_history as $log): ?>
                            <tr>
                                <td><?= htmlspecialchars($log['class_name']) ?></td>
                                <td><?= date('H:i', strtotime($log['scan_time'])) ?></td>
                                <td>
                                    <span class="status-badge status-<?= $log['status'] ?>">
                                        <?= strtoupper($log['status']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($attendance_history)): ?>
                            <tr>
                                <td colspan="3" class="text-center">Belum ada absensi hari ini.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
    <script>
        const html5QrCode = new Html5Qrcode("reader");
        const startBtn = document.getElementById('startScan');
        const stopBtn = document.getElementById('stopScan');
        const readerDiv = document.getElementById('reader');
        const resultDiv = document.getElementById('scanResult');

        startBtn.addEventListener('click', () => {
            // Check if served over HTTP (not localhost)
            if (location.protocol !== 'https:' && location.hostname !== 'localhost' && location.hostname !== '127.0.0.1') {
                alert("PERINGATAN: Anda mengakses via HTTP. Browser modern (Chrome/Safari) memblokir akses kamera di jaringan tidak aman. Mohon gunakan HTTPS atau localhost.");
            }

            readerDiv.style.display = 'block';
            startBtn.style.display = 'none';
            stopBtn.style.display = 'inline-block';
            resultDiv.style.display = 'none';

            html5QrCode.start(
                { facingMode: "environment" },
                {
                    fps: 10,
                    qrbox: { width: 250, height: 250 }
                },
                (decodedText, decodedResult) => {
                    // Success callback
                    handleScan(decodedText);
                },
                (errorMessage) => {
                    // Error callback
                }
            ).catch((err) => {
                console.log(err);
                alert("Gagal membuka kamera. \n\nPenyebab umum:\n1. Izin kamera ditolak.\n2. Mengakses via HTTP (IP Address) bukan HTTPS.\n3. Kamera sedang digunakan aplikasi lain.\n\nError detail: " + err);
                stopScanning();
            });
        });

        stopBtn.addEventListener('click', () => {
            stopScanning();
        });

        function stopScanning() {
            html5QrCode.stop().then((ignore) => {
                readerDiv.style.display = 'none';
                startBtn.style.display = 'inline-block';
                stopBtn.style.display = 'none';
            }).catch((err) => {
                console.log(err);
            });
        }

        function handleScan(decodedText) {
            // Stop scanning temporarily
            html5QrCode.stop().then(() => {
                readerDiv.style.display = 'none';
                startBtn.style.display = 'inline-block'; // Ensure start button reappears
                stopBtn.style.display = 'none';

                // Get selected subject
                const subject = document.getElementById('subjectSelect').value;

                resultDiv.style.display = 'block';
                resultDiv.className = 'alert alert-info';
                resultDiv.innerText = 'Memproses...';

                // Send to API
                fetch('api/scan.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        qr_code: decodedText,
                        subject: subject
                    }),
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            resultDiv.className = 'alert status-hadir'; // Use success color
                            resultDiv.innerText = data.message;
                            setTimeout(() => location.reload(), 2000);
                        } else {
                            resultDiv.className = 'alert alert-error';
                            resultDiv.innerText = data.message;
                        }
                    })
                    .catch(error => {
                        resultDiv.className = 'alert alert-error';
                        resultDiv.innerText = 'Terjadi kesalahan sistem';
                        console.error('Error:', error);
                    });
            });
        }
    </script>
</body>

</html>