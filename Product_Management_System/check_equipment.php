<?php
header('Content-Type: application/json');
require_once 'db_connect.php';
try {
    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    // Get all equipment with their status
    $sql = "SELECT equipment_id, equipment_name, status, notes, maintenance_schedule 
            FROM equipment_status 
            ORDER BY equipment_name";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt->execute()) {
        throw new Exception("Query execution failed");
    }
    
    $result = $stmt->get_result();
    $equipment = array();
    
    while ($row = $result->fetch_assoc()) {
        $equipment[] = array(
            'equipment_id' => $row['equipment_id'],
            'equipment_name' => $row['equipment_name'],
            'status' => $row['status'],
            'notes' => $row['notes'],
            'maintenance_schedule' => $row['maintenance_schedule']
        );
    }
    
    echo json_encode($equipment);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>