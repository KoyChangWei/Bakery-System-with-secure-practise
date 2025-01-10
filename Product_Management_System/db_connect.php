<?php
$host = "localhost:3308";
$user = "root";
$password = ""; // Replace with the password you just set
$dbname = "bakery_db";

try {
    $conn = new mysqli($host, $user,$password, $dbname);
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    echo "Database connected successfully!";
} catch (Exception $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
