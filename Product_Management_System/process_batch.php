<?php
session_start();
require_once 'db_connect.php';

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER["REQUEST_METHOD"] == "POST" && $_SESSION['user_role'] == 'baker') {
    try {
        $conn->begin_transaction();
    
        // Insert into batch_db
        $batch_sql = "INSERT INTO batch_db (batch_no_tbl, startDate_tbl, endDate_tbl, production_stage_tbl, quality_check_tbl, status_tbl) 
                      VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($batch_sql);
        $stmt->bind_param("ssssss", 
            $_POST['batch_no_tbl'],
            $_POST['startDate_tbl'],
            $_POST['endDate_tbl'],
            $_POST['production_stage_tbl'],
            $_POST['quality_check_tbl'],
            $_POST['status_tbl']
        );
        $stmt->execute();
    
        // Insert into batch_reports
        $report_sql = "INSERT INTO batch_reports (batch_no, worker_count, worker_names, temperature, moisture, weight, target_quantity, actual_quantity, defect_count) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($report_sql);
        $stmt->bind_param("sissssiii",
            $_POST['batch_no_tbl'],
            $_POST['worker_count'],
            $_POST['worker_names'],
            $_POST['temperature'],
            $_POST['moisture'],
            $_POST['weight'],
            $_POST['target_quantity'],
            $_POST['actual_quantity'],
            $_POST['defect_count']
        );
        $stmt->execute();
    
        $conn->commit();
        echo "<script>
                alert('Batch tracking record added successfully!');
                window.location.href='baker_dashboard.php#batch';
              </script>";
    
    } catch (Exception $e) {
        $conn->rollback();
        echo "<script>
                alert('Error: " . $e->getMessage() . "');
                window.location.href='baker_dashboard.php#batch';
              </script>";
    }
}

$conn->close();
?>