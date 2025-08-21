<?php
header('Content-Type: application/json');

// Database connection details
$host = "fdb1030.awardspace.net";
$dbname = "4584890_ccr";
$username = "4584890_ccr";
$password = "Sta12bucks.";

// Create a connection to the database
$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(["error" => "Database connection failed: " . $conn->connect_error]));
}

// Get the subject, questions, and answers from the POST request
$subject = $_POST['subject'];
$questions = json_decode($_POST['questions'], true);

// Insert questions and answers into the exams table
foreach ($questions as $question) {
    $stmt = $conn->prepare("INSERT INTO exams (subject, question, correct_answer) VALUES (?, ?, ?)");
    if (!$stmt) {
        die(json_encode(["error" => "Failed to prepare statement: " . $conn->error]));
    }
    $stmt->bind_param("sss", $subject, $question['question'], $question['correctAnswer']);
    $stmt->execute();
}

echo json_encode(["success" => true, "message" => "Exam saved successfully"]);

$stmt->close();
$conn->close();
?>