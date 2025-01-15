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
    $exclude_ids = isset($_GET['exclude_ids']) ? explode(',', $_GET['exclude_ids']) : [];

    // Base query to get available staff
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
            )";

    // Add exclusion for already selected staff
    if (!empty($exclude_ids)) {
        $sql .= " AND a.admin_id NOT IN (" . str_repeat('?,', count($exclude_ids) - 1) . "?)";
    }

    $sql .= " ORDER BY a.name_tbl";

    $stmt = $conn->prepare($sql);
    
    // Bind parameters
    $types = "s" . str_repeat("s", count($exclude_ids));
    $params = array_merge([$production_date], $exclude_ids);
    
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    $staff = [];
    while ($row = $result->fetch_assoc()) {
        $staff[] = [
            'admin_id' => $row['admin_id'],
            'name_tbl' => $row['name_tbl']
        ];
    }

    echo json_encode([
        'success' => true,
        'data' => $staff
    ]);

} catch (Exception $e) {
    error_log("Error in get_available_staff.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
