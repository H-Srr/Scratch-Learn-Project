<?php
require_once 'db.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo "❌ Invalid request.";
    exit;
}

$name = trim($_POST["name"] ?? '');
$message = trim($_POST["feedback"] ?? '');
$len = function_exists('mb_strlen') ? 'mb_strlen' : 'strlen';

if ($name === '' || $message === '') {
    http_response_code(400);
    echo "❌ Please enter your name and your feedback.";
    exit;
}
if ($len($name) > 100) {
    http_response_code(400);
    echo "❌ Your name is too long (100 characters maximum).";
    exit;
}
if ($len($message) > 1000) {
    http_response_code(400);
    echo "❌ Your feedback is too long (1000 characters maximum).";
    exit;
}

try {
    $stmt = $conn->prepare("INSERT INTO feedback (name, message) VALUES (:name, :message)");
    $stmt->execute([
        'name' => $name,
        'message' => $message
    ]);

    echo "success";
} catch (PDOException $e) {
    error_log('Scratch Learn feedback error: ' . $e->getMessage());
    http_response_code(500);
    echo "❌ Your feedback could not be saved. Please try again later.";
}
?>
