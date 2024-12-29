<?php
require_once "../includes/config.php";

// Vérifier la connexion
if($conn === false){
    die("ERREUR : Impossible de se connecter à la base de données. " . mysqli_connect_error());
}

// Vérifier si la table admin existe
$sql = "CREATE TABLE IF NOT EXISTS tb_admin (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)";

if(!mysqli_query($conn, $sql)){
    die("Erreur lors de la création de la table : " . mysqli_error($conn));
}

// Créer un compte admin par défaut
$username = "admin";
$password = password_hash("admin123", PASSWORD_DEFAULT);

// Vérifier si l'admin existe déjà
$check_sql = "SELECT id FROM tb_admin WHERE username = 'admin'";
$result = mysqli_query($conn, $check_sql);

if($result === false) {
    die("Erreur lors de la vérification de l'admin : " . mysqli_error($conn));
}

if(mysqli_num_rows($result) == 0) {
    $sql = "INSERT INTO tb_admin (username, password) VALUES (?, ?)";
    if($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "ss", $username, $password);
        if(mysqli_stmt_execute($stmt)) {
            echo "<div style='text-align: center; margin-top: 50px; font-family: Arial, sans-serif;'>";
            echo "<h2 style='color: #4e73df;'>Configuration réussie !</h2>";
            echo "<p style='margin: 20px 0;'>Compte administrateur créé avec succès.</p>";
            echo "<p><strong>Nom d'utilisateur :</strong> admin</p>";
            echo "<p><strong>Mot de passe :</strong> admin123</p>";
            echo "<p style='margin-top: 30px;'><a href='login.php' style='background-color: #4e73df; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Aller à la page de connexion</a></p>";
            echo "</div>";
        } else {
            echo "Erreur lors de la création du compte administrateur : " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    } else {
        echo "Erreur lors de la préparation de la requête : " . mysqli_error($conn);
    }
} else {
    echo "<div style='text-align: center; margin-top: 50px; font-family: Arial, sans-serif;'>";
    echo "<h2 style='color: #4e73df;'>Information</h2>";
    echo "<p style='margin: 20px 0;'>Un compte administrateur existe déjà.</p>";
    echo "<p style='margin-top: 30px;'><a href='login.php' style='background-color: #4e73df; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Aller à la page de connexion</a></p>";
    echo "</div>";
}

mysqli_close($conn);
?>
