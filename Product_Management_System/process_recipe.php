<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && $_SESSION['user_role'] == 'supervisor') {
    try {
        $conn->begin_transaction();

        // Check for duplicate recipe name
        if (empty($_POST['recipe_id'])) {  // Only check if recipe_id is not provided (new recipe)
            $check_sql = "SELECT recipe_id FROM recipe_db WHERE recipe_name = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("s", $_POST['recipe_name']);
            $check_stmt->execute();
            $result = $check_stmt->get_result();

            if ($result->num_rows > 0) {
                throw new Exception("A recipe with this name already exists!");
            }
        }

        // Debug: Print POST data
        error_log("POST data: " . print_r($_POST, true));

        // Check if we have all required data
        if (empty($_POST['recipe_name']) || 
            empty($_POST['ingredient_name']) || 
            !is_array($_POST['ingredient_name']) ||
            empty($_POST['equipment_tbl'])) {
            throw new Exception("All fields are required");
        }

        // Handle preparation steps
        $preparation_steps = [];
        if (!empty($_POST['preparation_steps']) && is_array($_POST['preparation_steps'])) {
            foreach ($_POST['preparation_steps'] as $index => $step) {
                if (!empty(trim($step))) {
                    $preparation_steps[] = ($index + 1) . ". " . trim($step);
                }
            }
        }
        $preparation_step_tbl = implode("\n", $preparation_steps);

        if (empty($preparation_step_tbl)) {
            throw new Exception("At least one preparation step is required");
        }

        // Insert/Update recipe
        if (!empty($_POST['recipe_id'])) {
            // Update existing recipe
            $sql = "UPDATE recipe_db SET 
                    recipe_name = ?,
                    preparation_step_tbl = ?,
                    equipment_tbl = ?,
                    updated_by = ?
                    WHERE recipe_id = ?";
            
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            
            $stmt->bind_param("sssii",
                $_POST['recipe_name'],
                $preparation_step_tbl,
                $_POST['equipment_tbl'],
                $_SESSION['user_id'],
                $_POST['recipe_id']
            );
            
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            
            $recipe_id = $_POST['recipe_id'];

            // Delete existing ingredients
            $delete_sql = "DELETE FROM recipe_ingredients WHERE recipe_id = ?";
            $delete_stmt = $conn->prepare($delete_sql);
            $delete_stmt->bind_param("i", $recipe_id);
            $delete_stmt->execute();
        } else {
            // Insert new recipe
            $sql = "INSERT INTO recipe_db (
                        recipe_name,
                        preparation_step_tbl,
                        equipment_tbl,
                        created_by,
                        updated_by
                    ) VALUES (?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            
            $stmt->bind_param("sssii",
                $_POST['recipe_name'],
                $preparation_step_tbl,
                $_POST['equipment_tbl'],
                $_SESSION['user_id'],
                $_SESSION['user_id']
            );
            
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            
            $recipe_id = $conn->insert_id;
            if (!$recipe_id) {
                throw new Exception("Failed to get new recipe ID");
            }
        }

        // Insert ingredients
        $ingredient_sql = "INSERT INTO recipe_ingredients (
                            recipe_id,
                            ingredient_name,
                            quantity,
                            unit_tbl
                        ) VALUES (?, ?, ?, ?)";
        $ingredient_stmt = $conn->prepare($ingredient_sql);
        if (!$ingredient_stmt) {
            throw new Exception("Prepare ingredients failed: " . $conn->error);
        }

        // Process each ingredient
        foreach ($_POST['ingredient_name'] as $key => $ingredient) {
            if (!empty($ingredient)) {
                $quantity = $_POST['quantity'][$key];
                $unit = $_POST['unit'][$key];
                
                $ingredient_stmt->bind_param("isds",
                    $recipe_id,
                    $ingredient,
                    $quantity,
                    $unit
                );
                
                if (!$ingredient_stmt->execute()) {
                    throw new Exception("Failed to insert ingredient: " . $ingredient_stmt->error);
                }
            }
        }

        $conn->commit();
        
        echo "<script>
                alert('Recipe saved successfully!');
                window.location.href='supervisor_dashboard.php';
              </script>";
        
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Recipe save error: " . $e->getMessage());
        echo "<script>
                alert('Error: " . $e->getMessage() . "');
                window.location.href='supervisor_dashboard.php';
              </script>";
    }
}

$conn->close();
?>