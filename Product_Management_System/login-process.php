<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Prepare statement to check user credentials
        $sql = "SELECT * FROM admin_db WHERE email_tbl = ? AND role_tbl = ?";
        $stmt = $conn->prepare($sql);
        
        // Bind parameters
        $stmt->bind_param("ss", 
            $_POST['email'],
            $_POST['role']
        );
        
        // Execute the statement
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            if (password_verify($_POST['password'], $row['password_tbl'])) {
                // Password is correct, create session
                $_SESSION['user_id'] = $row['admin_id'];
                $_SESSION['user_name'] = $row['name_tbl'];
                $_SESSION['user_role'] = $row['role_tbl'];

                // Redirect based on role
                if ($row['role_tbl'] == 'supervisor') {
                    header("Location: supervisor_dashboard.php");
                } else {
                    header("Location: baker_dashboard.php");
                }
                exit();
            } else {
                echo "<script>
                        alert('Invalid password!');
                        window.location.href='login.html';
                      </script>";
            }
        } else {
            echo "<script>
                    alert('Invalid email or role!');
                    window.location.href='login.html';
                  </script>";
        }
        
        $stmt->close();
        
    } catch (Exception $e) {
        echo "<script>
                alert('Error: " . $e->getMessage() . "');
                window.location.href='login.html';
              </script>";
    }
}

$conn->close();
?> 