<?php
require_once 'db_connect.php';

// Test database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h2>Testing Database Connection and Staff Query</h2>";

// Test query
$sql = "SELECT * FROM admin_db WHERE role_tbl = 'baker'";
$result = $conn->query($sql);

if ($result) {
    echo "<p>Query executed successfully</p>";
    echo "<p>Number of bakers found: " . $result->num_rows . "</p>";
    
    echo "<h3>Baker Details:</h3>";
    while ($row = $result->fetch_assoc()) {
        echo "<pre>";
        print_r($row);
        echo "</pre>";
    }
} else {
    echo "<p>Query failed: " . $conn->error . "</p>";
}

$conn->close();
?> 