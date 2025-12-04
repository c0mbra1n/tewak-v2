<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

requireRole(['super_admin']);

$message = '';

// Handle Class Actions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'add_class') {
        $class_name = $_POST['class_name'];
        $latitude = !empty($_POST['latitude']) ? $_POST['latitude'] : null;
        $longitude = !empty($_POST['longitude']) ? $_POST['longitude'] : null;
        $radius = !empty($_POST['radius']) ? $_POST['radius'] : 50;

        $qr_code = 'CLASS_' . strtoupper(preg_replace('/[^a-zA-Z0-9]/', '_', $class_name));

        try {
            $stmt = $pdo->prepare("INSERT INTO classes (class_name, qr_code, latitude, longitude, radius) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$class_name, $qr_code, $latitude, $longitude, $radius]);
            $message = "Kelas berhasil ditambahkan!";
        } catch (PDOException $e) {
            $message = "Error: " . $e->getMessage();
        }
    } elseif ($_POST['action'] == 'update_class') {
        $id = $_POST['id'];
        $class_name = $_POST['class_name'];
        $latitude = !empty($_POST['latitude']) ? $_POST['latitude'] : null;
        $longitude = !empty($_POST['longitude']) ? $_POST['longitude'] : null;
        $radius = !empty($_POST['radius']) ? $_POST['radius'] : 50;

        try {
            $stmt = $pdo->prepare("UPDATE classes SET class_name = ?, latitude = ?, longitude = ?, radius = ? WHERE id = ?");
            $stmt->execute([$class_name, $latitude, $longitude, $radius, $id]);
            $message = "Kelas berhasil diperbarui!";
        } catch (PDOException $e) {
            $message = "Error: " . $e->getMessage();
        }
    } elseif ($_POST['action'] == 'delete_class') {
        $id = $_POST['id'];
        try {
            $stmt = $pdo->prepare("DELETE FROM classes WHERE id = ?");
            $stmt->execute([$id]);
            $message = "Kelas berhasil dihapus!";
        } catch (PDOException $e) {
            $message = "Error: " . $e->getMessage();
        }
    }
}

