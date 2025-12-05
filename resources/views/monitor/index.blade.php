<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring Guru - Tewak</title>
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
    <script>
        if (localStorage.getItem('darkMode') === 'true') {
            document.documentElement.setAttribute('data-theme', 'dark');
        }
    </script>
    <style>
        :root {
            --header-height: 80px;
            --footer-height: 50px;
        }

        body {
            font-size: 1.1rem;
            /* Base font size increase */
            overflow-x: hidden;
        }

        .monitoring-header {
            background: linear-gradient(135deg, #4361ee, #3f37c9);
            color: white;
            padding: 1rem 2rem;
            /* Reduced padding */
            border-radius: 0 0 1rem 1rem;
            /* Rounded bottom only */
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 15px rgba(67, 97, 238, 0.3);
        }

        .monitoring-header h1 {
            font-size: 2.5rem;
            /* Larger title */
            margin-bottom: 0.5rem;
        }

        .monitoring-header p {
            font-size: 1.2rem;
        }

        .teacher-card {
            transition: transform 0.2s, box-shadow 0.2s;
            height: 100%;
        }

        .teacher-card .card-title {
            font-size: 1.4rem;
            /* Larger teacher name */
        }

        .teacher-card .status-badge {
            font-size: 1rem;
            /* Larger badge */
            padding: 0.5em 1em;
        }

        .avatar-circle {
            width: 100px;
            /* Larger avatar */
            height: 100px;
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .table {
            font-size: 1.2rem;
            /* Larger table text */
        }

        .table th {
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .table td {
            vertical-align: middle;
            padding: 1rem 0.75rem;
            /* More breathing room in cells */
        }

        .status-badge {
            font-size: 1rem;
            padding: 0.5em 1em;
        }

        /* Compact margins for public display */
        .container-fluid {
            padding-left: 2rem;
            padding-right: 2rem;
        }

        /* ... existing status colors ... */

        /* Dark Mode Global Styles */
        [data-theme="dark"] body {
            background-color: #0f172a !important;
            color: #e2e8f0 !important;
        }

        [data-theme="dark"] .bg-light,
        [data-theme="dark"] .bg-white {
            background-color: #1e293b !important;
            color: #e2e8f0 !important;
        }

        [data-theme="dark"] .card {
            background-color: #1e293b !important;
            border-color: #334155 !important;
            color: #e2e8f0 !important;
        }

        [data-theme="dark"] .card-header {
            background-color: #1e293b !important;
            border-bottom-color: #334155 !important;
            color: #e2e8f0 !important;
        }

        [data-theme="dark"] .card-title,
        [data-theme="dark"] h1,
        [data-theme="dark"] h2,
        [data-theme="dark"] h3,
        [data-theme="dark"] h4,
        [data-theme="dark"] h5,
        [data-theme="dark"] h6 {
            color: #f1f5f9 !important;
        }

        [data-theme="dark"] .text-muted {
            color: #94a3b8 !important;
        }

        [data-theme="dark"] .table {
            color: #e2e8f0 !important;
            border-color: #334155 !important;
        }

        [data-theme="dark"] .table-hover tbody tr:hover {
            color: #e2e8f0 !important;
            background-color: #334155 !important;
        }

        [data-theme="dark"] .table thead th {
            color: #e2e8f0 !important;
            background-color: #0f172a !important;
            border-bottom-color: #334155 !important;
        }

        [data-theme="dark"] .table td,
        [data-theme="dark"] .table th {
            border-bottom-color: #334155 !important;
            color: #e2e8f0 !important;
        }

        [data-theme="dark"] .monitoring-header {
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.5) !important;
        }

        [data-theme="dark"] .btn-outline-secondary {
            color: #cbd5e1 !important;
            border-color: #475569 !important;
        }

        [data-theme="dark"] .btn-outline-secondary:hover,
        [data-theme="dark"] .btn-outline-secondary.active {
            background-color: #475569 !important;
            color: #fff !important;
        }

        [data-theme="dark"] .form-select {
            background-color: #1e293b !important;
            border-color: #334155 !important;
            color: #e2e8f0 !important;
        }

        [data-theme="dark"] .border-top {
            border-top-color: #334155 !important;
        }

        [data-theme="dark"] .teacher-card {
            background-color: #1e293b !important;
            border: 1px solid #334155 !important;
        }

        [data-theme="dark"] .teacher-card:hover {
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3) !important;
            background-color: #26334d !important;
        }

        [data-theme="dark"] .schedule-info {
            background-color: #0f172a !important;
            color: #cbd5e1 !important;
        }

        [data-theme="dark"] .list-view-item:hover {
            background-color: #1e293b !important;
        }
    </style>
</head>

<body>
    @include('partials.loader')
    <div class="d-flex flex-column min-vh-100">
        <div class="container-fluid px-4 flex-grow-1">
            <div class="monitoring-header text-center position-relative mx-n4 mt-n4 pt-4">
                <h1 class="fw-bold mb-2">Monitoring Keberadaan Guru</h1>
                <p class="mb-0">
                    <span id="current-time">Loading time...</span>
                    <span class="badge bg-success ms-2" id="realtime-badge">
                        <i class="bi bi-broadcast"></i> LIVE
                    </span>
                </p>
                <div class="position-absolute top-50 end-0 translate-middle-y me-4 d-none d-md-flex gap-2">
                    <button type="button" class="btn btn-light" id="darkModeToggle" title="Toggle Dark Mode">
                        <i class="bi bi-moon-fill" id="darkModeIcon"></i>
                    </button>
                    <a href="{{ route('login') }}" class="btn btn-light fw-bold text-primary">Login</a>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <div class="d-flex gap-2 flex-wrap">
                    <span class="badge status-hadir">Hadir</span>
                    <span class="badge status-telat">Telat</span>
                    <span class="badge status-belum_hadir">Belum Hadir</span>
                    <span class="badge status-tidak_hadir">Tidak Hadir</span>
                    <span class="badge status-izin">Izin</span>
                    <span class="badge status-sakit">Sakit</span>
                    <span class="badge status-dinas">Dinas</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <!-- View Mode Selector -->
                    <div class="btn-group me-2" role="group">
                        <button type="button" class="btn btn-outline-primary active" id="btn-mode-all">Semua</button>
                        <button type="button" class="btn btn-outline-primary" id="btn-mode-block">Per Blok</button>
                    </div>

                    <!-- Block Selector (Hidden by default) -->
                    <select class="form-select d-none" id="block-selector" style="width: auto;">
                        <option value="" disabled selected>Pilih Blok</option>
                        @for($i = 1; $i <= 10; $i++)
                            <option value="{{ $i }}">Blok {{ $i }}</option>
                        @endfor
                    </select>

                    <div class="view-toggle btn-group" role="group" id="view-toggle-group">
                        <button type="button" class="btn btn-outline-secondary active" id="btn-list-view"
                            title="List View">
                            <i class="bi bi-list-ul"></i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="btn-grid-view" title="Grid View">
                            <i class="bi bi-grid-3x3-gap"></i>
                        </button>
                    </div>
                    <button type="button" class="btn btn-outline-secondary d-md-none" id="darkModeToggleMobile"
                        title="Toggle Dark Mode">
                        <i class="bi bi-moon-fill" id="darkModeIconMobile"></i>
                    </button>
                    <a href="{{ route('login') }}" class="btn btn-primary btn-sm d-md-none">Login</a>
                </div>
            </div>

            <div id="monitoring-container">
                <!-- List View (Default) -->
                <div id="list-view" class="card shadow-sm">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Guru</th>
                                    <th>Status</th>
                                    <th>Lokasi</th>
                                    <th>Mapel</th>
                                    <th>Waktu Scan</th>
                                    <th>Jadwal Aktif</th>
                                </tr>
                            </thead>
                            <tbody id="list-view-body">
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Grid View (Hidden by default) -->
                <div id="grid-view" class="row g-4 d-none">
                    <div class="col-12 text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>

                <!-- Block View (Hidden by default) -->
                <div id="block-view" class="card shadow-sm d-none">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold" id="block-title">Monitoring Blok</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Kelas</th>
                                    <th>Jadwal Pelajaran</th>
                                    <th>Guru Pengajar</th>
                                    <th>Status Kehadiran Guru</th>
                                </tr>
                            </thead>
                            <tbody id="block-view-body">
                                <tr>
                                    <td colspan="4" class="text-center py-4">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <footer class="text-center py-2 mt-auto border-top">
            <small class="text-muted" style="font-size: 0.9rem;">
                Copyright &copy; {{ date('Y') }} Developed by <strong>c0mbra1n</strong> in Banten 🇮🇩
            </small>
        </footer>
    </div>

    <script type="module">
        const statusLabels = {
            'hadir': 'HADIR',
            'telat': 'TELAT',
            'izin': 'IZIN',
            'sakit': 'SAKIT',
            'dinas': 'DINAS LUAR',
            'alpa': 'ALPA',
            'belum_hadir': 'BELUM HADIR',
            'belum_hadir_telat': 'BELUM HADIR (TELAT)',
            'tidak_hadir': 'TIDAK HADIR',
            'tidak_ada_jadwal': 'TIDAK ADA JADWAL'
        };

        let currentView = 'list';
        let monitoringData = [];

        // Dark Mode Toggle
        function updateDarkModeIcons() {
            const isDark = localStorage.getItem('darkMode') === 'true';
            const iconClass = isDark ? 'bi bi-sun-fill' : 'bi bi-moon-fill';
            document.getElementById('darkModeIcon').className = iconClass;
            document.getElementById('darkModeIconMobile').className = iconClass;
        }

        function toggleDarkMode() {
            const isDark = localStorage.getItem('darkMode') === 'true';
            localStorage.setItem('darkMode', !isDark);
            document.documentElement.setAttribute('data-theme', !isDark ? 'dark' : 'light');
            updateDarkModeIcons();
        }

        document.getElementById('darkModeToggle').addEventListener('click', toggleDarkMode);
        document.getElementById('darkModeToggleMobile').addEventListener('click', toggleDarkMode);
        updateDarkModeIcons();

        function updateTime() {
            const now = new Date();
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' };
            document.getElementById('current-time').innerText = now.toLocaleDateString('id-ID', options);
        }
        setInterval(updateTime, 1000);
        updateTime();

        // View Toggle
        document.getElementById('btn-list-view').addEventListener('click', function () {
            currentView = 'list';
            this.classList.add('active');
            document.getElementById('btn-grid-view').classList.remove('active');
            document.getElementById('list-view').classList.remove('d-none');
            document.getElementById('grid-view').classList.add('d-none');
        });

        document.getElementById('btn-grid-view').addEventListener('click', function () {
            currentView = 'grid';
            this.classList.add('active');
            document.getElementById('btn-list-view').classList.remove('active');
            document.getElementById('grid-view').classList.remove('d-none');
            document.getElementById('list-view').classList.add('d-none');
            renderGridView();
        });

        function renderListView() {
            const tbody = document.getElementById('list-view-body');

            if (monitoringData.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted">Belum ada data guru.</td></tr>';
                return;
            }

            tbody.innerHTML = monitoringData.map(teacher => {
                const initials = teacher.name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
                const statusLabel = statusLabels[teacher.status] || teacher.status.replace('_', ' ').toUpperCase();
                const statusClass = `status-${teacher.status}`;

                let scheduleInfo = '-';
                if (teacher.schedule) {
                    scheduleInfo = `${teacher.schedule.subject} (${teacher.schedule.start} - ${teacher.schedule.end})`;
                }

                // Photo or initials avatar
                const avatarHtml = teacher.photo
                    ? `<img src="${teacher.photo}" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">`
                    : `<div class="avatar-circle avatar-circle-sm bg-primary text-white">${initials}</div>`;

                return `
                    <tr class="list-view-item">
                        <td class="ps-4">
                            <div class="d-flex align-items-center">
                                <div class="me-3">${avatarHtml}</div>
                                <span class="fw-bold">${teacher.name}</span>
                            </div>
                        </td>
                        <td><span class="badge ${statusClass} status-badge">${statusLabel}</span></td>
                        <td>${teacher.location === '-' ? '<span class="text-muted">-</span>' : teacher.location}</td>
                        <td>${teacher.subject === '-' ? '<span class="text-muted">-</span>' : teacher.subject}</td>
                        <td>${teacher.time === '-' ? '<span class="text-muted">-</span>' : teacher.time}</td>
                        <td><small class="text-muted">${scheduleInfo}</small></td>
                    </tr>
                `;
            }).join('');
        }

        function renderGridView() {
            const grid = document.getElementById('grid-view');

            if (monitoringData.length === 0) {
                grid.innerHTML = '<div class="col-12 text-center text-muted">Belum ada data guru.</div>';
                return;
            }

            grid.innerHTML = monitoringData.map(teacher => {
                const initials = teacher.name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
                const statusLabel = statusLabels[teacher.status] || teacher.status.replace('_', ' ').toUpperCase();
                const statusClass = `status-${teacher.status}`;
                const locationText = teacher.location === '-' ? 'Belum ada di kelas' : teacher.location;

                let scheduleHtml = '';
                if (teacher.schedule) {
                    scheduleHtml = `
                        <div class="schedule-info">
                            <i class="bi bi-calendar-event me-1"></i>
                            <strong>${teacher.schedule.subject}</strong><br>
                            <small>${teacher.schedule.class} | ${teacher.schedule.start} - ${teacher.schedule.end}</small>
                        </div>
                    `;
                }

                // Photo or initials avatar
                const avatarHtml = teacher.photo
                    ? `<img src="${teacher.photo}" class="rounded-circle d-block mx-auto" style="width: 70px; height: 70px; object-fit: cover; margin-bottom: 0.5rem;">`
                    : `<div class="avatar-circle">${initials}</div>`;

                return `
                    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                        <div class="card teacher-card h-100 text-center p-3">
                            ${avatarHtml}
                            <h5 class="card-title fw-bold mb-1">${teacher.name}</h5>
                            <p class="text-muted small mb-2">${teacher.subject !== '-' ? teacher.subject : ''}</p>
                            
                            <div class="mb-2">
                                <span class="badge ${statusClass} status-badge">${statusLabel}</span>
                            </div>
                            
                            <div class="text-muted small">
                                <i class="bi bi-geo-alt-fill me-1"></i> ${locationText}
                            </div>
                            ${teacher.time !== '-' ? `<div class="text-muted small mt-1"><i class="bi bi-clock-fill me-1"></i> ${teacher.time}</div>` : ''}
                            ${scheduleHtml}
                        </div>
                    </div>
                `;
            }).join('');
        }

        let currentMode = 'all'; // 'all' or 'block'
        let currentBlock = null;

        document.getElementById('btn-mode-all').addEventListener('click', function () {
            setMode('all');
        });

        document.getElementById('btn-mode-block').addEventListener('click', function () {
            setMode('block');
        });

        document.getElementById('block-selector').addEventListener('change', function () {
            currentBlock = this.value;
            document.getElementById('block-title').textContent = `Monitoring Blok ${currentBlock}`;
            fetchBlockData();
        });

        function setMode(mode) {
            currentMode = mode;

            // Update buttons
            if (mode === 'all') {
                document.getElementById('btn-mode-all').classList.add('active');
                document.getElementById('btn-mode-block').classList.remove('active');
                document.getElementById('block-selector').classList.add('d-none');
                document.getElementById('view-toggle-group').classList.remove('d-none');

                document.getElementById('monitoring-container').classList.remove('d-none');
                document.getElementById('block-view').classList.add('d-none');

                // Restore previous view
                if (currentView === 'list') {
                    document.getElementById('list-view').classList.remove('d-none');
                    document.getElementById('grid-view').classList.add('d-none');
                } else {
                    document.getElementById('list-view').classList.add('d-none');
                    document.getElementById('grid-view').classList.remove('d-none');
                }

                fetchMonitoringData();
            } else {
                document.getElementById('btn-mode-all').classList.remove('active');
                document.getElementById('btn-mode-block').classList.add('active');
                document.getElementById('block-selector').classList.remove('d-none');
                document.getElementById('view-toggle-group').classList.add('d-none');

                document.getElementById('list-view').classList.add('d-none');
                document.getElementById('grid-view').classList.add('d-none');
                document.getElementById('block-view').classList.remove('d-none');

                if (currentBlock) {
                    fetchBlockData();
                } else {
                    document.getElementById('block-view-body').innerHTML = `
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">Silakan pilih blok terlebih dahulu</td>
                        </tr>
                    `;
                }
            }
        }

        function fetchBlockData() {
            if (!currentBlock) return;

            const badge = document.getElementById('realtime-badge');
            badge.innerHTML = '<i class="bi bi-arrow-repeat spin"></i> Updating...';
            badge.className = 'badge bg-warning ms-2';

            fetch(`/api/monitoring/block?block=${currentBlock}`)
                .then(response => response.json())
                .then(data => {
                    renderBlockView(data);
                    badge.innerHTML = '<i class="bi bi-broadcast"></i> LIVE';
                    badge.className = 'badge bg-success ms-2';
                })
                .catch(error => {
                    console.error('Error fetching block data:', error);
                    badge.innerHTML = '<i class="bi bi-exclamation-triangle"></i> Error';
                    badge.className = 'badge bg-danger ms-2';
                });
        }

        function renderBlockView(data) {
            const tbody = document.getElementById('block-view-body');

            if (data.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="4" class="text-center py-4 text-muted">Belum ada data kelas untuk blok ini.</td>
                    </tr>
                `;
                return;
            }

            tbody.innerHTML = data.map(item => {
                const statusLabel = statusLabels[item.status] || item.status.replace('_', ' ').toUpperCase();
                const statusClass = `status-${item.status}`;

                // Teacher photo
                const photoHtml = item.teacher_photo
                    ? `<img src="${item.teacher_photo}" class="rounded-circle me-2" style="width: 30px; height: 30px; object-fit: cover;">`
                    : `<div class="avatar-circle avatar-circle-sm bg-secondary text-white me-2" style="width: 30px; height: 30px; font-size: 0.8rem;"><i class="bi bi-person"></i></div>`;

                return `
                    <tr>
                        <td class="ps-4 fw-bold">${item.class_name}</td>
                        <td>${item.subject}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                ${photoHtml}
                                <span>${item.teacher_name}</span>
                            </div>
                        </td>
                        <td><span class="badge ${statusClass} status-badge">${statusLabel}</span></td>
                    </tr>
                `;
            }).join('');
        }

        function fetchMonitoringData() {
            if (currentMode === 'block') {
                fetchBlockData();
                return;
            }

            const badge = document.getElementById('realtime-badge');
            badge.innerHTML = '<i class="bi bi-arrow-repeat spin"></i> Updating...';
            badge.className = 'badge bg-warning ms-2';

            fetch('/api/monitoring')
                .then(response => response.json())

                .then(data => {
                    monitoringData = data;
                    renderListView();
                    if (currentView === 'grid') {
                        renderGridView();
                    }
                    badge.innerHTML = '<i class="bi bi-broadcast"></i> LIVE';
                    badge.className = 'badge bg-success ms-2';
                })
                .catch(error => {
                    console.error('Error:', error);
                    badge.innerHTML = '<i class="bi bi-exclamation-triangle"></i> Offline';
                    badge.className = 'badge bg-danger ms-2';
                });
        }

        // Initial load
        fetchMonitoringData();

        // Auto refresh every 3 seconds for realtime updates
        setInterval(fetchMonitoringData, 3000);
    </script>

    <style>
        @keyframes spin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        .spin {
            display: inline-block;
            animation: spin 1s linear infinite;
        }
    </style>
</body>

</html>