<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring Guru - Tewak</title>
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
    <style>
        .monitoring-header {
            background: linear-gradient(135deg, #4361ee, #3f37c9);
            color: white;
            padding: 2rem;
            border-radius: 1rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 20px rgba(67, 97, 238, 0.2);
        }

        .teacher-card {
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .teacher-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .avatar-circle {
            width: 80px;
            height: 80px;
            background-color: #e9ecef;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: #6c757d;
            margin: 0 auto 1rem;
        }

        .status-badge {
            font-size: 0.8rem;
            padding: 0.4em 0.8em;
            border-radius: 20px;
        }

        .status-hadir {
            background-color: #10b981;
            color: white;
        }

        .status-telat {
            background-color: #f59e0b;
            color: white;
        }

        .status-izin {
            background-color: #06b6d4;
            color: white;
        }

        .status-sakit {
            background-color: #8b5cf6;
            color: white;
        }

        .status-alpa {
            background-color: #ef4444;
            color: white;
        }

        .status-belum_hadir {
            background-color: #6b7280;
            color: white;
        }

        .status-belum_hadir_telat {
            background-color: #dc2626;
            color: white;
            animation: pulse 1s infinite;
        }

        .status-tidak_hadir {
            background-color: #991b1b;
            color: white;
        }

        .status-tidak_ada_jadwal {
            background-color: #d1d5db;
            color: #374151;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.7;
            }
        }

        .schedule-info {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 0.5rem;
            margin-top: 0.5rem;
            font-size: 0.75rem;
        }
    </style>
</head>

<body>
    <div class="container py-4">
        <div class="monitoring-header text-center position-relative">
            <h1 class="fw-bold mb-2">Monitoring Keberadaan Guru</h1>
            <p class="mb-0" id="current-time">Loading time...</p>
            <div class="position-absolute top-50 end-0 translate-middle-y me-4 d-none d-md-block">
                <a href="{{ route('login') }}" class="btn btn-light fw-bold text-primary">Login</a>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex gap-2 flex-wrap">
                <span class="badge status-hadir">Hadir</span>
                <span class="badge status-telat">Telat</span>
                <span class="badge status-belum_hadir">Belum Hadir</span>
                <span class="badge status-tidak_hadir">Tidak Hadir</span>
                <span class="badge status-izin">Izin</span>
                <span class="badge status-sakit">Sakit</span>
            </div>
            <a href="{{ route('login') }}" class="btn btn-primary btn-sm d-md-none">Login</a>
        </div>

        <div id="monitoring-grid" class="row g-4">
            <!-- Data will be loaded here -->
            <div class="col-12 text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>
    </div>

    <script type="module">
        const statusLabels = {
            'hadir': 'HADIR',
            'telat': 'TELAT',
            'izin': 'IZIN',
            'sakit': 'SAKIT',
            'alpa': 'ALPA',
            'belum_hadir': 'BELUM HADIR',
            'belum_hadir_telat': 'BELUM HADIR (TELAT)',
            'tidak_hadir': 'TIDAK HADIR',
            'tidak_ada_jadwal': 'TIDAK ADA JADWAL'
        };

        function updateTime() {
            const now = new Date();
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' };
            document.getElementById('current-time').innerText = now.toLocaleDateString('id-ID', options);
        }
        setInterval(updateTime, 1000);
        updateTime();

        function fetchMonitoringData() {
            fetch('/api/monitoring')
                .then(response => response.json())
                .then(data => {
                    const grid = document.getElementById('monitoring-grid');
                    grid.innerHTML = '';

                    if (data.length === 0) {
                        grid.innerHTML = '<div class="col-12 text-center text-muted">Belum ada data guru.</div>';
                        return;
                    }

                    data.forEach(teacher => {
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

                        const cardHtml = `
                            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                                <div class="card teacher-card h-100 text-center p-3">
                                    <div class="avatar-circle">
                                        ${initials}
                                    </div>
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
                        grid.innerHTML += cardHtml;
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }

        fetchMonitoringData();
        setInterval(fetchMonitoringData, 5000);
    </script>
</body>

</html>