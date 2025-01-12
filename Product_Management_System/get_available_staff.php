<?php
// Start output buffering to catch any unwanted output
ob_start();

require_once 'db_connect.php';

// Clear any previous output
ob_clean();

header('Content-Type: application/json');

try {
    // Get the selected production date
    $production_date = $_GET['production_date'] ?? date('Y-m-d');
    
    // Get all bakers who are NOT already assigned to any production on the selected date
    $sql = "SELECT DISTINCT a.admin_id, a.name_tbl 
            FROM admin_db a
            WHERE a.role_tbl = 'baker' 
            AND a.admin_id NOT IN (
                SELECT DISTINCT SUBSTRING_INDEX(SUBSTRING_INDEX(p.staff_availability, ',', numbers.position), ',', -1) staff_id
                FROM production_db p
                CROSS JOIN (
                    SELECT 1 AS position
                    UNION SELECT 2
                    UNION SELECT 3
                ) numbers
                WHERE p.production_date = ?
                AND LENGTH(p.staff_availability) - LENGTH(REPLACE(p.staff_availability, ',', '')) >= numbers.position - 1
            )
            ORDER BY a.name_tbl";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("s", $production_date);
    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    $result = $stmt->get_result();
    
    $staff = [];
    while ($row = $result->fetch_assoc()) {
        $staff[] = [
            'admin_id' => $row['admin_id'],
            'name_tbl' => $row['name_tbl']
        ];
    }

    error_log("Date: " . $production_date);
    error_log("Found " . count($staff) . " available staff members");
    error_log("Staff data: " . print_r($staff, true));

    echo json_encode([
        'success' => true,
        'data' => $staff,
        'debug' => [
            'date' => $production_date,
            'count' => count($staff)
        ]
    ]);

} catch (Exception $e) {
    error_log("Error in get_available_staff: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>
