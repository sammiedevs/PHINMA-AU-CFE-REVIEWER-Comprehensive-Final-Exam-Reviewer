<?php
header('Content-Type: application/json; charset=utf-8');

// Database configuration
$host = "fdb1030.awardspace.net";
$dbname = "4584890_ccr";
$username = "4584890_ccr";
$password = "Sta12bucks.";

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['error' => 'Database connection failed']));
}

// Get action parameter
$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'view':
            // Fetch all reviewers without user filtering
            $result = $conn->query("SELECT id, title, content FROM reviewers");
            if (!$result) {
                throw new Exception("Failed to fetch reviewers");
            }
            
            $reviewers = [];
            while ($row = $result->fetch_assoc()) {
                $reviewers[] = [
                    'id' => (int)$row['id'],
                    'title' => $row['title'],
                    'content' => $row['content']
                ];
            }
            
            echo json_encode($reviewers);
            break;
            
        case 'add':
            $title = trim($_POST['title'] ?? '');
            $content = trim($_POST['content'] ?? '');
            
            if (empty($title) || empty($content)) {
                throw new Exception("Title and content cannot be empty");
            }
            
            $stmt = $conn->prepare("INSERT INTO reviewers (title, content) VALUES (?, ?)");
            $stmt->bind_param("ss", $title, $content);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => 'Reviewer added successfully!']);
            } else {
                throw new Exception("Failed to add reviewer");
            }
            break;
            
        case 'update':
            $id = (int)($_POST['id'] ?? 0);
            $title = trim($_POST['title'] ?? '');
            $content = trim($_POST['content'] ?? '');
            
            if ($id <= 0 || empty($title) || empty($content)) {
                throw new Exception("Invalid input data");
            }
            
            $stmt = $conn->prepare("UPDATE reviewers SET title = ?, content = ? WHERE id = ?");
            $stmt->bind_param("ssi", $title, $content, $id);
            
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    echo json_encode(['success' => 'Reviewer updated successfully!']);
                } else {
                    throw new Exception("No reviewer found with that ID");
                }
            } else {
                throw new Exception("Failed to update reviewer");
            }
            break;
            
        case 'delete':
            $id = (int)($_POST['id'] ?? 0);
            
            if ($id <= 0) {
                throw new Exception("Invalid reviewer ID");
            }
            
            $stmt = $conn->prepare("DELETE FROM reviewers WHERE id = ?");
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    echo json_encode(['success' => 'Reviewer deleted successfully!']);
                } else {
                    throw new Exception("No reviewer found with that ID");
                }
            } else {
                throw new Exception("Failed to delete reviewer");
            }
            break;
            
        default:
            throw new Exception("Invalid action specified");
    }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    $conn->close();
}
?>