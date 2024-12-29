<?php
require_once "includes/config.php";

// Vérifier si la colonne is_admin existe
$check_column = "SHOW COLUMNS FROM tb_clients LIKE 'is_admin'";
$result = mysqli_query($conn, $check_column);

if (mysqli_num_rows($result) == 0) {
    // Ajouter la colonne is_admin si elle n'existe pas
    $add_column = "ALTER TABLE tb_clients ADD COLUMN is_admin TINYINT(1) DEFAULT 0";
    mysqli_query($conn, $add_column);
}

// Créer un compte admin
$nom = "Admin";
$prenom = "System";
$email = "admin@hotel.com";
$telephone = "0600000000";
$adresse = "Administration";
$password = password_hash("admin123", PASSWORD_DEFAULT);
$is_admin = 1;

// Vérifier si l'admin existe déjà
$check_admin = "SELECT id_client FROM tb_clients WHERE email = ?";
$stmt = mysqli_prepare($conn, $check_admin);
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

if(mysqli_stmt_num_rows($stmt) == 0) {
    // Insérer l'admin
    $sql = "INSERT INTO tb_clients (nom, prenom, email, telephone, adresse, password, is_admin) VALUES (?, ?, ?, ?, ?, ?, ?)";
    if($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "ssssssi", $nom, $prenom, $email, $telephone, $adresse, $password, $is_admin);
        
        if(mysqli_stmt_execute($stmt)) {
            echo "<div style='text-align: center; margin-top: 50px; font-family: Arial, sans-serif;'>";
            echo "<h2 style='color: #4e73df;'>Compte administrateur créé avec succès !</h2>";
            echo "<p><strong>Email :</strong> admin@hotel.com</p>";
            echo "<p><strong>Mot de passe :</strong> admin123</p>";
            echo "<p style='margin-top: 30px;'><a href='login.php' style='background-color: #4e73df; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Aller à la page de connexion</a></p>";
            echo "</div>";
        } else {
            echo "Erreur lors de la création du compte administrateur.";
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
