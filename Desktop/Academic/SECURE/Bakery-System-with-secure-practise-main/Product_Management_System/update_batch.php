<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && $_SESSION['user_role'] == 'baker') {
    try {
        // Prepare the UPDATE statement
        $sql = "UPDATE batch_db SET 
                startDate_tbl = ?,
                endDate_tbl = ?,
                production_stage_tbl = ?,
                quality_check_tbl = ?,
                status_tbl = ?
                WHERE batch_no_tbl = ?";
                
        $stmt = $conn->prepare($sql);
        
        // Handle end date being NULL
        $end_date = !empty($_POST['endDate_tbl']) ? $_POST['endDate_tbl'] : null;
        
        // Bind parameters
        $stmt->bind_param("ssssss",
            $_POST['startDate_tbl'],
            $end_date,
            $_POST['production_stage_tbl'],
            $_POST['quality_check_tbl'],
            $_POST['status_tbl'],
            $_POST['batch_no_tbl']
        );
        
        // Execute the statement
        if ($stmt->execute()) {
            echo "<script>
                    alert('Batch updated successfully!');
                    window.location.href='baker_dashboard.php#batch';
                  </script>";
        } else {
            throw new Exception($stmt->error);
        }
        
        $stmt->close();
        
    } catch (Exception $e) {
        echo "<script>
                alert('Error updating batch: " . $e->getMessage() . "');
                window.location.href='baker_dashboard.php#batch';
              </script>";
    }
}

$conn->close();
?> 