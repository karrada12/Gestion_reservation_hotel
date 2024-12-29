<?php
require_once "../includes/config.php";

// Add image_url column to hotels table
$sql = "ALTER TABLE tb_hotels ADD COLUMN IF NOT EXISTS image_url VARCHAR(255)";
if(mysqli_query($conn, $sql)) {
    // Array of professional hotel images
    $hotel_images = [
        "https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=800&q=80",
        "https://images.unsplash.com/photo-1582719508461-905c673771fd?auto=format&fit=crop&w=800&q=80",
        "https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?auto=format&fit=crop&w=800&q=80",
        "https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?auto=format&fit=crop&w=800&q=80",
        "https://images.unsplash.com/photo-1584132967334-10e028bd69f7?auto=format&fit=crop&w=800&q=80",
        "https://images.unsplash.com/photo-1445019980597-93fa8acb246c?auto=format&fit=crop&w=800&q=80",
        "https://images.unsplash.com/photo-1551882547-ff40c63fe5fa?auto=format&fit=crop&w=800&q=80",
        "https://images.unsplash.com/photo-1566665797739-1674de7a421a?auto=format&fit=crop&w=800&q=80"
    ];

    // Update each hotel with a random image
    $sql = "SELECT id_hotel FROM tb_hotels";
    $result = mysqli_query($conn, $sql);
    
    while($row = mysqli_fetch_assoc($result)) {
        $random_image = $hotel_images[array_rand($hotel_images)];
        $update_sql = "UPDATE tb_hotels SET image_url = ? WHERE id_hotel = ?";
        $stmt = mysqli_prepare($conn, $update_sql);
        mysqli_stmt_bind_param($stmt, "si", $random_image, $row['id_hotel']);
        mysqli_stmt_execute($stmt);
    }

    echo "<div style='text-align: center; margin-top: 50px; font-family: Arial, sans-serif;'>";
    echo "<h2 style='color: #4e73df;'>Images ajoutées avec succès!</h2>";
    echo "<p>Les images ont été ajoutées à tous les hôtels.</p>";
    echo "<a href='../index.php' style='display: inline-block; margin-top: 20px; padding: 10px 20px; background-color: #4e73df; color: white; text-decoration: none; border-radius: 5px;'>Voir les hôtels</a>";
    echo "</div>";
} else {
    echo "Erreur lors de la modification de la table: " . mysqli_error($conn);
}

mysqli_close($conn);
?>
