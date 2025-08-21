<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

require './connect.php';

$response = array();

if (isset($_POST['useremail']) && isset($_POST['current_password']) && isset($_POST['new_password'])) {
    $useremail = $_POST['useremail'];
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];

    // Fetch the user's current hashed password
    $stmt = $conn->prepare("SELECT userpassword FROM users WHERE useremail = :useremail");
    $stmt->bindParam(':useremail', $useremail);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $hashedPassword = $user['userpassword'];

        // Verify the current password
        if (password_verify($currentPassword, $hashedPassword)) {
            // Hash the new password
            $newHashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

            // Update the password in the database
            $updateStmt = $conn->prepare("UPDATE users SET userpassword = :new_password WHERE useremail = :useremail");
            $updateStmt->bindParam(':new_password', $newHashedPassword);
            $updateStmt->bindParam(':useremail', $useremail);
            $updateStmt->execute();

            $response['success'] = "1";
            $response['message'] = "Password changed successfully!";
        } else {
            $response['success'] = "0";
            $response['message'] = "Current password is incorrect.";
        }
    } else {
        $response['success'] = "0";
        $response['message'] = "User not found.";
    }
} else {
    $response['success'] = "0";
    $response['message'] = "Required fields are missing.";
}

echo json_encode($response);
?>