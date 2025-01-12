<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && $_SESSION['user_role'] == 'supervisor') {
    try {
        // Validate required fields
        $required_fields = ['ingredient_name_tbl', 'quantity_tbl', 'preparation_step_tbl', 'equipment_tbl'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("$field is required");
            }
        }

        $ingredient_id = isset($_POST['ingredient_id']) ? $_POST['ingredient_id'] : null;
        
        if ($ingredient_id) {
            // Update existing recipe
            $sql = "UPDATE recipe_db SET 
                    ingredient_name_tbl = ?,
                    quantity_tbl = ?,
                    preparation_step_tbl = ?,
                    equipment_tbl = ?,
                    updated_by = ?
                    WHERE ingredient_id = ?";
                    
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssii",
                $_POST['ingredient_name_tbl'],
                $_POST['quantity_tbl'],
                $_POST['preparation_step_tbl'],
                $_POST['equipment_tbl'],
                $_SESSION['user_id'],
                $ingredient_id
            );
        } else {
            // Insert new recipe
            $sql = "INSERT INTO recipe_db (
                        ingredient_name_tbl, 
                        quantity_tbl, 
                        preparation_step_tbl, 
                        equipment_tbl, 
                        created_by,
                        updated_by
                    ) VALUES (?, ?, ?, ?, ?, ?)";
                    
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssii",
                $_POST['ingredient_name_tbl'],
                $_POST['quantity_tbl'],
                $_POST['preparation_step_tbl'],
                $_POST['equipment_tbl'],
                $_SESSION['user_id'],
                $_SESSION['user_id']
            );
        }
        
        // Execute the statement
        if ($stmt->execute()) {
            echo "<script>
                    alert('Recipe " . ($ingredient_id ? "updated" : "saved") . " successfully!');
                    window.location.href='supervisor_dashboard.php';
                  </script>";
        } else {
            throw new Exception($stmt->error);
        }
        
        $stmt->close();
        
    } catch (Exception $e) {
        echo "<script>
                alert('Error: " . $e->getMessage() . "');
                window.location.href='supervisor_dashboard.php';
              </script>";
    }
}

$conn->close();
?> 