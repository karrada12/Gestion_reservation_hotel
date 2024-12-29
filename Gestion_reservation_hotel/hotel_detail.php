<?php
session_start();
require_once "includes/config.php";

if (!isset($_GET["id"]) || empty($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: index.php");
    exit;
}

$id = intval($_GET["id"]);
$sql = "SELECT * FROM tb_hotels WHERE id_hotel = ?";

if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $id);
    
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        
        if ($hotel = mysqli_fetch_assoc($result)) {
            $hotel_name = $hotel["nom_hotel"];
            $description = $hotel["description"];
            $adresse = $hotel["adresse"];
            $ville = $hotel["ville"] ?? "Non spécifiée";
            $pays = $hotel["pays"] ?? "Non spécifié";
            $etoiles = $hotel["etoiles"] ?? 3;
            $image = $hotel["image"] ?? "images/hotel-default.jpg";
            $telephone = $hotel["telephone"] ?? "Non spécifié";
            $email = $hotel["email"] ?? "contact@hotel.com";
            $site_web = $hotel["site_web"] ?? "#";
        } else {
            $_SESSION['error'] = "Hôtel non trouvé";
            header("Location: index.php");
            exit;
        }
    } else {
        $_SESSION['error'] = "Erreur lors de la recherche de l'hôtel";
        header("Location: index.php");
        exit;
    }
    mysqli_stmt_close($stmt);
} else {
    $_SESSION['error'] = "Erreur de préparation de la requête";
    header("Location: index.php");
    exit;
}

// Récupérer les chambres de l'hôtel
$sql_rooms = "SELECT * FROM tb_chambres WHERE id_hotel = ?";
$rooms = [];

