<?php
session_start();
require_once 'db_connect.php';
require_once 'includes/security.php';

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set JSON header
header('Content-Type: application/json');

// Check if user is logged in and is a baker
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'baker') {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Validate required fields
        $required_fields = [
            'batch_no_tbl', 'production_id', 'startDate_tbl', 'production_stage_tbl',
            'status_tbl', 'worker_names', 'target_quantity', 'actual_quantity'
        ];
        
        foreach ($required_fields as $field) {
            if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
                throw new Exception("Missing required field: " . $field);
            }
        }

        // Sanitize and validate inputs
        $batch_no = Security::sanitizeInput($_POST['batch_no_tbl']);
        $production_id = filter_var($_POST['production_id'], FILTER_VALIDATE_INT);
        
        // Format dates properly
        $start_date = date('Y-m-d H:i:s', strtotime(Security::sanitizeInput($_POST['startDate_tbl'])));
        $end_date = !empty($_POST['endDate_tbl']) ? date('Y-m-d H:i:s', strtotime(Security::sanitizeInput($_POST['endDate_tbl']))) : null;
        
        $production_stage = Security::sanitizeInput($_POST['production_stage_tbl']);
        $quality_check = Security::sanitizeHTML($_POST['quality_check_tbl'] ?? '', ['p', 'br', 'strong', 'em']);
        $status = Security::sanitizeInput($_POST['status_tbl']);
        $worker_names = Security::sanitizeInput($_POST['worker_names']);
        
        // Validate numeric inputs
        $temperature = Security::validateNumeric($_POST['temperature'] ?? '', 0, 1000);
        $moisture = Security::validateNumeric($_POST['moisture'] ?? '', 0, 100);
        $weight = Security::validateNumeric($_POST['weight'] ?? '', 0, 10000);
        $target_quantity = Security::validateNumeric($_POST['target_quantity'], 1);
        $actual_quantity = Security::validateNumeric($_POST['actual_quantity'], 0);
        $defect_count = Security::validateNumeric($_POST['defect_count'] ?? 0, 0);

        if ($target_quantity === false || $actual_quantity === false || $defect_count === false) {
            throw new Exception("Invalid quantity values");
        }

        // Additional validations
        if (!preg_match('/^[A-Za-z0-9-]+$/', $batch_no)) {
            throw new Exception("Invalid batch number format");
        }

        if (!in_array($production_stage, ['Preparation', 'Mixing', 'Baking', 'Cooling', 'Packaging'])) {
            throw new Exception("Invalid production stage");
        }

        if (!in_array($status, ['In Progress', 'Completed', 'Scheduled'])) {
            throw new Exception("Invalid status");
        }

        // Validate dates
        if (strtotime($start_date) === false) {
            throw new Exception("Invalid start date format");
        }
        if ($end_date !== null && strtotime($end_date) === false) {
            throw new Exception("Invalid end date format");
        }
        if ($end_date !== null && strtotime($end_date) < strtotime($start_date)) {
            throw new Exception("End date cannot be earlier than start date");
        }

        // Calculate worker count from sanitized worker names
        $worker_count = !empty($worker_names) ? count(array_filter(explode(',', $worker_names))) : 0;

        $conn->begin_transaction();

        // Validate production_id exists and hasn't been used
        $check_sql = "SELECT p.production_id 
                     FROM production_db p 
                     LEFT JOIN batch_db b ON p.production_id = b.production_id 
                     WHERE p.production_id = ? AND b.production_id IS NULL";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("i", $production_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("Invalid or already used production schedule selected");
        }

        // Check for duplicate batch number
        $check_batch_sql = "SELECT batch_no_tbl FROM batch_db WHERE batch_no_tbl = ?";
        $check_batch_stmt = $conn->prepare($check_batch_sql);
        $check_batch_stmt->bind_param("s", $batch_no);
        $check_batch_stmt->execute();
        if ($check_batch_stmt->get_result()->num_rows > 0) {
            throw new Exception("Batch number already exists");
        }
    
        // Insert into batch_db
        $batch_sql = "INSERT INTO batch_db (
            batch_no_tbl, 
            production_id,
            startDate_tbl, 
            endDate_tbl, 
            production_stage_tbl, 
            quality_check_tbl, 
            status_tbl
        ) VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($batch_sql);
        $stmt->bind_param("sisssss", 
            $batch_no,
            $production_id,
            $start_date,
            $end_date,
            $production_stage,
            $quality_check,
            $status
        );
        $stmt->execute();

        // Insert into batch_reports
        $report_sql = "INSERT INTO batch_reports (
            batch_no, 
            worker_count, 
            worker_names, 
            temperature, 
            moisture, 
            weight, 
            target_quantity, 
            actual_quantity, 
            defect_count
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($report_sql);
        $stmt->bind_param("sissssiii",
            $batch_no,
            $worker_count,
            $worker_names,
            $temperature,
            $moisture,
            $weight,
            $target_quantity,
            $actual_quantity,
            $defect_count
        );
        $stmt->execute();
    
        $conn->commit();
        echo Security::jsonEncode([
            'success' => true,
            'message' => 'Batch tracking record added successfully'
        ]);
    
    } catch (Exception $e) {
        $conn->rollback();
        echo Security::jsonEncode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    } finally {
        if (isset($stmt)) $stmt->close();
        if (isset($check_stmt)) $check_stmt->close();
        if (isset($check_batch_stmt)) $check_batch_stmt->close();
    }
}

$conn->close();
?>