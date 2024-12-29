<?php
require_once "includes/config.php";

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<pre>";
echo "Checking admin table contents:\n\n";

$sql = "SELECT id_admin, username, email, password FROM tb_admin";
$result = mysqli_query($conn, $sql);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        echo "ID: " . $row['id_admin'] . "\n";
        echo "Username: " . $row['username'] . "\n";
        echo "Email: " . $row['email'] . "\n";
        echo "Password Hash: " . $row['password'] . "\n";
        echo "Password Hash Length: " . strlen($row['password']) . "\n";
        echo "-------------------\n";
    }
} else {
    echo "Error querying database: " . mysqli_error($conn);
}

// Now let's try to create a test admin with a simple password
$test_password = "test123";
$hashed = password_hash($test_password, PASSWORD_DEFAULT);
echo "\nTest password hash:\n";
echo "Original password: " . $test_password . "\n";
echo "Generated hash: " . $hashed . "\n";
echo "Hash length: " . strlen($hashed) . "\n";
echo "Verification test: " . (password_verify($test_password, $hashed) ? "PASS" : "FAIL") . "\n";

mysqli_close($conn);
echo "</pre>";
?>
