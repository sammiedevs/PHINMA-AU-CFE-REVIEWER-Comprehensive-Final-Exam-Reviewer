<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['useremail'])) {
    die(json_encode(["error" => "User not logged in"]));
}

$oldSubject = $_POST['oldSubject'] ?? '';
$newSubject = $_POST['newSubject'] ?? '';
$questions = $_POST['questions'] ?? [];

if (empty($oldSubject) || empty($newSubject) || empty($questions)) {
    die(json_encode(["error" => "Missing required data"]));
}

$host = "fdb1030.awardspace.net"; 
$dbname = "4584890_ccr";
$username = "4584890_ccr";
$password = "Sta12bucks.";

try {
    $conn = new mysqli($host, $username, $password, $dbname);
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }

    // Get user ID
    $useremail = $_SESSION['useremail'];
    $stmt = $conn->prepare("SELECT id FROM users WHERE useremail = ?");
    $stmt->bind_param("s", $useremail);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if (!$user) {
        throw new Exception("User not found");
    }

    $user_id = $user['id'];

    // First delete the old exam
    $deleteStmt = $conn->prepare("DELETE FROM exams WHERE user_id = ? AND subject = ?");
    $deleteStmt->bind_param("is", $user_id, $oldSubject);
    $deleteStmt->execute();

    // Then insert the updated exam
    $insertStmt = $conn->prepare("INSERT INTO exams (user_id, subject, question, correct_answer) VALUES (?, ?, ?, ?)");
    
    foreach ($questions as $question) {
        $questionText = $question['question'];
        $correctAnswer = $question['correctAnswer'];
        
        $insertStmt->bind_param("isss", $user_id, $newSubject, $questionText, $correctAnswer);
        $insertStmt->execute();
    }

    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>