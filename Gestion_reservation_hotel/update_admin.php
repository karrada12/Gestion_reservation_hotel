<?php
require_once "includes/config.php";

// Admin credentials
$email = "admin@hotel.com";
$new_password = "Admin@123";

// Hash the new password
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

// First, check if admin exists
$check_sql = "SELECT id_admin FROM tb_admin WHERE email = ?";
if($stmt = mysqli_prepare($conn, $check_sql)) {
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    
    if(mysqli_stmt_num_rows($stmt) > 0) {
        // Admin exists, update password
        $update_sql = "UPDATE tb_admin SET password = ? WHERE email = ?";
        if($update_stmt = mysqli_prepare($conn, $update_sql)) {
            mysqli_stmt_bind_param($update_stmt, "ss", $hashed_password, $email);
            if(mysqli_stmt_execute($update_stmt)) {
                echo "Admin password updated successfully.<br>";
                echo "Email: " . $email . "<br>";
                echo "New password: " . $new_password . "<br>";
                echo "<br>Please delete this file after use for security reasons.";
            } else {
                echo "Error updating password: " . mysqli_error($conn);
            }
            mysqli_stmt_close($update_stmt);
        }
    } else {
        // Admin doesn't exist, create new admin
        $insert_sql = "INSERT INTO tb_admin (username, email, password) VALUES ('admin', ?, ?)";
        if($insert_stmt = mysqli_prepare($conn, $insert_sql)) {
            mysqli_stmt_bind_param($insert_stmt, "ss", $email, $hashed_password);
            if(mysqli_stmt_execute($insert_stmt)) {
                echo "New admin account created successfully.<br>";
                echo "Email: " . $email . "<br>";
                echo "Password: " . $new_password . "<br>";
                echo "<br>Please delete this file after use for security reasons.";
            } else {
                echo "Error creating admin: " . mysqli_error($conn);
            }
            mysqli_stmt_close($insert_stmt);
        }
    }
    mysqli_stmt_close($stmt);
}

mysqli_close($conn);
?>
