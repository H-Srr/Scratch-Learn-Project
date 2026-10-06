<?php
require_once 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["name"]);
    $message = trim($_POST["feedback"]);

    try {
        $stmt = $conn->prepare("INSERT INTO feedback (name, message) VALUES (:name, :message)");
        $stmt->execute([
            'name' => $name,
            'message' => $message
        ]);

        echo "<script>alert('Feedback submitted successfully!'); window.location.href = 'feed.html';</script>";
    } catch (PDOException $e) {
        echo "<script>alert('Error saving feedback: " . $e->getMessage() . "');</script>";
    }
}
?>
