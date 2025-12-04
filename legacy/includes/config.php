<?php
date_default_timezone_set('Asia/Jakarta');
$host = 'localhost';
$dbname = 'mogu';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Helper function for JSON response
function jsonResponse($status, $message, $data = null)
{
    header('Content-Type: application/json');
    echo json_encode([
        'status' => $status,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

// Load Settings
$SCHOOL_NAME = 'Tewak Apps';
$SCHOOL_LOGO = null;
$DEFAULT_START_TIME = '07:15:00';

try {
    $stmt = $pdo->query("SELECT * FROM settings WHERE id = 1");
    $settings = $stmt->fetch();
    if ($settings) {
        $SCHOOL_NAME = $settings['school_name'];
        $SCHOOL_LOGO = $settings['school_logo'];
        $DEFAULT_START_TIME = $settings['default_start_time'];
    }
} catch (PDOException $e) {
    // Fallback if table doesn't exist yet
}
?>