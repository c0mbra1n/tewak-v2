<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Jadwal Mengajar - Tewak</title>
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        if (localStorage.getItem('darkMode') === 'true') {
            document.documentElement.setAttribute('data-theme', 'dark');
        }
    </script>
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
                        <a class="nav-link" href="{{ route('teacher.dashboard') }}">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="{{ route('teacher.schedules') }}">Jadwal</a>
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
                    <div class="dropdown d-inline">
                        <button class="btn btn-outline-light btn-sm dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-gear-fill"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton1">
                            <li>
                                <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                                    <i class="bi bi-key me-2"></i>Ganti Password
                                </button>
                            </li>
                            <li><hr class="dropdown-divider"></li>
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
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row">
            <!-- Form Tambah Jadwal -->
            <div class="col-12 col-lg-5 mb-3 mb-lg-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white py-2 py-lg-3">
                        <h5 class="mb-0 fs-6 fs-lg-5"><i class="bi bi-plus-circle me-2"></i>Tambah Jadwal</h5>
                    </div>
                    <div class="card-body p-3">
                        <form action="{{ route('teacher.schedules.store') }}" method="POST">
                            @csrf

                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label for="day" class="form-label small">Hari <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select form-select-sm @error('day') is-invalid @enderror"
                                        id="day" name="day" required>
                                        <option value="">Pilih</option>
                                        <option value="Monday" {{ old('day') == 'Monday' ? 'selected' : '' }}>Senin
                                        </option>
                                        <option value="Tuesday" {{ old('day') == 'Tuesday' ? 'selected' : '' }}>Selasa
                                        </option>
                                        <option value="Wednesday" {{ old('day') == 'Wednesday' ? 'selected' : '' }}>Rabu
                                        </option>
                                        <option value="Thursday" {{ old('day') == 'Thursday' ? 'selected' : '' }}>Kamis
                                        </option>
                                        <option value="Friday" {{ old('day') == 'Friday' ? 'selected' : '' }}>Jumat
                                        </option>
                                        <option value="Saturday" {{ old('day') == 'Saturday' ? 'selected' : '' }}>Sabtu
                                        </option>
                                    </select>
                                </div>
                                <div class="col-6 mb-3">
                                    <label for="class_id" class="form-label small">Kelas <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select form-select-sm @error('class_id') is-invalid @enderror"
                                        id="class_id" name="class_id" required>
                                        <option value="">Pilih</option>
                                        @foreach($classes as $class)
                                            <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>
                                                {{ $class->class_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="subject" class="form-label small">Mata Pelajaran <span
                                        class="text-danger">*</span></label>
                                <select class="form-select form-select-sm @error('subject') is-invalid @enderror"
                                    id="subject" name="subject" required>
                                    <option value="">Pilih Mapel</option>
                                    @foreach($user->subjects as $subj)
                                        <option value="{{ $subj->name }}" {{ old('subject') == $subj->name ? 'selected' : '' }}>
                                            {{ $subj->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @if($user->subjects->isEmpty())
                                    <small class="text-danger">Mapel belum diatur admin</small>
                                @endif
                            </div>

                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label for="start_time" class="form-label small">Jam <span
                                            class="text-danger">*</span></label>
                                    <input type="time"
                                        class="form-control form-control-sm @error('start_time') is-invalid @enderror"
                                        id="start_time" name="start_time" value="{{ old('start_time') }}" required>
                                </div>
                                <div class="col-6 mb-3">
                                    <label for="lesson_hours" class="form-label small">JP <span
                                            class="text-danger">*</span></label>
                                    <select
                                        class="form-select form-select-sm @error('lesson_hours') is-invalid @enderror"
                                        id="lesson_hours" name="lesson_hours" required>
                                        @for($i = 1; $i <= 8; $i++)
                                            <option value="{{ $i }}" {{ old('lesson_hours', 2) == $i ? 'selected' : '' }}>
                                                {{ $i }} JP
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                            </div>

                            <div class="alert alert-info small py-2 mb-3">
                                <i class="bi bi-info-circle me-1"></i> 1 JP = 45 menit
                            </div>

                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                <i class="bi bi-check-lg me-1"></i> Simpan
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Daftar Jadwal -->
            <div class="col-12 col-lg-7 mb-3 mb-lg-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-white py-2 py-lg-3">
                        <h5 class="mb-0 fw-bold fs-6 fs-lg-5"><i class="bi bi-calendar3 me-2"></i>Jadwal Saya</h5>
                    </div>
                    <div class="card-body p-0">
                        <!-- Mobile View -->
                        <div class="d-lg-none">
                            @forelse($schedules as $schedule)
                                <div class="p-3 border-bottom">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <div class="fw-bold small">{{ $schedule->subject }}</div>
                                            <div class="text-muted" style="font-size: 0.75rem;">
                                                {{ $schedule->classRoom->class_name ?? '-' }}
                                            </div>
                                        </div>
                                        <div class="d-flex gap-1">
                                            <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2"
                                                data-bs-toggle="modal" data-bs-target="#editModal{{ $schedule->id }}">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2 btn-delete"
                                                data-form="delete-form-{{ $schedule->id }}">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                            <form id="delete-form-{{ $schedule->id }}"
                                                action="{{ route('teacher.schedules.destroy', $schedule) }}" method="POST"
                                                style="display: none;">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2 text-muted" style="font-size: 0.75rem;">
                                        @php
                                            $days = ['Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];
                                        @endphp
                                        <span><i
                                                class="bi bi-calendar me-1"></i>{{ $days[$schedule->day] ?? $schedule->day }}</span>
                                        <span>
                                            <i class="bi bi-clock me-1"></i>
                                            {{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }} -
                                            {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}
                                        </span>
                                        <span class="badge bg-secondary">{{ $schedule->lesson_hours }} JP</span>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-4 text-muted small">
                                    Belum ada jadwal. Tambahkan jadwal mengajar Anda.
                                </div>
                            @endforelse
                        </div>

                        <!-- Desktop View -->
                        <div class="table-responsive d-none d-lg-block">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Hari</th>
                                        <th>Kelas</th>
                                        <th>Mapel</th>
                                        <th>Waktu</th>
                                        <th>JP</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($schedules as $schedule)
                                        <tr>
                                            <td>
                                                @php
                                                    $days = ['Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu'];
                                                @endphp
                                                {{ $days[$schedule->day] ?? $schedule->day }}
                                            </td>
                                            <td>{{ $schedule->classRoom->class_name ?? '-' }}</td>
                                            <td>{{ $schedule->subject }}</td>
                                            <td>
                                                <small>
                                                    {{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }} -
                                                    {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}
                                                </small>
                                            </td>
                                            <td><span class="badge bg-secondary">{{ $schedule->lesson_hours }} JP</span>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline-primary me-1"
                                                    data-bs-toggle="modal" data-bs-target="#editModal{{ $schedule->id }}">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger btn-delete"
                                                    data-form="delete-form-desktop-{{ $schedule->id }}">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                                <form id="delete-form-desktop-{{ $schedule->id }}"
                                                    action="{{ route('teacher.schedules.destroy', $schedule) }}"
                                                    method="POST" style="display: none;">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">
                                                Belum ada jadwal. Tambahkan jadwal mengajar Anda.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-white text-center py-3 border-top mt-4">
        <div class="container">
            <small class="text-muted">
                Copyright &copy; {{ date('Y') }} Developed by <strong>c0mbra1n</strong> in Banten 🇮🇩
            </small>
        </div>
    </footer>

    <!-- Edit Modals -->
    @foreach($schedules as $schedule)
        <div class="modal fade" id="editModal{{ $schedule->id }}" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('teacher.schedules.update', $schedule) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Edit Jadwal</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label class="form-label">Hari</label>
                                    <select class="form-select" name="day" required>
                                        @php
                                            $days = ['Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];
                                        @endphp
                                        @foreach($days as $value => $label)
                                            <option value="{{ $value }}" {{ $schedule->day == $value ? 'selected' : '' }}>
                                                {{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-6 mb-3">
                                    <label class="form-label">Kelas</label>
                                    <select class="form-select" name="class_id" required>
                                        @foreach($classes as $class)
                                            <option value="{{ $class->id }}" {{ $schedule->class_id == $class->id ? 'selected' : '' }}>
                                                {{ $class->class_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Mata Pelajaran</label>
                                <select class="form-select" name="subject" required>
                                    @foreach($user->subjects as $subj)
                                        <option value="{{ $subj->name }}" {{ $schedule->subject == $subj->name ? 'selected' : '' }}>
                                            {{ $subj->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label class="form-label">Jam Mulai</label>
                                    <input type="time" class="form-control" name="start_time"
                                        value="{{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }}" required>
                                </div>
                                <div class="col-6 mb-3">
                                    <label class="form-label">Jumlah JP</label>
                                    <select class="form-select" name="lesson_hours" required>
                                        @for($i = 1; $i <= 8; $i++)
                                            <option value="{{ $i }}" {{ $schedule->lesson_hours == $i ? 'selected' : '' }}>
                                                {{ $i }} JP
                                            </option>
                                        @endfor
                                    </select>
                                </div>
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
    @endforeach

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

        // SweetAlert Delete Confirmation
        document.querySelectorAll('.btn-delete').forEach(button => {
            button.addEventListener('click', function() {
                const formId = this.getAttribute('data-form');
                Swal.fire({
                    title: 'Hapus Jadwal?',
                    text: 'Jadwal ini akan dihapus permanen!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById(formId).submit();
                    }
                });
            });
        });
    </script>
</body>

</html>