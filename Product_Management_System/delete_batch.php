<?php
ob_start();
header('Content-Type: application/json'); 

session_start();
require_once 'db_connect.php';

// Ensure all errors are caught and returned as JSON
set_error_handler(function($errno, $errstr) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => $errstr]);
    exit;
});

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if user is authenticated and has the correct role
    if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'baker') {
        ob_clean();
        echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
        exit;
    }

    // Validate batch_id
    if (empty($_POST['batch_id'])) {
        ob_clean();
        echo json_encode(['success' => false, 'error' => 'Missing batch ID']);
        exit;
    }

    $batch_id = $_POST['batch_id'];

    try {
        // Start transaction
        $conn->begin_transaction();

        // First check if batch exists
        $check_sql = "SELECT batch_no_tbl FROM batch_db WHERE batch_no_tbl = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $batch_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows === 0) {
            throw new Exception("Batch not found");
        }
        
        $check_stmt->close();

        // Delete from batch_reports
        $sql_reports = "DELETE FROM batch_reports WHERE batch_no = ?";
        $stmt_reports = $conn->prepare($sql_reports);
        
        if (!$stmt_reports) {
            throw new Exception("Failed to prepare batch reports statement");
        }

        $stmt_reports->bind_param("s", $batch_id);
        if (!$stmt_reports->execute()) {
            throw new Exception("Failed to delete batch reports");
        }
        $stmt_reports->close();

        // Delete from batch_db
        $sql_batch = "DELETE FROM batch_db WHERE batch_no_tbl = ?";
        $stmt_batch = $conn->prepare($sql_batch);
        
        if (!$stmt_batch) {
            throw new Exception("Failed to prepare batch statement");
        }

        $stmt_batch->bind_param("s", $batch_id);
        if (!$stmt_batch->execute()) {
            throw new Exception("Failed to delete batch");
        }

        // Commit transaction
        $conn->commit();
        
        ob_clean();
        echo json_encode([
            'success' => true,
            'message' => 'Batch and associated records deleted successfully'
        ]);

        $stmt_batch->close();

    } catch (Exception $e) {
        $conn->rollback();
        ob_clean();
        echo json_encode([
            'success' => false, 
            'error' => $e->getMessage()
        ]);
    }
} else {
    ob_clean();
    echo json_encode([
        'success' => false, 
        'error' => 'Invalid request method'
    ]);
}

$conn->close();
ob_end_flush();
?>