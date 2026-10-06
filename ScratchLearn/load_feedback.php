<?php
require_once 'db.php';

try {
    $stmt = $conn->query("SELECT name, message, created_at FROM feedback ORDER BY created_at DESC");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<div class='comment'>";
        echo "<strong>" . htmlspecialchars($row['name']) . ":</strong> " . htmlspecialchars($row['message']);
        echo "<br><small style='color:gray;'>(" . $row['created_at'] . ")</small>";
        echo "</div><br>";
    }
} catch (PDOException $e) {
    echo "<p>Error loading feedback: " . $e->getMessage() . "</p>";
}
?>
