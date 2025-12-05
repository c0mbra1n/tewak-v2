<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') - Tewak</title>
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
    <script>
        if (localStorage.getItem('darkMode') === 'true') {
            document.documentElement.setAttribute('data-theme', 'dark');
        }
    </script>
    <style>
        #sidebar-wrapper {
            min-height: 100vh;
            transition: margin 0.25s ease-out, transform 0.25s ease-out;
        }

        #page-content-wrapper {
            min-width: 0;
            width: 100%;
        }

        /* Mobile styles */
        @media (max-width: 991.98px) {
            #sidebar-wrapper {
                position: fixed;
                top: 0;
                left: 0;
                z-index: 1050;
                transform: translateX(-100%);
                width: 280px !important;
            }

            #sidebar-wrapper.show {
                transform: translateX(0);
            }

            .sidebar-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                z-index: 1040;
            }

            .sidebar-overlay.show {
                display: block;
            }

            #wrapper {
                flex-direction: column;
            }
        }

        /* Desktop styles */
        @media (min-width: 992px) {
            #sidebar-wrapper {
                width: 250px;
                flex-shrink: 0;
            }
        }

        .hamburger-btn {
            border: none;
            background: transparent;
            font-size: 1.5rem;
            padding: 0.5rem;
            cursor: pointer;
        }
    </style>
    @stack('styles')
</head>

