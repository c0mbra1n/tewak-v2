<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard Guru - Tewak</title>
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
    <script>
        if (localStorage.getItem('darkMode') === 'true') {
            document.documentElement.setAttribute('data-theme', 'dark');
        }
    </script>
    <style>
        #qr-reader {
            width: 100%;
            max-width: 350px;
            margin: 0 auto;
        }

        #qr-reader video {
            border-radius: 10px;
        }

        @media (max-width: 576px) {
            #qr-reader {
                max-width: 280px;
            }
        }

        .crop-container {
            max-height: 400px;
            overflow: hidden;
        }

        .crop-container img {
            max-width: 100%;
        }
    </style>
</head>

<body>
    @include('partials.loader')
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('teacher.dashboard') }}">Tewak</a>
            <div class="d-flex align-items-center d-lg-none order-lg-1">
                <button type="button" class="btn btn-link text-white p-1 me-1" id="darkModeToggleMobile"
                    title="Toggle Dark Mode">
                    <i class="bi bi-moon-fill" id="darkModeIconMobile"></i>
                </button>
                <button class="navbar-toggler border-0 p-1" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </div>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="{{ route('teacher.dashboard') }}">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('teacher.schedules') }}">Jadwal</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('monitor.index') }}" target="_blank">
                            <i class="bi bi-display me-1"></i> Monitoring
                        </a>
                    </li>
                </ul>
                <div class="d-flex align-items-center mt-3 mt-lg-0">
                    <button type="button" class="dark-mode-toggle btn btn-link text-white p-2 me-2 d-none d-lg-block"
                        id="darkModeToggle" title="Toggle Dark Mode">
                        <i class="bi bi-moon-fill" id="darkModeIcon"></i>
                    </button>
                    <!-- Profile Photo -->
                    <div class="me-2">
                        @if($user->photo)
                            <img src="{{ asset('uploads/profiles/' . $user->photo) }}"
                                class="rounded-circle border border-2 border-light"
                                style="width: 35px; height: 35px; object-fit: cover; cursor: pointer;"
                                data-bs-toggle="modal" data-bs-target="#photoModal" title="Ubah foto profil">
                        @else
                            <div class="rounded-circle bg-light text-primary d-flex align-items-center justify-content-center"
                                style="width: 35px; height: 35px; cursor: pointer;" data-bs-toggle="modal"
                                data-bs-target="#photoModal" title="Upload foto profil">
                                <i class="bi bi-person-fill"></i>
                            </div>
                        @endif
                    </div>
                    <span class="text-white me-3 d-none d-lg-inline">{{ $user->full_name }}</span>
                    <div class="dropdown d-inline">
                        <button class="btn btn-outline-light btn-sm dropdown-toggle" type="button"
                            id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-gear-fill"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton1">
                            <li>
                                <button type="button" class="dropdown-item" data-bs-toggle="modal"
                                    data-bs-target="#changePasswordModal">
                                    <i class="bi bi-key me-2"></i>Ganti Password
                                </button>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">Logout</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="container py-3 py-lg-4">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row">
            <!-- QR Scanner -->
            <div class="col-12 col-lg-6 mb-3 mb-lg-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white py-2 py-lg-3">
                        <h5 class="mb-0 fs-6 fs-lg-5"><i class="bi bi-qr-code-scan me-2"></i>Scan QR Code Kelas</h5>
                    </div>
                    <div class="card-body text-center p-3 p-lg-4">
                        <div id="qr-reader" class="mb-3"></div>

                        <div id="scan-controls">
                            <button id="btn-start-scan" class="btn btn-primary px-4 py-2 rounded-pill">
                                <i class="bi bi-camera-fill me-2"></i> Mulai Scan
                            </button>
                        </div>

                        <div id="scan-result" class="mt-3 d-none">
                            <div class="alert py-2" role="alert">
                                <span id="scan-message"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Today's Schedule -->
            <div class="col-12 col-lg-6 mb-3 mb-lg-4">
                <div class="card shadow-sm">
                    <div
                        class="card-header bg-info text-white d-flex justify-content-between align-items-center py-2 py-lg-3">
                        <h5 class="mb-0 fs-6 fs-lg-5"><i class="bi bi-calendar-event me-2"></i>Jadwal Hari Ini</h5>
                        <span
                            class="badge bg-white text-info small">{{ now()->locale('id')->translatedFormat('l') }}</span>
                    </div>
                    <div class="card-body p-0">
                        @if($schedules->count() > 0)
                            <ul class="list-group list-group-flush">
                                @foreach($schedules as $schedule)
                                    @php
                                        $attended = $attendances->where('class_id', $schedule->class_id)->where('subject', $schedule->subject)->first();
                                    @endphp
                                    <li class="list-group-item px-3 py-2 py-lg-3">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <strong class="small">{{ $schedule->subject }}</strong><br>
                                                <small class="text-muted">
                                                    <i class="bi bi-building me-1"></i>{{ $schedule->classRoom->class_name }}
                                                    <span class="ms-2">
                                                        <i
                                                            class="bi bi-clock me-1"></i>{{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }}
                                                        - {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}
                                                    </span>
                                                </small>
                                            </div>
                                            @if($attended)
                                                <span
                                                    class="badge bg-{{ $attended->status == 'hadir' ? 'success' : ($attended->status == 'telat' ? 'warning' : 'danger') }} rounded-pill small">
                                                    {{ ucfirst($attended->status) }}
                                                </span>
                                            @else
                                                <span class="badge bg-secondary rounded-pill small">Belum</span>
                                            @endif
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <div class="text-center py-4 text-muted">
                                <i class="bi bi-calendar-x fs-2 d-block mb-2"></i>
                                <p class="small mb-2">Tidak ada jadwal hari ini</p>
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
            <div class="card-header bg-white py-2 py-lg-3">
                <h5 class="mb-0 fw-bold fs-6 fs-lg-5"><i class="bi bi-clock-history me-2"></i>Riwayat Absensi Hari Ini
                </h5>
            </div>
            <div class="card-body p-0">
                <!-- Mobile View -->
                <div class="d-lg-none">
                    @forelse($attendances as $att)
                        <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-bold small">{{ $att->classRoom->class_name ?? '-' }}</div>
                                <div class="text-muted" style="font-size: 0.75rem;">
                                    {{ $att->subject }} • {{ \Carbon\Carbon::parse($att->scan_time)->format('H:i') }}
                                </div>
                            </div>
                            <span
                                class="badge bg-{{ $att->status == 'hadir' ? 'success' : ($att->status == 'telat' ? 'warning' : 'danger') }}">
                                {{ ucfirst($att->status) }}
                            </span>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted small">Belum ada absensi hari ini</div>
                    @endforelse
                </div>

                <!-- Desktop View -->
                <div class="table-responsive d-none d-lg-block">
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

    <!-- Photo Upload Modal -->
    <div class="modal fade" id="photoModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-person-circle me-2"></i>Upload Foto Profil</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">

                    <!-- Step 1: Select Image -->
                    <div id="step-select" class="text-center">
                        <div class="mb-3">
                            <label for="photoInput" class="btn btn-lg btn-outline-primary px-5 py-4">
                                <i class="bi bi-camera-fill fs-1 d-block mb-2"></i>
                                <span>Pilih atau Ambil Foto</span>
                            </label>
                            <input type="file" id="photoInput" accept="image/*" capture="environment" class="d-none">
                        </div>
                        <p class="text-muted small">Pilih foto dari galeri atau ambil dengan kamera</p>
                    </div>

                    <!-- Step 2: Crop Image -->
                    <div id="step-crop" class="d-none">
                        <p class="text-muted small mb-2 text-center">Atur posisi dan crop foto sesuai keinginan</p>
                        <div class="crop-container mb-3">
                            <img id="cropImage" src="" alt="Crop preview">
                        </div>
                        <div class="d-flex justify-content-center gap-2 mb-3">
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="btnRotateLeft">
                                <i class="bi bi-arrow-counterclockwise"></i> Putar Kiri
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="btnRotateRight">
                                <i class="bi bi-arrow-clockwise"></i> Putar Kanan
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="btnFlipH">
                                <i class="bi bi-symmetry-horizontal"></i> Flip H
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="btnFlipV">
                                <i class="bi bi-symmetry-vertical"></i> Flip V
                            </button>
                        </div>
                    </div>

                    <!-- Step 3: Preview -->
                    <div id="step-preview" class="d-none text-center">
                        <p class="text-muted small mb-2">Preview Foto</p>
                        <img id="previewImage" src="" class="img-fluid rounded mb-3" style="max-height: 300px;">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary d-none" id="btnCrop">
                        <i class="bi bi-crop me-1"></i> Crop
                    </button>
                    <button type="button" class="btn btn-warning d-none" id="btnRecrop">
                        <i class="bi bi-arrow-left me-1"></i> Crop Ulang
                    </button>
                    <button type="button" class="btn btn-success d-none" id="btnSavePhoto">
                        <i class="bi bi-check-lg me-1"></i> Simpan Foto
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Change Password Modal -->
    <div class="modal fade" id="changePasswordModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ganti Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('profile.password.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Password Saat Ini</label>
                            <input type="password" class="form-control" name="current_password" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password Baru</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Konfirmasi Password Baru</label>
                            <input type="password" class="form-control" name="password_confirmation" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Dark Mode Toggle
        function updateAllDarkModeIcons() {
            const isDark = localStorage.getItem('darkMode') === 'true';
            const iconClass = isDark ? 'bi bi-sun-fill' : 'bi bi-moon-fill';
            const desktopIcon = document.getElementById('darkModeIcon');
            const mobileIcon = document.getElementById('darkModeIconMobile');
            if (desktopIcon) desktopIcon.className = iconClass;
            if (mobileIcon) mobileIcon.className = iconClass;
        }

        function toggleDarkMode() {
            const isDark = localStorage.getItem('darkMode') === 'true';
            localStorage.setItem('darkMode', !isDark);
            document.documentElement.setAttribute('data-theme', !isDark ? 'dark' : 'light');
            updateAllDarkModeIcons();
        }

        document.getElementById('darkModeToggle')?.addEventListener('click', toggleDarkMode);
        document.getElementById('darkModeToggleMobile')?.addEventListener('click', toggleDarkMode);
        updateAllDarkModeIcons();

        // QR Scanner
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
            btn.innerHTML = '<i class="bi bi-stop-fill me-2"></i> Stop';
            btn.classList.remove('btn-primary');
            btn.classList.add('btn-danger');
            scanning = true;

            html5QrCode = new Html5Qrcode("qr-reader");
            html5QrCode.start(
                { facingMode: "environment" },
                { fps: 10, qrbox: { width: 200, height: 200 } },
                onScanSuccess,
                onScanFailure
            ).catch(err => {
                console.error("Camera error:", err);
                showResult('error', 'Gagal mengakses kamera.');
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

        function onScanFailure(error) { }

        function processQrCode(qrCode) {
            showResult('info', 'Memproses...');

            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        sendAttendance(qrCode, position.coords.latitude, position.coords.longitude);
                    },
                    (error) => {
                        sendAttendance(qrCode, null, null);
                    },
                    { enableHighAccuracy: true, timeout: 10000 }
                );
            } else {
                sendAttendance(qrCode, null, null);
            }
        }

        function sendAttendance(qrCode, latitude, longitude) {
            // QR code already contains date from admin_kelas (format: CLASS_CODE|YYYY-MM-DD)
            const data = {
                qr_code: qrCode,
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
                    showResult('error', 'Terjadi kesalahan.');
                });
        }

        function showResult(type, message) {
            const resultDiv = document.getElementById('scan-result');
            const alertDiv = resultDiv.querySelector('.alert');
            const msgSpan = document.getElementById('scan-message');

            resultDiv.classList.remove('d-none');
            alertDiv.className = 'alert py-2 alert-' + (type === 'success' ? 'success' : (type === 'error' ? 'danger' : 'info'));
            msgSpan.textContent = message;
        }

        // Photo Upload with Cropper
        let cropper = null;
        let croppedImageData = null;

        function resetPhotoModal() {
            document.getElementById('step-select').classList.remove('d-none');
            document.getElementById('step-crop').classList.add('d-none');
            document.getElementById('step-preview').classList.add('d-none');
            document.getElementById('btnCrop').classList.add('d-none');
            document.getElementById('btnRecrop').classList.add('d-none');
            document.getElementById('btnSavePhoto').classList.add('d-none');
            document.getElementById('photoInput').value = '';
            if (cropper) {
                cropper.destroy();
                cropper = null;
            }
            croppedImageData = null;
        }

        document.getElementById('photoInput').addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function (event) {
                const img = document.getElementById('cropImage');
                img.src = event.target.result;

                document.getElementById('step-select').classList.add('d-none');
                document.getElementById('step-crop').classList.remove('d-none');
                document.getElementById('btnCrop').classList.remove('d-none');

                // Initialize Cropper
                if (cropper) cropper.destroy();
                cropper = new Cropper(img, {
                    aspectRatio: 1,
                    viewMode: 2,
                    autoCropArea: 0.8,
                    responsive: true,
                    background: false
                });
            };
            reader.readAsDataURL(file);
        });

        document.getElementById('btnRotateLeft').addEventListener('click', function () {
            if (cropper) cropper.rotate(-90);
        });

        document.getElementById('btnRotateRight').addEventListener('click', function () {
            if (cropper) cropper.rotate(90);
        });

        document.getElementById('btnFlipH').addEventListener('click', function () {
            if (cropper) cropper.scaleX(-cropper.getData().scaleX || -1);
        });

        document.getElementById('btnFlipV').addEventListener('click', function () {
            if (cropper) cropper.scaleY(-cropper.getData().scaleY || -1);
        });

        document.getElementById('btnCrop').addEventListener('click', function () {
            if (!cropper) return;

            croppedImageData = cropper.getCroppedCanvas({
                width: 500,
                height: 500,
                imageSmoothingEnabled: true,
                imageSmoothingQuality: 'high'
            }).toDataURL('image/jpeg', 0.85);

            document.getElementById('previewImage').src = croppedImageData;
            document.getElementById('step-crop').classList.add('d-none');
            document.getElementById('step-preview').classList.remove('d-none');
            document.getElementById('btnCrop').classList.add('d-none');
            document.getElementById('btnRecrop').classList.remove('d-none');
            document.getElementById('btnSavePhoto').classList.remove('d-none');
        });

        document.getElementById('btnRecrop').addEventListener('click', function () {
            document.getElementById('step-preview').classList.add('d-none');
            document.getElementById('step-crop').classList.remove('d-none');
            document.getElementById('btnRecrop').classList.add('d-none');
            document.getElementById('btnSavePhoto').classList.add('d-none');
            document.getElementById('btnCrop').classList.remove('d-none');
        });

        document.getElementById('btnSavePhoto').addEventListener('click', function () {
            if (!croppedImageData) return;

            const btn = this;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';

            fetch('{{ route("teacher.profile-photo.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ photo: croppedImageData })
            })
                .then(res => res.json())
                .then(response => {
                    if (response.status === 'success') {
                        alert('Foto profil berhasil disimpan!');
                        location.reload();
                    } else {
                        alert('Error: ' + response.message);
                        btn.disabled = false;
                        btn.innerHTML = '<i class="bi bi-check-lg me-1"></i> Simpan Foto';
                    }
                })
                .catch(err => {
                    alert('Terjadi kesalahan saat upload foto.');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-check-lg me-1"></i> Simpan Foto';
                });
        });

        // Reset modal on close
        document.getElementById('photoModal').addEventListener('hidden.bs.modal', function () {
            resetPhotoModal();
        });
    </script>
</body>

</html>