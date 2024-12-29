<?php
require_once "../includes/config.php";

// Créer la table admin
$sql = "CREATE TABLE IF NOT EXISTS tb_admin (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)";

if(mysqli_query($conn, $sql)) {
    // Supprimer tous les administrateurs existants
    mysqli_query($conn, "TRUNCATE TABLE tb_admin");
    
    // Insérer le nouvel admin avec les identifiants spécifiés
    $email = "adminab@gmail.com";
    $password = password_hash("12122004", PASSWORD_DEFAULT);
    
    $sql = "INSERT INTO tb_admin (username, email, password) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sss", $email, $email, $password);
    
    if(mysqli_stmt_execute($stmt)) {
        echo "<div style='text-align: center; margin-top: 50px; font-family: Arial, sans-serif;'>";
        echo "<h2 style='color: #4e73df;'>Compte administrateur créé avec succès!</h2>";
        echo "<p>Email : adminab@gmail.com</p>";
        echo "<p>Mot de passe : 12122004</p>";
        echo "<p><a href='login.php' style='background: #4e73df; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin-top: 20px;'>Aller à la page de connexion</a></p>";
        echo "</div>";
    } else {
        echo "Erreur lors de la création de l'admin: " . mysqli_error($conn);
    }
} else {
    echo "Erreur lors de la création de la table: " . mysqli_error($conn);
}

mysqli_close($conn);
?>
