<?php
require_once "includes/config.php";

// Vérifier si la table admin existe
$check_table = "SHOW TABLES LIKE 'tb_admin'";
$table_exists = mysqli_query($conn, $check_table);

if (mysqli_num_rows($table_exists) == 0) {
    // Créer la table admin si elle n'existe pas
    $create_table = "CREATE TABLE tb_admin (
        id_admin INT PRIMARY KEY AUTO_INCREMENT,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        nom VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if (mysqli_query($conn, $create_table)) {
        echo "Table tb_admin créée avec succès.<br>";
        
        // Créer un compte admin par défaut
        $default_email = "admin@hotel.com";
        $default_password = password_hash("admin123", PASSWORD_DEFAULT);
        $default_nom = "Administrateur";
        
        $insert_admin = "INSERT INTO tb_admin (email, password, nom) VALUES (?, ?, ?)";
        if ($stmt = mysqli_prepare($conn, $insert_admin)) {
            mysqli_stmt_bind_param($stmt, "sss", $default_email, $default_password, $default_nom);
            if (mysqli_stmt_execute($stmt)) {
                echo "Compte administrateur créé avec succès.<br>";
                echo "Email: admin@hotel.com<br>";
                echo "Mot de passe: admin123<br>";
            } else {
                echo "Erreur lors de la création du compte admin: " . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt);
        }
    } else {
        echo "Erreur lors de la création de la table: " . mysqli_error($conn);
    }
} else {
    // Vérifier les comptes admin existants
    $check_admins = "SELECT id_admin, email, nom FROM tb_admin";
    $result = mysqli_query($conn, $check_admins);
    
    if (mysqli_num_rows($result) > 0) {
        echo "<h3>Comptes administrateurs existants:</h3>";
        while ($row = mysqli_fetch_assoc($result)) {
            echo "ID: " . $row['id_admin'] . " - Email: " . $row['email'] . " - Nom: " . $row['nom'] . "<br>";
        }
    } else {
        echo "Aucun compte administrateur trouvé.";
    }
}

mysqli_close($conn);
?>
