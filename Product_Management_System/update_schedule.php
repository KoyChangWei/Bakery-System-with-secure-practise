<?php
session_start();
require_once 'db_connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn->begin_transaction();
        
        $sql = "UPDATE production_db SET 
                recipe_id = ?,
                production_date = ?,
                order_volume = ?,
                capacity = ?,
                equipment_id = ?,
                staff_availability = ?,
                updated_at = NOW()
                WHERE production_id = ?";

        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        // Convert staff array to comma-separated string
        $staff = implode(',', $_POST['staff']);
        
        $stmt->bind_param(
            "isiiisi",
            $_POST['product'],
            $_POST['production_date'],
            $_POST['order_volume'],
            $_POST['capacity'],
            $_POST['equipment_id'],
            $staff,
            $_POST['schedule_id']
        );
        
        if ($stmt->execute()) {
            $conn->commit();
            echo json_encode([
                'success' => true,
                'message' => 'Schedule updated successfully'
            ]);
        } else {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}

// Close statement and connection
if (isset($stmt)) {
    $stmt->close();
}
$conn->close();
?>