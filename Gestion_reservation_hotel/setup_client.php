<?php
require_once "includes/config.php";

// Vérifier la connexion
if($conn === false){
    die("ERREUR : Impossible de se connecter à la base de données. " . mysqli_connect_error());
}

// Information du client par défaut
$email = "client@hotel-reservation.com";
$password = password_hash("client123", PASSWORD_DEFAULT);
$nom = "Client Test";
$adresse = "123 Rue Test";
$telephone = "0123456789";

// Vérifier si le client existe déjà
$check_sql = "SELECT id_client FROM tb_clients WHERE email = ?";
if($stmt = mysqli_prepare($conn, $check_sql)) {
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    
    if(mysqli_stmt_num_rows($stmt) == 0) {
        // Insérer le nouveau client
        $sql = "INSERT INTO tb_clients (nom, email, password, adresse, telephone) VALUES (?, ?, ?, ?, ?)";
        if($insert_stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($insert_stmt, "sssss", $nom, $email, $password, $adresse, $telephone);
            if(mysqli_stmt_execute($insert_stmt)) {
                echo "<div style='text-align: center; margin-top: 50px; font-family: Arial, sans-serif;'>";
                echo "<h2 style='color: #4e73df;'>Configuration réussie !</h2>";
                echo "<p style='margin: 20px 0;'>Compte client créé avec succès.</p>";
                echo "<p><strong>Email :</strong> " . htmlspecialchars($email) . "</p>";
                echo "<p><strong>Mot de passe :</strong> client123</p>";
                echo "<p style='margin-top: 30px;'><a href='login.php' style='background-color: #4e73df; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Aller à la page de connexion</a></p>";
                echo "</div>";
            } else {
                echo "Erreur lors de la création du compte client : " . mysqli_error($conn);
            }
            mysqli_stmt_close($insert_stmt);
        }
    } else {
        echo "<div style='text-align: center; margin-top: 50px; font-family: Arial, sans-serif;'>";
        echo "<h2 style='color: #4e73df;'>Information du compte client</h2>";
        echo "<p><strong>Email :</strong> " . htmlspecialchars($email) . "</p>";
        echo "<p><strong>Mot de passe :</strong> client123</p>";
        echo "<p style='margin-top: 30px;'><a href='login.php' style='background-color: #4e73df; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Aller à la page de connexion</a></p>";
        echo "</div>";
    }
    mysqli_stmt_close($stmt);
}

mysqli_close($conn);
?>
