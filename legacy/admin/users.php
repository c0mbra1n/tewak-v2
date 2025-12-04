<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

requireRole(['super_admin']);

$message = '';

// Handle User Addition
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_user') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $full_name = $_POST['full_name'];
    $subject = $_POST['subject'] ?? null;
    $role = $_POST['role'];
    $class_id = !empty($_POST['class_id']) ? $_POST['class_id'] : null;

    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, password, full_name, subject, role, class_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$username, $password, $full_name, $subject, $role, $class_id]);
        $_SESSION['swal_success'] = "User berhasil ditambahkan!";
    } catch (PDOException $e) {
        $_SESSION['swal_error'] = "Error: " . $e->getMessage();
    }
    header("Location: users.php");
    exit;
}

// Handle User Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'edit_user') {
    $id = $_POST['user_id'];
    $username = $_POST['username'];
    $full_name = $_POST['full_name'];
    $role = $_POST['role'];
    $class_id = !empty($_POST['class_id']) ? $_POST['class_id'] : null;
    $subject = $_POST['subject'] ?? null;

    $sql = "UPDATE users SET username=?, full_name=?, role=?, class_id=?, subject=?";
    $params = [$username, $full_name, $role, $class_id, $subject];

    if (!empty($_POST['password'])) {
        $sql .= ", password=?";
        $params[] = password_hash($_POST['password'], PASSWORD_DEFAULT);
    }

    $sql .= " WHERE id=?";
    $params[] = $id;

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $_SESSION['swal_success'] = "User berhasil diupdate!";
    } catch (PDOException $e) {
        $_SESSION['swal_error'] = "Error: " . $e->getMessage();
    }
    header("Location: users.php");
    exit;
}

// Handle User Delete
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete_user') {
    $id = $_POST['user_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['swal_success'] = "User berhasil dihapus!";
    } catch (PDOException $e) {
        $_SESSION['swal_error'] = "Error: " . $e->getMessage();
    }
    header("Location: users.php");
    exit;
}

// Fetch Users with Filter
$where = "";
$params = [];
$role_filter = $_GET['role_filter'] ?? '';

if ($role_filter) {
    $where = "WHERE u.role = ?";
    $params[] = $role_filter;
}

$stmt = $pdo->prepare("SELECT u.*, c.class_name as assigned_class FROM users u LEFT JOIN classes c ON u.class_id = c.id $where ORDER BY u.created_at DESC");
$stmt->execute($params);
$users = $stmt->fetchAll();

