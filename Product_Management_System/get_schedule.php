<?php
session_start();
require_once 'db_connect.php';

if (isset($_GET['id']) && $_SESSION['user_role'] == 'supervisor') {
    try {
        // Prepare the SELECT statement
        $sql = "SELECT * FROM production_db WHERE production_id = ?";
        $stmt = $conn->prepare($sql);
        
        // Bind parameter
        $stmt->bind_param("i", $_GET['id']);
        
        // Execute the statement
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            echo json_encode([
                'success' => true,
                'data' => $row
            ]);
        } else {
            throw new Exception("Schedule not found");
        }
        
        $stmt->close();
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
}

$conn->close();
?> 