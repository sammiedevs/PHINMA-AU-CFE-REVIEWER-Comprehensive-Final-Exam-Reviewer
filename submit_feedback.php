<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Allow cross-origin requests

// Database configuration
$host = "fdb1030.awardspace.net"; 
$dbname = "4584890_ccr";
$username = "4584890_ccr";
$password = "Sta12bucks.";

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]));
}

session_start();
if (!isset($_SESSION['useremail'])) {
    die(json_encode(["error" => "User not logged in"]));
}

$useremail = $_SESSION['useremail'];

// Fetch user_id from the database
$stmt = $conn->prepare("SELECT id FROM users WHERE useremail = ?");
$stmt->bind_param("s", $useremail);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die(json_encode(["error" => "User not found"]));
}

$user_id = $user['id'];

// Get data from the request
$data = json_decode(file_get_contents('php://input'), true);

// Debugging: Check if $data is null
if (!$data) {
    echo json_encode(['error' => 'Invalid or missing JSON data']);
    exit;
}

$message = $data['message'] ?? null;
$userfullname = $data['userfullname'] ?? null;

// Validate input
if (empty($message) || empty($userfullname)) {
    echo json_encode(['error' => 'Message and user full name are required']);
    exit;
}

// Insert feedback into the database
$stmt = $conn->prepare('INSERT INTO feedback (user_id, message, userfullname) VALUES (?, ?, ?)');
$stmt->bind_param('sss', $user_id, $message, $userfullname);

if ($stmt->execute()) {
    // Fetch the inserted feedback to get the created_at timestamp
    $feedbackId = $stmt->insert_id;
    $result = $conn->query("SELECT * FROM feedback WHERE id = $feedbackId");
    $feedback = $result->fetch_assoc();

    echo json_encode([
        'id' => $feedbackId,
        'message' => $message,
        'userfullname' => $userfullname,
        'created_at' => $feedback['created_at'] // Include the created_at timestamp
    ]);
} else {
    echo json_encode(['error' => 'Failed to save feedback']);
}

$stmt->close();
$conn->close();
?>
