<?php
require_once "includes/config.php";

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Admin credentials
$email = "admin@hotel.com";
$password = "Admin@123";
$username = "admin";

// First, clear any existing admin accounts with this email
$clear_sql = "DELETE FROM tb_admin WHERE email = ?";
if($stmt = mysqli_prepare($conn, $clear_sql)) {
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

// Create new admin account with properly hashed password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Insert new admin
$sql = "INSERT INTO tb_admin (username, email, password) VALUES (?, ?, ?)";
if($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "sss", $username, $email, $hashed_password);
    
    if(mysqli_stmt_execute($stmt)) {
        echo "Admin account created successfully!<br>";
        echo "Email: " . $email . "<br>";
        echo "Password: " . $password . "<br>";
        
        // Verify the password hash
        if(password_verify($password, $hashed_password)) {
            echo "<br>Password hash verification successful!<br>";
            echo "Stored hash: " . $hashed_password . "<br>";
        } else {
            echo "<br>Warning: Password hash verification failed!<br>";
        }
        
        echo "<br>Please delete this file after use for security reasons.";
    } else {
        echo "Error creating admin account: " . mysqli_error($conn);
    }
    mysqli_stmt_close($stmt);
}

mysqli_close($conn);
?>
