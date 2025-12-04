<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

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
            $upload_dir = '../assets/uploads/teachers/';

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

// Get Today's Schedule for Auto-Subject Selection
$day_eng = date('l');
$stmt = $pdo->prepare("
    SELECT subject, start_time, end_time 
    FROM schedules 
    WHERE user_id = ? AND day = ?
");
$stmt->execute([$user_id, $day_eng]);
$todays_schedule = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Guru - Tewak Apps</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/themes.css">
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

<body class="theme-flat">
    <nav class="navbar">
        <a href="#" class="navbar-brand">Tewak Apps Guru</a>
        <ul class="navbar-nav">
            <li class="nav-item"><a href="#" class="nav-link active">Dashboard</a></li>
            <li class="nav-item"><a href="teacher_schedules.php" class="nav-link">Kelola Jadwal</a></li>
            <li class="nav-item"><a href="teacher_profile.php" class="nav-link">Profil Saya</a></li>
            <li class="nav-item"><a href="../monitor/monitor.php" class="nav-link" target="_blank">Monitoring</a></li>
            <li class="nav-item"><a href="../logout.php" class="nav-link">Logout</a></li>
        </ul>
    </nav>

    <div class="container dashboard-container teacher-dashboard">
        <div class="card dashboard-left">
            <h3>Absensi Guru</h3>
            <div style="text-align: center; margin-bottom: 20px;">
                <?php if ($user_photo): ?>
                    <img src="../assets/uploads/teachers/<?= htmlspecialchars($user_photo) ?>" alt="Foto Profil"
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
            // Parse subjects from profile (fallback)
            // But we prefer using the schedule if available
            // Let's get ALL unique subjects from the user's profile AND schedule to be safe
            $profile_subjects = [];
            // Always fetch latest subjects from DB to ensure accuracy
            $stmt = $pdo->prepare("SELECT subjects FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $db_subjects = $stmt->fetchColumn();
            if ($db_subjects) {
                $profile_subjects = array_map('trim', explode(',', $db_subjects));
            }

            // Merge with subjects from today's schedule (just in case)
            $schedule_subjects = array_column($todays_schedule, 'subject');
            $all_subjects = array_unique(array_merge($profile_subjects, $schedule_subjects));
            ?>

            <div class="form-group" style="margin-bottom: 20px; text-align: left;">
                <label>Mata Pelajaran:</label>
                <?php if (!empty($all_subjects)): ?>
                    <select id="subjectSelect" class="form-control">
                        <?php foreach ($all_subjects as $subj): ?>
                            <option value="<?= htmlspecialchars($subj) ?>"><?= htmlspecialchars($subj) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <small id="autoSelectMsg" style="color: #28a745; display: none;">✅ Otomatis dipilih sesuai
                        jadwal</small>
                <?php else: ?>
                    <input type="text" id="subjectSelect" class="form-control" placeholder="Masukkan Mata Pelajaran">
                <?php endif; ?>
            </div>

            <div id="reader"></div>
            <div id="scanResult" class="alert" style="display:none; margin-top: 20px;"></div>

            <button id="startScan" class="btn btn-primary">Mulai Scan QR</button>
            <button id="stopScan" class="btn btn-danger" style="display:none;">Stop Scan</button>

            <!-- Monitoring UI (Hidden by default) -->
            <div id="monitoringUI"
                style="display: none; margin-top: 20px; padding: 20px; border: 2px solid #28a745; border-radius: 10px; background-color: #f0fff4;">
                <h4 style="color: #28a745;">✅ Mode Mengajar Aktif</h4>
                <p>Anda sedang berada di dalam kelas.</p>
                <div style="font-size: 3rem; margin: 20px 0;">🏫</div>
                <p><strong>Jarak ke Titik Kelas:</strong> <span id="distanceDisplay">0</span> meter</p>
                <p style="font-size: 0.9rem; color: #666;">Harap biarkan halaman ini terbuka selama mengajar.</p>
                <div id="locationStatus" class="alert alert-success" style="margin-top: 10px;">Posisi Aman</div>
            </div>
        </div>

        <div class="card dashboard-right">
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

    <script src="../assets/js/main.js"></script>
    <script>
        // Pass PHP schedule data to JS
        const todaysSchedule = <?= json_encode($todays_schedule) ?>;

        function autoSelectSubject() {
            const now = new Date();
            const currentTime = now.toTimeString().split(' ')[0]; // HH:MM:SS

            const subjectSelect = document.getElementById('subjectSelect');
            const msg = document.getElementById('autoSelectMsg');

            console.log('Checking Auto-Select Subject...');
            console.log('Current Time:', currentTime);
            console.log('Todays Schedule:', todaysSchedule);

            if (!subjectSelect || !todaysSchedule) return;

            let found = false;
            for (let sch of todaysSchedule) {
                console.log(`Checking: ${sch.subject} (${sch.start_time} - ${sch.end_time})`);
                if (currentTime >= sch.start_time && currentTime <= sch.end_time) {
                    console.log('Match found:', sch.subject);
                    subjectSelect.value = sch.subject;
                    found = true;
                    break;
                }
            }

            if (found) {
                if (msg) msg.style.display = 'block';
            } else {
                if (msg) msg.style.display = 'none';
            }
        }

        // Run on load
        document.addEventListener('DOMContentLoaded', autoSelectSubject);

        const html5QrCode = new Html5Qrcode("reader");
        const startBtn = document.getElementById('startScan');
        const stopBtn = document.getElementById('stopScan');
        const readerDiv = document.getElementById('reader');
        const resultDiv = document.getElementById('scanResult');
        const monitoringUI = document.getElementById('monitoringUI');
        const distanceDisplay = document.getElementById('distanceDisplay');
        const locationStatus = document.getElementById('locationStatus');

        let currentLat = null;
        let currentLng = null;
        let watchId = null;
        let wakeLock = null;
        let targetLat = null;
        let targetLng = null;
        let targetRadius = 50;
        let monitoringInterval = null;

        // 1. Start Watch Position Immediately
        if (navigator.geolocation) {
            watchId = navigator.geolocation.watchPosition(
                (position) => {
                    currentLat = position.coords.latitude;
                    currentLng = position.coords.longitude;
                    // console.log("Location updated:", currentLat, currentLng);
                },
                (error) => {
                    console.error("WatchPosition Error:", error);
                },
                {
                    enableHighAccuracy: true,
                    maximumAge: 0,
                    timeout: 5000
                }
            );
        } else {
            alert("Browser tidak mendukung Geolocation.");
        }

        // 2. Wake Lock Function
        async function requestWakeLock() {
            try {
                wakeLock = await navigator.wakeLock.request('screen');
                console.log('Wake Lock active');
                wakeLock.addEventListener('release', () => {
                    console.log('Wake Lock released');
                });
            } catch (err) {
                console.error(`${err.name}, ${err.message}`);
            }
        }

        // Re-request wake lock if visibility changes (e.g. tab switch)
        document.addEventListener('visibilitychange', async () => {
            if (wakeLock !== null && document.visibilityState === 'visible') {
                await requestWakeLock();
            }
        });

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
                    handleScan(decodedText);
                },
                (errorMessage) => {
                    // Error callback
                }
            ).catch((err) => {
                console.log(err);
                alert("Gagal membuka kamera. \n\nError detail: " + err);
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
            html5QrCode.stop().then(() => {
                readerDiv.style.display = 'none';
                startBtn.style.display = 'inline-block';
                stopBtn.style.display = 'none';

                const subject = document.getElementById('subjectSelect').value;

                resultDiv.style.display = 'block';
                resultDiv.className = 'alert alert-info';
                resultDiv.innerText = 'Memproses data...';

                // Use watched position if available, otherwise try getCurrentPosition
                if (currentLat && currentLng) {
                    sendScanData(decodedText, subject, currentLat, currentLng);
                } else {
                    // Fallback if watchPosition hasn't fired yet
                    navigator.geolocation.getCurrentPosition((position) => {
                        sendScanData(decodedText, subject, position.coords.latitude, position.coords.longitude);
                    }, (error) => {
                        alert("Gagal mengambil lokasi. Pastikan GPS aktif.");
                        sendScanData(decodedText, subject, null, null);
                    }, { enableHighAccuracy: true });
                }
            });
        }

        function sendScanData(qrCode, subject, lat, lng) {
            fetch('../api/scan.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    qr_code: qrCode,
                    subject: subject,
                    latitude: lat,
                    longitude: lng
                }),
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        resultDiv.className = 'alert status-hadir';
                        resultDiv.innerText = data.message;

                        // Start Monitoring Mode
                        if (data.class_lat && data.class_lng) {
                            startMonitoring(data.class_lat, data.class_lng, data.radius);
                        } else {
                            setTimeout(() => location.reload(), 2000);
                        }
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
        }

        function startMonitoring(lat, lng, radius) {
            targetLat = parseFloat(lat);
            targetLng = parseFloat(lng);
            targetRadius = parseFloat(radius);

            // Hide scan UI, Show Monitoring UI
            document.querySelector('.dashboard-left h3').innerText = 'Monitoring Kelas';
            document.querySelector('.dashboard-left p').style.display = 'none'; // Hide "Halo..."
            document.querySelector('.form-group').style.display = 'none'; // Hide subject select
            document.querySelector('.dashboard-left div[style*="text-align: center"]').style.display = 'none'; // Hide photo
            startBtn.style.display = 'none';
            resultDiv.style.display = 'none';
            monitoringUI.style.display = 'block';

            // Activate Wake Lock
            requestWakeLock();

            // Start Interval Check
            monitoringInterval = setInterval(checkDistance, 3000);
            checkDistance(); // Initial check
        }

        function checkDistance() {
            if (!currentLat || !currentLng || !targetLat || !targetLng) return;

            const distance = calculateDistance(currentLat, currentLng, targetLat, targetLng);
            distanceDisplay.innerText = Math.round(distance);

            if (distance > targetRadius) {
                // OUT OF RANGE
                monitoringUI.style.borderColor = '#dc3545';
                monitoringUI.style.backgroundColor = '#fff5f5';
                document.querySelector('#monitoringUI h4').innerText = '⚠️ PERINGATAN!';
                document.querySelector('#monitoringUI h4').style.color = '#dc3545';
                locationStatus.className = 'alert alert-danger';
                locationStatus.innerText = 'ANDA KELUAR DARI RADIUS KELAS! Mohon kembali.';

                // Optional: Play sound or vibrate
                if (navigator.vibrate) navigator.vibrate([200, 100, 200]);
            } else {
                // IN RANGE
                monitoringUI.style.borderColor = '#28a745';
                monitoringUI.style.backgroundColor = '#f0fff4';
                document.querySelector('#monitoringUI h4').innerText = '✅ Mode Mengajar Aktif';
                document.querySelector('#monitoringUI h4').style.color = '#28a745';
                locationStatus.className = 'alert alert-success';
                locationStatus.innerText = 'Posisi Aman';
            }
        }

        function calculateDistance(lat1, lon1, lat2, lon2) {
            const R = 6371e3; // metres
            const φ1 = lat1 * Math.PI / 180; // φ, λ in radians
            const φ2 = lat2 * Math.PI / 180;
            const Δφ = (lat2 - lat1) * Math.PI / 180;
            const Δλ = (lon2 - lon1) * Math.PI / 180;

            const a = Math.sin(Δφ / 2) * Math.sin(Δφ / 2) +
                Math.cos(φ1) * Math.cos(φ2) *
                Math.sin(Δλ / 2) * Math.sin(Δλ / 2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

            return R * c; // in metres
        }
    </script>
</body>

</html>