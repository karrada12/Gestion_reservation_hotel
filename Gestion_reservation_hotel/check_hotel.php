<?php
require_once "includes/config.php";

$sql = "SELECT id_hotel, nom_hotel FROM tb_hotels";
$result = mysqli_query($conn, $sql);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        echo "ID: " . $row['id_hotel'] . " - Nom: " . $row['nom_hotel'] . "<br>";
    }
} else {
    echo "Erreur: " . mysqli_error($conn);
}

mysqli_close($conn);
?>
