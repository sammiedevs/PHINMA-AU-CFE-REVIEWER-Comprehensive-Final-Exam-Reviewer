<?php
// Ensure no output before headers
ob_start();

// Set proper error reporting
error_reporting(0); // Turn off error display (log them instead)
ini_set('display_errors', 0);

// Database configuration
$servername = "fdb1030.awardspace.net";  
$username = "4584890_ccr";
$password = "Sta12bucks.";
$dbname = "4584890_ccr";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    // Log error instead of outputting
    error_log("Database connection failed: " . $e->getMessage());
    sendJsonResponse(0, "Database connection error");
    exit;
}

// Process login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $required = ['useremail', 'userpassword', 'usertype'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            sendJsonResponse(0, "Missing required field: $field");
            exit;
        }
    }

    $email = trim($_POST['useremail']);
    $password = trim($_POST['userpassword']);
    $usertype = trim($_POST['usertype']);

    try {
        $stmt = $conn->prepare("SELECT * FROM users WHERE useremail = :email AND usertype = :usertype");
        $stmt->execute([':email' => $email, ':usertype' => $usertype]);
        
        if ($stmt->rowCount() === 1) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (password_verify($password, $user['userpassword'])) {
                session_start();
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['useremail'] = $user['useremail'];
                $_SESSION['usertype'] = $user['usertype'];
                
                sendJsonResponse(1, "Login successful", ["redirect" => "admin_user_manage.html"]);
            } else {
                sendJsonResponse(0, "Invalid credentials");
            }
        } else {
            sendJsonResponse(0, "No admin found with that email");
        }
    } catch(PDOException $e) {
        error_log("Login query failed: " . $e->getMessage());
        sendJsonResponse(0, "Login processing error");
    }
} else {
    sendJsonResponse(0, "Invalid request method");
}

// Helper function for JSON responses
function sendJsonResponse($success, $message, $additionalData = []) {
    ob_end_clean(); // Clean any previous output
    header('Content-Type: application/json');
    die(json_encode(array_merge([
        'success' => $success,
        'message' => $message
    ], $additionalData)));
}
?>