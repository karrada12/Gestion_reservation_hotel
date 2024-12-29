<?php
require_once "includes/config.php";

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Simple admin credentials
$email = "admin@test.com";
$password = "123456";
$username = "admin";

// Clear existing admin
mysqli_query($conn, "DELETE FROM tb_admin WHERE email = '$email'");

// Create simple hash
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Insert new admin with simple password
$sql = "INSERT INTO tb_admin (username, email, password) VALUES (?, ?, ?)";
if($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "sss", $username, $email, $hashed_password);
    
    if(mysqli_stmt_execute($stmt)) {
        echo "Test admin account created successfully!<br>";
        echo "Email: " . $email . "<br>";
        echo "Password: " . $password . "<br>";
        echo "<br>Hash details:<br>";
        echo "Generated hash: " . $hashed_password . "<br>";
        echo "Hash length: " . strlen($hashed_password) . "<br>";
        
        // Verify the hash works
        if(password_verify($password, $hashed_password)) {
            echo "<br>✅ Password verification successful!<br>";
        } else {
            echo "<br>❌ Password verification failed!<br>";
        }
    } else {
        echo "Error: " . mysqli_error($conn);
    }
    mysqli_stmt_close($stmt);
}

mysqli_close($conn);
?>
