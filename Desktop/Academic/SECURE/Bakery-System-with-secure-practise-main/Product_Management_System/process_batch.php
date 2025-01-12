<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && $_SESSION['user_role'] == 'baker') {
    try {
        // Prepare the INSERT statement
        $sql = "INSERT INTO batch_db (
                    batch_no_tbl,
                    startDate_tbl,
                    endDate_tbl,
                    production_stage_tbl,
                    quality_check_tbl,
                    status_tbl
                ) VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        
        // Process quality check data
        $temperature = isset($_POST['temperature']) ? floatval($_POST['temperature']) : null;
        $moisture = isset($_POST['moisture']) ? floatval($_POST['moisture']) : null;
        $weight = isset($_POST['weight']) ? floatval($_POST['weight']) : null;
        $visual_checks = isset($_POST['visual_checks']) ? implode(", ", $_POST['visual_checks']) : '';
        
        // Combine quality check data
        $quality_check = "Temperature: {$temperature}°C\n" .
                        "Moisture: {$moisture}%\n" .
                        "Weight: {$weight}g\n" .
                        "Visual Checks: {$visual_checks}\n" .
                        "Notes: " . ($_POST['quality_check_tbl'] ?? '');

        // Handle end date being NULL
        $end_date = !empty($_POST['endDate_tbl']) ? $_POST['endDate_tbl'] : null;
        
        // Bind parameters
        $stmt->bind_param("ssssss", 
            $_POST['batch_no_tbl'],
            $_POST['startDate_tbl'],
            $end_date,
            $_POST['production_stage_tbl'],
            $quality_check,
            $_POST['status_tbl']
        );
        
        // Execute the statement
        if ($stmt->execute()) {
            echo "<script>
                    alert('Batch tracking record added successfully!');
                    window.location.href='baker_dashboard.php#batch';
                  </script>";
        } else {
            throw new Exception($stmt->error);
        }
        
        $stmt->close();
        
    } catch (Exception $e) {
        echo "<script>
                alert('Error: " . $e->getMessage() . "');
                window.location.href='baker_dashboard.php#batch';
              </script>";
    }
}

$conn->close();
?> 