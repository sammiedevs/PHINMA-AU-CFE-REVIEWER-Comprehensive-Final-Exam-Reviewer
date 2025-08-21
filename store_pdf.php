<?php
error_reporting(E_ALL); // Report all PHP errors
ini_set('display_errors', 1); // Display errors to the browser
ini_set('display_startup_errors', 1); // Display startup errors

header('Content-Type: application/json'); // Ensure the response is JSON

$host = "fdb1030.awardspace.net";
$dbname = "4584890_ccr";
$username = "4584890_ccr";
$password = "Sta12bucks.";

// Create a database connection
$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]));
}

// Start the session
session_start();
if (!isset($_SESSION['useremail'])) {
    die(json_encode(['error' => 'User not logged in']));
}

// Fetch the logged-in user's email
$useremail = $_SESSION['useremail'];

// Fetch the user's ID from the database
$stmt = $conn->prepare("SELECT id FROM users WHERE useremail = ?");
if (!$stmt) {
    die(json_encode(['error' => 'Prepare failed: ' . $conn->error]));
}
$stmt->bind_param("s", $useremail);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die(json_encode(['error' => 'User not found']));
}

$student_id = $user['id']; // Use the fetched student_id

// Read the JSON input from the request body
$input = file_get_contents('php://input');
if (!$input) {
    die(json_encode(['error' => 'No input data received']));
}

$data = json_decode($input, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    die(json_encode(['error' => 'Invalid JSON input']));
}

if (!isset($data['pdf_id'])) {
    die(json_encode(['error' => 'PDF ID not provided']));
}

$pdf_id = $data['pdf_id'];

// Fetch PDF details from the teacher's PDF table
$stmt = $conn->prepare("SELECT pdf_name, pdf_path FROM pdf_resources WHERE id = ?");
if (!$stmt) {
    die(json_encode(['error' => 'Prepare failed: ' . $conn->error]));
}
$stmt->bind_param("i", $pdf_id);
$stmt->execute();
$result = $stmt->get_result();
$pdf = $result->fetch_assoc();

if (!$pdf) {
    die(json_encode(['error' => 'PDF not found']));
}

$pdf_name = $pdf['pdf_name'];
$pdf_path = $pdf['pdf_path'];

// Insert the PDF into the saved_pdfs table with the student_id
$stmt = $conn->prepare("INSERT INTO pdf_resources (pdf_id, student_id, pdf_name, pdf_path) VALUES (?, ?, ?, ?)");
if (!$stmt) {
    die(json_encode(['error' => 'Prepare failed: ' . $conn->error]));
}
$stmt->bind_param("iiss", $pdf_id, $student_id, $pdf_name, $pdf_path);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'PDF saved successfully']);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to save PDF: ' . $stmt->error]);
}

$conn->close();
?>