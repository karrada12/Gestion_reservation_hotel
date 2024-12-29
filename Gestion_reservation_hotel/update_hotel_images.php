<?php
require_once "includes/config.php";

$hotel_images = [
    'https://images.unsplash.com/photo-1566073771259-6a8506099945',
    'https://images.unsplash.com/photo-1582719508461-905c673771fd',
    'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb',
    'https://images.unsplash.com/photo-1571896349842-33c89424de2d',
    'https://images.unsplash.com/photo-1520250497591-112f2f40a3f4',
    'https://images.unsplash.com/photo-1584132967334-10e028bd69f7'
];

// Récupérer tous les hôtels
$sql = "SELECT id_hotel FROM tb_hotels ORDER BY id_hotel";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    $i = 0;
    while ($row = mysqli_fetch_assoc($result)) {
        $image_url = $hotel_images[$i % count($hotel_images)];
        $update_sql = "UPDATE tb_hotels SET image = ? WHERE id_hotel = ?";
        
        if ($stmt = mysqli_prepare($conn, $update_sql)) {
            mysqli_stmt_bind_param($stmt, "si", $image_url, $row['id_hotel']);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
        
        $i++;
    }
    echo "Images des hôtels mises à jour avec succès!";
} else {
    echo "Aucun hôtel trouvé.";
}

mysqli_close($conn);
?>
