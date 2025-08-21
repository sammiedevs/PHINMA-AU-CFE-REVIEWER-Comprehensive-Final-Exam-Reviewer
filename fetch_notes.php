<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start output buffering to capture any unintended output
ob_start();

header('Content-Type: application/json');

$host = "fdb1030.awardspace.net";
$dbname = "4584890_ccr";
$username = "4584890_ccr";
$password = "Sta12bucks.";

// Function to connect to the database
function connectToDatabase($host, $username, $password, $dbname) {
    $conn = new mysqli($host, $username, $password, $dbname);
    if ($conn->connect_error) {
        die(json_encode(["error" => "Database connection failed: " . $conn->connect_error]));
    }
    return $conn;
}

// Connect to the database
$conn = connectToDatabase($host, $username, $password, $dbname);

session_start();
if (!isset($_SESSION['useremail'])) {
    die(json_encode(["error" => "User not logged in"]));
}

$useremail = $_SESSION['useremail'];

// Fetch user_id from the database
$stmt = $conn->prepare("SELECT id FROM users WHERE useremail = ?");
if (!$stmt) {
    // If the connection is lost, reconnect and try again
    $conn = connectToDatabase($host, $username, $password, $dbname);
    $stmt = $conn->prepare("SELECT id FROM users WHERE useremail = ?");
    if (!$stmt) {
        die(json_encode(["error" => "Failed to prepare statement: " . $conn->error]));
    }
}
$stmt->bind_param("s", $useremail);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die(json_encode(["error" => "User not found"]));
}

$user_id = $user['id'];

// Check if 'subject' parameter is provided
if (!isset($_GET['subject']) || empty($_GET['subject'])) {
    die(json_encode(["error" => "Subject parameter is missing or empty"]));
}

$subject = $_GET['subject'];

// Fetch notes for the selected subject
$sql = "SELECT content FROM reviewers WHERE user_id = ? AND subject = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    // If the connection is lost, reconnect and try again
    $conn = connectToDatabase($host, $username, $password, $dbname);
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die(json_encode(["error" => "Failed to prepare statement: " . $conn->error]));
    }
}
$stmt->bind_param("is", $user_id, $subject);
$stmt->execute();
$result = $stmt->get_result();

$notes = [];
while ($row = $result->fetch_assoc()) {
    $notes[] = $row['content']; // Add each note to the array
}

// Combine all notes into a single string
$content = implode(' ', $notes);

// Clear the output buffer to ensure no extra content is sent
ob_end_clean();

// Return the content as JSON
echo json_encode(["content" => $content]);

$stmt->close();
$conn->close();
?>