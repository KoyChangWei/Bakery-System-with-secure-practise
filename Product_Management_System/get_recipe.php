<?php
ob_start();
require_once 'db_connect.php';
ob_clean();
header('Content-Type: application/json');

if (!isset($_GET['recipe_id'])) {
    echo json_encode(['success' => false, 'message' => 'Recipe ID is required']);
    exit;
}

try {
    // Get recipe details including ingredients
    $sql = "SELECT r.*, 
            GROUP_CONCAT(
                CONCAT(ri.ingredient_name, '|', ri.quantity, '|', ri.unit_tbl)
                SEPARATOR ';;'
            ) as ingredients_data,
            e.equipment_id, 
            e.equipment_name
            FROM recipe_db r
            LEFT JOIN recipe_ingredients ri ON r.recipe_id = ri.recipe_id
            LEFT JOIN equipment_status e ON e.equipment_name = r.equipment_tbl
            WHERE r.recipe_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $_GET['recipe_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $recipe = $result->fetch_assoc();

    if (!$recipe) {
        throw new Exception("Recipe not found");
    }

    // Process ingredients
    $ingredients = [];
    if ($recipe['ingredients_data']) {
        foreach (explode(';;', $recipe['ingredients_data']) as $ingredient) {
            list($name, $qty, $unit) = explode('|', $ingredient);
            $ingredients[] = [
                'ingredient_name' => $name,
                'quantity' => $qty,
                'unit' => $unit
            ];
        }
    }

    // Get production rate
    $production_rate = 100; // Default value
    $capacity = $production_rate . ' units/hour';

    echo json_encode([
        'success' => true,
        'recipe' => [
            'recipe_id' => $recipe['recipe_id'],
            'recipe_name' => $recipe['recipe_name'],
            'equipment_id' => $recipe['equipment_id'],
            'equipment_name' => $recipe['equipment_name'],
            'ingredients' => $ingredients
        ],
        'production_rate' => $production_rate,
        'production_capacity' => $capacity
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>
