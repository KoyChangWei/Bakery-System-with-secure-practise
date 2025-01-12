<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && $_SESSION['user_role'] == 'supervisor') {
    try {
        if (isset($_POST['production_id'])) {
            // UPDATE existing schedule
            $sql = "UPDATE production_db SET 
                    order_volumn_tbl = ?,
                    capacity_tbl = ?,
                    staff_availability_tbl = ?,
                    equipment_status_tbl = ?,
                    updated_by = ?
                    WHERE production_id = ?";
                    
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iissii",
                $_POST['order_volumn_tbl'],
                $_POST['capacity_tbl'],
                $_POST['staff_availability_tbl'],
                $_POST['equipment_status_tbl'],
                $_SESSION['user_id'],
                $_POST['production_id']
            );
        } else {
            // INSERT new schedule
            $sql = "INSERT INTO production_db (
                        order_volumn_tbl,
                        capacity_tbl,
                        staff_availability_tbl,
                        equipment_status_tbl,
                        created_by,
                        updated_by
                    ) VALUES (?, ?, ?, ?, ?, ?)";
                    
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iissii",
                $_POST['order_volumn_tbl'],
                $_POST['capacity_tbl'],
                $_POST['staff_availability_tbl'],
                $_POST['equipment_status_tbl'],
                $_SESSION['user_id'],
                $_SESSION['user_id']
            );
        }
        
        // Execute the statement
        if ($stmt->execute()) {
            echo "<script>
                    alert('Schedule " . (isset($_POST['production_id']) ? "updated" : "added") . " successfully!');
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