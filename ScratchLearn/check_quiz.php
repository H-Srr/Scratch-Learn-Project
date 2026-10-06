<?php
header('Content-Type: application/json');

require_once __DIR__ . '/config.php';

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    $quizName = $_POST['quiz'] ?? '';
    $userAnswers = $_POST['answers'] ?? [];
    
    // Get quiz ID
    $stmt = $conn->prepare("SELECT id FROM quizzes WHERE quiz_name LIKE ?");
    $quizParam = "%$quizName%";
    $stmt->bind_param("s", $quizParam);
    $stmt->execute();
    $result = $stmt->get_result();
    $quiz = $result->fetch_assoc();
    
    if (!$quiz) {
        throw new Exception("Quiz not found");
    }
    
    $quizId = $quiz['id'];
    
    // Get correct answers
    $correctAnswers = [];
    $stmt = $conn->prepare("
        SELECT q.id AS question_id, a.id AS answer_id 
        FROM questions q
        JOIN answers a ON q.id = a.question_id
        WHERE q.quiz_id = ? AND a.is_correct = 1
    ");
    $stmt->bind_param("i", $quizId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $correctAnswers[$row['question_id']] = $row['answer_id'];
    }
    
    // Check answers
    $score = 0;
    $totalQuestions = count($correctAnswers);
    
    foreach ($userAnswers as $questionId => $answerId) {
        if (isset($correctAnswers[$questionId]) {
            if ($correctAnswers[$questionId] == $answerId) {
                $score++;
            }
        }
    }
    
    echo json_encode([
        'success' => true,
        'score' => $score,
        'total' => $totalQuestions
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} finally {
    if (isset($conn)) $conn->close();
}
?>