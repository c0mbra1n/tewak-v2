<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring Detail - Mogu</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/themes.css">
    <style>
        .monitoring-header {
            text-align: center;
            margin-bottom: 40px;
            padding: 20px;
            background: var(--primary-color);
            color: var(--on-primary);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
        }

        .status-badge {
            font-size: 0.9rem;
            padding: 6px 12px;
        }

        .status-belum_hadir {
            background-color: #9e9e9e;
            color: white;
        }
    </style>
</head>

<body class="theme-material">
    <div class="container">
        <div class="monitoring-header">
            <h1>Jadwal & Keberadaan Guru</h1>
            <p id="current-time"></p>
        </div>

        <div class="card">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Nama Guru</th>
                            <th>Mata Pelajaran</th>
                            <th>Lokasi Saat Ini</th>
                            <th>Status</th>
                            <th>Waktu Scan</th>
                        </tr>
                    </thead>
                    <tbody id="monitoring-table-body">
                        <tr>
                            <td colspan="5" class="text-center">Memuat data...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="text-center" style="margin-top: 30px;">
            <a href="index.php" class="btn btn-secondary">Kembali ke Grid View</a>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
    <script>
        function updateTime() {
            const now = new Date();
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' };
            document.getElementById('current-time').innerText = now.toLocaleDateString('id-ID', options);
        }
        setInterval(updateTime, 1000);
        updateTime();

        function fetchMonitoringData() {
            fetch('api/monitoring.php')
                .then(response => response.json())
                .then(data => {
                    const tbody = document.getElementById('monitoring-table-body');
                    tbody.innerHTML = '';

                    data.forEach(teacher => {
                        const tr = document.createElement('tr');

                        let statusLabel = teacher.status.replace('_', ' ').toUpperCase();
                        let locationText = teacher.location === '-' ? 'Belum ada di kelas' : teacher.location;
                        let statusClass = `status-${teacher.status}`;

                        tr.innerHTML = `
                            <td><strong>${teacher.name}</strong></td>
                            <td>${teacher.subject}</td>
                            <td>${locationText}</td>
                            <td><span class="status-badge ${statusClass}">${statusLabel}</span></td>
                            <td>${teacher.time}</td>
                        `;
                        tbody.appendChild(tr);
                    });
                })
                .catch(error => console.error('Error fetching data:', error));
        }

        // Fetch immediately then every 5 seconds
        fetchMonitoringData();
        setInterval(fetchMonitoringData, 5000);
    </script>
</body>

</html>