<body>
    @include('partials.loader')

    <!-- Sidebar Overlay for Mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <div class="d-flex" id="wrapper">
        <!-- Sidebar -->
        <div class="bg-white border-end" id="sidebar-wrapper">
            <div
                class="sidebar-heading border-bottom bg-light p-3 p-lg-4 text-center d-flex justify-content-between align-items-center">
                <h4 class="fw-bold text-primary mb-0">Tewak Admin</h4>
                <button class="btn-close d-lg-none" id="closeSidebar" aria-label="Close"></button>
            </div>
            <div class="list-group list-group-flush p-3">
                <a href="{{ route('admin.dashboard') }}"
                    class="list-group-item list-group-item-action list-group-item-light p-3 rounded mb-2 {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                </a>
                <a href="{{ route('admin.teachers.index') }}"
                    class="list-group-item list-group-item-action list-group-item-light p-3 rounded mb-2 {{ request()->routeIs('admin.teachers.*') ? 'active' : '' }}">
                    <i class="bi bi-people-fill me-2"></i> Data Pengguna
                </a>
                <a href="{{ route('admin.classes.index') }}"
                    class="list-group-item list-group-item-action list-group-item-light p-3 rounded mb-2 {{ request()->routeIs('admin.classes.*') ? 'active' : '' }}">
                    <i class="bi bi-building me-2"></i> Data Kelas
                </a>
                <a href="{{ route('admin.attendance.index') }}"
                    class="list-group-item list-group-item-action list-group-item-light p-3 rounded mb-2 {{ request()->routeIs('admin.attendance.*') ? 'active' : '' }}">
                    <i class="bi bi-calendar-check me-2"></i> Laporan Absensi
                </a>

                <!-- Jadwal Mengajar Submenu -->
                <div class="mb-2">
                    <a href="#jadwalSubmenu"
                        class="list-group-item list-group-item-action list-group-item-light p-3 rounded d-flex justify-content-between align-items-center {{ request()->routeIs('admin.schedules.*') || request()->routeIs('admin.subjects.*') ? 'active' : '' }}"
                        data-bs-toggle="collapse"
                        aria-expanded="{{ request()->routeIs('admin.schedules.*') || request()->routeIs('admin.subjects.*') ? 'true' : 'false' }}">
                        <span><i class="bi bi-calendar3 me-2"></i> Jadwal Mengajar</span>
                        <i class="bi bi-chevron-down small"></i>
                    </a>
                    <div class="collapse {{ request()->routeIs('admin.schedules.*') || request()->routeIs('admin.subjects.*') ? 'show' : '' }}"
                        id="jadwalSubmenu">
                        <div class="ps-4 mt-1">
                            <a href="{{ route('admin.schedules.index') }}"
                                class="list-group-item list-group-item-action list-group-item-light p-2 rounded mb-1 {{ request()->routeIs('admin.schedules.*') ? 'active' : '' }}">
                                <i class="bi bi-clock me-2"></i> Jadwal
                            </a>
                            <a href="{{ route('admin.subjects.index') }}"
                                class="list-group-item list-group-item-action list-group-item-light p-2 rounded mb-1 {{ request()->routeIs('admin.subjects.*') ? 'active' : '' }}">
                                <i class="bi bi-book me-2"></i> Data Mapel
                            </a>
                        </div>
                    </div>
                </div>

                <a href="{{ route('admin.manual-attendance.index') }}"
                    class="list-group-item list-group-item-action list-group-item-light p-3 rounded mb-2 {{ request()->routeIs('admin.manual-attendance.*') ? 'active' : '' }}">
                    <i class="bi bi-pencil-square me-2"></i> Absen Manual
                </a>
                <a href="#" class="list-group-item list-group-item-action list-group-item-light p-3 rounded mb-2"
                    data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                    <i class="bi bi-key me-2"></i> Ganti Password
                </a>
                <a href="#"
                    class="list-group-item list-group-item-action list-group-item-light p-3 rounded mb-2 text-danger"
                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="bi bi-box-arrow-right me-2"></i> Logout
                </a>
            </div>
        </div>

        <!-- Page Content -->
        <div id="page-content-wrapper" class="bg-light d-flex flex-column min-vh-100">
            <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom px-3 px-lg-4 py-2 py-lg-3">
                <div class="d-flex align-items-center">
                    <button class="hamburger-btn d-lg-none me-2" id="toggleSidebar">
                        <i class="bi bi-list"></i>
                    </button>
                    <h2 class="fs-5 fs-lg-4 fw-bold mb-0">@yield('page-title', 'Dashboard')</h2>
                </div>

                <div class="ms-auto d-flex align-items-center">
                    <button type="button" class="dark-mode-toggle btn btn-link p-2 me-1 me-lg-2" id="darkModeToggle"
                        title="Toggle Dark Mode">
                        <i class="bi bi-moon-fill" id="darkModeIcon"></i>
                    </button>
                    <a href="{{ route('monitor.index') }}" class="btn btn-outline-primary btn-sm me-2" target="_blank">
                        <i class="bi bi-display"></i><span class="d-none d-lg-inline ms-1">Monitoring</span>
                    </a>
                    <span class="me-2 text-muted d-none d-md-inline">{{ auth()->user()->full_name ?? 'Admin' }}</span>
                    <div class="avatar-circle bg-primary text-white d-flex align-items-center justify-content-center rounded-circle"
                        style="width: 35px; height: 35px; font-size: 0.9rem;">
                        {{ strtoupper(substr(auth()->user()->full_name ?? 'A', 0, 1)) }}
                    </div>
                </div>
            </nav>

            <div class="container-fluid px-3 px-lg-4 py-3 py-lg-4">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @yield('content')
            </div>

            <footer class="bg-white text-center py-3 border-top mt-auto">
                <small class="text-muted">
                    Copyright &copy; {{ date('Y') }} Developed by <strong>c0mbra1n</strong> in Banten 🇮🇩
                </small>
            </footer>
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

    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
        @csrf
    </form>

    <script>
        // Dark Mode Toggle
        const darkModeToggle = document.getElementById('darkModeToggle');
        const darkModeIcon = document.getElementById('darkModeIcon');

        function updateDarkModeIcon() {
            const isDark = localStorage.getItem('darkMode') === 'true';
            darkModeIcon.className = isDark ? 'bi bi-sun-fill' : 'bi bi-moon-fill';
        }

        darkModeToggle.addEventListener('click', function () {
            const isDark = localStorage.getItem('darkMode') === 'true';
            localStorage.setItem('darkMode', !isDark);
            document.documentElement.setAttribute('data-theme', !isDark ? 'dark' : 'light');
            updateDarkModeIcon();
        });

        updateDarkModeIcon();

        // Mobile Sidebar Toggle
        const sidebar = document.getElementById('sidebar-wrapper');
        const overlay = document.getElementById('sidebarOverlay');
        const toggleBtn = document.getElementById('toggleSidebar');
        const closeBtn = document.getElementById('closeSidebar');

        function openSidebar() {
            sidebar.classList.add('show');
            overlay.classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function closeSidebar() {
            sidebar.classList.remove('show');
            overlay.classList.remove('show');
            document.body.style.overflow = '';
        }

        toggleBtn.addEventListener('click', openSidebar);
        closeBtn.addEventListener('click', closeSidebar);
        overlay.addEventListener('click', closeSidebar);
    </script>
    @stack('scripts')
</body>

</html>