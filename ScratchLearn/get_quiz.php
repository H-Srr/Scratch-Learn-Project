<?php
// Returns the questions and answer options of one quiz as JSON.
// It never sends which answer is correct - that is checked on the server in check_quiz.php.
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

    $quizName = trim($_GET['quiz'] ?? '');
    if ($quizName === '') {
        respond(['success' => false, 'error' => 'No quiz was requested.'], 400);
    }

    $stmt = $conn->prepare("SELECT id, quiz_name FROM quizzes WHERE quiz_name = ?");
    $stmt->bind_param("s", $quizName);
    $stmt->execute();
    $quiz = $stmt->get_result()->fetch_assoc();

    if (!$quiz) {
        respond(['success' => false, 'error' => 'Quiz not found.'], 404);
    }

    // One query for all questions and their answer options
    $stmt = $conn->prepare("
        SELECT q.id AS question_id, q.question_text, a.id AS answer_id, a.answer_text
        FROM questions q
        JOIN answers a ON a.question_id = q.id
        WHERE q.quiz_id = ?
        ORDER BY q.id, a.id
    ");
    $stmt->bind_param("i", $quiz['id']);
    $stmt->execute();
    $result = $stmt->get_result();

    $questions = [];
    while ($row = $result->fetch_assoc()) {
        $qid = (int)$row['question_id'];
        if (!isset($questions[$qid])) {
            $questions[$qid] = ['id' => $qid, 'text' => $row['question_text'], 'answers' => []];
        }
        $questions[$qid]['answers'][] = ['id' => (int)$row['answer_id'], 'text' => $row['answer_text']];
    }

    respond(['success' => true, 'quiz' => $quiz['quiz_name'], 'questions' => array_values($questions)]);

} catch (mysqli_sql_exception $e) {
    error_log('Scratch Learn get_quiz error: ' . $e->getMessage());
    respond(['success' => false, 'error' => 'The quiz could not be loaded right now. Please try again later.'], 500);
}
