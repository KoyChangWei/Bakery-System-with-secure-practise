<?php
session_start();
require_once 'db_connect.php';
require_once 'includes/security.php';

// Set JSON header
header('Content-Type: application/json');

// Initialize Security class
$security = new Security();

// Check if user is logged in and is a supervisor
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'supervisor') {
    echo $security->jsonEncode(['success' => false, 'error' => 'Unauthorized access']);
    exit();
}

if (isset($_GET['id'])) {
    $recipe_id = intval($_GET['id']);
    
    try {
        // Start transaction
        $conn->begin_transaction();

        // Get the equipment name before deleting the recipe
        $stmt = $conn->prepare("SELECT equipment_tbl FROM recipe_db WHERE recipe_id = ?");
        $stmt->bind_param("i", $recipe_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $recipe = $result->fetch_assoc();
        $equipment_name = $recipe['equipment_tbl'];

        // First delete related ingredients
        $stmt = $conn->prepare("DELETE FROM recipe_ingredients WHERE recipe_id = ?");
        $stmt->bind_param("i", $recipe_id);
        $stmt->execute();

        // Then delete the recipe
        $stmt = $conn->prepare("DELETE FROM recipe_db WHERE recipe_id = ?");
        $stmt->bind_param("i", $recipe_id);
        $stmt->execute();

        // Update equipment status to 'Available' if no other recipe is using it
        if ($equipment_name) {
            $stmt = $conn->prepare("
                UPDATE equipment_status 
                SET status = 'Available'
                WHERE equipment_name = ?
                AND NOT EXISTS (
                    SELECT 1 
                    FROM recipe_db 
                    WHERE equipment_tbl = ?
                )
            ");
            $stmt->bind_param("ss", $equipment_name, $equipment_name);
            $stmt->execute();
        }

        // Commit transaction
        $conn->commit();

        echo $security->jsonEncode([
            'success' => true,
            'message' => 'Recipe deleted successfully'
        ]);
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        echo $security->jsonEncode([
            'success' => false,
            'error' => 'Failed to delete recipe: ' . $security->sanitizeInput($e->getMessage())
        ]);
    }
} else {
    echo $security->jsonEncode([
        'success' => false,
        'error' => 'Invalid recipe ID'
    ]);
}

$conn->close();
?>
