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
$sql = "SELECT r.*, c.type_chambre, c.prix, h.nom_hotel 
        FROM tb_reservations r 
        JOIN tb_chambres c ON r.id_chambre = c.id_chambre 
        JOIN tb_hotels h ON c.id_hotel = h.id_hotel 
        WHERE r.id_client = ? 
        ORDER BY r.date_arrivee DESC";

$result = null;
$error_message = null;

if($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $id_client);
    if(mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
    } else {
        $error_message = "Erreur lors de l'exécution de la requête.";
    }
    mysqli_stmt_close($stmt);
} else {
    $error_message = "Erreur lors de la préparation de la requête.";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Réservations - Hotel Reservation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="css/reservations-style.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="reservations-container">
        <div class="container">
            <h2 class="page-title">
                <i class="fas fa-calendar-alt me-2"></i>Mes Réservations
            </h2>
            
            <?php if($error_message): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error_message; ?>
                </div>
            <?php elseif($result && mysqli_num_rows($result) > 0): ?>
                <div class="reservation-table">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th><i class="fas fa-hotel me-2"></i>Hôtel</th>
                                    <th><i class="fas fa-bed me-2"></i>Type de chambre</th>
                                    <th><i class="fas fa-calendar-plus me-2"></i>Date d'arrivée</th>
                                    <th><i class="fas fa-calendar-minus me-2"></i>Date de départ</th>
                                    <th><i class="fas fa-euro-sign me-2"></i>Prix/nuit</th>
                                    <th><i class="fas fa-cog me-2"></i>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = mysqli_fetch_assoc($result)): ?>
                                    <tr>
                                        <td data-label="Hôtel">
                                            <?php echo htmlspecialchars($row['nom_hotel']); ?>
                                        </td>
                                        <td data-label="Type de chambre">
                                            <?php echo htmlspecialchars($row['type_chambre']); ?>
                                        </td>
                                        <td data-label="Date d'arrivée" class="date">
                                            <?php echo date('d/m/Y', strtotime($row['date_arrivee'])); ?>
                                        </td>
                                        <td data-label="Date de départ" class="date">
                                            <?php echo date('d/m/Y', strtotime($row['date_depart'])); ?>
                                        </td>
                                        <td data-label="Prix/nuit" class="price">
                                            <?php echo number_format($row['prix'], 2, ',', ' '); ?> €
                                        </td>
                                        <td data-label="Actions">
                                            <a href="reservation_details.php?id=<?php echo $row['id_reservation']; ?>" 
                                               class="btn btn-action btn-details">
                                                <i class="fas fa-eye"></i> Détails
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    Vous n'avez aucune réservation pour le moment.
                </div>
            <?php endif; ?>
            
            <div class="mt-4">
                <a href="index.php#hotels" class="btn btn-search">
                    <i class="fas fa-search me-2"></i>Rechercher un hôtel
                </a>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
