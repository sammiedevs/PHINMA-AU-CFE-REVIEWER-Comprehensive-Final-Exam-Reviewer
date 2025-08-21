<?php
error_reporting(E_ALL); // Report all PHP errors
ini_set('display_errors', 1); // Display errors to the browser
ini_set('display_startup_errors', 1); // Display startup errors

header('Content-Type: application/json');

$host = "fdb1030.awardspace.net";
$dbname = "4584890_ccr";
$username = "4584890_ccr";
$password = "Sta12bucks.";

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]));
}

session_start();
if (!isset($_SESSION['useremail'])) {
    die(json_encode(['error' => 'User not logged in']));
}

$useremail = $_SESSION['useremail'];

// Fetch user_id from the database
$stmt = $conn->prepare("SELECT id FROM users WHERE useremail = ?");
$stmt->bind_param("s", $useremail);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die(json_encode(['error' => 'User not found']));
}

$student_id = $user['id'];

// Fetch saved PDFs for the student
$stmt = $conn->prepare("SELECT id, pdf_name, pdf_path FROM pdf_resources WHERE student_id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

$pdfs = [];
while ($row = $result->fetch_assoc()) {
    $pdfs[] = $row;
}

if (empty($pdfs)) {
    echo json_encode(['success' => true, 'message' => 'No PDFs saved yet', 'pdfs' => []]);
} else {
    echo json_encode(['success' => true, 'pdfs' => $pdfs]);
}

$conn->close();
?>