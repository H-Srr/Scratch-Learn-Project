<?php
// Checks the submitted answers on the server and returns the score.
// If the visitor is logged in, their best score for the quiz is saved for the leaderboard.
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/config.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // make database errors throw exceptions

function respond($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(['success' => false, 'error' => 'Invalid request.'], 405);
}

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $conn->set_charset('utf8mb4');

    $quizName = trim($_POST['quiz'] ?? '');
    $userAnswers = $_POST['answers'] ?? [];
    if ($quizName === '' || !is_array($userAnswers)) {
        respond(['success' => false, 'error' => 'Invalid request.'], 400);
    }

    // Find the quiz
    $stmt = $conn->prepare("SELECT id FROM quizzes WHERE quiz_name = ?");
    $stmt->bind_param("s", $quizName);
    $stmt->execute();
    $quiz = $stmt->get_result()->fetch_assoc();

    if (!$quiz) {
        respond(['success' => false, 'error' => 'Quiz not found.'], 404);
    }
    $quizId = (int)$quiz['id'];

    // The correct answer of every question in this quiz
    $stmt = $conn->prepare("
        SELECT q.id AS question_id, a.id AS answer_id
        FROM questions q
        JOIN answers a ON q.id = a.question_id
        WHERE q.quiz_id = ? AND a.is_correct = 1
    ");
    $stmt->bind_param("i", $quizId);
    $stmt->execute();
    $result = $stmt->get_result();

    $correctAnswers = [];
    while ($row = $result->fetch_assoc()) {
        $correctAnswers[(int)$row['question_id']] = (int)$row['answer_id'];
    }

    // Count the correct answers
    $score = 0;
    foreach ($correctAnswers as $questionId => $correctId) {
        if (isset($userAnswers[$questionId]) && (int)$userAnswers[$questionId] === $correctId) {
            $score++;
        }
    }
    $total = count($correctAnswers);

    // Save the best score for logged-in users. This must never break the quiz itself,
    // so a problem here is only logged and the score is still shown.
    $loggedIn = isset($_SESSION['user_id']);
    $saved = false;
    $best = null;
    if ($loggedIn) {
        try {
            $userId = (int)$_SESSION['user_id'];

            $stmt = $conn->prepare("SELECT score FROM scores WHERE user_id = ? AND quiz_id = ?");
            $stmt->bind_param("ii", $userId, $quizId);
            $stmt->execute();
            $old = $stmt->get_result()->fetch_assoc();

            if (!$old) {
                $stmt = $conn->prepare("INSERT INTO scores (user_id, quiz_id, score, total) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("iiii", $userId, $quizId, $score, $total);
                $stmt->execute();
                $saved = true;
                $best = $score;
            } elseif ($score > (int)$old['score']) {
                $stmt = $conn->prepare("UPDATE scores SET score = ?, total = ?, taken_at = NOW() WHERE user_id = ? AND quiz_id = ?");
                $stmt->bind_param("iiii", $score, $total, $userId, $quizId);
                $stmt->execute();
                $saved = true;
                $best = $score;
            } else {
                $best = (int)$old['score'];
            }
        } catch (mysqli_sql_exception $e) {
            error_log('Scratch Learn could not save the score: ' . $e->getMessage());
            $saved = false;
            $best = null;
        }
    }

    respond([
        'success' => true,
        'score' => $score,
        'total' => $total,
        'logged_in' => $loggedIn,
        'saved' => $saved,
        'best' => $best
    ]);

} catch (mysqli_sql_exception $e) {
    error_log('Scratch Learn check_quiz error: ' . $e->getMessage());
    respond(['success' => false, 'error' => 'Your answers could not be checked right now. Please try again later.'], 500);
}