// Fetch Classes for Dropdown
$stmt = $pdo->query("SELECT * FROM classes ORDER BY class_name ASC");
$classes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen User - Tewak Apps</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/themes.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="theme-flat">
    <nav class="navbar">
        <a href="#" class="navbar-brand">Tewak Apps Admin</a>
        <ul class="navbar-nav">
            <li class="nav-item"><a href="admin_dashboard.php" class="nav-link">Dashboard</a></li>
            <li class="nav-item"><a href="users.php" class="nav-link active">Users</a></li>
            <li class="nav-item"><a href="classes.php" class="nav-link">Kelas</a></li>
            <li class="nav-item"><a href="schedules.php" class="nav-link">Jadwal</a></li>
            <li class="nav-item"><a href="reports.php" class="nav-link">Laporan</a></li>
            <li class="nav-item"><a href="permissions.php" class="nav-link">Izin/Sakit</a></li>
            <li class="nav-item"><a href="settings.php" class="nav-link">Settings</a></li>
            <li class="nav-item"><a href="../monitor/monitor.php" class="nav-link" target="_blank">Monitoring</a></li>

            <li class="nav-item"><a href="../logout.php" class="nav-link">Logout</a></li>
        </ul>
    </nav>

    <div class="container dashboard-container">
        <div class="card dashboard-left">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h3>Tambah User Baru</h3>
                <a href="import_teachers.php" class="btn btn-secondary"
                    style="padding: 5px 10px; font-size: 0.9rem;">Import Guru</a>
            </div>
            <?php if ($message): ?>
                <div class="alert alert-info"><?= $message ?></div>
            <?php endif; ?>
            <form method="POST">
                <input type="hidden" name="action" value="add_user">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="full_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Mata Pelajaran (Khusus Guru)</label>
                    <input type="text" name="subject" class="form-control" placeholder="Contoh: Matematika">
                </div>
                <div class="form-group">
                    <label>Role</label>
                    <select name="role" class="form-control" id="roleSelect" onchange="toggleInputs()">
                        <option value="guru">Guru</option>
                        <option value="admin_kelas">Admin Kelas</option>
                        <option value="super_admin">Super Admin</option>
                    </select>
                </div>
                <div class="form-group" id="classInput" style="display:none;">
                    <label>Kelas yang Diampu (Khusus Admin Kelas)</label>
                    <select name="class_id" class="form-control">
                        <option value="">-- Pilih Kelas --</option>
                        <?php foreach ($classes as $class): ?>
                            <option value="<?= $class['id'] ?>"><?= htmlspecialchars($class['class_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Tambah User</button>
            </form>
        </div>

        <script>
            function toggleInputs() {
                const role = document.getElementById('roleSelect').value;
                const classInput = document.getElementById('classInput');
                if (role === 'admin_kelas') {
                    classInput.style.display = 'block';
                } else {
                    classInput.style.display = 'none';
                }
            }
        </script>

        <div class="card dashboard-right">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3>Daftar User</h3>
                <form method="GET" style="display: flex; gap: 10px; align-items: center;">
                    <select name="role_filter" class="form-control" onchange="this.form.submit()" style="width: auto;">
                        <option value="">Semua Role</option>
                        <option value="guru" <?= $role_filter == 'guru' ? 'selected' : '' ?>>Guru</option>
                        <option value="admin_kelas" <?= $role_filter == 'admin_kelas' ? 'selected' : '' ?>>Admin Kelas
                        </option>
                        <option value="super_admin" <?= $role_filter == 'super_admin' ? 'selected' : '' ?>>Super Admin
                        </option>
                    </select>
                </form>
            </div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Nama Lengkap</th>
                            <th>Mapel</th>
                            <th>Role</th>
                            <th>Kelas (Admin)</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= $user['id'] ?></td>
                                <td><?= htmlspecialchars($user['username']) ?></td>
                                <td><?= htmlspecialchars($user['full_name']) ?></td>
                                <td><?= htmlspecialchars($user['subject'] ?? '-') ?></td>
                                <td>
                                    <span class="status-badge" style="background: #eee; color: #333;">
                                        <?= $user['role'] ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($user['assigned_class'] ?? '-') ?></td>
                                <td>
                                    <button class="btn btn-primary" style="padding: 5px 10px; font-size: 0.8rem;"
                                        data-id="<?= $user['id'] ?>"
                                        data-username="<?= htmlspecialchars($user['username']) ?>"
                                        data-fullname="<?= htmlspecialchars($user['full_name']) ?>"
                                        data-subject="<?= htmlspecialchars($user['subject'] ?? '') ?>"
                                        data-role="<?= $user['role'] ?>" data-classid="<?= $user['class_id'] ?? '' ?>"
                                        onclick="openEditModal(this)">Edit</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal"
        style="display:none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.4);">
        <div class="modal-content"
            style="background-color: var(--surface-color); margin: 10% auto; padding: 20px; border: 1px solid #888; width: 90%; max-width: 500px; border-radius: var(--border-radius);">
            <span class="close" onclick="closeEditModal()"
                style="color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer;">&times;</span>
            <h3>Edit User</h3>
            <form method="POST">
                <input type="hidden" name="action" value="edit_user">
                <input type="hidden" name="user_id" id="edit_user_id">

                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" id="edit_username" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Password (Kosongkan jika tidak ingin mengubah)</label>
                    <input type="password" name="password" class="form-control" placeholder="Biarkan kosong jika tetap">
                </div>
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="full_name" id="edit_full_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Mata Pelajaran (Khusus Guru)</label>
                    <input type="text" name="subject" id="edit_subject" class="form-control">
                </div>
                <div class="form-group">
                    <label>Role</label>
                    <select name="role" class="form-control" id="edit_role" onchange="toggleEditInputs()">
                        <option value="guru">Guru</option>
                        <option value="admin_kelas">Admin Kelas</option>
                        <option value="super_admin">Super Admin</option>
                    </select>
                </div>
                <div class="form-group" id="edit_classInput" style="display:none;">
                    <label>Kelas yang Diampu (Khusus Admin Kelas)</label>
                    <select name="class_id" id="edit_class_id" class="form-control">
                        <option value="">-- Pilih Kelas --</option>
                        <?php foreach ($classes as $class): ?>
                            <option value="<?= $class['id'] ?>"><?= htmlspecialchars($class['class_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Simpan Perubahan</button>
                <button type="button" class="btn btn-danger btn-block"
                    style="margin-top: 10px; background-color: #dc3545;" onclick="confirmDelete()">Hapus User</button>
            </form>

            <form id="deleteForm" method="POST" style="display:none;">
                <input type="hidden" name="action" value="delete_user">
                <input type="hidden" name="user_id" id="delete_user_id">
            </form>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
    <script>
        // SweetAlert2 Messages
        <?php if (isset($_SESSION['swal_success'])): ?>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: '<?= $_SESSION['swal_success'] ?>',
                timer: 2000,
                showConfirmButton: false
            });
            <?php unset($_SESSION['swal_success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['swal_error'])): ?>
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: '<?= $_SESSION['swal_error'] ?>'
            });
            <?php unset($_SESSION['swal_error']); ?>
        <?php endif; ?>

        function toggleInputs() {
            const role = document.getElementById('roleSelect').value;
            const classInput = document.getElementById('classInput');
            if (role === 'admin_kelas') {
                classInput.style.display = 'block';
            } else {
                classInput.style.display = 'none';
            }
        }

        function toggleEditInputs() {
            const role = document.getElementById('edit_role').value;
            const classInput = document.getElementById('edit_classInput');
            if (role === 'admin_kelas') {
                classInput.style.display = 'block';
            } else {
                classInput.style.display = 'none';
            }
        }

        function openEditModal(button) {
            document.getElementById('editModal').style.display = 'block';
            document.getElementById('edit_user_id').value = button.getAttribute('data-id');
            document.getElementById('delete_user_id').value = button.getAttribute('data-id'); // Set for delete form too
            document.getElementById('edit_username').value = button.getAttribute('data-username');
            document.getElementById('edit_full_name').value = button.getAttribute('data-fullname');
            document.getElementById('edit_subject').value = button.getAttribute('data-subject');
            document.getElementById('edit_role').value = button.getAttribute('data-role');
            document.getElementById('edit_class_id').value = button.getAttribute('data-classid');
            toggleEditInputs();
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        function confirmDelete() {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "User yang dihapus tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('deleteForm').submit();
                }
            })
        }

        window.onclick = function (event) {
            if (event.target == document.getElementById('editModal')) {
                closeEditModal();
            }
        }
    </script>

</html>