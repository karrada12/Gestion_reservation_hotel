<?php
require_once "includes/config.php";

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<pre>";
echo "Fixing admin passwords...\n\n";

// Update admin with ID 3 to use proper password hashing
$admin_id = 3;
$raw_password = "12122004";  // Current unhashed password
$hashed_password = password_hash($raw_password, PASSWORD_DEFAULT);

$sql = "UPDATE tb_admin SET password = ? WHERE id_admin = ?";
if($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "si", $hashed_password, $admin_id);
    
    if(mysqli_stmt_execute($stmt)) {
        echo "✅ Successfully updated password for admin ID: " . $admin_id . "\n";
        echo "Email: adminab@hotel.com\n";
        echo "Password: " . $raw_password . "\n";
        echo "New hash: " . $hashed_password . "\n";
        
        // Verify the hash works
        if(password_verify($raw_password, $hashed_password)) {
            echo "✅ Password verification test successful!\n";
        } else {
            echo "❌ Password verification test failed!\n";
        }
    } else {
        echo "Error updating password: " . mysqli_error($conn) . "\n";
    }
    mysqli_stmt_close($stmt);
}

mysqli_close($conn);
echo "</pre>";
?>
