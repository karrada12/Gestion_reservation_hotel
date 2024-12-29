<?php
require_once "../includes/config.php";

// Création de la table des réservations
$sql = "CREATE TABLE IF NOT EXISTS tb_reservations (
    id_reservation INT PRIMARY KEY AUTO_INCREMENT,
    id_client INT NOT NULL,
    id_hotel INT NOT NULL,
    id_chambre INT NOT NULL,
    date_debut DATE NOT NULL,
    date_fin DATE NOT NULL,
    nb_personnes INT NOT NULL,
    statut VARCHAR(20) NOT NULL DEFAULT 'en_attente',
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_client) REFERENCES tb_clients(id_client),
    FOREIGN KEY (id_hotel) REFERENCES tb_hotels(id_hotel),
    FOREIGN KEY (id_chambre) REFERENCES tb_chambres(id_chambre)
)";

if (mysqli_query($conn, $sql)) {
    echo "Table des réservations créée avec succès.";
} else {
    echo "Erreur lors de la création de la table des réservations: " . mysqli_error($conn);
}

// Création de la table des chambres si elle n'existe pas
$sql_chambres = "CREATE TABLE IF NOT EXISTS tb_chambres (
    id_chambre INT PRIMARY KEY AUTO_INCREMENT,
    id_hotel INT NOT NULL,
    numero_chambre VARCHAR(10) NOT NULL,
    type_chambre VARCHAR(50) NOT NULL,
    prix_nuit DECIMAL(10,2) NOT NULL,
    capacite INT NOT NULL,
    statut VARCHAR(20) NOT NULL DEFAULT 'disponible',
    FOREIGN KEY (id_hotel) REFERENCES tb_hotels(id_hotel)
)";

if (mysqli_query($conn, $sql_chambres)) {
    echo "Table des chambres créée avec succès.";

    // Ajouter quelques chambres de test pour chaque hôtel
    $sql_hotels = "SELECT id_hotel FROM tb_hotels";
    $result_hotels = mysqli_query($conn, $sql_hotels);

    while ($hotel = mysqli_fetch_assoc($result_hotels)) {
        // Ajouter des chambres standards
        for ($i = 1; $i <= 5; $i++) {
            $sql_insert = "INSERT IGNORE INTO tb_chambres (id_hotel, numero_chambre, type_chambre, prix_nuit, capacite) 
                          VALUES (?, ?, 'standard', 500.00, 2)";
            $stmt = mysqli_prepare($conn, $sql_insert);
            $numero = "S" . str_pad($i, 3, "0", STR_PAD_LEFT);
            mysqli_stmt_bind_param($stmt, "is", $hotel['id_hotel'], $numero);
            mysqli_stmt_execute($stmt);
        }

        // Ajouter des chambres deluxe
        for ($i = 1; $i <= 3; $i++) {
            $sql_insert = "INSERT IGNORE INTO tb_chambres (id_hotel, numero_chambre, type_chambre, prix_nuit, capacite) 
                          VALUES (?, ?, 'deluxe', 800.00, 3)";
            $stmt = mysqli_prepare($conn, $sql_insert);
            $numero = "D" . str_pad($i, 3, "0", STR_PAD_LEFT);
            mysqli_stmt_bind_param($stmt, "is", $hotel['id_hotel'], $numero);
            mysqli_stmt_execute($stmt);
        }

        // Ajouter des suites
        for ($i = 1; $i <= 2; $i++) {
            $sql_insert = "INSERT IGNORE INTO tb_chambres (id_hotel, numero_chambre, type_chambre, prix_nuit, capacite) 
                          VALUES (?, ?, 'suite', 1200.00, 4)";
            $stmt = mysqli_prepare($conn, $sql_insert);
            $numero = "ST" . str_pad($i, 3, "0", STR_PAD_LEFT);
            mysqli_stmt_bind_param($stmt, "is", $hotel['id_hotel'], $numero);
            mysqli_stmt_execute($stmt);
        }
    }
} else {
    echo "Erreur lors de la création de la table des chambres: " . mysqli_error($conn);
}

mysqli_close($conn);
?>
