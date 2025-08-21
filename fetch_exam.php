<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['useremail'])) {
    die(json_encode(["error" => "User not logged in"]));
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

    // Get all exams for this user, grouped by subject
    $query = "SELECT subject, GROUP_CONCAT(id) as exam_ids 
              FROM exams 
              WHERE user_id = ? 
              GROUP BY subject 
              ORDER BY subject";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $subjects = [];
    while ($row = $result->fetch_assoc()) {
        $subjects[] = [
            'subject' => $row['subject'],
            'exam_ids' => explode(',', $row['exam_ids'])
        ];
    }

    echo json_encode(['success' => true, 'subjects' => $subjects]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>