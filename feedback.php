<?php
header('Content-Type: application/json');

$host = "fdb1030.awardspace.net";
$dbname = "4584890_ccr";
$username = "4584890_ccr";
$password = "Sta12bucks.";

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Database connection failed"]));
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'view':
            // Return only messages and dates (no names)
            $result = $conn->query("SELECT message, created_at FROM feedback ORDER BY created_at DESC");
            if (!$result) {
                throw new Exception("Database query failed");
            }
            
            $feedback = [];
            while ($row = $result->fetch_assoc()) {
                $feedback[] = [
                    'message' => $row['message'],
                    'created_at' => $row['created_at']
                ];
            }
            echo json_encode(["status" => "success", "data" => $feedback]);
            break;
            
        case 'add':
            $name = $conn->real_escape_string($_POST['userfullname'] ?? 'Anonymous');
            $message = $conn->real_escape_string($_POST['message'] ?? '');
            
            if (empty($message)) {
                throw new Exception("Feedback message is required");
            }
            
            $stmt = $conn->prepare("INSERT INTO feedback (userfullname, message) VALUES (?, ?)");
            if (!$stmt) {
                throw new Exception("Database preparation failed");
            }
            
            $stmt->bind_param("ss", $name, $message);
            if (!$stmt->execute()) {
                throw new Exception("Failed to submit feedback");
            }
            
            echo json_encode(["status" => "success", "message" => "Feedback submitted"]);
            break;
            
        default:
            throw new Exception("Invalid action");
    }
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}

$conn->close();
?>