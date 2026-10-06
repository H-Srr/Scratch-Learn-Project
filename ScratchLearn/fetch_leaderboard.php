<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *"); 

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config.php';

$response = ['success' => false, 'data' => []];

try {
    // Create connection
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }

    // Query to get users (excluding passwords)
    $sql = "SELECT id, username, email FROM users ORDER BY username ASC";
    $result = $conn->query($sql);

    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }

    // Fetch data
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }

    $response = [
        'success' => true,
        'data' => $users
    ];

} catch (Exception $e) {
    $response['error'] = $e->getMessage();
} finally {
    // Close connection
    if (isset($conn)) {
        $conn->close();
    }
}

// Ensure we output valid JSON
echo json_encode($response, JSON_PRETTY_PRINT);
exit;
?>