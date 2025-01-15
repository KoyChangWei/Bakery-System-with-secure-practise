<?php
require_once 'db_connect.php';
require_once 'includes/security.php';
header('Content-Type: application/json');

try {
    // Initialize Security class
    $security = new Security();

    // Validate and sanitize recipe name
    $recipe_name = isset($_POST['recipe_name']) ? $_POST['recipe_name'] : '';
    $recipe_name = $security->validateRecipeInput($recipe_name);
    if ($recipe_name === false) {
        throw new Exception("Invalid recipe name - only letters, numbers, and basic punctuation allowed");
    }

    // Validate and sanitize equipment
    $equipment = isset($_POST['equipment_tbl']) ? $_POST['equipment_tbl'] : '';
    $equipment = $security->validateRecipeInput($equipment);
    if ($equipment === false) {
        throw new Exception("Invalid equipment name - only letters, numbers, and basic punctuation allowed");
    }

    // Start transaction
    $conn->begin_transaction();

    // Validate ingredients
    if (!isset($_POST['ingredient_name']) || !isset($_POST['quantity']) || !isset($_POST['unit'])) {
        throw new Exception("Ingredients data is incomplete");
    }

    $ingredient_names = $_POST['ingredient_name'];
    $quantities = $_POST['quantity'];
    $units = $_POST['unit'];

    // Validate arrays have same length
    if (count($ingredient_names) !== count($quantities) || count($quantities) !== count($units)) {
        throw new Exception("Invalid ingredients data format");
    }

    // Validate each ingredient
    foreach ($ingredient_names as $index => $name) {
        // Validate ingredient name
        $validated_name = $security->validateRecipeInput($name, 'ingredient');
        if ($validated_name === false) {
            throw new Exception("Invalid ingredient name at position " . ($index + 1) . " - only letters, numbers, and basic punctuation allowed");
        }

        // Validate quantity
        $validated_qty = $security->validateRecipeInput($quantities[$index], 'quantity');
        if ($validated_qty === false) {
            throw new Exception("Invalid quantity at position " . ($index + 1) . " - must be a positive number with up to 2 decimal places");
        }

        // Validate unit
        $validated_unit = $security->validateRecipeInput($units[$index], 'unit');
        if ($validated_unit === false) {
            throw new Exception("Invalid unit at position " . ($index + 1) . " - must be one of: g, kg, ml, L, pcs, cups, tbsp, tsp");
        }
    }

    // Validate preparation steps
    if (!isset($_POST['preparation_steps'])) {
        throw new Exception("Preparation steps are required");
    }

    $steps = $_POST['preparation_steps'];
    $sanitized_steps = [];

    foreach ($steps as $index => $step) {
        // Validate step
        $validated_step = $security->validateRecipeInput($step, 'step');
        if ($validated_step === false) {
            throw new Exception("Invalid preparation step at position " . ($index + 1) . " - only letters, numbers, and basic punctuation allowed");
        }
        $sanitized_steps[] = $validated_step;
    }

    // Combine steps with proper formatting
    $preparation_steps = implode("\n", array_map(function($index, $step) {
        return ($index + 1) . ". " . $step;
    }, array_keys($sanitized_steps), $sanitized_steps));

    // Insert or update recipe
    if (isset($_POST['recipe_id']) && !empty($_POST['recipe_id'])) {
        // Update existing recipe
        $recipe_id = filter_var($_POST['recipe_id'], FILTER_VALIDATE_INT);
        if (!$recipe_id) {
            throw new Exception("Invalid recipe ID");
        }

        $stmt = $conn->prepare("UPDATE recipe_db SET recipe_name = ?, equipment_tbl = ?, preparation_step_tbl = ? WHERE recipe_id = ?");
        $stmt->bind_param("sssi", $recipe_name, $equipment, $preparation_steps, $recipe_id);
        $stmt->execute();

        // Delete existing ingredients
        $stmt = $conn->prepare("DELETE FROM recipe_ingredients WHERE recipe_id = ?");
        $stmt->bind_param("i", $recipe_id);
        $stmt->execute();
    } else {
        // Insert new recipe
        $stmt = $conn->prepare("INSERT INTO recipe_db (recipe_name, equipment_tbl, preparation_step_tbl) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $recipe_name, $equipment, $preparation_steps);
        $stmt->execute();
        $recipe_id = $conn->insert_id;
    }

    // Insert ingredients
    $ingredient_stmt = $conn->prepare("INSERT INTO recipe_ingredients (recipe_id, ingredient_name, quantity, unit_tbl) VALUES (?, ?, ?, ?)");
    
    foreach ($ingredient_names as $index => $name) {
        $sanitized_name = $security->sanitizeInput($name);
        $sanitized_qty = filter_var($quantities[$index], FILTER_VALIDATE_FLOAT);
        $sanitized_unit = $security->sanitizeInput($units[$index]);
        
        $ingredient_stmt->bind_param("isds", $recipe_id, $sanitized_name, $sanitized_qty, $sanitized_unit);
        $ingredient_stmt->execute();
    }

    // Commit transaction
    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Recipe saved successfully'
    ]);

} catch (Exception $e) {
    // Rollback on error
    if (isset($conn)) {
        $conn->rollback();
    }
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

// Close connections
if (isset($ingredient_stmt)) {
    $ingredient_stmt->close();
}
if (isset($stmt)) {
    $stmt->close();
}
$conn->close();
?>
