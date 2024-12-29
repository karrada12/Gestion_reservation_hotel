<?php
session_start();
require_once "includes/config.php";

// Vérifier si l'ID de l'hôtel est fourni
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$hotel_id = $_GET['id'];
$error_message = "";
$success_message = "";

// Récupérer les détails de l'hôtel
$sql = "SELECT h.*, COUNT(c.id_chambre) as total_chambres 
        FROM tb_hotels h 
        LEFT JOIN tb_chambres c ON h.id_hotel = c.id_hotel 
        WHERE h.id_hotel = ?
        GROUP BY h.id_hotel";

if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $hotel_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $hotel = mysqli_fetch_assoc($result);

    if (!$hotel) {
        header("Location: index.php");
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}

// Traitement de la réservation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    $check_in = $_POST['check_in'] ?? '';
    $check_out = $_POST['check_out'] ?? '';
    $nb_personnes = $_POST['nb_personnes'] ?? '';
    $type_chambre = $_POST['type_chambre'] ?? '';

    if (empty($check_in) || empty($check_out) || empty($nb_personnes) || empty($type_chambre)) {
        $error_message = "Veuillez remplir tous les champs.";
    } else {
        // Vérifier la disponibilité
        $sql_check = "SELECT id_chambre FROM tb_chambres 
                     WHERE id_hotel = ? AND type_chambre = ? 
                     AND id_chambre NOT IN (
                         SELECT id_chambre FROM tb_reservations 
                         WHERE (date_debut BETWEEN ? AND ?) 
                         OR (date_fin BETWEEN ? AND ?)
                         OR (date_debut <= ? AND date_fin >= ?)
                     )
                     LIMIT 1";

        if ($stmt_check = mysqli_prepare($conn, $sql_check)) {
            mysqli_stmt_bind_param($stmt_check, "isssssss", 
                $hotel_id, $type_chambre, 
                $check_in, $check_out, 
                $check_in, $check_out,
                $check_in, $check_out
            );
            mysqli_stmt_execute($stmt_check);
            $result_check = mysqli_stmt_get_result($stmt_check);

            if ($chambre = mysqli_fetch_assoc($result_check)) {
                // Créer la réservation
                $sql_reserve = "INSERT INTO tb_reservations (id_client, id_hotel, id_chambre, date_debut, date_fin, nb_personnes, statut) 
                               VALUES (?, ?, ?, ?, ?, ?, 'confirmé')";
                
                if ($stmt_reserve = mysqli_prepare($conn, $sql_reserve)) {
                    mysqli_stmt_bind_param($stmt_reserve, "iiissi", 
                        $_SESSION["id"], 
                        $hotel_id,
                        $chambre['id_chambre'],
                        $check_in,
                        $check_out,
                        $nb_personnes
                    );

                    if (mysqli_stmt_execute($stmt_reserve)) {
                        $success_message = "Réservation effectuée avec succès !";
                    } else {
                        $error_message = "Erreur lors de la réservation. Veuillez réessayer.";
                    }
                }
            } else {
                $error_message = "Désolé, aucune chambre n'est disponible pour ces dates.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($hotel['nom_hotel']); ?> - Gloobin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/navbar-style.css" rel="stylesheet">
    <link href="css/hotel-details-v2.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container hotel-details">
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="reservation-form">
                    <h2>
                        <i class="fas fa-concierge-bell"></i>
                        Réserver votre séjour
                    </h2>
                    
                    <?php if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-info-circle"></i>
                            <div>Veuillez vous <a href="login.php">connecter</a> pour effectuer une réservation.</div>
                        </div>
                    <?php else: ?>
                        <?php if ($error_message): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle"></i>
                                <div><?php echo $error_message; ?></div>
                            </div>
                        <?php endif; ?>
                        <?php if ($success_message): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i>
                                <div><?php echo $success_message; ?></div>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-calendar-alt me-2"></i>
                                    Date d'arrivée
                                </label>
                                <input type="date" class="form-control" id="check_in" name="check_in" required 
                                       min="<?php echo date('Y-m-d'); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-calendar-alt me-2"></i>
                                    Date de départ
                                </label>
                                <input type="date" class="form-control" id="check_out" name="check_out" required 
                                       min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-user-friends me-2"></i>
                                    Nombre de personnes
                                </label>
                                <select class="form-control" id="nb_personnes" name="nb_personnes" required>
                                    <?php for($i = 1; $i <= 4; $i++): ?>
                                        <option value="<?php echo $i; ?>"><?php echo $i; ?> personne<?php echo $i > 1 ? 's' : ''; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-bed me-2"></i>
                                    Type de chambre
                                </label>
                                <select class="form-control" id="type_chambre" name="type_chambre" required>
                                    <option value="standard">Chambre Standard</option>
                                    <option value="deluxe">Chambre Deluxe</option>
                                    <option value="suite">Suite Luxe</option>
                                </select>
                            </div>
                            
                            <button type="submit" class="btn btn-reserve">
                                <i class="fas fa-calendar-check me-2"></i>
                                Réserver maintenant
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="hotel-main-info">
                    <div class="hotel-header">
                        <h1><?php echo htmlspecialchars($hotel['nom_hotel']); ?></h1>
                        <p class="lead">
                            <i class="fas fa-map-marker-alt"></i>
                            <span><?php echo htmlspecialchars($hotel['adresse']); ?></span>
                        </p>
                    </div>

                    <div class="price-info">
                        <div class="price">À partir de <?php echo number_format(500, 0, ',', ' '); ?> DHS</div>
                        <div class="price-detail">par nuit, taxes et petit-déjeuner inclus</div>
                    </div>

                    <div class="hotel-info">
                        <div class="info-section">
                            <h3>Photos de l'hôtel</h3>
                            <div class="hotel-images row">
                                <?php if($hotel['nom_hotel'] == 'Movenpick Hotel Casablanca'): ?>
                                    <div class="col-md-6 mb-4">
                                        <img src="https://media-cdn.tripadvisor.com/media/photo-s/1c/a8/f8/2d/le-16eme-floor-lounge.jpg" class="img-fluid rounded" alt="Hotel Image 1">
                                    </div>
                                    <div class="col-md-6 mb-4">
                                        <img src="https://queridohotels.com/wp-content/uploads/2021/09/DSC00700-HDR.jpg" class="img-fluid rounded" alt="Hotel Image 2">
                                    </div>
                                    <div class="col-md-6 mb-4">
                                        <img src="https://th.bing.com/th/id/R.e0ee80dfe0af791c7cdb94bfde996484?rik=kHWty19Ury3i8Q&pid=ImgRaw&r=0" class="img-fluid rounded" alt="Hotel Image 3">
                                    </div>
                                    <div class="col-md-6 mb-4">
                                        <img src="https://www.travelplusstyle.com/wp-content/gallery/royal-mansour-marrakech/74832858-h1-la_table.jpg" class="img-fluid rounded" alt="Hotel Image 4">
                                    </div>
                                <?php elseif($hotel['nom_hotel'] == 'Riad Dar El Kebira Rabat'): ?>
                                    <div class="col-md-6 mb-4">
                                        <img src="https://th.bing.com/th/id/OIP.43NDiyQFFWtHzpV6vTCflwHaE8?w=550&h=367&rs=1&pid=ImgDetMain" class="img-fluid rounded" alt="Hotel Image 1">
                                    </div>
                                    <div class="col-md-6 mb-4">
                                        <img src="https://queridohotels.com/wp-content/uploads/2021/09/DSC00700-HDR.jpg" class="img-fluid rounded" alt="Hotel Image 2">
                                    </div>
                                    <div class="col-md-6 mb-4">
                                        <img src="https://th.bing.com/th/id/R.e0ee80dfe0af791c7cdb94bfde996484?rik=kHWty19Ury3i8Q&pid=ImgRaw&r=0" class="img-fluid rounded" alt="Hotel Image 3">
                                    </div>
                                    <div class="col-md-6 mb-4">
                                        <img src="https://www.travelplusstyle.com/wp-content/gallery/royal-mansour-marrakech/74832858-h1-la_table.jpg" class="img-fluid rounded" alt="Hotel Image 4">
                                    </div>
                                <?php elseif($hotel['nom_hotel'] == 'Sofitel Rabat Jardin des Roses'): ?>
                                    <div class="col-md-6 mb-4">
                                        <img src="https://th.bing.com/th/id/OIP.YRcL4nUg55rv3i9hdp3nHAHaEq?rs=1&pid=ImgDetMain" class="img-fluid rounded" alt="Hotel Image 1">
                                    </div>
                                    <div class="col-md-6 mb-4">
                                        <img src="https://queridohotels.com/wp-content/uploads/2021/09/DSC00700-HDR.jpg" class="img-fluid rounded" alt="Hotel Image 2">
                                    </div>
                                    <div class="col-md-6 mb-4">
                                        <img src="https://th.bing.com/th/id/R.e0ee80dfe0af791c7cdb94bfde996484?rik=kHWty19Ury3i8Q&pid=ImgRaw&r=0" class="img-fluid rounded" alt="Hotel Image 3">
                                    </div>
                                    <div class="col-md-6 mb-4">
                                        <img src="https://www.travelplusstyle.com/wp-content/gallery/royal-mansour-marrakech/74832858-h1-la_table.jpg" class="img-fluid rounded" alt="Hotel Image 4">
                                    </div>
                                <?php elseif($hotel['nom_hotel'] == 'Sofitel Agadir Royal Bay Resort'): ?>
                                    <div class="col-md-6 mb-4">
                                        <img src="https://th.bing.com/th/id/OIP.l5gag0_o5x5wlF3EFSEs_QHaE7?rs=1&pid=ImgDetMain" class="img-fluid rounded" alt="Hotel Image 1">
                                    </div>
                                    <div class="col-md-6 mb-4">
                                        <img src="https://queridohotels.com/wp-content/uploads/2021/09/DSC00700-HDR.jpg" class="img-fluid rounded" alt="Hotel Image 2">
                                    </div>
                                    <div class="col-md-6 mb-4">
                                        <img src="https://th.bing.com/th/id/R.e0ee80dfe0af791c7cdb94bfde996484?rik=kHWty19Ury3i8Q&pid=ImgRaw&r=0" class="img-fluid rounded" alt="Hotel Image 3">
                                    </div>
                                    <div class="col-md-6 mb-4">
                                        <img src="https://www.travelplusstyle.com/wp-content/gallery/royal-mansour-marrakech/74832858-h1-la_table.jpg" class="img-fluid rounded" alt="Hotel Image 4">
                                    </div>
                                <?php elseif($hotel['nom_hotel'] == 'Royal Mansour Marrakech'): ?>
                                    <div class="col-md-6 mb-4">
                                        <img src="https://th.bing.com/th/id/OIP.89uZXQ9YSe-oRMmCH91lYgHaEK?rs=1&pid=ImgDetMain" class="img-fluid rounded" alt="Hotel Image 1">
                                    </div>
                                    <div class="col-md-6 mb-4">
                                        <img src="https://queridohotels.com/wp-content/uploads/2021/09/DSC00700-HDR.jpg" class="img-fluid rounded" alt="Hotel Image 2">
                                    </div>
                                    <div class="col-md-6 mb-4">
                                        <img src="https://th.bing.com/th/id/R.e0ee80dfe0af791c7cdb94bfde996484?rik=kHWty19Ury3i8Q&pid=ImgRaw&r=0" class="img-fluid rounded" alt="Hotel Image 3">
                                    </div>
                                    <div class="col-md-6 mb-4">
                                        <img src="https://www.travelplusstyle.com/wp-content/gallery/royal-mansour-marrakech/74832858-h1-la_table.jpg" class="img-fluid rounded" alt="Hotel Image 4">
                                    </div>
                                <?php elseif($hotel['nom_hotel'] == 'Riad Fès'): ?>
                                    <div class="col-md-6 mb-4">
                                        <img src="https://cf.bstatic.com/xdata/images/hotel/max1024x768/342697245.jpg?k=70c9c595f24ec90d4724a25b5b632a1eb02890d739d872901d8cc659d7f9a957&o=&hp=1" class="img-fluid rounded" alt="Hotel Image 1">
                                    </div>
                                    <div class="col-md-6 mb-4">
                                        <img src="https://queridohotels.com/wp-content/uploads/2021/09/DSC00700-HDR.jpg" class="img-fluid rounded" alt="Hotel Image 2">
                                    </div>
                                    <div class="col-md-6 mb-4">
                                        <img src="https://th.bing.com/th/id/R.e0ee80dfe0af791c7cdb94bfde996484?rik=kHWty19Ury3i8Q&pid=ImgRaw&r=0" class="img-fluid rounded" alt="Hotel Image 3">
                                    </div>
                                    <div class="col-md-6 mb-4">
                                        <img src="https://www.travelplusstyle.com/wp-content/gallery/royal-mansour-marrakech/74832858-h1-la_table.jpg" class="img-fluid rounded" alt="Hotel Image 4">
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="info-section">
                            <h3>Description</h3>
                            <p>
                                Découvrez l'élégance et le raffinement de notre établissement. Niché dans un cadre 
                                exceptionnel, notre hôtel vous offre une expérience unique alliant confort moderne 
                                et service personnalisé. Chaque chambre a été méticuleusement conçue pour vous 
                                garantir un séjour inoubliable.
                            </p>
                            <p>
                                Notre équipe dévouée est à votre disposition 24h/24 pour répondre à tous vos besoins 
                                et faire de votre séjour un moment d'exception. Que vous soyez en voyage d'affaires 
                                ou en quête d'évasion, nous nous engageons à dépasser vos attentes.
                            </p>
                        </div>

                        <div class="info-section">
                            <h3>Équipements et services</h3>
                            <div class="amenities">
                                <div class="amenity-item">
                                    <i class="fas fa-wifi"></i>
                                    <span>WiFi haut débit gratuit</span>
                                </div>
                                <div class="amenity-item">
                                    <i class="fas fa-parking"></i>
                                    <span>Parking sécurisé</span>
                                </div>
                                <div class="amenity-item">
                                    <i class="fas fa-utensils"></i>
                                    <span>Restaurant gastronomique</span>
                                </div>
                                <div class="amenity-item">
                                    <i class="fas fa-swimming-pool"></i>
                                    <span>Piscine chauffée</span>
                                </div>
                                <div class="amenity-item">
                                    <i class="fas fa-spa"></i>
                                    <span>Spa & Bien-être</span>
                                </div>
                                <div class="amenity-item">
                                    <i class="fas fa-dumbbell"></i>
                                    <span>Salle de sport</span>
                                </div>
                                <div class="amenity-item">
                                    <i class="fas fa-concierge-bell"></i>
                                    <span>Service en chambre 24/7</span>
                                </div>
                                <div class="amenity-item">
                                    <i class="fas fa-coffee"></i>
                                    <span>Petit-déjeuner buffet</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Gérer les dates minimales pour le formulaire
            var today = new Date().toISOString().split('T')[0];
            var tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            tomorrow = tomorrow.toISOString().split('T')[0];
            
            document.getElementById('check_in').setAttribute('min', today);
            document.getElementById('check_out').setAttribute('min', tomorrow);
            
            // Gérer le changement de date d'arrivée
            document.getElementById('check_in').addEventListener('change', function() {
                var checkIn = new Date(this.value);
                var minCheckOut = new Date(checkIn);
                minCheckOut.setDate(minCheckOut.getDate() + 1);
                document.getElementById('check_out').setAttribute('min', minCheckOut.toISOString().split('T')[0]);
            });

            // Effet de scroll pour la navbar
            window.addEventListener('scroll', function() {
                var navbar = document.querySelector('.navbar');
                if (window.scrollY > 50) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }
            });
        });
    </script>
</body>
</html>
