<?php
session_start();
require_once 'db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? '');
    $password = trim($_POST["password"] ?? '');

    try {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);

        if ($stmt->rowCount() === 1) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                echo "success";
            } else {
                echo "❌ Incorrect password.";
            }
        } else {
            echo "❌ Email not found.";
        }
    } catch (PDOException $e) {
        error_log('Scratch Learn login error: ' . $e->getMessage());
        http_response_code(500);
        echo "❌ Something went wrong on the server. Please try again later.";
    }
} else {
    http_response_code(405);
    echo "❌ Invalid request.";
}
