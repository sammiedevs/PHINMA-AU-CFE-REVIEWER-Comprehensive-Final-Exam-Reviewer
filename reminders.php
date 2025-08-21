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
            $result = $conn->query("SELECT event_id, event_name, event_start_date FROM calendar_event_master");
            if (!$result) {
                throw new Exception("Database query failed: " . $conn->error);
            }
            
            $events = [];
            while ($row = $result->fetch_assoc()) {
                $events[] = [
                    'event_id' => $row['event_id'],
                    'event_name' => $row['event_name'],
                    'event_date' => $row['event_start_date'] // Using event_start_date as the date
                ];
            }
            echo json_encode(["status" => "success", "data" => $events]);
            break;
            
        case 'add':
            $name = $conn->real_escape_string($_POST['event_name'] ?? '');
            $date = $conn->real_escape_string($_POST['event_date'] ?? '');
            
            if (empty($name) || empty($date)) {
                throw new Exception("Event name and date are required");
            }
            
            $stmt = $conn->prepare("INSERT INTO calendar_event_master (event_name, event_start_date, event_end_date) VALUES (?, ?, ?)");
            if (!$stmt) {
                throw new Exception("Database preparation failed: " . $conn->error);
            }
            
            // Using the same date for both start and end since we're doing simple reminders
            $stmt->bind_param("sss", $name, $date, $date);
            if (!$stmt->execute()) {
                throw new Exception("Failed to add reminder: " . $stmt->error);
            }
            
            echo json_encode(["status" => "success", "message" => "Reminder added successfully"]);
            break;
            
        case 'delete':
            $id = $conn->real_escape_string($_POST['event_id'] ?? '');
            
            $stmt = $conn->prepare("DELETE FROM calendar_event_master WHERE event_id = ?");
            if (!$stmt) {
                throw new Exception("Database preparation failed: " . $conn->error);
            }
            
            $stmt->bind_param("s", $id);
            if (!$stmt->execute()) {
                throw new Exception("Failed to delete reminder: " . $stmt->error);
            }
            
            echo json_encode(["status" => "success", "message" => "Reminder deleted successfully"]);
            break;
            
        default:
            throw new Exception("Invalid action");
    }
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}

$conn->close();
?>