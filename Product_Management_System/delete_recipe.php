<?php
require_once 'db_connect.php';

if (isset($_GET['id'])) {
    $recipe_id = intval($_GET['id']);
    $stmt = $conn->prepare("DELETE FROM recipe_db WHERE recipe_id = ?");
    $stmt->bind_param("i", $recipe_id);
    $stmt->execute();
    header("Location: recipes.php?success=1");
} else {
    header("Location: recipes.php?error=1");
}
?>
