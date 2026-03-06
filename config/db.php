<?php
// ============================================================
// config/db.php
// ============================================================
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'sales_dashboard');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode(['success' => false, 'message' => 'DB Error: ' . $conn->connect_error]));
}

$conn->set_charset('utf8mb4');
?>
