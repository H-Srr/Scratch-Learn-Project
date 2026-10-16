<?php
require_once __DIR__ . '/config.php';

try {
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Details go to the server's error log, never to the visitor.
    error_log('Scratch Learn: database connection failed: ' . $e->getMessage());
    http_response_code(500);
    echo "❌ The database is not reachable right now. Please try again later.";
    exit; // stop here so the calling script never runs with a missing connection
}
?>
