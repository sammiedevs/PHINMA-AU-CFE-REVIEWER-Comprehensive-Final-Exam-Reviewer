<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
$host = "fdb1030.awardspace.net";
$dbname = "4584890_ccr";
$username = "4584890_ccr";
$password = "Sta12bucks.";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode([
        'success' => '0',
        'message' => 'Database connection failed: ' . $e->getMessage()
    ]);
    exit;
}

// Get raw POST data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// If json_decode fails, try regular POST
if ($data === null) {
    $data = $_POST;
}

// Required fields
$required = ['userfullname', 'useremail', 'username', 'userphone', 'userpassword', 'userpassword1'];
foreach ($required as $field) {
    if (empty($data[$field])) {
        echo json_encode([
            'success' => '0',
            'message' => "$field is required"
        ]);
        exit;
    }
}

// Validate email
if (!filter_var($data['useremail'], FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'success' => '0',
        'message' => 'Invalid email format'
    ]);
    exit;
}

// Check password match
if ($data['userpassword'] !== $data['userpassword1']) {
    echo json_encode([
        'success' => '0',
        'message' => 'Passwords do not match'
    ]);
    exit;
}

// Check if email exists
$stmt = $conn->prepare("SELECT useremail FROM users WHERE useremail = ?");
$stmt->execute([$data['useremail']]);
if ($stmt->rowCount() > 0) {
    echo json_encode([
        'success' => '0',
        'message' => 'Email already exists'
    ]);
    exit;
}

// Check if username exists
$stmt = $conn->prepare("SELECT username FROM users WHERE username = ?");
$stmt->execute([$data['username']]);
if ($stmt->rowCount() > 0) {
    echo json_encode([
        'success' => '0',
        'message' => 'Username already exists'
    ]);
    exit;
}

// Hash password
$hashedPassword = password_hash($data['userpassword'], PASSWORD_BCRYPT);

// Insert new user
try {
    $stmt = $conn->prepare("INSERT INTO users 
        (userfullname, useremail, username, userphone, userpassword, usertype) 
        VALUES (?, ?, ?, ?, ?, 'Student')");
    
    $stmt->execute([
        $data['userfullname'],
        $data['useremail'],
        $data['username'],
        $data['userphone'],
        $hashedPassword
    ]);
    
    echo json_encode([
        'success' => '1',
        'message' => 'Registration successful!'
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => '0',
        'message' => 'Registration failed: ' . $e->getMessage()
    ]);
}
?>