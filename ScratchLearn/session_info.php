<?php
require_once __DIR__ . '/security.php';
start_secure_session();
send_api_headers('text/html; charset=utf-8');

// The markup matches the default login area in js/layout.js (styles are in css/css.css)
if (isset($_SESSION['user_id'])) {
    echo '<span class="welcome">Welcome, ' . htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8') . '</span>';
    echo '<span class="sep" aria-hidden="true">|</span>';
    echo '<a class="logout-link" href="logout.php">Log out</a>';
} else {
    echo '<a href="LogIn_SL.html">Log in</a>';
    echo '<span class="sep" aria-hidden="true">|</span>';
    echo '<a href="SignUp_SL.html">Sign up</a>';
}
?>
