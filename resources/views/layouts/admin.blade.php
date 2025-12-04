<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') - Tewak</title>
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
    @stack('styles')
</head>

<body>
    <div class="d-flex" id="wrapper">
        <!-- Sidebar -->
        <div class="bg-white border-end" id="sidebar-wrapper" style="width: 250px; min-height: 100vh;">
            <div class="sidebar-heading border-bottom bg-light p-4 text-center">
                <h4 class="fw-bold text-primary mb-0">Tewak Admin</h4>
            </div>
            <div class="list-group list-group-flush p-3">
                <a href="{{ route('admin.dashboard') }}"
                    class="list-group-item list-group-item-action list-group-item-light p-3 rounded mb-2 {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                </a>
                <a href="{{ route('admin.teachers.index') }}"
                    class="list-group-item list-group-item-action list-group-item-light p-3 rounded mb-2 {{ request()->routeIs('admin.teachers.*') ? 'active' : '' }}">
                    <i class="bi bi-people-fill me-2"></i> Data Guru
                </a>
                <a href="{{ route('admin.classes.index') }}"
                    class="list-group-item list-group-item-action list-group-item-light p-3 rounded mb-2 {{ request()->routeIs('admin.classes.*') ? 'active' : '' }}">
                    <i class="bi bi-building me-2"></i> Data Kelas
                </a>
                <a href="{{ route('admin.attendance.index') }}"
                    class="list-group-item list-group-item-action list-group-item-light p-3 rounded mb-2 {{ request()->routeIs('admin.attendance.*') ? 'active' : '' }}">
                    <i class="bi bi-calendar-check me-2"></i> Laporan Absensi
                </a>
                <a href="{{ route('admin.schedules.index') }}"
                    class="list-group-item list-group-item-action list-group-item-light p-3 rounded mb-2 {{ request()->routeIs('admin.schedules.*') ? 'active' : '' }}">
                    <i class="bi bi-calendar3 me-2"></i> Jadwal Mengajar
                </a>
                <a href="#"
                    class="list-group-item list-group-item-action list-group-item-light p-3 rounded mb-2 text-danger"
                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="bi bi-box-arrow-right me-2"></i> Logout
                </a>
            </div>
        </div>

        <!-- Page Content -->
        <div id="page-content-wrapper" class="w-100 bg-light">
            <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom px-4 py-3">
                <div class="d-flex align-items-center">
                    <h2 class="fs-4 fw-bold mb-0">@yield('page-title', 'Dashboard')</h2>
                </div>

                <div class="ms-auto d-flex align-items-center">
                    <span class="me-3 text-muted">Halo, {{ auth()->user()->full_name ?? 'Admin' }}</span>
                    <div class="avatar-circle bg-primary text-white d-flex align-items-center justify-content-center rounded-circle"
                        style="width: 40px; height: 40px;">
                        {{ strtoupper(substr(auth()->user()->full_name ?? 'A', 0, 1)) }}
                    </div>
                </div>
            </nav>

            <div class="container-fluid px-4 py-4">
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
        </div>
    </div>

    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
        @csrf
    </form>

    @stack('scripts')
</body>

</html>