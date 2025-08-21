<?php
header('Content-Type: application/json');

$host = "fdb1030.awardspace.net";
$dbname = "4584890_ccr";
$username = "4584890_ccr";
$password = "Sta12bucks.";

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Database connection failed"]));
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'get_subjects':
            $result = $conn->query("SELECT DISTINCT subject FROM exams ORDER BY subject");
            $subjects = [];
            while ($row = $result->fetch_assoc()) {
                $subjects[] = $row['subject'];
            }
            echo json_encode($subjects);
            break;
            
        case 'add_subject':
            $subject = $conn->real_escape_string($_POST['subject'] ?? '');
            
            if (empty($subject)) {
                throw new Exception("Subject name is required");
            }
            
            // Check if subject already exists
            $check = $conn->query("SELECT 1 FROM exams WHERE subject = '$subject' LIMIT 1");
            if ($check->num_rows > 0) {
                throw new Exception("Subject already exists");
            }
            
            // Add a dummy entry to create the subject
            $sql = "INSERT INTO exams (subject, question, correct_answer) 
                    VALUES ('$subject', 'Sample question', 'Sample answer')";
            
            if ($conn->query($sql)) {
                echo json_encode(["status" => "success", "message" => "Subject added"]);
            } else {
                throw new Exception("Failed to add subject");
            }
            break;
            
        case 'save_exam':
            $subject = $conn->real_escape_string($_POST['subject']);
            $questions = json_decode($_POST['questions'], true);
            
            if (empty($subject) || empty($questions)) {
                throw new Exception("Invalid exam data");
            }
            
            // Start transaction
            $conn->begin_transaction();
            
            try {
                // First delete any existing questions for this subject
                $conn->query("DELETE FROM exams WHERE subject = '$subject'");
                
                // Add new questions
                foreach ($questions as $question) {
                    $q = $conn->real_escape_string($question['question']);
                    $correct = $conn->real_escape_string($question['correct_answer']);
                    
                    $sql = "INSERT INTO exams 
                            (subject, question, correct_answer) 
                            VALUES ('$subject', '$q', '$correct')";
                    
                    if (!$conn->query($sql)) {
                        throw new Exception("Failed to save question: " . $conn->error);
                    }
                }
                
                $conn->commit();
                echo json_encode(["status" => "success", "message" => "Exam saved successfully"]);
            } catch (Exception $e) {
                $conn->rollback();
                throw $e;
            }
            break;
            
        default:
            throw new Exception("Invalid action");
    }
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}

$conn->close();
?>