if ($stmt = mysqli_prepare($conn, $sql_rooms)) {
    mysqli_stmt_bind_param($stmt, "i", $id);
    
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        
        while ($room = mysqli_fetch_assoc($result)) {
            $rooms[] = $room;
        }
    }
    mysqli_stmt_close($stmt);
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $hotel_name; ?> - Détails</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/main-style.css" rel="stylesheet">
    <style>
        .hotel-detail-container {
            padding: 80px 0;
            background-color: #f8f9fa;
        }

        .hotel-header {
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('<?php echo $image; ?>');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 150px 0 50px;
            margin-bottom: 40px;
            text-align: center;
        }

        .hotel-header h1 {
            font-size: 3rem;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
            margin-bottom: 20px;
        }

        .hotel-info {
            background: white;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            padding: 30px;
            margin-bottom: 30px;
        }

        .hotel-info h2 {
            color: #2c3e50;
            font-size: 1.8rem;
            margin-bottom: 20px;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }

        .amenities-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }

        .amenity-item {
            display: flex;
            align-items: center;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 8px;
            transition: transform 0.2s;
        }

        .amenity-item:hover {
            transform: translateY(-2px);
            background: #e9ecef;
        }

        .amenity-item i {
            color: #3498db;
            margin-right: 10px;
            font-size: 1.2rem;
        }

        .room-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            overflow: hidden;
            transition: transform 0.3s;
        }

        .room-card:hover {
            transform: translateY(-5px);
        }

        .room-image {
            height: 200px;
            overflow: hidden;
        }

        .room-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s;
        }

        .room-card:hover .room-image img {
            transform: scale(1.1);
        }

        .room-info {
            padding: 20px;
        }

        .room-info h3 {
            color: #2c3e50;
            margin-bottom: 15px;
        }

        .room-features {
            margin: 15px 0;
            padding: 0;
            list-style: none;
        }

        .room-features li {
            display: inline-block;
            margin-right: 15px;
            color: #666;
        }

        .room-features i {
            color: #3498db;
            margin-right: 5px;
        }

        .room-price {
            font-size: 1.5rem;
            color: #2c3e50;
            font-weight: 700;
            margin: 15px 0;
        }

        .btn-reserve {
            background: #3498db;
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 25px;
            transition: all 0.3s;
        }

        .btn-reserve:hover {
            background: #2980b9;
            transform: translateY(-2px);
        }

        .map-container {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            margin-top: 30px;
        }

        .reviews-section {
            background: white;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            padding: 30px;
            margin-top: 30px;
        }

        .review-card {
            border-bottom: 1px solid #eee;
            padding: 20px 0;
        }

        .review-card:last-child {
            border-bottom: none;
        }

        .reviewer-info {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .reviewer-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 15px;
            background: #3498db;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        .review-rating {
            color: #f1c40f;
            margin-bottom: 10px;
        }

        .review-date {
            color: #7f8c8d;
            font-size: 0.9rem;
        }

        .carousel-item img {
            height: 400px;
            object-fit: cover;
            border-radius: 10px;
        }

        .carousel-control-prev,
        .carousel-control-next {
            width: 5%;
            background: rgba(0,0,0,0.2);
            border-radius: 50%;
            height: 50px;
            top: 50%;
            transform: translateY(-50%);
        }

        .carousel-control-prev {
            left: 15px;
        }

        .carousel-control-next {
            right: 15px;
        }

        .carousel-control-prev:hover,
        .carousel-control-next:hover {
            background: rgba(0,0,0,0.5);
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="hotel-header" style="background-image: url('https://dynamic-media-cdn.tripadvisor.com/media/photo-o/29/34/f9/e9/sofitel-agadir-royal.jpg');">
        <div class="container">
            <h1><?php echo htmlspecialchars($hotel_name); ?></h1>
            <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($ville); ?></p>
        </div>
    </div>

    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <div class="hotel-info">
                    <div id="hotelCarousel" class="carousel slide mb-4" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            <div class="carousel-item active">
                                <img src="https://dynamic-media-cdn.tripadvisor.com/media/photo-o/29/34/f9/e9/sofitel-agadir-royal.jpg" class="d-block w-100" alt="Sofitel Agadir Royal Bay Resort">
                            </div>
                            <div class="carousel-item">
                                <img src="https://th.bing.com/th/id/R.cc98cb245ead069d39e5be7b63281bea?rik=ugV0OUYYeAxDAA&pid=ImgRaw&r=0" class="d-block w-100" alt="Sofitel Agadir Royal Bay Resort - Vue extérieure">
                            </div>
                            <div class="carousel-item">
                                <img src="https://th.bing.com/th/id/R.ea9395506aa5c8765bc92706a30ee341?rik=rY9TXrMe8mWzQA&pid=ImgRaw&r=0" class="d-block w-100" alt="Sofitel Agadir Royal Bay Resort - Piscine">
                            </div>
                            <div class="carousel-item">
                                <img src="https://images.classic-collection.co.uk/content/DataObjects/PropertyReference/Image/image_612_v16.jpg" class="d-block w-100" alt="Sofitel Agadir Royal Bay Resort - Vue aérienne">
                            </div>
                            <div class="carousel-item">
                                <img src="https://th.bing.com/th/id/OIP.S6usydvWJ6IxjGBz5VhNlAHaEH?rs=1&pid=ImgDetMain" class="d-block w-100" alt="Sofitel Agadir Royal Bay Resort - Plage">
                            </div>
                            <div class="carousel-item">
                                <img src="https://www.avenuedesvoyages.fr/wp-content/uploads/2020/07/Sofitel-Agadir-Royal-Bay-Resort-6.jpg" class="d-block w-100" alt="Sofitel Agadir Royal Bay Resort - Vue de nuit">
                            </div>
                            <div class="carousel-item">
                                <img src="https://dynamic-media-cdn.tripadvisor.com/media/photo-o/29/34/f9/ec/sofitel-agadir-royal.jpg" class="d-block w-100" alt="Sofitel Agadir Royal Bay Resort">
                            </div>
                            <div class="carousel-item">
                                <img src="https://dynamic-media-cdn.tripadvisor.com/media/photo-o/0d/9c/ab/57/pool--v13940105.jpg" class="d-block w-100" alt="Sofitel Agadir Royal Bay Resort">
                            </div>
                            <div class="carousel-item">
                                <img src="https://dynamic-media-cdn.tripadvisor.com/media/photo-o/29/34/f9/ea/sofitel-agadir-royal.jpg" class="d-block w-100" alt="Sofitel Agadir Royal Bay Resort">
                            </div>
                        </div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#hotelCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#hotelCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    </div>
                    <h2>À propos de l'hôtel</h2>
                    <p><?php echo nl2br(htmlspecialchars($description)); ?></p>
                    
                    <h2>Équipements et services</h2>
                    <div class="amenities-list">
                        <div class="amenity-item">
                            <i class="fas fa-wifi"></i>
                            <span>WiFi gratuit</span>
                        </div>
                        <div class="amenity-item">
                            <i class="fas fa-swimming-pool"></i>
                            <span>Piscine</span>
                        </div>
                        <div class="amenity-item">
                            <i class="fas fa-parking"></i>
                            <span>Parking gratuit</span>
                        </div>
                        <div class="amenity-item">
                            <i class="fas fa-spa"></i>
                            <span>Spa</span>
                        </div>
                        <div class="amenity-item">
                            <i class="fas fa-utensils"></i>
                            <span>Restaurant</span>
                        </div>
                        <div class="amenity-item">
                            <i class="fas fa-dumbbell"></i>
                            <span>Salle de sport</span>
                        </div>
                    </div>
                </div>

                <div class="reviews-section">
                    <h2>Avis des clients</h2>
                    <div class="review-card">
                        <div class="reviewer-info">
                            <div class="reviewer-avatar">JD</div>
                            <div>
                                <h5>John Doe</h5>
                                <div class="review-rating">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star-half-alt"></i>
                                </div>
                            </div>
                        </div>
                        <p>Excellent séjour ! L'hôtel est magnifique et le personnel très attentionné.</p>
                        <div class="review-date">Il y a 2 jours</div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="hotel-info">
                    <h2>Chambres disponibles</h2>
                    <?php foreach ($rooms as $room): ?>
                        <div class="room-card">
                            <div class="room-image">
                                <img src="<?php echo !empty($room['image']) ? $room['image'] : 'https://via.placeholder.com/300x200'; ?>" 
                                     alt="<?php echo htmlspecialchars($room['type_chambre']); ?>">
                            </div>
                            <div class="room-info">
                                <h3><?php echo htmlspecialchars($room['type_chambre']); ?></h3>
                                <ul class="room-features">
                                    <li><i class="fas fa-user"></i> <?php echo $room['capacite']; ?> personnes</li>
                                    <li><i class="fas fa-bed"></i> Grand lit</li>
                                </ul>
                                <div class="room-price">
                                    <?php echo number_format($room['prix'], 2); ?> DHS / nuit
                                </div>
                                <a href="reservation.php?room_id=<?php echo $room['id_chambre']; ?>" 
                                   class="btn btn-reserve w-100">
                                    Réserver maintenant
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Carte conservée -->
        <div class="map-container">
            <iframe 
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3323.846447471348!2d-7.589843785271385!3d33.57382048073799!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMzPCsDM0JzI1LjgiTiA3wrAzNScyMy40Ilc!5e0!3m2!1sfr!2sma!4v1635789012345!5m2!1sfr!2sma"
                width="100%" 
                height="100%" 
                style="border:0;" 
                allowfullscreen="" 
                loading="lazy">
            </iframe>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
