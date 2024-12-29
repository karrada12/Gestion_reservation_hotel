<?php
require_once "../includes/config.php";

// Définir les chemins des images locales
$hotel_images = [
    'hotel1.jpg' => '../images/hotels/hotel1.jpg',
    'hotel2.jpg' => '../images/hotels/hotel2.jpg',
    'hotel3.jpg' => '../images/hotels/hotel3.jpg',
    'hotel4.jpg' => '../images/hotels/hotel4.jpg',
    'hotel5.jpg' => '../images/hotels/hotel5.jpg',
    'hotel6.jpg' => '../images/hotels/hotel6.jpg',
    'hotel7.jpg' => '../images/hotels/hotel7.jpg',
    'hotel8.jpg' => '../images/hotels/hotel8.jpg'
];

// Télécharger les images depuis Unsplash et les sauvegarder localement
$unsplash_images = [
    'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=800&q=80',
    'https://images.unsplash.com/photo-1582719508461-905c673771fd?auto=format&fit=crop&w=800&q=80',
    'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?auto=format&fit=crop&w=800&q=80',
    'https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?auto=format&fit=crop&w=800&q=80',
    'https://images.unsplash.com/photo-1584132967334-10e028bd69f7?auto=format&fit=crop&w=800&q=80',
    'https://images.unsplash.com/photo-1445019980597-93fa8acb246c?auto=format&fit=crop&w=800&q=80',
    'https://images.unsplash.com/photo-1551882547-ff40c63fe5fa?auto=format&fit=crop&w=800&q=80',
    'https://images.unsplash.com/photo-1566665797739-1674de7a421a?auto=format&fit=crop&w=800&q=80'
];

// Créer le dossier s'il n'existe pas
if (!file_exists('../images/hotels')) {
    mkdir('../images/hotels', 0777, true);
}

// Télécharger les images
$i = 1;
foreach ($unsplash_images as $url) {
    $image_data = file_get_contents($url);
    if ($image_data !== false) {
        file_put_contents("../images/hotels/hotel{$i}.jpg", $image_data);
        $i++;
    }
}

// Ajouter la colonne image_url si elle n'existe pas
$sql = "SHOW COLUMNS FROM tb_hotels LIKE 'image_url'";
$result = mysqli_query($conn, $sql);
if (mysqli_num_rows($result) == 0) {
    $sql = "ALTER TABLE tb_hotels ADD COLUMN image_url VARCHAR(255)";
    mysqli_query($conn, $sql);
}

// Mettre à jour chaque hôtel avec une image
$sql = "SELECT id_hotel FROM tb_hotels";
$result = mysqli_query($conn, $sql);

while ($row = mysqli_fetch_assoc($result)) {
    $image_name = 'hotel' . (($row['id_hotel'] % 8) + 1) . '.jpg';
    $image_path = 'images/hotels/' . $image_name;
    
    $update_sql = "UPDATE tb_hotels SET image_url = ? WHERE id_hotel = ?";
    $stmt = mysqli_prepare($conn, $update_sql);
    mysqli_stmt_bind_param($stmt, "si", $image_path, $row['id_hotel']);
    mysqli_stmt_execute($stmt);
}

echo "<div style='text-align: center; margin-top: 50px; font-family: Arial, sans-serif;'>";
echo "<h2 style='color: #4e73df;'>Images mises à jour avec succès!</h2>";
echo "<p>Les images ont été téléchargées et associées aux hôtels.</p>";
echo "<a href='../index.php' style='display: inline-block; margin-top: 20px; padding: 10px 20px; background-color: #4e73df; color: white; text-decoration: none; border-radius: 5px;'>Voir les hôtels</a>";
echo "</div>";

mysqli_close($conn);
?>
