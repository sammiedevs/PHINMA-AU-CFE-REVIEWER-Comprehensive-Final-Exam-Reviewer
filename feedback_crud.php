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
    die(json_encode([
        'success' => false,
        'error' => 'Database connection failed',
        'details' => $conn->connect_error
    ]));
}

session_start();
if (!isset($_SESSION['useremail'])) {
    die(json_encode([
        'success' => false,
        'error' => 'Unauthorized - Please login first'
    ]));
}

try {
    // First check if we can join with users table
    $canJoinWithUsers = false;
    $joinCheck = $conn->query("SHOW COLUMNS FROM feedback LIKE 'user_id'");
    if ($joinCheck->num_rows > 0) {
        $canJoinWithUsers = true;
    }

    if ($canJoinWithUsers) {
        // Get feedback with user info where available
        $query = "
            SELECT 
                IFNULL(u.id, 0) AS user_id,
                IFNULL(u.userfullname, 'Anonymous') AS userfullname,
                f.message,
                DATE_FORMAT(f.created_at, '%Y-%m-%d %H:%i') AS created_at
            FROM feedback f
            LEFT JOIN users u ON f.user_id = u.id
            ORDER BY f.created_at DESC
        ";
    } else {
        // Fallback if no user_id column exists
        $query = "
            SELECT 
                0 AS user_id,
                'Anonymous' AS userfullname,
                message,
                DATE_FORMAT(created_at, '%Y-%m-%d %H:%i') AS created_at
            FROM feedback
            ORDER BY created_at DESC
        ";
    }

    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception('Query failed: ' . $conn->error);
    }

    $feedback = $result->fetch_all(MYSQLI_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $feedback
    ]);

} catch (Exception $e) {
    die(json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'query_error' => $conn->error ?? null
    ]));
} finally {
    $conn->close();
}
?>