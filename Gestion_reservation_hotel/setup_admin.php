<?php
require_once "includes/config.php";

// Créer la table tb_admin si elle n'existe pas
$sql = "CREATE TABLE IF NOT EXISTS tb_admin (
    id_admin INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)";

if(!mysqli_query($conn, $sql)){
    die("Erreur lors de la création de la table : " . mysqli_error($conn));
}

// Créer le compte admin
$email = "admin@hotel.com";
$password = password_hash("admin123", PASSWORD_DEFAULT);

// Vérifier si l'admin existe déjà
$check_sql = "SELECT id_admin FROM tb_admin WHERE email = ?";
$stmt = mysqli_prepare($conn, $check_sql);
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

if(mysqli_stmt_num_rows($stmt) == 0) {
    // Insérer l'admin
    $sql = "INSERT INTO tb_admin (email, password) VALUES (?, ?)";
    if($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "ss", $email, $password);
        
        if(mysqli_stmt_execute($stmt)) {
            echo "<div style='text-align: center; margin-top: 50px; font-family: Arial, sans-serif;'>";
            echo "<h2 style='color: #4e73df;'>Configuration réussie !</h2>";
            echo "<p>Compte administrateur créé avec succès.</p>";
            echo "<p><strong>Email :</strong> admin@hotel.com</p>";
            echo "<p><strong>Mot de passe :</strong> admin123</p>";
            echo "<p style='margin-top: 30px;'><a href='login.php' style='background-color: #4e73df; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Aller à la page de connexion</a></p>";
            echo "</div>";
        } else {
            echo "Erreur lors de la création du compte administrateur : " . mysqli_error($conn);
        }
    }
} else {
    echo "<div style='text-align: center; margin-top: 50px; font-family: Arial, sans-serif;'>";
    echo "<h2 style='color: #4e73df;'>Un compte administrateur existe déjà</h2>";
    echo "<p>Utilisez les identifiants suivants :</p>";
    echo "<p><strong>Email :</strong> admin@hotel.com</p>";
    echo "<p><strong>Mot de passe :</strong> admin123</p>";
    echo "<p style='margin-top: 30px;'><a href='login.php' style='background-color: #4e73df; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Aller à la page de connexion</a></p>";
    echo "</div>";
}

mysqli_close($conn);
?>
