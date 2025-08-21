<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start output buffering to catch any stray output
ob_start();

try {
    // Database connection
    $host = "fdb1030.awardspace.net";
    $dbname = "4584890_ccr";
    $username = "4584890_ccr";
    $password = "Sta12bucks.";
    
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get POST data
    $data = $_POST;

    // Validate required fields
    $required = ['userfullname', 'useremail', 'username', 'userphone', 'userpassword', 'userpassword1'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            throw new Exception("$field is required");
        }
    }

    // Validate email
    if (!filter_var($data['useremail'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Invalid email format");
    }

    // Check password match
    if ($data['userpassword'] !== $data['userpassword1']) {
        throw new Exception("Passwords do not match");
    }

    // Check if email exists
    $stmt = $conn->prepare("SELECT useremail FROM users WHERE useremail = ?");
    $stmt->execute([$data['useremail']]);
    if ($stmt->rowCount() > 0) {
        throw new Exception("Email already exists");
    }

    // Check if username exists
    $stmt = $conn->prepare("SELECT username FROM users WHERE username = ?");
    $stmt->execute([$data['username']]);
    if ($stmt->rowCount() > 0) {
        throw new Exception("Username already exists");
    }

    // Hash password and insert
    $hashedPassword = password_hash($data['userpassword'], PASSWORD_BCRYPT);
    $stmt = $conn->prepare("INSERT INTO users (userfullname, useremail, username, userphone, userpassword, usertype) VALUES (?, ?, ?, ?, ?, 'Student')");
    $stmt->execute([
        $data['userfullname'],
        $data['useremail'],
        $data['username'],
        $data['userphone'],
        $hashedPassword
    ]);

    $response = [
        'success' => '1',
        'message' => 'Registration successful!'
    ];

} catch (Exception $e) {
    $response = [
        'success' => '0',
        'message' => $e->getMessage()
    ];
}

// Clear any unexpected output
ob_end_clean();

// Send JSON response
echo json_encode($response);
exit;
?>