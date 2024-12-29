<?php
require_once "../includes/config.php";

// Mettre à jour la table tb_chambres avec les colonnes manquantes
$sql = "ALTER TABLE tb_chambres 
        MODIFY COLUMN type_chambre VARCHAR(50) NOT NULL,
        ADD COLUMN IF NOT EXISTS capacite INT DEFAULT 2,
        ADD COLUMN IF NOT EXISTS nb_lits INT DEFAULT 1,
        ADD COLUMN IF NOT EXISTS superficie INT DEFAULT 25,
        MODIFY COLUMN prix DECIMAL(10,2) NOT NULL";

if (mysqli_query($conn, $sql)) {
    echo "Table tb_chambres mise à jour avec succès.";
} else {
    echo "Erreur lors de la mise à jour de la table: " . mysqli_error($conn);
}

mysqli_close($conn);
?>
