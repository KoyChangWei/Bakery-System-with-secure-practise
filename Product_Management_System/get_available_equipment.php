<?php
ob_start();
require_once 'db_connect.php';
ob_clean();
header('Content-Type: application/json');

try {
    $production_date = $_GET['production_date'] ?? date('Y-m-d');
    $exclude_id = $_GET['exclude_id'] ?? null;
    $recipe_id = $_GET['recipe_id'] ?? null;
    
    // Base query to get equipment
    $sql = "SELECT e.equipment_id, e.equipment_name 
            FROM equipment_status e
            WHERE 1=1";
    
    $params = [];
    $types = "";
    
    // If we have a recipe_id, filter by recipe's required equipment
    if ($recipe_id) {
        $sql .= " AND e.equipment_name IN (
            SELECT equipment_tbl FROM recipe_db WHERE recipe_id = ?
        )";
        $params[] = $recipe_id;
        $types .= "i";
    }
    
    // Exclude currently used equipment for the date, except the excluded ID
    $sql .= " AND (e.equipment_id NOT IN (
        SELECT equipment_id 
        FROM production_db 
        WHERE production_date = ?";
    
    if ($exclude_id) {
        $sql .= " AND equipment_id != ?";
        $params[] = $exclude_id;
        $types .= "i";
    }
    
    $sql .= ") OR e.equipment_id = ?)";
    
    $params = array_merge([$production_date], $params);
    $types = "s" . $types;
    
    if ($exclude_id) {
        $params[] = $exclude_id;
        $types .= "i";
    }
    
    $sql .= " ORDER BY e.equipment_name";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Failed to prepare statement: " . $conn->error);
    }
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to execute query: " . $stmt->error);
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
        'data' => $equipment
    ]);

} catch (Exception $e) {
    error_log("Error in get_available_equipment.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?> 