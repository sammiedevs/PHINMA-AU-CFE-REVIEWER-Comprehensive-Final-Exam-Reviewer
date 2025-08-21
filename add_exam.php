<?php
header('Content-Type: application/json');

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session to access user data
session_start();

// Debugging: Log the POST data
error_log(print_r($_POST, true));

// Database connection details
$host = "fdb1030.awardspace.net"; 
$dbname = "4584890_ccr";
$username = "4584890_ccr";
$password = "Sta12bucks.";

try {
    // Create a connection to the database
    $conn = new mysqli($host, $username, $password, $dbname);
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }

    // Check if user is logged in
    if (!isset($_SESSION['useremail'])) {
        throw new Exception("User not logged in");
    }

    $useremail = $_SESSION['useremail'];

    // Fetch user_id from the database
    $userStmt = $conn->prepare("SELECT id FROM users WHERE useremail = ?");
    $userStmt->bind_param("s", $useremail);
    $userStmt->execute();
    $result = $userStmt->get_result();
    $user = $result->fetch_assoc();
    $userStmt->close();

    if (!$user) {
        throw new Exception("User not found");
    }

    $user_id = $user['id'];

    // Get the POST data
    $subject = $_POST['subject'] ?? '';

    // Validate the subject
    if (empty($subject)) {
        throw new Exception("Subject cannot be empty");
    }

    // Get the questions
    $questions = $_POST['questions'] ?? [];

    // Validate the questions
    if (empty($questions)) {
        throw new Exception("At least one question is required");
    }

    // Insert the exam into the database with user_id
    $stmt = $conn->prepare("INSERT INTO exams (user_id, subject, question, correct_answer) VALUES (?, ?, ?, ?)");
    if (!$stmt) {
        throw new Exception("Failed to prepare statement: " . $conn->error);
    }

    foreach ($questions as $question) {
        $questionText = $question['question'];
        $correctAnswer = $question['correctAnswer'];

        if (!empty($questionText) && !empty($correctAnswer)) {
            $stmt->bind_param("isss", $user_id, $subject, $questionText, $correctAnswer);
            if (!$stmt->execute()) {
                throw new Exception("Failed to execute statement: " . $stmt->error);
            }
        }
    }

    echo json_encode(['success' => true, 'message' => 'Exam added successfully']);
    
    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    
    // Close connections if they exist
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
    if (isset($userStmt)) $userStmt->close();
}
?>