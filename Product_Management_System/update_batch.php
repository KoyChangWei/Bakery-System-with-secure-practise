<?php
session_start();
require_once 'db_connect.php';

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set JSON header
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST" && $_SESSION['user_role'] == 'baker') {
    try {
        $conn->begin_transaction();
        
        // Calculate worker count from worker names
        $worker_count = !empty($_POST['worker_names']) ? count(array_filter(explode(',', $_POST['worker_names']))) : 0;
        
        // Update batch_db
        $sql = "UPDATE batch_db SET 
                startDate_tbl = ?,
                endDate_tbl = ?,
                production_stage_tbl = ?,
                quality_check_tbl = ?,
                status_tbl = ?
                WHERE batch_no_tbl = ?";
                
        $stmt = $conn->prepare($sql);
        
        $end_date = !empty($_POST['endDate_tbl']) ? $_POST['endDate_tbl'] : null;
        
        $stmt->bind_param("ssssss",
            $_POST['startDate_tbl'],
            $end_date,
            $_POST['production_stage_tbl'],
            $_POST['quality_check_tbl'],
            $_POST['status_tbl'],
            $_POST['batch_no_tbl']
        );
        
        if ($stmt->execute()) {
            // Update batch_reports
            $report_sql = "UPDATE batch_reports SET 
                           worker_count = ?, 
                           worker_names = ?, 
                           temperature = ?, 
                           moisture = ?, 
                           weight = ?, 
                           target_quantity = ?, 
                           actual_quantity = ?, 
                           defect_count = ?
                           WHERE batch_no = ?";
                           
            $stmt = $conn->prepare($report_sql);
            $stmt->bind_param("issssiiis",
                $worker_count,  // Use calculated worker count
                $_POST['worker_names'],
                $_POST['temperature'],
                $_POST['moisture'],
                $_POST['weight'],
                $_POST['target_quantity'],
                $_POST['actual_quantity'],
                $_POST['defect_count'],
                $_POST['batch_no_tbl']
            );
            
            if ($stmt->execute()) {
                $conn->commit();
                echo json_encode([
                    'success' => true,
                    'message' => 'Batch updated successfully'
                ]);
            } else {
                throw new Exception("Failed to update batch report: " . $stmt->error);
            }
        } else {
            throw new Exception("Failed to update batch: " . $stmt->error);
        }
        
        $stmt->close();
        
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode([
            'success' => false,
            'message' => 'Error updating batch: ' . $e->getMessage()
        ]);
    }
}

$conn->close();
?>