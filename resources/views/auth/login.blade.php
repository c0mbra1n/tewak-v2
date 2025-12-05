<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Tewak</title>
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
    <script>
        if (localStorage.getItem('darkMode') === 'true') {
            document.documentElement.setAttribute('data-theme', 'dark');
        }
    </script>
</head>

<body>
    @include('partials.loader')
    <div class="container d-flex align-items-center justify-content-center min-vh-100">
        <div class="card p-4" style="max-width: 400px; width: 100%;">
            <div class="card-body">
                <div class="text-center mb-4 position-relative">
                    <button type="button" class="btn btn-link position-absolute top-0 end-0 p-0" id="darkModeToggle"
                        title="Toggle Dark Mode">
                        <i class="bi bi-moon-fill" id="darkModeIcon"></i>
                    </button>
                    <h3 class="fw-bold text-primary">Tewak</h3>
                    <p class="text-muted">Monitoring Guru & Absensi</p>
                </div>

                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-person"></i></span>
                            <input type="text"
                                class="form-control border-start-0 ps-0 @error('username') is-invalid @enderror"
                                id="username" name="username" value="{{ old('username') }}" required autofocus
                                placeholder="Enter your username">
                        </div>
                        @error('username')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock"></i></span>
                            <input type="password"
                                class="form-control border-start-0 ps-0 @error('password') is-invalid @enderror"
                                id="password" name="password" required placeholder="Enter your password">
                        </div>
                        @error('password')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-primary py-2 fw-bold">Sign In</button>
                    </div>

                    <div class="text-center">
                        <a href="{{ route('monitor.index') }}" class="text-muted small">
                            <i class="bi bi-display me-1"></i> Lihat Monitoring
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

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
    </script>
</body>

</html>