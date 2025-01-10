<?php
header('Content-Type: application/json');
require_once 'db_connect.php';

// Get list of equipment
$equipment = [
    ['equipment_name' => 'Industrial Mixer'],
    ['equipment_name' => 'Commercial Oven'],
    ['equipment_name' => 'Dough Sheeter'],
    ['equipment_name' => 'Proofing Cabinet'],
    ['equipment_name' => 'Cooling Racks'],
    ['equipment_name' => 'Baking Pans'],
    ['equipment_name' => 'Stand Mixer'],
    ['equipment_name' => 'Hand Tools']
];

echo json_encode($equipment);
?>
