<?php
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

// Database configuration
$host = "fdb1030.awardspace.net";
$dbname = "4584890_ccr";
$username = "4584890_ccr";
$password = "Sta12bucks.";

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]));
}

// Start session for write operations
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    session_start();
    if (!isset($_SESSION['useremail'])) {
        http_response_code(401);
        die(json_encode(['error' => 'Unauthorized']));
    }
}

try {
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            if (isset($_GET['user_id'])) {
                // Get single user
                $stmt = $conn->prepare("SELECT id, useremail, username, userfullname, userphone, usertype FROM users WHERE id = ?");
                $stmt->bind_param("i", $_GET['user_id']);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows === 0) {
                    http_response_code(404);
                    die(json_encode(['error' => 'User not found']));
                }
                
                echo json_encode($result->fetch_assoc());
            } else {
                // List all users
                $stmt = $conn->prepare("SELECT id, useremail, username, userfullname, userphone, usertype FROM users");
                $stmt->execute();
                $result = $stmt->get_result();
                $users = $result->fetch_all(MYSQLI_ASSOC);
                echo json_encode($users ?: []);
            }
            break;

        case 'POST':
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                http_response_code(400);
                die(json_encode(['error' => 'Invalid JSON data']));
            }
            
            $required = ['useremail', 'username', 'userfullname', 'userphone', 'usertype', 'userpassword'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    http_response_code(400);
                    die(json_encode(['error' => "Field $field is required"]));
                }
            }
            
            // Hash password securely
            $hashedPassword = password_hash($data['userpassword'], PASSWORD_DEFAULT);
            
            $stmt = $conn->prepare("INSERT INTO users (useremail, username, userfullname, userphone, userpassword, usertype) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", 
                $data['useremail'],
                $data['username'],
                $data['userfullname'],
                $data['userphone'],
                $hashedPassword,
                $data['usertype']
            );
            
            if (!$stmt->execute()) {
                throw new Exception('Database error: ' . $conn->error);
            }
            
            echo json_encode([
                'success' => true,
                'user_id' => $conn->insert_id,
                'message' => 'User created successfully'
            ]);
            break;

        case 'PUT':
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                http_response_code(400);
                die(json_encode(['error' => 'Invalid JSON data']));
            }
            
            $required = ['user_id', 'useremail', 'username', 'userfullname', 'userphone', 'usertype'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    http_response_code(400);
                    die(json_encode(['error' => "Field $field is required"]));
                }
            }
            
            // Check if password is being updated
            $updatePassword = !empty($data['userpassword']);
            $query = "UPDATE users SET useremail = ?, username = ?, userfullname = ?, userphone = ?, usertype = ?";
            $types = "sssss";
            $params = [
                $data['useremail'],
                $data['username'],
                $data['userfullname'],
                $data['userphone'],
                $data['usertype']
            ];
            
            if ($updatePassword) {
                $query .= ", userpassword = ?";
                $types .= "s";
                $hashedPassword = password_hash($data['userpassword'], PASSWORD_DEFAULT);
                $params[] = $hashedPassword;
            }
            
            $query .= " WHERE id = ?";
            $types .= "i";
            $params[] = $data['user_id'];
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param($types, ...$params);
            
            if (!$stmt->execute()) {
                throw new Exception('Database error: ' . $conn->error);
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'User updated successfully'
            ]);
            break;

        case 'DELETE':
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                http_response_code(400);
                die(json_encode(['error' => 'Invalid JSON data']));
            }
            
            if (empty($data['user_id'])) {
                http_response_code(400);
                die(json_encode(['error' => 'User ID is required']));
            }
            
            // Prevent deleting yourself
            if ($data['user_id'] == $_SESSION['user_id']) {
                http_response_code(400);
                die(json_encode(['error' => 'You cannot delete your own account']));
            }
            
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $data['user_id']);
            
            if (!$stmt->execute()) {
                throw new Exception('Database error: ' . $conn->error);
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'User deleted successfully'
            ]);
            break;

        default:
            http_response_code(405);
            die(json_encode(['error' => 'Method not allowed']));
    }
} catch (Exception $e) {
    http_response_code(500);
    die(json_encode(['error' => $e->getMessage()]));
} finally {
    $conn->close();
}
?>