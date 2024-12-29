<?php
require_once "../includes/config.php";

// Mettre à jour la table tb_clients
$sql = "CREATE TABLE IF NOT EXISTS tb_clients (
    id_client INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100),
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    telephone VARCHAR(20),
    adresse TEXT,
    date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if (mysqli_query($conn, $sql)) {
    echo "Table tb_clients créée/mise à jour avec succès.<br>";
} else {
    echo "Erreur lors de la création/mise à jour de la table tb_clients: " . mysqli_error($conn) . "<br>";
}

// Ajouter les colonnes manquantes si elles n'existent pas
$alter_queries = [
    "ALTER TABLE tb_clients ADD COLUMN IF NOT EXISTS prenom VARCHAR(100) AFTER nom",
    "ALTER TABLE tb_clients ADD COLUMN IF NOT EXISTS telephone VARCHAR(20) AFTER password",
    "ALTER TABLE tb_clients ADD COLUMN IF NOT EXISTS adresse TEXT AFTER telephone",
    "ALTER TABLE tb_clients ADD COLUMN IF NOT EXISTS date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP"
];

foreach ($alter_queries as $query) {
    if (mysqli_query($conn, $query)) {
        echo "Structure de la table mise à jour.<br>";
    } else {
        echo "Erreur lors de la mise à jour: " . mysqli_error($conn) . "<br>";
    }
}

mysqli_close($conn);
?>
