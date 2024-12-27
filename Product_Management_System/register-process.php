<?php
require_once 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // First check if email exists
        $check_sql = "SELECT * FROM admin_db WHERE email_tbl = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $_POST['email']);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows > 0) {
            echo "<script>
                    alert('Email already exists! Please use a different email.');
                    window.location.href='register.html';
                  </script>";
        } else {
            // Prepare INSERT statement
            $sql = "INSERT INTO admin_db (name_tbl, gender_tbl, role_tbl, email_tbl, password_tbl) 
                    VALUES (?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);
            
            // Hash the password
            $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            
            // Bind parameters
            $stmt->bind_param("sssss",
                $_POST['name'],
                $_POST['gender'],
                $_POST['role'],
                $_POST['email'],
                $hashed_password
            );
            
            // Execute the statement
            if ($stmt->execute()) {
                echo "<script>
                        alert('Registration successful! Please login.');
                        window.location.href='login.html';
                      </script>";
            } else {
                throw new Exception($stmt->error);
            }
            
            $stmt->close();
        }
        
        $check_stmt->close();
        
    } catch (Exception $e) {
        echo "<script>
                alert('Error: " . $e->getMessage() . "');
                window.location.href='register.html';
              </script>";
    }
}

$conn->close();
?> 