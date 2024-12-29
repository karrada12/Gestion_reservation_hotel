<?php
require_once "../includes/config.php";

// 1. Drop and recreate the admin table
$drop_table = "DROP TABLE IF EXISTS tb_admin";
$create_table = "CREATE TABLE tb_admin (
    id_admin INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (mysqli_query($conn, $drop_table) && mysqli_query($conn, $create_table)) {
    echo "Table tb_admin recréée avec succès.<br>";
} else {
    echo "Erreur lors de la recréation de la table: " . mysqli_error($conn) . "<br>";
    exit;
}

// 2. Create new admin account
$admin_email = "admin@hotel.com";
$admin_password = "admin123";
$admin_username = "admin";
$hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);

$insert_admin = "INSERT INTO tb_admin (username, password, email) VALUES (?, ?, ?)";
if($stmt = mysqli_prepare($conn, $insert_admin)) {
    mysqli_stmt_bind_param($stmt, "sss", $admin_username, $hashed_password, $admin_email);
    
    if(mysqli_stmt_execute($stmt)) {
        echo "Compte administrateur créé avec succès!<br><br>";
        echo "Identifiants de connexion :<br>";
        echo "Email: " . $admin_email . "<br>";
        echo "Mot de passe: " . $admin_password . "<br>";
        echo "Nom d'utilisateur: " . $admin_username . "<br>";
    } else {
        echo "Erreur lors de la création du compte admin: " . mysqli_error($conn) . "<br>";
    }
    mysqli_stmt_close($stmt);
} else {
    echo "Erreur de préparation de la requête: " . mysqli_error($conn) . "<br>";
}

// 3. Verify the admin account
$verify_sql = "SELECT id_admin, username, password, email FROM tb_admin WHERE email = ?";
if($stmt = mysqli_prepare($conn, $verify_sql)) {
    mysqli_stmt_bind_param($stmt, "s", $admin_email);
    
    if(mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        if($row = mysqli_fetch_assoc($result)) {
            echo "<br>Vérification du compte :<br>";
            echo "ID Admin: " . $row['id_admin'] . "<br>";
            echo "Email trouvé: " . $row['email'] . "<br>";
            echo "Username trouvé: " . $row['username'] . "<br>";
            echo "Le hash du mot de passe est présent: " . (!empty($row['password']) ? "Oui" : "Non") . "<br>";
            
            // Test password verification
            if(password_verify($admin_password, $row['password'])) {
                echo "Test de vérification du mot de passe: Succès<br>";
            } else {
                echo "Test de vérification du mot de passe: Échec<br>";
            }
        } else {
            echo "Erreur: Compte admin non trouvé après création<br>";
        }
    } else {
        echo "Erreur lors de la vérification: " . mysqli_error($conn) . "<br>";
    }
    mysqli_stmt_close($stmt);
}

mysqli_close($conn);
?>
