<?php
session_start();
header('Content-Type: application/json');

require './connect.php';

$response = ['success' => 0, 'message' => ''];

try {
    // Check if user is logged in
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['useremail'])) {
        $response['message'] = "User not logged in";
        echo json_encode($response);
        exit();
    }

    $user_id = $_SESSION['user_id'];
    $user_email = $_SESSION['useremail'];

    // Get user profile data
    $stmt = $pdo->prepare("SELECT userfullname, username, useremail, userphone FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $response['success'] = 1;
        $response['user'] = $user;
    } else {
        $response['message'] = "User not found in database";
    }
} catch (PDOException $e) {
    $response['message'] = "Database error: " . $e->getMessage();
} catch (Exception $e) {
    $response['message'] = "Error: " . $e->getMessage();
}

echo json_encode($response);
?>