<?php
require_once __DIR__ . '/security.php';
start_secure_session();
send_api_headers('text/plain; charset=utf-8');

const REGISTER_MAX_PER_IP = 20;      // sign-up attempts per visitor address per hour
const REGISTER_WINDOW_SECONDS = 3600;

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

require_once __DIR__ . '/db.php';

$ipKey = rl_key('register-ip', client_ip());
if (rate_limit_count($conn, 'register', $ipKey, REGISTER_WINDOW_SECONDS) >= REGISTER_MAX_PER_IP) {
    http_response_code(429);
    echo "❌ Too many sign-up attempts. Please try again in an hour.";
    exit;
}
rate_limit_hit($conn, 'register', $ipKey);

$email = trim(post_string('email'));
$username = trim(post_string('username'));
$password = post_string('password');                  // never trimmed: spaces can be part of a password
$confirm_password = post_string('confirm_password');

if ($email === '' || $username === '' || $password === '' || $confirm_password === '') {
    echo "❌ Please fill in all fields.";
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 255) {
    echo "❌ Invalid email format.";
    exit;
}

// Letters (any language), digits, spaces, dots, dashes and underscores only: no markup characters
if (!preg_match('/^[\p{L}\p{N}_. -]{3,30}$/u', $username)) {
    echo "❌ The username must be 3 to 30 characters: letters, numbers, spaces, dots, dashes or underscores.";
    exit;
}

if (strlen($password) < 8) {
    echo "❌ The password must be at least 8 characters long.";
    exit;
}

if (strlen($password) > 72) { // the hashing method only uses the first 72 bytes
    echo "❌ The password is too long (72 characters maximum).";
    exit;
}

if (strcasecmp($password, $email) === 0 || strcasecmp($password, $username) === 0) {
    echo "❌ The password must be different from your email and username.";
    exit;
}

if ($password !== $confirm_password) {
    echo "❌ Passwords do not match.";
    exit;
}

try {
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = :email OR username = :username");
    $stmt->execute(['email' => $email, 'username' => $username]);

    if ($stmt->rowCount() > 0) {
        echo "❌ Email or username already in use.";
        exit;
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $insert = $conn->prepare("INSERT INTO users (email, username, password) VALUES (:email, :username, :password)");
    $insert->execute([
        'email' => $email,
        'username' => $username,
        'password' => $hashedPassword
    ]);

    echo "success";

} catch (PDOException $e) {
    // 1062 = duplicate entry (two people registering the same name at the same moment)
    if (isset($e->errorInfo[1]) && (int)$e->errorInfo[1] === 1062) {
        echo "❌ Email or username already in use.";
        exit;
    }
    error_log('Scratch Learn register error: ' . $e->getMessage());
    http_response_code(500);
    echo "❌ Something went wrong on the server. Please try again later.";
}
