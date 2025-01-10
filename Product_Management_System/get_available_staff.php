<?php
require_once 'db_connect.php';
header('Content-Type: application/json');

try {
    // Simple query to get all bakers
    $sql = "SELECT admin_id, name_tbl 
            FROM admin_db 
            WHERE role_tbl = 'baker'
            ORDER BY name_tbl";

    $result = $conn->query($sql);
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }

    $staff = [];
    while ($row = $result->fetch_assoc()) {
        $staff[] = [
            'admin_id' => $row['admin_id'],
            'name_tbl' => $row['name_tbl']
        ];
    }

    // Debug output
    error_log("Staff found: " . count($staff));
    error_log("Staff data: " . print_r($staff, true));

    echo json_encode([
        'success' => true,
        'data' => $staff
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
