<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

$servername = "fdb1030.awardspace.net";  
$username = "4584890_ccr";
$password = "Sta12bucks.";
$dbname = "4584890_ccr";

try {
    // Create connection
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(["success" => "0", "message" => "Connection failed: " . $e->getMessage()]);
    exit;
}

// Check if useremail and userpassword are provided
if (isset($_POST['useremail']) && isset($_POST['userpassword'])) {
    $useremail = trim($_POST['useremail']);
    $userpassword = trim($_POST['userpassword']);

    // Debugging: Log received data
    error_log("Received useremail: " . $useremail);
    error_log("Received userpassword: " . $userpassword);

    // Use prepared statements to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM users WHERE useremail = :useremail");
    $stmt->bindParam(':useremail', $useremail);
    $stmt->execute();

    // Check if the user exists
    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Debugging: Log stored hashed password
        error_log("Stored hashed password: " . $user['userpassword']);

        // Verify the password
        if (password_verify($userpassword, $user['userpassword'])) {
            // Password is correct
            error_log("Password verification: Success");

            // Check if user is a Student
            if ($user['usertype'] === 'Student') {
                // Start a session and store the user's ID, email, and usertype
                session_start();
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['useremail'] = $user['useremail'];
                $_SESSION['usertype'] = $user['usertype'];

                echo json_encode([
                    "success" => "1",
                    "message" => "Login successful. Redirecting to student dashboard...",
                    "redirect" => "student_dashboard.php",
                    "details" => $user
                ]);
            } else {
                // User is not a Student
                error_log("User is not a Student");
                echo json_encode([
                    "success" => "0", 
                    "message" => "Access denied. This dashboard is for Students only."
                ]);
            }
        } else {
            // Password is incorrect
            error_log("Password verification: Failed");
            echo json_encode(["success" => "0", "message" => "Invalid login credentials"]);
        }
    } else {
        // User does not exist
        error_log("User not found");
        echo json_encode(["success" => "0", "message" => "Invalid login credentials"]);
    }
} else {
    echo json_encode(["success" => "0", "message" => "useremail and userpassword are required."]);
}
?>