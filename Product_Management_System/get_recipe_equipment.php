<?php
ob_start();
require_once 'db_connect.php';
ob_clean();
header('Content-Type: application/json');

try {
    if (!isset($_GET['recipe_id']) || !isset($_GET['production_date'])) {
        throw new Exception("Missing required parameters");
    }

    $recipe_id = $_GET['recipe_id'];
    $production_date = $_GET['production_date'];

    // Get required equipment for the recipe and check availability
    $sql = "SELECT DISTINCT e.equipment_id, e.equipment_name 
            FROM equipment_status e
            INNER JOIN recipe_db r ON FIND_IN_SET(e.equipment_name, r.equipment_tbl)
            WHERE r.recipe_id = ?
            AND e.status = 'Available'
            AND e.equipment_id NOT IN (
                SELECT p.equipment_id
                FROM production_db p
                WHERE p.production_date = ?
            )
            ORDER BY e.equipment_name";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("is", $recipe_id, $production_date);
    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    $result = $stmt->get_result();
    $equipment = [];
    
    while ($row = $result->fetch_assoc()) {
        $equipment[] = [
            'equipment_id' => $row['equipment_id'],
            'equipment_name' => $row['equipment_name']
        ];
    }

    echo json_encode([
        'success' => true,
        'equipment' => $equipment,
        'recipe_id' => $recipe_id,
        'date' => $production_date
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?> 