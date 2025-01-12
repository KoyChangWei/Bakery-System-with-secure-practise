<?php
ob_start();
require_once 'db_connect.php';
ob_clean();
header('Content-Type: application/json');

try {
    $production_date = $_GET['production_date'] ?? date('Y-m-d');
    $recipe_id = $_GET['recipe_id'] ?? null;
    
    if (!$recipe_id) {
        throw new Exception("Recipe ID is required");
    }

    // First get the equipment required for this recipe
    $sql = "SELECT equipment_tbl FROM recipe_db WHERE recipe_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $recipe_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $recipe = $result->fetch_assoc();

    if (!$recipe) {
        throw new Exception("Recipe not found");
    }

    // Get the equipment that's both required for the recipe and available
    $equipment_list = explode(',', $recipe['equipment_tbl']);
    $placeholders = str_repeat('?,', count($equipment_list) - 1) . '?';
    
    $sql = "SELECT equipment_id, equipment_name 
            FROM equipment_status 
            WHERE equipment_name IN ($placeholders)
            AND status = 'Available'
            AND equipment_id NOT IN (
                SELECT equipment_id 
                FROM production_db 
                WHERE production_date = ?
            )
            ORDER BY equipment_name";

    $stmt = $conn->prepare($sql);
    
    // Create array of parameters for bind_param
    $types = str_repeat('s', count($equipment_list)) . 's'; // all strings plus one for date
    $params = array_merge($equipment_list, [$production_date]);
    
    // Bind parameters dynamically
    $bind_params = array($types);
    foreach ($params as $key => $value) {
        $bind_params[] = &$params[$key];
    }
    call_user_func_array(array($stmt, 'bind_param'), $bind_params);

    $stmt->execute();
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
        'data' => $equipment,
        'recipe_equipment' => $recipe['equipment_tbl']
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?> 