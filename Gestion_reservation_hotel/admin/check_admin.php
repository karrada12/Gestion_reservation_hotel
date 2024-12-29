<?php
require_once "../includes/config.php";

// Check if admin exists
$sql = "SELECT id_admin, email, password FROM tb_admin";
$result = mysqli_query($conn, $sql);

if ($result) {
    echo "<h3>Admin accounts in database:</h3>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "ID: " . $row['id_admin'] . "<br>";
        echo "Email: " . $row['email'] . "<br>";
        echo "Password Hash: " . substr($row['password'], 0, 20) . "...<br><br>";
    }
} else {
    echo "Error querying database: " . mysqli_error($conn);
}

// Check if table exists and its structure
echo "<h3>Table Structure:</h3>";
$sql = "DESCRIBE tb_admin";
$result = mysqli_query($conn, $sql);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        echo "Field: " . $row['Field'] . " | Type: " . $row['Type'] . "<br>";
    }
} else {
    echo "Error checking table structure: " . mysqli_error($conn);
}

mysqli_close($conn);
?>
