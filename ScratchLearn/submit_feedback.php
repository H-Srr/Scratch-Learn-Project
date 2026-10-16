<?php
require_once __DIR__ . '/security.php';
start_secure_session();
send_api_headers('text/plain; charset=utf-8');

const FEEDBACK_MAX_PER_IP = 5;        // comments per visitor address ...
const FEEDBACK_WINDOW_SECONDS = 600;  // ... in 10 minutes

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo "❌ Invalid request.";
    exit;
}

if (!csrf_is_valid()) {
    http_response_code(403);
    echo csrf_error_text();
    exit;
}

// Trap for spam robots: this field is hidden from people, but robots fill in every field.
// They are told "success" so they do not try to adapt, and nothing is saved.
if (trim(post_string('website')) !== '') {
    echo "success";
    exit;
}

require_once __DIR__ . '/db.php';

$name = trim(post_string('name'));
$message = trim(post_string('feedback'));
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

$ipKey = rl_key('feedback-ip', client_ip());
if (rate_limit_count($conn, 'feedback', $ipKey, FEEDBACK_WINDOW_SECONDS) >= FEEDBACK_MAX_PER_IP) {
    http_response_code(429);
    echo "❌ You are sending comments too quickly. Please wait a few minutes and try again.";
    exit;
}

try {
    $stmt = $conn->prepare("INSERT INTO feedback (name, message) VALUES (:name, :message)");
    $stmt->execute([
        'name' => $name,
        'message' => $message
    ]);
    rate_limit_hit($conn, 'feedback', $ipKey);

    echo "success";
} catch (PDOException $e) {
    error_log('Scratch Learn feedback error: ' . $e->getMessage());
    http_response_code(500);
    echo "❌ Your feedback could not be saved. Please try again later.";
}
?>
