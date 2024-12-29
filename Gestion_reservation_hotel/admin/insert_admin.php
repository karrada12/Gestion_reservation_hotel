<?php
require_once(__DIR__ . '/../includes/config.php');

// Default admin credentials
$username = 'admin';
$password = 'admin123'; // This will be the password to login
$email = 'admin@hotel.com';

// Hash the password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// First, delete existing admin if exists (for testing purposes)
$delete_sql = "DELETE FROM admin WHERE email = ? OR username = ?";
$delete_stmt = $conn->prepare($delete_sql);
$delete_stmt->bind_param("ss", $email, $username);
$delete_stmt->execute();

// Insert new admin
$sql = "INSERT INTO admin (username, password, email) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $username, $hashed_password, $email);

if ($stmt->execute()) {
    echo "Admin account created successfully!<br>";
    echo "Email: " . $email . "<br>";
    echo "Username: " . $username . "<br>";
    echo "Password: " . $password . "<br>";
    echo "<a href='login.php'>Go to login page</a>";
} else {
    echo "Error creating admin account: " . $conn->error;
}

$conn->close();
?>
