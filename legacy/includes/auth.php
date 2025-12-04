<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

function requireLogin()
{
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function requireRole($roles)
{
    requireLogin();
    if (!in_array($_SESSION['role'], (array) $roles)) {
        // Redirect to appropriate dashboard based on role
        if ($_SESSION['role'] == 'guru') {
            header('Location: teacher_dashboard.php');
        } elseif ($_SESSION['role'] == 'admin_kelas') {
            header('Location: class_admin_dashboard.php');
        } else {
            header('Location: admin_dashboard.php');
        }
        exit;
    }
}

function getCurrentUser()
{
    return $_SESSION['user_id'] ?? null;
}
?>