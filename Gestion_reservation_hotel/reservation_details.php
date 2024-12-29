<?php
session_start();
require_once "includes/config.php";

// Vérifier si l'utilisateur est connecté
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Vérifier si l'ID de réservation est fourni
if(!isset($_GET["id"]) || empty($_GET["id"])) {
    header("location: mes_reservations.php");
    exit;
}

$id_reservation = $_GET["id"];
$id_client = $_SESSION["id_client"];

// Récupérer les détails de la réservation
$sql = "SELECT r.*, h.nom_hotel, h.adresse as hotel_adresse, h.description as hotel_description, 
               h.image as image_url, c.type_chambre, c.description as chambre_description, c.prix_nuit as prix
        FROM tb_reservations r 
        JOIN tb_chambres c ON r.id_chambre = c.id_chambre 
        JOIN tb_hotels h ON c.id_hotel = h.id_hotel 
        WHERE r.id_reservation = ? AND r.id_client = ?";

$reservation = null;
$error_message = null;

try {
    if($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "ii", $id_reservation, $id_client);
        if(mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            if(mysqli_num_rows($result) > 0) {
                $reservation = mysqli_fetch_assoc($result);
            } else {
                $error_message = "Réservation non trouvée.";
            }
        } else {
            $error_message = "Erreur lors de la récupération des détails de la réservation: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    } else {
        $error_message = "Erreur de préparation de la requête: " . mysqli_error($conn);
    }
} catch (Exception $e) {
    $error_message = "Une erreur est survenue: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails de la Réservation - Hotel Reservation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <style>
        .reservation-details {
            padding-top: 90px;
            min-height: 100vh;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8eb 100%);
        }
        .detail-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 0 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-bottom: 30px;
        }
        .detail-card .card-header {
            background: linear-gradient(135deg, #2c3e50, #3498db);
            color: white;
            padding: 20px;
            border: none;
        }
        .detail-card .card-body {
            padding: 30px;
        }
        .detail-item {
            margin-bottom: 20px;
        }
        .detail-label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        .detail-value {
            color: #34495e;
        }
        .hotel-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 10px;
        }
        .price {
            font-size: 1.5rem;
            color: #3498db;
            font-weight: 600;
        }
        .status-badge {
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.9rem;
        }
        .status-confirmed {
            background: #d4edda;
            color: #155724;
        }
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        .btn-action {
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .btn-action:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="reservation-details">
        <div class="container">
            <?php if($error_message): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo $error_message; ?>
                </div>
                <a href="mes_reservations.php" class="btn btn-primary">
                    <i class="fas fa-arrow-left me-2"></i>Retour aux réservations
                </a>
            <?php elseif($reservation): ?>
                <div class="row">
                    <div class="col-md-8">
                        <div class="detail-card">
                            <div class="card-header">
                                <h2 class="m-0">
                                    <i class="fas fa-calendar-check me-2"></i>
                                    Détails de la Réservation
                                </h2>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="detail-item">
                                            <div class="detail-label">
                                                <i class="fas fa-hotel me-2"></i>Hôtel
                                            </div>
                                            <div class="detail-value">
                                                <?php echo htmlspecialchars($reservation['nom_hotel']); ?>
                                            </div>
                                        </div>
                                        <div class="detail-item">
                                            <div class="detail-label">
                                                <i class="fas fa-map-marker-alt me-2"></i>Adresse
                                            </div>
                                            <div class="detail-value">
                                                <?php echo htmlspecialchars($reservation['hotel_adresse']); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="detail-item">
                                            <div class="detail-label">
                                                <i class="fas fa-bed me-2"></i>Type de Chambre
                                            </div>
                                            <div class="detail-value">
                                                <?php echo htmlspecialchars($reservation['type_chambre']); ?>
                                            </div>
                                        </div>
                                        <div class="detail-item">
                                            <div class="detail-label">
                                                <i class="fas fa-euro-sign me-2"></i>Prix par nuit
                                            </div>
                                            <div class="price">
                                                <?php echo number_format($reservation['prix'], 2, ',', ' '); ?> €
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <hr>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="detail-item">
                                            <div class="detail-label">
                                                <i class="fas fa-calendar-plus me-2"></i>Date d'arrivée
                                            </div>
                                            <div class="detail-value">
                                                <?php echo date('d/m/Y', strtotime($reservation['date_arrivee'])); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="detail-item">
                                            <div class="detail-label">
                                                <i class="fas fa-calendar-minus me-2"></i>Date de départ
                                            </div>
                                            <div class="detail-value">
                                                <?php echo date('d/m/Y', strtotime($reservation['date_depart'])); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <?php if(strtotime($reservation['date_arrivee']) > time()): ?>
                                    <div class="mt-4">
                                        <a href="annuler_reservation.php?id=<?php echo $id_reservation; ?>" 
                                           class="btn btn-danger btn-action"
                                           onclick="return confirm('Êtes-vous sûr de vouloir annuler cette réservation ?')">
                                            <i class="fas fa-times me-2"></i>Annuler la réservation
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="detail-card">
                            <div class="card-header">
                                <h3 class="m-0">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Informations
                                </h3>
                            </div>
                            <div class="card-body">
                                <?php if($reservation['image_url']): ?>
                                    <img src="<?php echo htmlspecialchars($reservation['image_url']); ?>" 
                                         alt="<?php echo htmlspecialchars($reservation['nom_hotel']); ?>" 
                                         class="hotel-image mb-4">
                                <?php endif; ?>
                                
                                <div class="detail-item">
                                    <div class="detail-label">
                                        <i class="fas fa-info-circle me-2"></i>Description de l'hôtel
                                    </div>
                                    <div class="detail-value">
                                        <?php echo nl2br(htmlspecialchars($reservation['hotel_description'])); ?>
                                    </div>
                                </div>
                                
                                <div class="detail-item">
                                    <div class="detail-label">
                                        <i class="fas fa-bed me-2"></i>Description de la chambre
                                    </div>
                                    <div class="detail-value">
                                        <?php echo nl2br(htmlspecialchars($reservation['chambre_description'])); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4">
                    <a href="mes_reservations.php" class="btn btn-primary btn-action">
                        <i class="fas fa-arrow-left me-2"></i>Retour aux réservations
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
