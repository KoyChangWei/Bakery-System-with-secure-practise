<?php
require_once 'db_connect.php';
header('Content-Type: application/json');

if (!isset($_GET['recipe_id'])) {
    echo json_encode(['success' => false, 'message' => 'Recipe ID is required']);
    exit;
}

try {
    // Get recipe details including ingredients and equipment
    $sql = "SELECT r.*, 
            GROUP_CONCAT(
                CONCAT(ri.ingredient_name, '|', ri.quantity, '|', ri.unit_tbl)
                SEPARATOR ';;'
            ) as ingredients_data,
            e.status as equipment_status
            FROM recipe_db r
            LEFT JOIN recipe_ingredients ri ON r.recipe_id = ri.recipe_id
            LEFT JOIN equipment_status e ON r.equipment_tbl = e.equipment_name
            WHERE r.recipe_id = ?
            GROUP BY r.recipe_id";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $_GET['recipe_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $recipe = $result->fetch_assoc();

    if (!$recipe) {
        throw new Exception("Recipe not found");
    }

    // Calculate ingredients for order volume if provided
    $order_volume = $_GET['volume'] ?? 1;
    $ingredients = [];
    
    if ($recipe['ingredients_data']) {
        foreach (explode(';;', $recipe['ingredients_data']) as $ingredient) {
            list($name, $qty, $unit) = explode('|', $ingredient);
            $ingredients[] = [
                'ingredient_name' => $name,
                'quantity' => $qty * $order_volume,
                'unit' => $unit
            ];
        }
    }

    // Get available staff (bakers)
    $staff_sql = "SELECT admin_id, name_tbl 
                  FROM admin_db 
                  WHERE role_tbl = 'baker' 
                  AND admin_id NOT IN (
                      SELECT SUBSTRING_INDEX(SUBSTRING_INDEX(staff_availability, ',', n.n), ',', -1) as staff_id
                      FROM production_db 
                      WHERE production_date = ? 
                  )";
    
    $staff_stmt = $conn->prepare($staff_sql);
    $production_date = $_GET['production_date'] ?? date('Y-m-d');
    $staff_stmt->bind_param("s", $production_date);
    $staff_stmt->execute();
    $available_staff = $staff_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Calculate production capacity based on equipment and staff
    $capacity = min(
        $order_volume * 1.2, // 20% buffer
        count($available_staff) * 100 // Example: each staff can handle 100 units
    );

    echo json_encode([
        'success' => true,
        'recipe' => [
            'recipe_id' => $recipe['recipe_id'],
            'recipe_name' => $recipe['recipe_name'],
            'equipment' => $recipe['equipment_tbl'],
            'equipment_status' => $recipe['equipment_status'],
            'ingredients' => $ingredients,
            'available_staff' => $available_staff,
            'suggested_capacity' => $capacity
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
