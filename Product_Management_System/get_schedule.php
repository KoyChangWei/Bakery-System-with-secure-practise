<?php
// Start output buffering
ob_start();

session_start();
require_once 'db_connect.php';
require_once 'includes/security.php';

// Clear any existing output
ob_clean();

header('Content-Type: application/json');

try {
    // Validate schedule ID
    if (!isset($_GET['id'])) {
        throw new Exception('Schedule ID is required');
    }

    $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    if ($id === false) {
        throw new Exception('Invalid schedule ID');
    }

    $sql = "SELECT p.*, 
            r.recipe_name,
            e.equipment_name,
            GROUP_CONCAT(DISTINCT a.admin_id) as staff_availability,
            GROUP_CONCAT(DISTINCT a.name_tbl) as staff_names
            FROM production_db p
            LEFT JOIN recipe_db r ON p.recipe_id = r.recipe_id
            LEFT JOIN equipment_status e ON p.equipment_id = e.equipment_id
            LEFT JOIN admin_db a ON FIND_IN_SET(a.admin_id, p.staff_availability)
            WHERE p.production_id = ?
            GROUP BY p.production_id";
            
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Failed to prepare statement: " . $conn->error);
    }
    
    $stmt->bind_param("i", $id);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to execute query: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $schedule = $result->fetch_assoc();
    
    if (!$schedule) {
        throw new Exception('Schedule not found');
    }

    // Format and sanitize the data
    if (isset($schedule['production_date'])) {
        $schedule['production_date'] = date('Y-m-d', strtotime($schedule['production_date']));
    }

    // Ensure staff data is properly formatted
    if (empty($schedule['staff_availability'])) {
        $schedule['staff_availability'] = '';
        $schedule['staff_names'] = '';
    } else {
        // Convert staff_availability to string and ensure it's properly formatted
        $staff_availability = $schedule['staff_availability'];
        if (is_array($staff_availability)) {
            $schedule['staff_availability'] = implode(',', $staff_availability);
        } else {
            $schedule['staff_availability'] = strval($staff_availability);
        }
        
        // Ensure staff_names is also a string
        $staff_names = $schedule['staff_names'];
        if (is_array($staff_names)) {
            $schedule['staff_names'] = implode(',', $staff_names);
        } else {
            $schedule['staff_names'] = strval($staff_names);
        }
    }

    // Convert numeric values to integers
    $schedule['production_id'] = (int)$schedule['production_id'];
    $schedule['recipe_id'] = (int)$schedule['recipe_id'];
    $schedule['order_volume'] = (int)$schedule['order_volume'];
    $schedule['capacity'] = (int)$schedule['capacity'];
    $schedule['equipment_id'] = (int)$schedule['equipment_id'];

    // Sanitize string values
    $schedule['recipe_name'] = Security::sanitizeInput($schedule['recipe_name'] ?? '');
    $schedule['equipment_name'] = Security::sanitizeInput($schedule['equipment_name'] ?? '');
    $schedule['staff_names'] = Security::sanitizeInput($schedule['staff_names'] ?? '');

    // Ensure we're sending valid JSON data
    $response = [
        'success' => true,
        'data' => array_map(function($value) {
            return is_null($value) ? '' : $value;
        }, $schedule)
    ];

    echo json_encode($response, JSON_NUMERIC_CHECK);

} catch (Exception $e) {
    error_log("Error in get_schedule.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

// Close statement and connection
if (isset($stmt)) {
    $stmt->close();
}
$conn->close();
?>