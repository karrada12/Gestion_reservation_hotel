<?php
require_once "../includes/config.php";

// Ajouter les colonnes manquantes à la table tb_hotels
$sql = "ALTER TABLE tb_hotels 
        ADD COLUMN IF NOT EXISTS ville VARCHAR(100) DEFAULT 'Non spécifiée',
        ADD COLUMN IF NOT EXISTS pays VARCHAR(100) DEFAULT 'Non spécifié',
        ADD COLUMN IF NOT EXISTS etoiles INT DEFAULT 3,
        ADD COLUMN IF NOT EXISTS image VARCHAR(255) DEFAULT 'images/hotel-default.jpg'";

if (mysqli_query($conn, $sql)) {
    echo "Table tb_hotels mise à jour avec succès.";
} else {
    echo "Erreur lors de la mise à jour de la table: " . mysqli_error($conn);
}

mysqli_close($conn);
?>
