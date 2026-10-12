<?php
require_once 'db.php';

try {
    $stmt = $conn->query("SELECT name, message, created_at FROM feedback ORDER BY created_at DESC, id DESC");
    $count = 0;
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $count++;
        echo "<div class='comment'>";
        echo "<strong>" . htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') . ":</strong> " . htmlspecialchars($row['message'], ENT_QUOTES, 'UTF-8');
        echo "<br><small style='color:gray;'>(" . htmlspecialchars($row['created_at'], ENT_QUOTES, 'UTF-8') . ")</small>";
        echo "</div><br>";
    }
    if ($count === 0) {
        echo "<p>No comments yet. Be the first to leave one!</p>";
    }
} catch (PDOException $e) {
    error_log('Scratch Learn load_feedback error: ' . $e->getMessage());
    http_response_code(500);
    echo "<p>Comments could not be loaded right now. Please try again later.</p>";
}
?>
