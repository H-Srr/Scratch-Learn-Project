<?php
// =====================================================================
// Shared security helpers, used by every PHP file that handles a request.
// It only defines functions and prints nothing, so opening it in a
// browser shows an empty page.
// =====================================================================

// Starts the PHP session with safe cookie settings:
//   HttpOnly -> JavaScript can never read the session cookie
//   SameSite -> the cookie is not sent along with requests started by other websites
//   Secure   -> only sent over HTTPS (switched on automatically when the site runs on HTTPS)
function start_secure_session() {
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
          || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);

    ini_set('session.use_strict_mode', '1');   // never accept a session id the server did not create
    ini_set('session.use_only_cookies', '1');  // never accept a session id from the URL
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => $https,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// Headers for answers that must not be cached or "sniffed" into another file type.
function send_api_headers($contentType = 'application/json; charset=utf-8') {
    header('Content-Type: ' . $contentType);
    header('X-Content-Type-Options: nosniff');
    header('Cache-Control: no-store');
}

// A value sent by the browser, as a plain string ('' if it is missing or not a string).
function post_string($name) {
    return (isset($_POST[$name]) && is_string($_POST[$name])) ? $_POST[$name] : '';
}

// ---------------------------------------------------------------------
// CSRF protection: every request that changes something must carry the secret
// token that only pages of this site can obtain (csrf_token.php). A page of
// another website cannot read it, so it cannot forge a request.
// ---------------------------------------------------------------------
function csrf_token() {
    start_secure_session();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_is_valid() {
    start_secure_session();
    $known = isset($_SESSION['csrf_token']) ? (string)$_SESSION['csrf_token'] : '';
    $sent = post_string('csrf_token');
    return $known !== '' && hash_equals($known, $sent);
}

function csrf_error_text() {
    return "❌ Your session has expired. Please reload the page and try again.";
}

// ---------------------------------------------------------------------
// Rate limiting (slows down password guessing and spam).
// Attempts are stored in the `rate_limits` table under a hashed key, so the
// database never contains a visitor's IP address or a typed email address.
// If the table does not exist yet, these helpers do nothing (and log it) instead
// of breaking the site.
// ---------------------------------------------------------------------
function client_ip() {
    // Only REMOTE_ADDR is trusted. Headers such as X-Forwarded-For can be faked by anyone.
    return isset($_SERVER['REMOTE_ADDR']) ? (string)$_SERVER['REMOTE_ADDR'] : 'unknown';
}

function rl_key($type, $value) {
    $secret = (defined('DB_NAME') ? DB_NAME : '') . '|' . (defined('DB_USER') ? DB_USER : '') . '|' . (defined('DB_PASS') ? DB_PASS : '');
    return hash_hmac('sha256', $type . '|' . strtolower((string)$value), $secret);
}

// How many attempts of this kind were recorded in the last $windowSeconds seconds
function rate_limit_count(PDO $conn, $action, $key, $windowSeconds) {
    try {
        $seconds = (int)$windowSeconds;
        $stmt = $conn->prepare("SELECT COUNT(*) FROM rate_limits WHERE action = :a AND identifier = :i AND created_at > DATE_SUB(NOW(), INTERVAL $seconds SECOND)");
        $stmt->execute(['a' => $action, 'i' => $key]);
        return (int)$stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log('Scratch Learn rate limit unavailable (is the rate_limits table installed?): ' . $e->getMessage());
        return 0;
    }
}

function rate_limit_hit(PDO $conn, $action, $key) {
    try {
        $stmt = $conn->prepare("INSERT INTO rate_limits (action, identifier) VALUES (:a, :i)");
        $stmt->execute(['a' => $action, 'i' => $key]);
        if (mt_rand(1, 20) === 1) { // now and then, forget attempts older than a day
            $conn->exec("DELETE FROM rate_limits WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 DAY)");
        }
    } catch (PDOException $e) {
        error_log('Scratch Learn rate limit unavailable: ' . $e->getMessage());
    }
}

function rate_limit_clear(PDO $conn, $action, $key) {
    try {
        $stmt = $conn->prepare("DELETE FROM rate_limits WHERE action = :a AND identifier = :i");
        $stmt->execute(['a' => $action, 'i' => $key]);
    } catch (PDOException $e) {
        error_log('Scratch Learn rate limit unavailable: ' . $e->getMessage());
    }
}
