<?php
session_start();
require_once 'db_connect.php';

if (isset($_GET['schedule_id']) && $_SESSION['user_role'] == 'supervisor') {
    try {
        // Prepare the DELETE statement
        $sql = "DELETE FROM production_db WHERE production_id = ?";
        $stmt = $conn->prepare($sql);
        
        // Bind parameter
        $stmt->bind_param("i", $_GET['schedule_id']);
        
        // Execute the statement
        if ($stmt->execute()) {
            echo "<script>
                    alert('Schedule deleted successfully!');
                    window.location.href='supervisor_dashboard.php';
                  </script>";
        } else {
            throw new Exception($stmt->error);
        }
        
        $stmt->close();
        
    } catch (Exception $e) {
        echo "<script>
                alert('Error deleting schedule: " . $e->getMessage() . "');
                window.location.href='supervisor_dashboard.php';
              </script>";
    }
}

$conn->close();
?>