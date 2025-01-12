<?php
// Start output buffering
ob_start();

session_start();
require_once 'db_connect.php';

// Clear any existing output
ob_clean();

header('Content-Type: application/json');

if (isset($_GET['id'])) {
    try {
        $sql = "SELECT p.*, 
                r.recipe_name,
                e.equipment_name,
                GROUP_CONCAT(DISTINCT a.name_tbl) as staff_names
                FROM production_db p
                LEFT JOIN recipe_db r ON p.recipe_id = r.recipe_id
                LEFT JOIN equipment_status e ON p.equipment_id = e.equipment_id
                LEFT JOIN admin_db a ON FIND_IN_SET(a.admin_id, p.staff_availability)
                WHERE p.production_id = ?
                GROUP BY p.production_id";
                
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("i", $_GET['id']);
        
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $schedule = $result->fetch_assoc();
            
            if ($schedule) {
                // Format the date to be compatible with the form
                if (isset($schedule['production_date'])) {
                    $schedule['production_date'] = date('Y-m-d', strtotime($schedule['production_date']));
                }
                
                echo json_encode([
                    'success' => true,
                    'data' => $schedule
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Schedule not found'
                ]);
            }
        } else {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'No schedule ID provided'
    ]);
}

// Close statement and connection
if (isset($stmt)) {
    $stmt->close();
}
$conn->close();
?>