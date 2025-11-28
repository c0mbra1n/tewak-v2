<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring Guru - Tewak Apps</title>
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
            position: relative;
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

            <div class="header-controls-centered">
                <div class="view-toggle">
                    <button class="view-btn active" onclick="toggleView('grid')" title="Grid View">⊞</button>
                    <button class="view-btn" onclick="toggleView('list')" title="List View">☰</button>
                </div>
                <a href="login.php" class="btn-login">Login Admin/Guru</a>
            </div>
        </div>

        <div id="monitoring-container" class="view-mode-grid">
            <!-- Grid View -->
            <div id="monitoring-grid" class="monitoring-grid">
                <div class="text-center" style="grid-column: 1/-1;">Memuat data...</div>
            </div>

            <!-- List View -->
            <div id="monitoring-list" class="monitoring-list">
                <table>
                    <thead>
                        <tr>
                            <th>Foto</th>
                            <th>Nama Guru</th>
                            <th>Mata Pelajaran</th>
                            <th>Lokasi Saat Ini</th>
                            <th>Status</th>
                            <th>Waktu Scan</th>
                        </tr>
                    </thead>
                    <tbody id="monitoring-table-body">
                        <!-- Data will be loaded here -->
                    </tbody>
                </table>
            </div>
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

        // Load view preference
        const savedView = localStorage.getItem('viewMode') || 'grid';
        toggleView(savedView);

        function fetchMonitoringData() {
            fetch('api/monitoring.php')
                .then(response => response.json())
                .then(data => {
                    // Update Grid
                    const grid = document.getElementById('monitoring-grid');
                    grid.innerHTML = '';

                    // Update List
                    const tbody = document.getElementById('monitoring-table-body');
                    tbody.innerHTML = '';

                    data.forEach(teacher => {
                        // Common Data
                        const initials = teacher.name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
                        let statusLabel = teacher.status.replace('_', ' ').toUpperCase();
                        let locationText = teacher.location === '-' ? 'Belum ada di kelas' : teacher.location;
                        let statusClass = `status-${teacher.status}`;

                        let photoUrl = teacher.photo ? `assets/uploads/teachers/${teacher.photo}` : null;
                        let avatarHtml = photoUrl
                            ? `<img src="${photoUrl}" alt="${teacher.name}" style="width: 100%; height: 100%; object-fit: cover;">`
                            : initials;

                        let listPhotoHtml = photoUrl
                            ? `<img src="${photoUrl}" alt="${teacher.name}">`
                            : `<div style="width: 40px; height: 40px; border-radius: 50%; background: #ccc; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: bold;">${initials}</div>`;


                        // Grid Card
                        const card = document.createElement('div');
                        card.className = 'card teacher-card';
                        card.innerHTML = `
                            <div class="teacher-avatar" style="overflow: hidden;">${avatarHtml}</div>
                            <div class="teacher-name">${teacher.name}</div>
                            <div class="teacher-location">${locationText === 'Belum ada di kelas' ? locationText : 'Di Kelas: ' + locationText}</div>
                            <span class="status-badge ${statusClass}">${statusLabel}</span>
                            <div style="margin-top: 5px; font-size: 0.8rem;">${teacher.time !== '-' ? 'Jam: ' + teacher.time : ''}</div>
                        `;
                        grid.appendChild(card);

                        // List Row
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td>${listPhotoHtml}</td>
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