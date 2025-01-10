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

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'supervisor') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

try {
    $conn->begin_transaction();

    // Debug: Log the values we're trying to insert
    error_log("Inserting values - Product ID: {$_POST['product']}, Date: {$_POST['production_date']}, Volume: {$_POST['order_volume']}");

    // Insert into production_db table (changed from production_schedule)
    $sql = "INSERT INTO production_db (
        recipe_id,
        production_date,
        order_volume,
        capacity,
        staff_availability,
        equipment_id,
        created_by,
        created_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    // Set capacity equal to order volume for now
    $capacity = $_POST['order_volume'];
    // No need to validate maximum staff count
    if (empty($_POST['staff'])) {
        throw new Exception("At least one staff member must be assigned");
    }
    
    $staff_availability = implode(',', $_POST['staff']); // Convert staff array to comma-separated string
    
    $stmt->bind_param(
        "isiisii",
        $_POST['product'],
        $_POST['production_date'],
        $_POST['order_volume'],
        $capacity,
        $staff_availability,
        $_POST['equipment_id'],
        $_SESSION['user_id']
    );

    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    $production_id = $conn->insert_id;
    error_log("Successfully inserted production record with ID: " . $production_id);

    $conn->commit();
    echo json_encode([
        'success' => true, 
        'message' => 'Schedule saved successfully',
        'debug' => [
            'post_data' => $_POST,
            'production_id' => $production_id
        ]
    ]);

} catch (Exception $e) {
    $conn->rollback();
    error_log("Schedule save error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage(),
        'debug' => [
            'post_data' => $_POST,
            'error' => $e->getMessage()
        ]
    ]);
}

// End output buffering and send response
ob_end_flush();
$conn->close();
?> 