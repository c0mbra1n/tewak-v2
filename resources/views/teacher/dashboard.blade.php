<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard Guru - Tewak</title>
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <style>
        #qr-reader {
            width: 100%;
            max-width: 400px;
            margin: 0 auto;
        }

        #qr-reader video {
            border-radius: 10px;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('teacher.dashboard') }}">Tewak Guru</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="{{ route('teacher.dashboard') }}">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('teacher.schedules') }}">Jadwal Mengajar</a>
                    </li>
                </ul>
                <div class="d-flex align-items-center">
                    <span class="text-white me-3">{{ $user->full_name }}</span>
                    <form action="{{ route('logout') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-light btn-sm">Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <div class="container">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row">
            <!-- QR Scanner -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-qr-code-scan me-2"></i>Scan QR Code Kelas</h5>
                    </div>
                    <div class="card-body text-center p-4">
                        <div id="qr-reader" class="mb-3"></div>

                        <div id="scan-controls">
                            <button id="btn-start-scan" class="btn btn-primary btn-lg px-5 rounded-pill">
                                <i class="bi bi-camera-fill me-2"></i> Mulai Scan
                            </button>
                        </div>

                        <div id="scan-result" class="mt-3 d-none">
                            <div class="alert" role="alert">
                                <span id="scan-message"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Today's Schedule -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-calendar-event me-2"></i>Jadwal Hari Ini</h5>
                        <span class="badge bg-white text-info">{{ now()->locale('id')->translatedFormat('l') }}</span>
                    </div>
                    <div class="card-body p-0">
                        @if($schedules->count() > 0)
                            <ul class="list-group list-group-flush">
                                @foreach($schedules as $schedule)
                                    @php
                                        $attended = $attendances->where('class_id', $schedule->class_id)->where('subject', $schedule->subject)->first();
                                    @endphp
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>{{ $schedule->subject }}</strong><br>
                                            <small class="text-muted">
                                                <i class="bi bi-building me-1"></i>{{ $schedule->classRoom->class_name }}
                                                <i
                                                    class="bi bi-clock ms-2 me-1"></i>{{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }}
                                                - {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}
                                                ({{ $schedule->lesson_hours }} JP)
                                            </small>
                                        </div>
                                        @if($attended)
                                            <span
                                                class="badge bg-{{ $attended->status == 'hadir' ? 'success' : ($attended->status == 'telat' ? 'warning' : 'danger') }} rounded-pill">
                                                {{ ucfirst($attended->status) }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary rounded-pill">Belum Absen</span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <div class="text-center py-4 text-muted">
                                <i class="bi bi-calendar-x fs-1 d-block mb-2"></i>
                                <p>Tidak ada jadwal hari ini</p>
                                <a href="{{ route('teacher.schedules') }}" class="btn btn-sm btn-outline-primary">Tambah
                                    Jadwal</a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Today's Attendance History -->
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0 fw-bold"><i class="bi bi-clock-history me-2"></i>Riwayat Absensi Hari Ini</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Waktu</th>
                                <th>Kelas</th>
                                <th>Mata Pelajaran</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($attendances as $att)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($att->scan_time)->format('H:i:s') }}</td>
                                    <td>{{ $att->classRoom->class_name ?? '-' }}</td>
                                    <td>{{ $att->subject }}</td>
                                    <td>
                                        <span
                                            class="badge bg-{{ $att->status == 'hadir' ? 'success' : ($att->status == 'telat' ? 'warning' : 'danger') }}">
                                            {{ ucfirst($att->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">Belum ada absensi hari ini</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        let html5QrCode = null;
        let scanning = false;

        document.getElementById('btn-start-scan').addEventListener('click', function () {
            if (scanning) {
                stopScanning();
            } else {
                startScanning();
            }
        });

        function startScanning() {
            const btn = document.getElementById('btn-start-scan');
            btn.innerHTML = '<i class="bi bi-stop-fill me-2"></i> Stop Scan';
            btn.classList.remove('btn-primary');
            btn.classList.add('btn-danger');
            scanning = true;

            html5QrCode = new Html5Qrcode("qr-reader");
            html5QrCode.start(
                { facingMode: "environment" },
                { fps: 10, qrbox: { width: 250, height: 250 } },
                onScanSuccess,
                onScanFailure
            ).catch(err => {
                console.error("Camera error:", err);
                showResult('error', 'Gagal mengakses kamera. Pastikan izin kamera diberikan.');
                stopScanning();
            });
        }

        function stopScanning() {
            const btn = document.getElementById('btn-start-scan');
            btn.innerHTML = '<i class="bi bi-camera-fill me-2"></i> Mulai Scan';
            btn.classList.remove('btn-danger');
            btn.classList.add('btn-primary');
            scanning = false;

            if (html5QrCode) {
                html5QrCode.stop().then(() => {
                    html5QrCode.clear();
                }).catch(err => console.log(err));
            }
        }

        function onScanSuccess(decodedText, decodedResult) {
            stopScanning();
            processQrCode(decodedText);
        }

        function onScanFailure(error) {
            // Ignore scan failures (no QR detected)
        }

        function processQrCode(qrCode) {
            showResult('info', 'Memproses absensi...');

            // Get current location
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        sendAttendance(qrCode, position.coords.latitude, position.coords.longitude);
                    },
                    (error) => {
                        // Send without location if GPS fails
                        sendAttendance(qrCode, null, null);
                    },
                    { enableHighAccuracy: true, timeout: 10000 }
                );
            } else {
                sendAttendance(qrCode, null, null);
            }
        }

        function sendAttendance(qrCode, latitude, longitude) {
            const data = {
                qr_code: qrCode + '|' + new Date().toISOString().split('T')[0],
                latitude: latitude,
                longitude: longitude
            };

            fetch('/api/scan', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(data)
            })
                .then(res => res.json())
                .then(response => {
                    if (response.status === 'success') {
                        showResult('success', response.message + (response.data?.status === 'telat' ? ' (Telat)' : ''));
                        setTimeout(() => location.reload(), 2000);
                    } else {
                        showResult('error', response.message);
                    }
                })
                .catch(err => {
                    console.error(err);
                    showResult('error', 'Terjadi kesalahan saat mengirim data.');
                });
        }

        function showResult(type, message) {
            const resultDiv = document.getElementById('scan-result');
            const alertDiv = resultDiv.querySelector('.alert');
            const msgSpan = document.getElementById('scan-message');

            resultDiv.classList.remove('d-none');
            alertDiv.className = 'alert alert-' + (type === 'success' ? 'success' : (type === 'error' ? 'danger' : 'info'));
            msgSpan.textContent = message;
        }
    </script>
</body>

</html>