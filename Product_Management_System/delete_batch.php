<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && $_SESSION['user_role'] == 'baker') {
    try {
        // Prepare the DELETE statement
        $sql = "DELETE FROM batch_db WHERE batch_no_tbl = ?";
        $stmt = $conn->prepare($sql);
        
        // Bind parameter
        $stmt->bind_param("s", $_POST['batch_id']);
        
        // Execute the statement
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            throw new Exception($stmt->error);
        }
        
        $stmt->close();
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

$conn->close();
?> 