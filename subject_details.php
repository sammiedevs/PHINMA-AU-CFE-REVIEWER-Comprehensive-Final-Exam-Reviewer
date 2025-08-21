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

$user_id = $user['id'];

$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($action === 'add') {
    // Add a new title
    $title = $_POST['title'];
    $subjectId = $_POST['subject_id'];

    // Fetch the subject name based on the subject ID
    $stmt = $conn->prepare("SELECT subject FROM reviewers WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $subjectId, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $subjectRow = $result->fetch_assoc();

    if (!$subjectRow) {
        die(json_encode(['success' => false, 'error' => 'Subject not found']));
    }

    $subject = $subjectRow['subject'];

    // Insert the new title
    $stmt = $conn->prepare("INSERT INTO reviewers (title, subject, user_id) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $title, $subject, $user_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to add title']);
    }
} elseif ($action === 'view') {
    // Fetch the subject based on the provided ID
    if (!isset($_GET['id'])) {
        die(json_encode(['success' => false, 'error' => 'ID not provided']));
    }

    $id = $_GET['id'];

    // Validate ID
    if (!is_numeric($id)) {
        die(json_encode(['success' => false, 'error' => 'Invalid ID']));
    }

    // Fetch the subject from the database
    $stmt = $conn->prepare("SELECT subject FROM reviewers WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $subjectRow = $result->fetch_assoc();

    if (!$subjectRow) {
        die(json_encode(['success' => false, 'error' => 'Subject not found']));
    }

    $subject = $subjectRow['subject'];

    // Fetch titles from the database for the given subject and user
    $stmt = $conn->prepare("SELECT id, title FROM reviewers WHERE subject = ? AND user_id = ?");
    $stmt->bind_param("si", $subject, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $titles = [];
    while ($row = $result->fetch_assoc()) {
        $titles[] = $row; // Add each title to the array
    }

    echo json_encode(['success' => true, 'titles' => $titles]); // Return the titles as a JSON array
} elseif ($action === 'fetch_title') {
    // Fetch a single title for updating
    $titleId = $_GET['title_id'];

    // Validate title ID
    if (!is_numeric($titleId)) {
        die(json_encode(['success' => false, 'error' => 'Invalid title ID']));
    }

    // Fetch the title from the database
    $stmt = $conn->prepare("SELECT id, title FROM reviewers WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $titleId, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $title = $result->fetch_assoc(); // Fetch the title data
        echo json_encode(['success' => true, 'title' => $title]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Title not found']);
    }
} elseif ($action === 'update') {
    // Update an existing title
    $titleId = $_POST['title_id'];
    $titleName = $_POST['title_name'];

    $stmt = $conn->prepare("UPDATE reviewers SET title = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("sii", $titleName, $titleId, $user_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to update title']);
    }
} elseif ($action === 'delete') {
    // Delete a title
    $titleId = $_POST['id'];

    $stmt = $conn->prepare("DELETE FROM reviewers WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $titleId, $user_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to delete title']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid action']);
}

$conn->close();
?>