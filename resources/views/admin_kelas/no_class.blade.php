<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin Kelas - Tewak</title>
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
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
            <a class="navbar-brand fw-bold" href="#">Tewak</a>
            <div class="d-flex align-items-center">
                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-light btn-sm">Logout</button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-exclamation-triangle text-warning display-1 mb-4"></i>
                        <h4 class="fw-bold mb-3">Belum Ada Kelas</h4>
                        <p class="text-muted mb-4">
                            Akun Anda belum ditetapkan ke kelas manapun.
                            Silakan hubungi Admin untuk menetapkan kelas Anda.
                        </p>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-box-arrow-right me-2"></i> Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>