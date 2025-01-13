<?php
// Start output buffering to catch any unwanted output
ob_start();

require_once 'db_connect.php';

// Clear any previous output
ob_clean();

header('Content-Type: application/json');

try {
    // Get the production ID from query params
    $production_id = $_GET['id'] ?? null;
    
    if (!$production_id) {
        throw new Exception("Production ID is required");
    }

    $sql = "SELECT p.*, r.recipe_name, 
            GROUP_CONCAT(a.name_tbl) as staff_names
            FROM production_db p
            JOIN recipe_db r ON p.recipe_id = r.recipe_id
            LEFT JOIN admin_db a ON FIND_IN_SET(a.admin_id, p.staff_availability)
            WHERE p.production_id = ?
            GROUP BY p.production_id";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("i", $production_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    error_log("Production ID: " . $production_id);
    error_log("Production data: " . print_r($data, true));

    echo json_encode([
        'success' => true,
        'data' => $data,
        'debug' => [
            'production_id' => $production_id
        ]
    ]);

} catch (Exception $e) {
    error_log("Error in get_production_details: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>