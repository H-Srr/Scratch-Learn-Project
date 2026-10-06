<?php
header('Content-Type: application/json');

require_once __DIR__ . '/config.php';

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    $quizName = $_GET['quiz'] ?? '';
    
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
    
    // Get questions
    $questions = [];
    $stmt = $conn->prepare("SELECT id, question_text FROM questions WHERE quiz_id = ?");
    $stmt->bind_param("i", $quizId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $questionId = $row['id'];
        
        // Get answers for each question
        $answerStmt = $conn->prepare("SELECT id, answer_text FROM answers WHERE question_id = ?");
        $answerStmt->bind_param("i", $questionId);
        $answerStmt->execute();
        $answerResult = $answerStmt->get_result();
        
        $answers = [];
        while ($answerRow = $answerResult->fetch_assoc()) {
            $answers[] = [
                'id' => $answerRow['id'],
                'text' => $answerRow['answer_text']
            ];
        }
        
        $questions[] = [
            'id' => $row['id'],
            'text' => $row['question_text'],
            'answers' => $answers
        ];
    }
    
    echo json_encode([
        'success' => true,
        'questions' => $questions
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