<?php
session_start();
require_once "includes/config.php";

// Check if user is logged in
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

$id_client = $_SESSION["id_client"];

// Get user's reservations
$sql = "SELECT r.*, c.type_chambre, c.prix, h.nom_hotel, h.id_hotel, r.statut, 
               DATEDIFF(r.date_depart, r.date_arrivee) as duree_sejour
        FROM tb_reservations r 
        JOIN tb_chambres c ON r.id_chambre = c.id_chambre 
        JOIN tb_hotels h ON c.id_hotel = h.id_hotel 
        WHERE r.id_client = ? 
        ORDER BY r.date_arrivee DESC";

if($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $id_client);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Réservations</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        .reservation-card {
            transition: transform 0.2s;
        }
        .reservation-card:hover {
            transform: translateY(-5px);
        }
        .status-badge {
            font-size: 0.9em;
            padding: 0.5em 1em;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container my-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-calendar-check me-2"></i>Mes Réservations</h2>
            <a href="index.php" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Nouvelle Réservation
            </a>
        </div>

        <?php if(isset($_SESSION["success"])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php 
                echo $_SESSION["success"];
                unset($_SESSION["success"]);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if(isset($_SESSION["error"])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php 
                echo $_SESSION["error"];
                unset($_SESSION["error"]);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if(mysqli_num_rows($result) > 0): ?>
            <div class="row">
                <?php while($reservation = mysqli_fetch_assoc($result)): 
                    $total_prix = $reservation['prix'] * $reservation['duree_sejour'];
                    $est_passee = strtotime($reservation['date_arrivee']) < time();
                    $status_class = $reservation['statut'] == 'confirmed' ? 'success' : 
                                 ($reservation['statut'] == 'en_attente' ? 'warning' : 'secondary');
                    $status_text = $reservation['statut'] == 'confirmed' ? 'Confirmée' : 
                                 ($reservation['statut'] == 'en_attente' ? 'En attente' : 'Annulée');
                ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card reservation-card h-100 shadow-sm">
                            <div class="card-header bg-transparent">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0"><?php echo htmlspecialchars($reservation['nom_hotel']); ?></h5>
                                    <span class="badge bg-<?php echo $status_class; ?> status-badge">
                                        <?php echo $status_text; ?>
                                    </span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <small class="text-muted">Type de chambre</small>
                                    <p class="mb-0"><?php echo htmlspecialchars($reservation['type_chambre']); ?></p>
                                </div>
                                <div class="mb-3">
                                    <small class="text-muted">Dates</small>
                                    <p class="mb-0">
                                        <i class="fas fa-calendar-alt me-1"></i>
                                        Du <?php echo date('d/m/Y', strtotime($reservation['date_arrivee'])); ?>
                                        au <?php echo date('d/m/Y', strtotime($reservation['date_depart'])); ?>
                                    </p>
                                </div>
                                <div class="mb-3">
                                    <small class="text-muted">Prix total</small>
                                    <p class="mb-0">
                                        <i class="fas fa-money-bill-wave me-1"></i>
                                        <?php echo number_format($total_prix, 2); ?> MAD
                                        <small class="text-muted">(<?php echo $reservation['duree_sejour']; ?> nuits)</small>
                                    </p>
                                </div>
                            </div>
                            <div class="card-footer bg-transparent">
                                <div class="d-flex justify-content-between align-items-center">
                                    <?php if(!$est_passee && $reservation['statut'] != 'annulee'): ?>
                                        <button type="button" 
                                                class="btn btn-outline-danger" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#cancelModal<?php echo $reservation['id_reservation']; ?>">
                                            <i class="fas fa-times me-1"></i>Annuler
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-outline-secondary" disabled>
                                            <i class="fas fa-lock me-1"></i>Terminée
                                        </button>
                                    <?php endif; ?>
                                    <a href="hotel_detail.php?id=<?php echo htmlspecialchars($reservation['id_hotel']); ?>" 
                                       class="btn btn-outline-primary">
                                        <i class="fas fa-hotel me-1"></i>Voir l'hôtel
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal de confirmation d'annulation -->
                    <div class="modal fade" id="cancelModal<?php echo $reservation['id_reservation']; ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Confirmer l'annulation</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p>Êtes-vous sûr de vouloir annuler cette réservation à l'hôtel 
                                    <strong><?php echo htmlspecialchars($reservation['nom_hotel']); ?></strong> ?</p>
                                    <p class="text-danger"><small>Cette action est irréversible.</small></p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                        <i class="fas fa-times me-1"></i>Non, garder
                                    </button>
                                    <a href="annuler_reservation.php?id=<?php echo $reservation['id_reservation']; ?>" 
                                       class="btn btn-danger">
                                        <i class="fas fa-check me-1"></i>Oui, annuler
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                <h3 class="text-muted">Aucune réservation</h3>
                <p class="text-muted mb-4">Vous n'avez aucune réservation pour le moment.</p>
                <a href="index.php" class="btn btn-primary">
                    <i class="fas fa-search me-2"></i>Parcourir les hôtels
                </a>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
