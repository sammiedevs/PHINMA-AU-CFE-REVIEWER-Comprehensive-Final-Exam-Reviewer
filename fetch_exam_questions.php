<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['useremail'])) {
    die(json_encode(["error" => "User not logged in"]));
}

$subject = $_GET['subject'] ?? '';
if (empty($subject)) {
    die(json_encode(["error" => "Subject not specified"]));
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

    // Get all questions for this subject and user
    $query = "SELECT question, correct_answer FROM exams 
              WHERE user_id = ? AND subject = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $user_id, $subject);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $questions = [];
    while ($row = $result->fetch_assoc()) {
        $questions[] = [
            'question' => $row['question'],
            'correct_answer' => $row['correct_answer']
        ];
    }

    echo json_encode(['success' => true, 'questions' => $questions]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>