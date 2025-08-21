<?php
session_start();

$host = "fdb1030.awardspace.net";
$dbname = "4584890_ccr";
$username = "4584890_ccr";
$password = "Sta12bucks.";

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(["status" => false, "msg" => "Database connection failed: " . $conn->connect_error]));
}

if (!isset($_SESSION['useremail'])) {
    die(json_encode(["status" => false, "msg" => "User not logged in"]));
}

$useremail = $_SESSION['useremail'];

$stmt = $conn->prepare("SELECT id FROM users WHERE useremail = ?");
$stmt->bind_param("s", $useremail);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die(json_encode(["status" => false, "msg" => "User not found"]));
}

$user_id = $user['id'];

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['id'])) {
    echo json_encode(['status' => false, 'msg' => 'Flashcard ID required']);
    exit;
}

try {
    $stmt = $conn->prepare("DELETE FROM flashcards WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $data['id'], $user_id);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
        echo json_encode(['status' => true, 'msg' => 'Flashcard deleted successfully']);
    } else {
        echo json_encode(['status' => false, 'msg' => 'No flashcard found or already deleted']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => false, 'msg' => 'Error deleting flashcard: ' . $e->getMessage()]);
}

$conn->close();
?>