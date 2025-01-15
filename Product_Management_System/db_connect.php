<?php
$servername = "localhost:3307";
$username = "root";
$password = "";
$dbname = "bakery_db(4)";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    error_log("Connection failed: " . $conn->connect_error);
    die("Connection failed: " . $conn->connect_error);
} else {
    // Log success to error log instead of outputting to response
    error_log("Database connected successfully!");
}
