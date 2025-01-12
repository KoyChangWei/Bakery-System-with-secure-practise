<?php
session_start();
require_once 'db_connect.php';

// Check if user is logged in and is a supervisor
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'supervisor') {
    header("Location: login.html");
    exit();
}

if (isset($_GET['id'])) {
    $recipe_id = intval($_GET['id']);
    
    try {
        // Start transaction
        $conn->begin_transaction();

        // First delete related ingredients
        $stmt = $conn->prepare("DELETE FROM recipe_ingredients WHERE recipe_id = ?");
        $stmt->bind_param("i", $recipe_id);
        $stmt->execute();

        // Then delete the recipe
        $stmt = $conn->prepare("DELETE FROM recipe_db WHERE recipe_id = ?");
        $stmt->bind_param("i", $recipe_id);
        $stmt->execute();

        // Commit transaction
        $conn->commit();

        header("Location: supervisor_dashboard.php?success=Recipe deleted successfully");
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        header("Location: supervisor_dashboard.php?error=Failed to delete recipe");
    }
} else {
    header("Location: supervisor_dashboard.php?error=Invalid recipe ID");
}

$conn->close();
?>
