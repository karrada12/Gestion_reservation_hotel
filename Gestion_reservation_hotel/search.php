<?php
session_start();
require_once "includes/config.php";

$destination = isset($_GET['destination']) ? $_GET['destination'] : '';
$check_in = isset($_GET['check_in']) ? $_GET['check_in'] : '';
$check_out = isset($_GET['check_out']) ? $_GET['check_out'] : '';

$sql = "SELECT h.*, COUNT(c.id_chambre) as total_chambres 
        FROM tb_hotels h 
        LEFT JOIN tb_chambres c ON h.id_hotel = c.id_hotel 
        WHERE h.adresse LIKE ? OR h.nom_hotel LIKE ?
        GROUP BY h.id_hotel";

$search_term = "%$destination%";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ss", $search_term, $search_term);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Résultats de recherche - Gloobin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="css/navbar-style.css" rel="stylesheet">
    <link href="css/search-style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#hotels">Nos Hôtels</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Contact</a>
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

    <!-- Search Results -->
    <div class="search-container">
        <div class="container">
            <!-- Search Form -->
            <form class="search-form" action="search.php" method="GET">
                <div class="row g-3">
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="destination" 
                               value="<?php echo htmlspecialchars($destination); ?>" 
                               placeholder="Destination" required>
                    </div>
                    <div class="col-md-3">
                        <input type="date" class="form-control" name="check_in" 
                               value="<?php echo htmlspecialchars($check_in); ?>" required>
                    </div>
                    <div class="col-md-3">
                        <input type="date" class="form-control" name="check_out" 
                               value="<?php echo htmlspecialchars($check_out); ?>" required>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn book-now-btn w-100">Rechercher</button>
                    </div>
                </div>
            </form>

            <div class="row">
                <!-- Filters -->
                <div class="col-md-3">
                    <div class="filters">
                        <div class="filter-section">
                            <h3 class="filter-title">Prix</h3>
                            <div class="filter-options">
                                <label class="filter-option">
                                    <input type="checkbox"> Moins de 500 DHS
                                </label>
                                <label class="filter-option">
                                    <input type="checkbox"> 500 - 1000 DHS
                                </label>
                                <label class="filter-option">
                                    <input type="checkbox"> Plus de 1000 DHS
                                </label>
                            </div>
                        </div>
                        <div class="filter-section">
                            <h3 class="filter-title">Équipements</h3>
                            <div class="filter-options">
                                <label class="filter-option">
                                    <input type="checkbox"> WiFi gratuit
                                </label>
                                <label class="filter-option">
                                    <input type="checkbox"> Piscine
                                </label>
                                <label class="filter-option">
                                    <input type="checkbox"> Parking
                                </label>
                                <label class="filter-option">
                                    <input type="checkbox"> Restaurant
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Results -->
                <div class="col-md-9">
                    <div class="search-results">
                        <?php
                        if(mysqli_num_rows($result) > 0) {
                            while($hotel = mysqli_fetch_assoc($result)) {
                                ?>
                                <div class="hotel-search-card">
                                    <div class="hotel-image-container">
                                        <?php if($hotel['nom_hotel'] == 'Movenpick Hotel Casablanca'): ?>
                                            <img src="https://media-cdn.tripadvisor.com/media/photo-s/1c/a8/f8/2d/le-16eme-floor-lounge.jpg" 
                                                 alt="<?php echo htmlspecialchars($hotel['nom_hotel']); ?>">
                                        <?php elseif($hotel['nom_hotel'] == 'Royal Mansour Marrakech'): ?>
                                            <img src="https://dynamic-media-cdn.tripadvisor.com/media/photo-o/25/3e/95/fe/royal-mansour-marrakech.jpg" 
                                                 alt="<?php echo htmlspecialchars($hotel['nom_hotel']); ?>">
                                        <?php elseif($hotel['nom_hotel'] == 'Riad Fès'): ?>
                                            <img src="https://riadfes.com/_novaimg/4632223-1425597_0_0_1954_1464_1200_900.jpg" 
                                                 alt="<?php echo htmlspecialchars($hotel['nom_hotel']); ?>">
                                        <?php elseif($hotel['nom_hotel'] == 'Sofitel Agadir Royal Bay Resort'): ?>
                                            <img src="https://www.avenuedesvoyages.fr/wp-content/uploads/2020/07/Sofitel-Agadir-Royal-Bay-Resort-6.jpg" 
                                                 alt="<?php echo htmlspecialchars($hotel['nom_hotel']); ?>">
                                        <?php else: ?>
                                            <img src="<?php echo !empty($hotel['image']) ? $hotel['image'] : 'images/hotel-default.jpg'; ?>" 
                                                 alt="<?php echo htmlspecialchars($hotel['nom_hotel']); ?>">
                                        <?php endif; ?>
                                    </div>
                                    <div class="hotel-info-container">
                                        <div class="hotel-header">
                                            <h2 class="hotel-name"><?php echo htmlspecialchars($hotel['nom_hotel']); ?></h2>
                                            <div class="hotel-location">
                                                <i class="fas fa-map-marker-alt"></i>
                                                <?php echo htmlspecialchars($hotel['adresse']); ?>
                                            </div>
                                        </div>
                                        <div class="hotel-details">
                                            <div class="amenities">
                                                <span class="amenity">
                                                    <i class="fas fa-bed"></i>
                                                    <?php echo $hotel['total_chambres']; ?> chambres
                                                </span>
                                                <span class="amenity">
                                                    <i class="fas fa-wifi"></i>
                                                    WiFi gratuit
                                                </span>
                                                <span class="amenity">
                                                    <i class="fas fa-parking"></i>
                                                    Parking
                                                </span>
                                            </div>
                                            <p class="hotel-description">
                                                Un séjour confortable vous attend dans cet établissement élégant.
                                            </p>
                                        </div>
                                        <div class="hotel-footer">
                                            <div class="price-info">
                                                <span class="price">500 DHS</span>
                                                <span class="price-period">par nuit</span>
                                            </div>
                                            <a href="hotel_detail.php?id=<?php echo $hotel['id_hotel']; ?>" 
                                               class="book-now-btn">
                                                <i class="fas fa-calendar-check"></i>
                                                Réserver maintenant
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            }
                        } else {
                            ?>
                            <div class="no-results">
                                <i class="fas fa-search"></i>
                                <h3>Aucun hôtel trouvé</h3>
                                <p>Essayez de modifier vos critères de recherche</p>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
