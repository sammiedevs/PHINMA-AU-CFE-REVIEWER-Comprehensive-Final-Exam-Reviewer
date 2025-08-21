<?php
session_start();
header('Content-Type: application/json');
require './connect.php';

$response = ['success' => 0, 'message' => ''];

try {
    // 1. Verify user is logged in
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['useremail'])) {
        $response['message'] = "User not logged in";
        echo json_encode($response);
        exit();
    }

    $user_id = $_SESSION['user_id'];
    $user_email = $_SESSION['useremail'];

    // 2. Get and validate JSON input
    $json = file_get_contents('php://input');
    if (empty($json)) {
        $response['message'] = "No data received";
        echo json_encode($response);
        exit();
    }

    $data = json_decode($json, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $response['message'] = "Invalid JSON data";
        echo json_encode($response);
        exit();
    }

    // 3. Build dynamic update query based on provided fields
    $updateFields = [];
    $params = [':user_id' => $user_id]; // Using session user_id only
    
    if (isset($data['userfullname'])) {
        $updateFields[] = "userfullname = :userfullname";
        $params[':userfullname'] = htmlspecialchars($data['userfullname']);
    }
    
    if (isset($data['username'])) {
        $updateFields[] = "username = :username";
        $params[':username'] = htmlspecialchars($data['username']);
    }
    
    if (isset($data['userphone'])) {
        $updateFields[] = "userphone = :userphone";
        $params[':userphone'] = htmlspecialchars($data['userphone']);
    }

    // 4. Check if at least one field is being updated
    if (empty($updateFields)) {
        $response['message'] = "No fields to update";
        echo json_encode($response);
        exit();
    }

    // 5. Execute update
    $query = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = :user_id";
    $stmt = $pdo->prepare($query);
    $success = $stmt->execute($params);

    if ($success) {
        $response['success'] = 1;
        $response['message'] = "Profile updated successfully";
        
        // Update session data if changed
        if (isset($data['username'])) {
            $_SESSION['username'] = $data['username'];
        }
        if (isset($data['userfullname'])) {
            $_SESSION['userfullname'] = $data['userfullname'];
        }
    } else {
        $response['message'] = "No changes made to profile";
    }

} catch (PDOException $e) {
    $response['message'] = "Database error: " . $e->getMessage();
} catch (Exception $e) {
    $response['message'] = "Error: " . $e->getMessage();
}

echo json_encode($response);
?>