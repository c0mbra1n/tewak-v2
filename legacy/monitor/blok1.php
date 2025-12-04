<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring Jadwal Kelas - Tewak Apps</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/themes.css">
    <style>
        body {
            background-color: #f4f6f9;
            padding: 20px;
        }

        .monitor-container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .monitor-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #eee;
        }

        .monitor-header h1 {
            color: var(--primary-color);
            margin: 0;
        }

        .monitor-header p {
            color: #666;
            margin-top: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background-color: var(--primary-color);
            color: white;
            font-weight: 500;
        }

        tr:hover {
            background-color: #f9f9f9;
        }

        .status-present {
            color: #28a745;
            font-weight: bold;
            background-color: #d4edda;
            padding: 5px 10px;
            border-radius: 5px;
            display: inline-block;
        }

        .status-absent {
            color: #dc3545;
            font-weight: bold;
            background-color: #f8d7da;
            padding: 5px 10px;
            border-radius: 5px;
            display: inline-block;
        }

        .status-permission {
            color: #856404;
            font-weight: bold;
            background-color: #fff3cd;
            padding: 5px 10px;
            border-radius: 5px;
            display: inline-block;
        }

        .time-badge {
            background: #e9ecef;
            padding: 4px 8px;
            border-radius: 4px;
            font-family: monospace;
            font-size: 0.9em;
        }

        .subject-badge {
            background: #e2e6ea;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: 500;
        }
    </style>
</head>

<body class="theme-flat">

    <div class="monitor-container">
        <div class="monitor-header">
            <h1>Monitoring Jadwal Kelas Aktif</h1>
            <p id="current-time">Memuat waktu...</p>
        </div>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Kelas</th>
                        <th>Jadwal Pelajaran</th>
                        <th>Guru Pengajar</th>
                        <th>Status Kehadiran</th>
                    </tr>
                </thead>
                <tbody id="schedule-body">
                    <tr>
                        <td colspan="4" style="text-align: center;">Memuat data...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function updateTime() {
            const now = new Date();
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' };
            document.getElementById('current-time').innerText = now.toLocaleDateString('id-ID', options);
        }

        function fetchData() {
            fetch('../api/blok1_data.php')
                .then(response => response.json())
                .then(result => {
                    if (result.status === 'success') {
                        const tbody = document.getElementById('schedule-body');
                        tbody.innerHTML = ''; // Clear existing data

                        if (result.data.length === 0) {
                            tbody.innerHTML = `
                                <tr>
                                    <td colspan="4" style="text-align: center; padding: 30px;">
                                        <div style="font-size: 1.2rem; color: #666;">Tidak ada jadwal pelajaran yang sedang berlangsung saat ini.</div>
                                    </td>
                                </tr>
                            `;
                            return;
                        }

                        result.data.forEach(row => {
                            const startTime = row.start_time.substring(0, 5);
                            const endTime = row.end_time.substring(0, 5);
                            // scan_time is "YYYY-MM-DD HH:MM:SS", we want "HH:MM" (index 11 to 16)
                            const scanTime = row.scan_time ? row.scan_time.substring(11, 16) : null;

                            let statusHtml = '';
                            if (row.permission_status) {
                                statusHtml = `
                                    <span class="status-permission">
                                        ⚠️ Guru ${row.permission_status.charAt(0).toUpperCase() + row.permission_status.slice(1)}
                                    </span>
                                `;
                            } else if (scanTime) {
                                statusHtml = `
                                    <span class="status-present">
                                        ✅ Guru Masuk / Ada di Kelas
                                    </span>
                                    <br>
                                    <small style="color: #28a745;">Tiba: ${scanTime}</small>
                                `;
                            } else {
                                statusHtml = `
                                    <span class="status-absent">
                                        ❌ Guru Tidak Hadir
                                    </span>
                                `;
                            }

                            const tr = document.createElement('tr');
                            tr.innerHTML = `
                                <td><strong>${row.class_name}</strong></td>
                                <td>
                                    <span class="subject-badge">${row.subject}</span>
                                    <br>
                                    <small style="color: #666;">
                                        ${startTime} - ${endTime}
                                    </small>
                                </td>
                                <td>
                                    ${row.teacher_name}
                                </td>
                                <td>
                                    ${statusHtml}
                                </td>
                            `;
                            tbody.appendChild(tr);
                        });
                    }
                })
                .catch(error => console.error('Error fetching data:', error));
        }

        // Initial load
        updateTime();
        fetchData();

        // Intervals
        setInterval(updateTime, 1000);
        setInterval(fetchData, 3000); // Refresh data every 3 seconds
    </script>
</body>

</html>