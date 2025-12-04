<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

requireRole(['super_admin']);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Statistik - Tewak Apps</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/themes.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            border: 1px solid #ddd;
        }

        .stat-value {
            font-size: 2.5rem;
            font-weight: bold;
            color: var(--primary-color);
            margin: 10px 0;
        }

        .stat-label {
            color: #666;
            font-size: 1rem;
        }

        .chart-container {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #ddd;
            height: 400px;
            max-width: 50%; /* Resize to half screen */
            margin: 0 auto; /* Center it */
        }
    </style>
</head>

<body class="theme-flat">
    <nav class="navbar">
        <a href="#" class="navbar-brand">Tewak Apps Admin</a>
        <ul class="navbar-nav">
            <li class="nav-item"><a href="admin_dashboard.php" class="nav-link active">Dashboard</a></li>
            <li class="nav-item"><a href="users.php" class="nav-link">Users</a></li>
            <li class="nav-item"><a href="classes.php" class="nav-link">Kelas</a></li>
            <li class="nav-item"><a href="schedules.php" class="nav-link">Jadwal</a></li>
            <li class="nav-item"><a href="reports.php" class="nav-link">Laporan</a></li>
            <li class="nav-item"><a href="permissions.php" class="nav-link">Izin/Sakit</a></li>
            <li class="nav-item"><a href="settings.php" class="nav-link">Settings</a></li>
            <li class="nav-item"><a href="../monitor/monitor.php" class="nav-link" target="_blank">Monitoring</a></li>
            <li class="nav-item"><a href="../logout.php" class="nav-link">Logout</a></li>
        </ul>
    </nav>

    <div class="container">
        <h2>Dashboard Statistik</h2>

        <!-- Summary Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Total Guru</div>
                <div class="stat-value" id="totalTeachers">-</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Hadir Hari Ini</div>
                <div class="stat-value" id="presentToday" style="color: #28a745;">-</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Izin / Sakit / Dinas</div>
                <div class="stat-value" id="permissionToday" style="color: #ffc107;">-</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Tidak Hadir</div>
                <div class="stat-value" id="absentToday" style="color: #dc3545;">-</div>
            </div>
        </div>

        <!-- Charts -->
        <div class="chart-container">
            <canvas id="attendanceChart"></canvas>
        </div>
    </div>

    <script>
        let attendanceChart = null;

        function fetchStats() {
            fetch('../api/dashboard_stats.php')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Update Cards
                        document.getElementById('totalTeachers').innerText = data.total_teachers;
                        document.getElementById('presentToday').innerText = data.present_today;
                        document.getElementById('absentToday').innerText = data.absent_today;
                        document.getElementById('permissionToday').innerText = data.permission_today;

                        // Update Chart
                        updateChart(data.chart_labels, data.chart_data_present, data.chart_data_absent);
                    }
                })
                .catch(error => console.error('Error fetching stats:', error));
        }

        function updateChart(labels, dataPresent, dataAbsent) {
            const ctx = document.getElementById('attendanceChart').getContext('2d');
            
            if (attendanceChart) {
                attendanceChart.data.labels = labels;
                attendanceChart.data.datasets[0].data = dataPresent;
                attendanceChart.data.datasets[1].data = dataAbsent;
                attendanceChart.update();
            } else {
                attendanceChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'Hadir',
                                data: dataPresent,
                                backgroundColor: '#28a745',
                                borderColor: '#28a745',
                                borderWidth: 1
                            },
                            {
                                label: 'Tidak Hadir',
                                data: dataAbsent,
                                backgroundColor: '#dc3545',
                                borderColor: '#dc3545',
                                borderWidth: 1
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            },
                            x: {
                                // Default is side-by-side
                            }
                        },
                        animation: {
                            duration: 0 // Disable animation for smoother updates
                        }
                    }
                });
            }
        }

        // Initial Load
        fetchStats();

        // Real-time Update (every 5 seconds)
        setInterval(fetchStats, 5000);
    </script>
</body>

</html>