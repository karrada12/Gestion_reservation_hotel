<?php
require_once "../includes/config.php";

// Add image_url column to hotels table if it doesn't exist
$sql = "SHOW COLUMNS FROM tb_hotels LIKE 'image_url'";
$result = mysqli_query($conn, $sql);
if(mysqli_num_rows($result) == 0) {
    $sql = "ALTER TABLE tb_hotels ADD image_url VARCHAR(255)";
    if(!mysqli_query($conn, $sql)) {
        die("Error adding image_url column: " . mysqli_error($conn));
    }
}

// Array of high-quality hotel images from Unsplash
$hotel_images = [
    "https://images.unsplash.com/photo-1566073771259-6a8506099945?w=800", // Luxury hotel 1
    "https://images.unsplash.com/photo-1582719508461-905c673771fd?w=800", // Luxury hotel 2
    "https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?w=800", // Luxury hotel 3
    "https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?w=800", // Luxury hotel 4
    "https://images.unsplash.com/photo-1584132967334-10e028bd69f7?w=800", // Luxury hotel 5
    "https://images.unsplash.com/photo-1445019980597-93fa8acb246c?w=800", // Luxury hotel 6
    "https://images.unsplash.com/photo-1551882547-ff40c63fe5fa?w=800", // Luxury hotel 7
    "https://images.unsplash.com/photo-1566665797739-1674de7a421a?w=800", // Luxury hotel 8
    "https://images.unsplash.com/photo-1571003123894-1f0594d2b5d9?w=800", // Luxury hotel 9
    "https://images.unsplash.com/photo-1564501049412-61c2a3083791?w=800"  // Luxury hotel 10
];

// Get all hotels
$sql = "SELECT id_hotel FROM tb_hotels";
$result = mysqli_query($conn, $sql);

if($result) {
    $i = 0;
    while($row = mysqli_fetch_assoc($result)) {
        // Assign an image to each hotel
        $image_url = $hotel_images[$i % count($hotel_images)];
        
        // Update the hotel with the image URL
        $update_sql = "UPDATE tb_hotels SET image_url = ? WHERE id_hotel = ?";
        $stmt = mysqli_prepare($conn, $update_sql);
        mysqli_stmt_bind_param($stmt, "si", $image_url, $row['id_hotel']);
        
        if(!mysqli_stmt_execute($stmt)) {
            echo "Error updating hotel " . $row['id_hotel'] . ": " . mysqli_error($conn) . "<br>";
        }
        
        $i++;
    }
    
    echo "<div style='text-align: center; margin-top: 50px; font-family: Arial, sans-serif;'>";
    echo "<h2 style='color: #4e73df;'>Images ajoutées avec succès!</h2>";
    echo "<p>Toutes les images ont été ajoutées aux hôtels.</p>";
    echo "<p><a href='../index.php' style='background: #4e73df; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin-top: 20px;'>Voir le site</a></p>";
    echo "</div>";
} else {
    echo "Error getting hotels: " . mysqli_error($conn);
}

mysqli_close($conn);
?>
