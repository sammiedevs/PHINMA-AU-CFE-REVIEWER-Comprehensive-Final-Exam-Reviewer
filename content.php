<?php
header('Content-Type: application/json'); // Set the correct Content-Type header

$host = "fdb1030.awardspace.net"; 
$db = "4584890_ccr";
$user = "4584890_ccr";
$pass = "Sta12bucks.";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}

// Fetch subject, title, and content by ID
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT subject, title, content FROM reviewers WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row) {
        echo json_encode(['success' => true, 'subject' => $row['subject'], 'title' => $row['title'], 'content' => $row['content']]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No content found for this ID.']);
    }
    exit();
}

// Update content in the database
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid JSON input']);
        exit();
    }

    // Check if 'id' and 'content' keys exist in the $data array
    if (!isset($data['id']) || !isset($data['content'])) {
        echo json_encode(['status' => 'error', 'message' => 'Missing required fields: id or content']);
        exit();
    }

    $id = $data['id']; // Get the ID from the request
    $content = $data['content']; // Get the content from the request

    try {
        // Update the content for the given ID
        $sql = "UPDATE reviewers SET content = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $content, $id);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Reviewer updated successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update reviewer']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit();
}
?>