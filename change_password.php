<?php
header('Content-Type: application/json');
require './connect.php';

$inputData = json_decode(file_get_contents("php://input"), true);
if ($inputData) {
    $_POST = $inputData;
}

$response = array();

$requiredFields = ['useremail', 'current_password', 'new_password'];
foreach ($requiredFields as $field) {
    if (!isset($_POST[$field])) {
        $response['success'] = 0;
        $response['message'] = "Missing required fields";
        echo json_encode($response);
        exit();
    }
}

$email = $_POST['useremail'];
$currentPassword = $_POST['current_password'];
$newPassword = $_POST['new_password'];

try {
    // First verify current password
    $stmt = $pdo->prepare("SELECT userpassword FROM users WHERE useremail = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        $response['success'] = 0;
        $response['message'] = "User not found";
    } elseif (!password_verify($currentPassword, $user['userpassword'])) {
        $response['success'] = 0;
        $response['message'] = "Current password is incorrect";
    } else {
        // Update password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $updateStmt = $pdo->prepare("UPDATE users SET userpassword = ? WHERE useremail = ?");
        $updateStmt->execute([$hashedPassword, $email]);

        if ($updateStmt->rowCount() > 0) {
            $response['success'] = 1;
            $response['message'] = "Password updated successfully";
        } else {
            $response['success'] = 0;
            $response['message'] = "Failed to update password";
        }
    }
} catch (PDOException $e) {
    $response['success'] = 0;
    $response['message'] = "Database error: " . $e->getMessage();
}

echo json_encode($response);
?>