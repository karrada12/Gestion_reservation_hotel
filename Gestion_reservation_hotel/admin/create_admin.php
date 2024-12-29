<?php
require_once "../includes/config.php";

// Admin credentials
$admin_email = "admin@hotel.com";
$admin_password = "admin123";
$admin_username = "admin";

// Hash the password
$hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);

// First, check if admin already exists
$check_sql = "SELECT id_admin FROM tb_admin WHERE email = ?";
$check_stmt = mysqli_prepare($conn, $check_sql);
mysqli_stmt_bind_param($check_stmt, "s", $admin_email);
mysqli_stmt_execute($check_stmt);
mysqli_stmt_store_result($check_stmt);

if (mysqli_stmt_num_rows($check_stmt) > 0) {
    // Update existing admin
    $update_sql = "UPDATE tb_admin SET password = ?, username = ? WHERE email = ?";
    $stmt = mysqli_prepare($conn, $update_sql);
    mysqli_stmt_bind_param($stmt, "sss", $hashed_password, $admin_username, $admin_email);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "Admin account updated successfully!<br>";
    } else {
        echo "Error updating admin account: " . mysqli_error($conn) . "<br>";
    }
} else {
    // Create new admin
    $insert_sql = "INSERT INTO tb_admin (email, password, username) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($conn, $insert_sql);
    mysqli_stmt_bind_param($stmt, "sss", $admin_email, $hashed_password, $admin_username);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "Admin account created successfully!<br>";
    } else {
        echo "Error creating admin account: " . mysqli_error($conn) . "<br>";
    }
}

echo "<br>Admin credentials:<br>";
echo "Email: " . $admin_email . "<br>";
echo "Password: " . $admin_password . "<br>";
echo "Username: " . $admin_username . "<br>";

mysqli_close($conn);
?>
