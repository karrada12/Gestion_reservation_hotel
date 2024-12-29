<?php
require_once "../includes/config.php";

// Le mot de passe que vous voulez définir
$new_password = "admin123";
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

// Mettre à jour le mot de passe pour l'admin
$sql = "UPDATE tb_admin SET password = ? WHERE email = 'admin@hotel.com'";

if($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "s", $hashed_password);
    
    if(mysqli_stmt_execute($stmt)) {
        echo "Le mot de passe admin a été réinitialisé avec succès.<br>";
        echo "Email: admin@hotel.com<br>";
        echo "Mot de passe: admin123";
    } else {
        echo "Erreur lors de la réinitialisation du mot de passe.";
    }
    
    mysqli_stmt_close($stmt);
} else {
    echo "Erreur de préparation de la requête.";
}

mysqli_close($conn);
?>
