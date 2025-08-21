<?php
header('Content-Type: application/json');

$host = "fdb1030.awardspace.net";
$dbname = "4584890_ccr";
$username = "4584890_ccr";
$password = "Sta12bucks.";

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(["error" => "Database connection failed"]));
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'get_subjects':
        $result = $conn->query("SELECT DISTINCT subject FROM exams");
        $subjects = [];
        while ($row = $result->fetch_assoc()) {
            $subjects[] = $row['subject'];
        }
        echo json_encode($subjects);
        break;
        
    case 'get_questions':
        $subject = $conn->real_escape_string($_GET['subject'] ?? '');
        $result = $conn->query("SELECT id, question, correct_answer FROM exams WHERE subject = '$subject'");
        $questions = [];
        while ($row = $result->fetch_assoc()) {
            $questions[] = [
                'id' => $row['id'],
                'question' => $row['question'],
                'correct_answer' => $row['correct_answer']
            ];
        }
        echo json_encode($questions);
        break;
        
    default:
        echo json_encode(["error" => "Invalid action"]);
}

$conn->close();
?>