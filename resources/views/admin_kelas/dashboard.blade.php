<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard Admin Kelas - Tewak</title>
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
    <script>
        if (localStorage.getItem('darkMode') === 'true') {
            document.documentElement.setAttribute('data-theme', 'dark');
        }
    </script>
    <style>
        .qr-container {
            background: white;
            padding: 1.5rem;
            border-radius: 1rem;
            text-align: center;
        }

        .qr-container img {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>

<body>
    @include('partials.loader')
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('admin_kelas.dashboard') }}">Tewak</a>
            <div class="d-flex align-items-center d-lg-none order-lg-1">
                <button type="button" class="btn btn-link text-white p-1 me-1" id="darkModeToggleMobile"
                    title="Toggle Dark Mode">
                    <i class="bi bi-moon-fill" id="darkModeIconMobile"></i>
                </button>
            </div>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="{{ route('admin_kelas.dashboard') }}">Dashboard</a>
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
                    <span class="text-white me-3 d-none d-lg-inline">{{ $user->full_name }}</span>
                    <form action="{{ route('logout') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-light btn-sm">Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white text-center py-3">
                        <h4 class="mb-0 fw-bold">{{ $class->class_name }}</h4>
                        <small>QR Code Absensi Kelas</small>
                    </div>
                    <div class="card-body p-4">
                        <div class="qr-container">
                            <h5 class="fw-bold mb-3">{{ $class->class_name }}</h5>
                            <div class="mb-3">
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data={{ urlencode($qrData) }}"
                                    alt="QR Code {{ $class->class_name }}" width="250" height="250">
                            </div>
                            <p class="text-muted mb-0">
                                <i class="bi bi-calendar me-1"></i>
                                {{ \Carbon\Carbon::parse($today)->locale('id')->translatedFormat('l, d F Y') }}
                            </p>
                        </div>

                        <div class="alert alert-info mt-4 mb-0">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Info:</strong> QR Code ini berlaku untuk hari ini saja. Besok akan berubah otomatis.
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm mt-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold"><i class="bi bi-geo-alt me-2"></i>Lokasi Kelas</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-4">
                                <small class="text-muted">Latitude</small>
                                <p class="fw-bold mb-0 small">{{ $class->latitude }}</p>
                            </div>
                            <div class="col-4">
                                <small class="text-muted">Longitude</small>
                                <p class="fw-bold mb-0 small">{{ $class->longitude }}</p>
                            </div>
                            <div class="col-4">
                                <small class="text-muted">Radius</small>
                                <p class="fw-bold mb-0">{{ $class->radius }}m</p>
                            </div>
                        </div>
                    </div>
                </div>
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
    </script>
</body>

</html>