<?php
$host = "fdb1030.awardspace.net"; // Or your server's DB host
$dbname = "4584890_ccr";
$username = "4584890_ccr";
$password = "Sta12bucks.";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die(json_encode(["success" => "0", "message" => "Database connection failed: " . $e->getMessage()]));
}
?>
