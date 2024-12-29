<?php
require_once "../includes/config.php";

// Définir les chemins des images statiques
$hotel_images = [
    '/images/hotels/hotel1.jpg' => 'https://source.unsplash.com/800x600/?luxury,hotel',
    '/images/hotels/hotel2.jpg' => 'https://source.unsplash.com/800x600/?resort,hotel',
    '/images/hotels/hotel3.jpg' => 'https://source.unsplash.com/800x600/?hotel,room',
    '/images/hotels/hotel4.jpg' => 'https://source.unsplash.com/800x600/?hotel,pool',
    '/images/hotels/hotel5.jpg' => 'https://source.unsplash.com/800x600/?hotel,suite',
    '/images/hotels/hotel6.jpg' => 'https://source.unsplash.com/800x600/?hotel,lobby'
];

// Créer le dossier images s'il n'existe pas
if (!file_exists('../images/hotels')) {
    mkdir('../images/hotels', 0777, true);
}

// Télécharger les images
foreach ($hotel_images as $local_path => $url) {
    $full_path = __DIR__ . '/..' . $local_path;
    if (!file_exists($full_path)) {
        $image_data = file_get_contents($url);
        if ($image_data !== false) {
            file_put_contents($full_path, $image_data);
        }
    }
}

// Mettre à jour la base de données
$sql = "UPDATE tb_hotels SET image_url = CASE 
        WHEN MOD(id_hotel, 6) = 0 THEN '/images/hotels/hotel6.jpg'
        WHEN MOD(id_hotel, 6) = 1 THEN '/images/hotels/hotel1.jpg'
        WHEN MOD(id_hotel, 6) = 2 THEN '/images/hotels/hotel2.jpg'
        WHEN MOD(id_hotel, 6) = 3 THEN '/images/hotels/hotel3.jpg'
        WHEN MOD(id_hotel, 6) = 4 THEN '/images/hotels/hotel4.jpg'
        ELSE '/images/hotels/hotel5.jpg'
        END";

if(mysqli_query($conn, $sql)) {
    echo "<div style='text-align: center; margin-top: 50px; font-family: Arial, sans-serif;'>";
    echo "<h2 style='color: #4e73df;'>Images mises à jour avec succès!</h2>";
    echo "<p>Les images ont été téléchargées et associées aux hôtels.</p>";
    echo "<a href='../index.php' style='display: inline-block; margin-top: 20px; padding: 10px 20px; background-color: #4e73df; color: white; text-decoration: none; border-radius: 5px;'>Voir les hôtels</a>";
    echo "</div>";
} else {
    echo "Erreur lors de la mise à jour des images : " . mysqli_error($conn);
}

mysqli_close($conn);
?>
