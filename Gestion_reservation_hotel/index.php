<?php
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réservation d'Hôtel</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="css/main-style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <span>Gloobin</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#hotels">Nos Hôtels</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">Contact</a>
                    </li>
                    <?php if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="profile.php">Mon Profil</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">Déconnexion</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Connexion</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">Inscription</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-content">
            <h1>Trouvez l'hôtel parfait pour votre séjour</h1>
            <p>Découvrez notre sélection d'hôtels de luxe et réservez votre chambre dès maintenant</p>
        </div>
    </section>

    <!-- Search Form -->
    <div class="container">
        <form class="search-form" action="search.php" method="GET">
            <div class="row g-3">
                <div class="col-md-3">
                    <input type="text" class="form-control" name="destination" placeholder="Destination" required>
                </div>
                <div class="col-md-3">
                    <input type="date" class="form-control" name="check_in" required>
                </div>
                <div class="col-md-3">
                    <input type="date" class="form-control" name="check_out" required>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn search-btn w-100">Rechercher</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Hotels Section -->
    <section class="hotels-section" id="hotels">
        <div class="container">
            <div class="section-title">
                <h2>Nos Hôtels</h2>
                <p>Découvrez notre sélection d'établissements de qualité</p>
            </div>

            <div class="row">
                <?php
                require_once "includes/config.php";
                
                $sql = "SELECT h.*, COUNT(c.id_chambre) as total_chambres 
                        FROM tb_hotels h 
                        LEFT JOIN tb_chambres c ON h.id_hotel = c.id_hotel 
                        GROUP BY h.id_hotel 
                        ORDER BY h.nom_hotel";
                $result = mysqli_query($conn, $sql);

                if(mysqli_num_rows($result) > 0) {
                    while($hotel = mysqli_fetch_assoc($result)) {
                        ?>
                        <div class="col-12">
                            <div class="hotel-card">
                                <div class="hotel-image">
                                    <?php if($hotel['nom_hotel'] == 'Movenpick Hotel Casablanca'): ?>
                                        <img src="https://media-cdn.tripadvisor.com/media/photo-s/1c/a8/f8/2d/le-16eme-floor-lounge.jpg" 
                                             alt="<?php echo htmlspecialchars($hotel['nom_hotel']); ?>">
                                    <?php elseif($hotel['nom_hotel'] == 'Riad Dar El Kebira Rabat'): ?>
                                        <img src="https://th.bing.com/th/id/OIP.43NDiyQFFWtHzpV6vTCflwHaE8?w=550&h=367&rs=1&pid=ImgDetMain" 
                                             alt="<?php echo htmlspecialchars($hotel['nom_hotel']); ?>">
                                    <?php elseif($hotel['nom_hotel'] == 'Sofitel Rabat Jardin des Roses'): ?>
                                        <img src="https://th.bing.com/th/id/OIP.YRcL4nUg55rv3i9hdp3nHAHaEq?rs=1&pid=ImgDetMain" 
                                             alt="<?php echo htmlspecialchars($hotel['nom_hotel']); ?>">
                                    <?php elseif($hotel['nom_hotel'] == 'Sofitel Agadir Royal Bay Resort'): ?>
                                        <img src="https://th.bing.com/th/id/OIP.l5gag0_o5x5wlF3EFSEs_QHaE7?rs=1&pid=ImgDetMain" 
                                             alt="<?php echo htmlspecialchars($hotel['nom_hotel']); ?>">
                                    <?php elseif($hotel['nom_hotel'] == 'Royal Mansour Marrakech'): ?>
                                        <img src="https://th.bing.com/th/id/OIP.89uZXQ9YSe-oRMmCH91lYgHaEK?rs=1&pid=ImgDetMain" 
                                             alt="<?php echo htmlspecialchars($hotel['nom_hotel']); ?>">
                                    <?php elseif($hotel['nom_hotel'] == 'Riad Fès'): ?>
                                        <img src="https://cf.bstatic.com/xdata/images/hotel/max1024x768/342697245.jpg?k=70c9c595f24ec90d4724a25b5b632a1eb02890d739d872901d8cc659d7f9a957&o=&hp=1" 
                                             alt="<?php echo htmlspecialchars($hotel['nom_hotel']); ?>">
                                    <?php else: ?>
                                        <img src="<?php echo !empty($hotel['image']) ? $hotel['image'] : 'images/hotel-default.jpg'; ?>" 
                                             alt="<?php echo htmlspecialchars($hotel['nom_hotel']); ?>">
                                    <?php endif; ?>
                                </div>
                                <div class="hotel-info">
                                    <div>
                                        <h3><?php echo htmlspecialchars($hotel['nom_hotel']); ?></h3>
                                        <p><?php echo htmlspecialchars($hotel['adresse']); ?></p>
                                        <ul class="hotel-features">
                                            <li><i class="fas fa-bed"></i> <?php echo $hotel['total_chambres']; ?> chambres disponibles</li>
                                            <li><i class="fas fa-wifi"></i> WiFi gratuit</li>
                                            <li><i class="fas fa-parking"></i> Parking gratuit</li>
                                            <li><i class="fas fa-utensils"></i> Restaurant</li>
                                        </ul>
                                    </div>
                                    <div class="price-booking">
                                        <span class="price">À partir de <?php echo number_format(500, 0, ',', ' '); ?> DHS</span>
                                        <a href="hotel_details.php?id=<?php echo $hotel['id_hotel']; ?>" class="book-btn">
                                            <i class="fas fa-calendar-check"></i> Réserver maintenant
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    echo "<p class='text-center'>Aucun hôtel disponible pour le moment.</p>";
                }
                ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h3>À propos</h3>
                    <p>Notre plateforme de réservation d'hôtels vous permet de trouver et de réserver facilement votre hébergement idéal.</p>
                </div>
                <div class="col-md-4">
                    <h3>Liens utiles</h3>
                    <ul class="footer-links">
                        <li><a href="#hotels">Nos Hôtels</a></li>
                        <li><a href="about.php">À propos</a></li>
                        <li><a href="contact.php">Contact</a></li>
                        <li><a href="terms.php">Conditions générales</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h3>Contact</h3>
                    <ul class="footer-links">
                        <li><i class="fas fa-phone"></i> +212 123 456 789</li>
                        <li><i class="fas fa-envelope"></i> contact@hotelbooking.com</li>
                        <li><i class="fas fa-map-marker-alt"></i> 123 Rue Example, Ville, Maroc</li>
                    </ul>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
