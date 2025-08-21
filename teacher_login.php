<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

$servername = "fdb1030.awardspace.net";  
$username = "4584890_ccr";
$password = "Sta12bucks.";
$dbname = "4584890_ccr";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(["success" => "0", "message" => "Connection failed: " . $e->getMessage()]);
    exit;
}

if (isset($_POST['useremail']) && isset($_POST['userpassword']) && isset($_POST['usertype'])) {
    $useremail = trim($_POST['useremail']);
    $userpassword = trim($_POST['userpassword']);
    $usertype = trim($_POST['usertype']);

    $stmt = $conn->prepare("SELECT * FROM users WHERE useremail = :useremail AND usertype = :usertype");
    $stmt->bindParam(':useremail', $useremail);
    $stmt->bindParam(':usertype', $usertype);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (password_verify($userpassword, $user['userpassword'])) {
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['useremail'] = $user['useremail'];
            $_SESSION['usertype'] = $user['usertype'];

            echo json_encode([
                "success" => "1",
                "message" => "Login successful. Redirecting...",
                "redirect" => "teacher_dashboard.html",
                "details" => $user
            ]);
        } else {
            echo json_encode(["success" => "0", "message" => "Invalid login credentials"]);
        }
    } else {
        echo json_encode(["success" => "0", "message" => "No teacher found with that email"]);
    }
} else {
    echo json_encode(["success" => "0", "message" => "Required fields are missing"]);
}
?>