// Fetch Classes
$stmt = $pdo->query("SELECT * FROM classes ORDER BY class_name ASC");
$classes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Kelas - Tewak Apps</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/themes.css">
    <style>
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 10% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 90%;
            max-width: 500px;
            border-radius: 8px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function getLocation(targetLatId, targetLngId) {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition((position) => {
                    document.getElementById(targetLatId).value = position.coords.latitude;
                    document.getElementById(targetLngId).value = position.coords.longitude;
                }, showError);
            } else {
                Swal.fire('Error', 'Geolocation is not supported by this browser.', 'error');
            }
        }

        function showError(error) {
            let msg = "";
            switch (error.code) {
                case error.PERMISSION_DENIED:
                    msg = "User denied the request for Geolocation.";
                    break;
                case error.POSITION_UNAVAILABLE:
                    msg = "Location information is unavailable.";
                    break;
                case error.TIMEOUT:
                    msg = "The request to get user location timed out.";
                    break;
                case error.UNKNOWN_ERROR:
                    msg = "An unknown error occurred.";
                    break;
            }
            Swal.fire('Geolocation Error', msg, 'error');
        }

        function editClass(id, name, lat, lng, radius) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_class_name').value = name;
            document.getElementById('edit_latitude').value = lat;
            document.getElementById('edit_longitude').value = lng;
            document.getElementById('edit_radius').value = radius;
            document.getElementById('editModal').style.display = "block";
        }

        function deleteClass(id, name) {
            Swal.fire({
                title: 'Hapus Kelas?',
                text: "Anda yakin ingin menghapus kelas '" + name + "'? Data yang dihapus tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.innerHTML = `<input type="hidden" name="action" value="delete_class"><input type="hidden" name="id" value="${id}">`;
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        function closeModal() {
            document.getElementById('editModal').style.display = "none";
        }

        window.onclick = function (event) {
            if (event.target == document.getElementById('editModal')) {
                closeModal();
            }
        }
    </script>
</head>

<body class="theme-flat">
    <nav class="navbar">
        <a href="#" class="navbar-brand">Tewak Apps Admin</a>
        <ul class="navbar-nav">
            <li class="nav-item"><a href="users.php" class="nav-link">Users</a></li>
            <li class="nav-item"><a href="classes.php" class="nav-link">Kelas</a></li>
            <li class="nav-item"><a href="../monitor/monitor.php" class="nav-link" target="_blank">Monitoring</a></li>
            <li class="nav-item"><a href="../logout.php" class="nav-link">Logout</a></li>
        </ul>
    </nav>

    <div class="container dashboard-container">
        <div class="card dashboard-left">
            <h3>Tambah Kelas Baru</h3>
            <?php if ($message): ?>
                <div class="alert alert-info"><?= $message ?></div>
            <?php endif; ?>
            <form method="POST">
                <input type="hidden" name="action" value="add_class">
                <div class="form-group">
                    <label>Nama Kelas</label>
                    <input type="text" name="class_name" class="form-control" required placeholder="Contoh: X IPA 1">
                </div>

                <div class="form-group">
                    <label>Lokasi Kelas (Opsional)</label>
                    <div style="display: flex; gap: 10px; margin-bottom: 10px;">
                        <input type="text" name="latitude" id="add_latitude" class="form-control"
                            placeholder="Latitude">
                        <input type="text" name="longitude" id="add_longitude" class="form-control"
                            placeholder="Longitude">
                        <button type="button" class="btn btn-secondary"
                            onclick="getLocation('add_latitude', 'add_longitude')">Ambil Lokasi Saat
                            Ini</button>
                    </div>
                    <label>Radius Toleransi (meter)</label>
                    <input type="number" name="radius" class="form-control" value="50" placeholder="50">
                    <small>Guru harus berada dalam radius ini untuk bisa scan.</small>
                </div>

                <button type="submit" class="btn btn-primary">Tambah Kelas</button>
            </form>
        </div>

        <div class="card dashboard-right">
            <h3>Daftar Kelas</h3>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama Kelas</th>
                            <th>Kode QR</th>
                            <th>Lokasi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($classes as $class): ?>
                            <tr>
                                <td><?= $class['id'] ?></td>
                                <td><?= htmlspecialchars($class['class_name']) ?></td>
                                <td><?= $class['qr_code'] ?></td>
                                <td>
                                    <?php if ($class['latitude']): ?>
                                        <?= $class['latitude'] ?>, <?= $class['longitude'] ?>
                                        <br><small>Radius: <?= $class['radius'] ?>m</small>
                                    <?php else: ?>
                                        <span style="color: #999;">Tidak diset</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="../monitor/view_qr.php?id=<?= $class['id'] ?>" target="_blank"
                                        class="btn btn-sm btn-primary">QR</a>
                                    <button class="btn btn-sm btn-secondary"
                                        onclick="editClass(<?= $class['id'] ?>, '<?= addslashes($class['class_name']) ?>', '<?= $class['latitude'] ?>', '<?= $class['longitude'] ?>', <?= $class['radius'] ?>)">Edit</button>
                                    <button class="btn btn-sm btn-danger"
                                        onclick="deleteClass(<?= $class['id'] ?>, '<?= addslashes($class['class_name']) ?>')">Hapus</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h3>Edit Kelas</h3>
            <form method="POST">
                <input type="hidden" name="action" value="update_class">
                <input type="hidden" name="id" id="edit_id">

                <div class="form-group">
                    <label>Nama Kelas</label>
                    <input type="text" name="class_name" id="edit_class_name" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Lokasi Kelas</label>
                    <div style="display: flex; gap: 10px; margin-bottom: 10px;">
                        <input type="text" name="latitude" id="edit_latitude" class="form-control"
                            placeholder="Latitude">
                        <input type="text" name="longitude" id="edit_longitude" class="form-control"
                            placeholder="Longitude">
                        <button type="button" class="btn btn-secondary"
                            onclick="getLocation('edit_latitude', 'edit_longitude')">Ambil</button>
                    </div>
                    <label>Radius (meter)</label>
                    <input type="number" name="radius" id="edit_radius" class="form-control" placeholder="50">
                </div>

                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </form>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
</body>

</html>