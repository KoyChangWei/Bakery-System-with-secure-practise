<?php
// Turn on error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Start output buffering to catch any unwanted output
ob_start();

session_start();
require_once 'db_connect.php';

// Clear any existing output
ob_clean();

header('Content-Type: application/json');

// Debug: Log POST data
error_log("POST data received: " . print_r($_POST, true));

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $conn->begin_transaction();

        // Validate required fields
        if (empty($_POST['product']) || empty($_POST['production_date']) || 
            empty($_POST['order_volume']) || empty($_POST['equipment_id']) || 
            empty($_POST['staff']) || empty($_POST['capacity'])) {
            throw new Exception("All fields are required");
        }

        // Insert production schedule
        $sql = "INSERT INTO production_db (
                    recipe_id, 
                    production_date, 
                    order_volume,
                    capacity, 
                    equipment_id, 
                    staff_availability, 
                    created_by, 
                    created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $staff = implode(',', $_POST['staff']);
        
        $stmt->bind_param(
            "isiiiis",
            $_POST['product'],
            $_POST['production_date'],
            $_POST['order_volume'],
            $_POST['capacity'], // Use the user-input capacity
            $_POST['equipment_id'],
            $staff,
            $_SESSION['user_id']
        );

        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        $conn->commit();
        echo json_encode(['success' => true]);

    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

$conn->close();

// End output buffering and send response
ob_end_flush();
?> 