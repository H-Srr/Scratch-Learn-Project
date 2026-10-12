<?php
// Returns the leaderboard as JSON: one row per user who has taken at least one quiz,
// ranked by their total points (best score of every quiz added together).
// Only usernames and scores are returned - never emails or ids.
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/config.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // make database errors throw exceptions

function respond($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $conn->set_charset('utf8mb4');

    // The quizzes, used as the score columns of the table
    $quizNames = [];
    $result = $conn->query("SELECT quiz_name FROM quizzes ORDER BY id");
    while ($row = $result->fetch_assoc()) {
        $quizNames[] = $row['quiz_name'];
    }

    // Every saved score with the name of its owner
    $result = $conn->query("
        SELECT u.id AS user_id, u.username, qz.quiz_name, s.score, s.total
        FROM scores s
        JOIN users u ON u.id = s.user_id
        JOIN quizzes qz ON qz.id = s.quiz_id
    ");

    $players = [];
    while ($row = $result->fetch_assoc()) {
        $uid = (int)$row['user_id'];
        if (!isset($players[$uid])) {
            $players[$uid] = ['username' => $row['username'], 'scores' => [], 'points' => 0];
        }
        $players[$uid]['scores'][$row['quiz_name']] = ['score' => (int)$row['score'], 'total' => (int)$row['total']];
        $players[$uid]['points'] += (int)$row['score'];
    }

    // Most points first, then alphabetical
    $players = array_values($players);
    usort($players, function ($a, $b) {
        if ($a['points'] !== $b['points']) {
            return $b['points'] - $a['points'];
        }
        return strcasecmp($a['username'], $b['username']);
    });

    // Rank (players with the same points share the same rank: 1, 2, 2, 4 ...)
    foreach ($players as $i => &$player) {
        $player['rank'] = ($i > 0 && $player['points'] === $players[$i - 1]['points'])
            ? $players[$i - 1]['rank']
            : $i + 1;
    }
    unset($player);

    respond(['success' => true, 'quizzes' => $quizNames, 'data' => $players]);

} catch (mysqli_sql_exception $e) {
    error_log('Scratch Learn leaderboard error: ' . $e->getMessage());
    respond(['success' => false, 'data' => [], 'error' => 'The leaderboard could not be loaded right now. Please try again later.'], 500);
}
