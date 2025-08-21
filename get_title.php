<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Database credentials
$host = "fdb1030.awardspace.net"; 
$dbname = "4584890_ccr";
$username = "4584890_ccr";
$password = "Sta12bucks.";

// Connect to database
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]));
}

// Get the title ID from the URL
$titleId = isset($_GET['id']) ? intval($_GET['id']) : null;

if (!$titleId) {
    echo json_encode(['error' => 'Title ID is required']);
    exit;
}

// Debugging: Log incoming ID
error_log("Received title ID: " . $titleId);

// Fetch the title details using the ID
$stmt = $conn->prepare('SELECT id, title, subject, content FROM reviewers WHERE id = ?');
if (!$stmt) {
    die(json_encode(['error' => 'SQL error: ' . $conn->error]));
}

$stmt->bind_param('i', $titleId);
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    echo json_encode(['error' => 'Failed to fetch title details']);
    exit;
}

$titleRow = $result->fetch_assoc();

if (!$titleRow) {
    echo json_encode(['error' => 'Title not found']);
    exit;
}

// Debugging: Log the fetched title details
error_log("Fetched title details: " . print_r($titleRow, true));

// Return the title details in JSON format
echo json_encode(['success' => true, 'title' => $titleRow]);

$stmt->close();
$conn->close();
?>