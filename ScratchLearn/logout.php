<?php
require_once __DIR__ . '/security.php';
start_secure_session();

$_SESSION = [];  // Remove all session variables

// Delete the session cookie in the browser as well
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', [
        'expires'  => time() - 3600,
        'path'     => $p['path'],
        'domain'   => $p['domain'],
        'secure'   => $p['secure'],
        'httponly' => $p['httponly'],
        'samesite' => 'Lax',
    ]);
}
session_destroy(); // Destroy the session on the server

// Redirect to login page
header("Location: LogIn_SL.html");
exit();
?>
