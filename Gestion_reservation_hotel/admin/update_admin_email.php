<?php
require_once "../includes/config.php";

// Vérifier la connexion
if($conn === false){
    die("ERREUR : Impossible de se connecter à la base de données. " . mysqli_connect_error());
}

// Ajouter la colonne email si elle n'existe pas
$sql_alter = "ALTER TABLE tb_admin ADD COLUMN IF NOT EXISTS email VARCHAR(100)";
if(!mysqli_query($conn, $sql_alter)){
    die("Erreur lors de l'ajout de la colonne email : " . mysqli_error($conn));
}

// Email par défaut pour l'admin
$admin_email = "admin@hotel-reservation.com";

// Mettre à jour l'email de l'admin
$sql_update = "UPDATE tb_admin SET email = ? WHERE username = 'admin' AND (email IS NULL OR email = '')";
if($stmt = mysqli_prepare($conn, $sql_update)) {
    mysqli_stmt_bind_param($stmt, "s", $admin_email);
    if(mysqli_stmt_execute($stmt)) {
        echo "<div style='text-align: center; margin-top: 50px; font-family: Arial, sans-serif;'>";
        echo "<h2 style='color: #4e73df;'>Mise à jour réussie !</h2>";
        echo "<p style='margin: 20px 0;'>L'email de l'administrateur a été mis à jour avec succès.</p>";
        echo "<p><strong>Email :</strong> " . htmlspecialchars($admin_email) . "</p>";
        echo "<p style='margin-top: 30px;'><a href='login.php' style='background-color: #4e73df; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Aller à la page de connexion</a></p>";
        echo "</div>";
    } else {
        echo "Erreur lors de la mise à jour de l'email : " . mysqli_error($conn);
    }
    mysqli_stmt_close($stmt);
} else {
    echo "Erreur lors de la préparation de la requête : " . mysqli_error($conn);
}

mysqli_close($conn);
?>
