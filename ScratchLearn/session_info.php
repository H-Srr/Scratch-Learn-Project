<?php
session_start();

if (isset($_SESSION['user_id'])) {
    echo "<span style='color:white; font-size:18px;'>Welcome, " . htmlspecialchars($_SESSION['username']) . "</span> | ";
    echo "<a href='logout.php' style='color: red;'>Log out</a>";
} else {
    echo "<a href='LogIn_SL.html'>Log in</a> | <a href='SignUp_SL.html'>Sign up</a>";
}
?>
