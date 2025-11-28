<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring Guru - Mogu</title>
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

        .status-belum_hadir {
            background-color: #9e9e9e;
            color: white;
        }

        .teacher-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
            height: 100%;
        }

        .teacher-avatar {
            width: 80px;
            height: 80px;
            background-color: #ddd;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin-bottom: 15px;
            color: #555;
        }

        .teacher-name {
            font-size: 1.2rem;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .teacher-location {
            font-size: 1rem;
            color: #666;
            margin-bottom: 10px;
        }

        body.dark-mode .teacher-location {
            color: #aaa;
        }
    </style>
</head>

<body class="theme-material">
    <div class="container">
        <div class="monitoring-header">
            <h1>Monitoring Keberadaan Guru</h1>
            <p id="current-time"></p>
        </div>

        <div id="monitoring-grid" class="monitoring-grid">
            <!-- Content will be loaded here -->
            <div class="text-center" style="grid-column: 1/-1;">Memuat data...</div>
        </div>

        <div class="text-center" style="margin-top: 30px;">
            <a href="login.php" class="btn btn-secondary">Login Admin/Guru</a>
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
                    const grid = document.getElementById('monitoring-grid');
                    grid.innerHTML = '';

                    data.forEach(teacher => {
                        const card = document.createElement('div');
                        card.className = 'card teacher-card';

                        // Get initials
                        const initials = teacher.name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();

                        let statusLabel = teacher.status.replace('_', ' ').toUpperCase();
                        let locationText = teacher.location === '-' ? 'Belum ada di kelas' : `Di Kelas: ${teacher.location}`;

                        card.innerHTML = `
                            <div class="teacher-avatar">${initials}</div>
                            <div class="teacher-name">${teacher.name}</div>
                            <div class="teacher-location">${locationText}</div>
                            <span class="status-badge status-${teacher.status}">${statusLabel}</span>
                            <div style="margin-top: 5px; font-size: 0.8rem;">${teacher.time !== '-' ? 'Jam: ' + teacher.time : ''}</div>
                        `;
                        grid.appendChild(card);
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