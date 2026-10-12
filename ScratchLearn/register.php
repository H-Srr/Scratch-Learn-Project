<?php
require_once 'db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    if (empty($email) || empty($username) || empty($password) || empty($confirm_password)) {
        echo "❌ Please fill in all fields.";
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "❌ Invalid email format.";
        exit;
    }

    if ($password !== $confirm_password) {
        echo "❌ Passwords do not match.";
        exit;
    }

    try {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email OR username = :username");
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
} else {
    http_response_code(405);
    echo "❌ Invalid request.";
}
