<?php
require_once __DIR__ . '/security.php';
start_secure_session();
send_api_headers('text/plain; charset=utf-8');

// Failed attempts allowed in 15 minutes before the visitor must wait
const LOGIN_MAX_PER_ACCOUNT = 5;   // per email address typed
const LOGIN_MAX_PER_IP      = 30;  // per visitor address (generous: many people can share one)
const LOGIN_WINDOW_SECONDS  = 900;
// The demo account's password is public (it is in the README), so locking it would protect
// nothing and could only stop a visitor from trying the demo.
const NO_LOCKOUT_EMAILS = ['demo@example.com'];

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

$email = trim(post_string('email'));
$password = post_string('password'); // never trimmed: spaces can be part of a password

$emailKey = rl_key('login-email', $email);
$ipKey = rl_key('login-ip', client_ip());

// Too many recent failures? Refuse before even looking at the password.
$accountLocked = !in_array(strtolower($email), NO_LOCKOUT_EMAILS, true)
    && rate_limit_count($conn, 'login', $emailKey, LOGIN_WINDOW_SECONDS) >= LOGIN_MAX_PER_ACCOUNT;
$ipLocked = rate_limit_count($conn, 'login-ip', $ipKey, LOGIN_WINDOW_SECONDS) >= LOGIN_MAX_PER_IP;
if ($accountLocked || $ipLocked) {
    http_response_code(429);
    echo "❌ Too many failed attempts. Please wait 15 minutes and try again.";
    exit;
}

try {
    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // The password is checked even when the email does not exist, so the answer takes about
    // the same time and gives nothing away about which emails have an account.
    $hash = $user ? $user['password'] : password_hash('not-a-real-password', PASSWORD_DEFAULT);
    $passwordMatches = ($password !== '' && strlen($password) <= 1000) && password_verify($password, $hash);

    if ($user && $passwordMatches) {
        rate_limit_clear($conn, 'login', $emailKey);

        // A new session id after login: an id someone planted earlier becomes useless
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['username'] = $user['username'];
        unset($_SESSION['csrf_token']);

        // Upgrade the stored hash if PHP's recommended settings became stronger since it was made
        if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
            $upgrade = $conn->prepare("UPDATE users SET password = :p WHERE id = :id");
            $upgrade->execute(['p' => password_hash($password, PASSWORD_DEFAULT), 'id' => $user['id']]);
        }

        echo "success";
    } else {
        rate_limit_hit($conn, 'login', $emailKey);
        rate_limit_hit($conn, 'login-ip', $ipKey);
        // One message for "no such email" and "wrong password"
        echo "❌ Incorrect email or password.";
    }
} catch (PDOException $e) {
    error_log('Scratch Learn login error: ' . $e->getMessage());
    http_response_code(500);
    echo "❌ Something went wrong on the server. Please try again later.";
}
