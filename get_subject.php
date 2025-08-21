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

// Get the subject ID from the URL
$subjectId = isset($_GET['id']) ? intval($_GET['id']) : null;

if (!$subjectId) {
    echo json_encode(['error' => 'Subject ID is required']);
    exit;
}

// Debugging: Log incoming ID
error_log("Received subject ID: " . $subjectId);

// Fetch the subject name using the ID
$stmt = $conn->prepare('SELECT subject FROM reviewers WHERE id = ?');
if (!$stmt) {
    die(json_encode(['error' => 'SQL error: ' . $conn->error]));
}

$stmt->bind_param('i', $subjectId);
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    echo json_encode(['error' => 'Failed to fetch subject name']);
    exit;
}

$subjectRow = $result->fetch_assoc();

if (!$subjectRow) {
    echo json_encode(['error' => 'Subject not found']);
    exit;
}

$subjectName = $subjectRow['subject'];

// Debugging: Log the fetched subject name
error_log("Fetched subject name: " . $subjectName);

// Fetch titles from the reviewers table where the subject matches
$stmt = $conn->prepare('SELECT title FROM reviewers WHERE subject = ?');
if (!$stmt) {
    die(json_encode(['error' => 'SQL error: ' . $conn->error]));
}

$stmt->bind_param('s', $subjectName);
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    echo json_encode(['error' => 'Failed to fetch titles']);
    exit;
}

$titles = [];
while ($row = $result->fetch_assoc()) {
    $titles[] = $row['title'];
}

if (!empty($titles)) {
    echo json_encode(['success' => true, 'titles' => $titles, 'debug' => ['cleaned_subject' => $subjectName]]);
} else {
    echo json_encode(['error' => 'No titles found for this subject']);
}

$stmt->close();
$conn->close();
