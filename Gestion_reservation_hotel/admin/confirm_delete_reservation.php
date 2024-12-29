<?php
session_start();
require_once "../includes/config.php";

// Check if admin is logged in
if(!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Check if ID is provided
if(!isset($_GET["id"])) {
    $_SESSION['error'] = "ID de la réservation manquant";
    header("location: reservations.php");
    exit;
}

$id = intval($_GET["id"]);

// Get reservation info
$sql = "SELECT r.*, c.type_chambre, h.nom_hotel, cl.nom as client_nom, cl.prenom as client_prenom, cl.email
        FROM tb_reservations r
        JOIN tb_chambres c ON r.id_chambre = c.id_chambre
        JOIN tb_hotels h ON c.id_hotel = h.id_hotel
        JOIN tb_clients cl ON r.id_client = cl.id_client
        WHERE r.id_reservation = ?";

$reservation = null;

if($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $id);
    if(mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        if($reservation = mysqli_fetch_assoc($result)) {
            // Reservation found
        } else {
            $_SESSION['error'] = "Réservation non trouvée";
            header("location: reservations.php");
            exit;
        }
    } else {
        $_SESSION['error'] = "Erreur lors de la recherche de la réservation";
        header("location: reservations.php");
        exit;
    }
    mysqli_stmt_close($stmt);
} else {
    $_SESSION['error'] = "Erreur de préparation de la requête";
    header("location: reservations.php");
    exit;
}

// If form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST") {
    // Delete reservation
    $delete_sql = "DELETE FROM tb_reservations WHERE id_reservation = ?";
    if($stmt = mysqli_prepare($conn, $delete_sql)) {
        mysqli_stmt_bind_param($stmt, "i", $id);
        
        if(mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "La réservation a été supprimée avec succès";
            header("location: reservations.php");
            exit;
        } else {
            $_SESSION['error'] = "Erreur lors de la suppression : " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    }
}

// If we get here, we should have a valid reservation
if(!$reservation) {
    $_SESSION['error'] = "Erreur lors de la récupération des détails de la réservation";
    header("location: reservations.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Supprimer la réservation - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/admin_header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/admin_sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Supprimer la réservation</h1>
                    <a href="reservations.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Retour
                    </a>
                </div>

                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title text-danger">
                            <i class="fas fa-exclamation-triangle"></i>
                            Êtes-vous sûr de vouloir supprimer cette réservation ?
                        </h5>
                        <p class="card-text">
                            <strong>Client:</strong> <?php echo htmlspecialchars($reservation['client_prenom'] . ' ' . $reservation['client_nom']); ?><br>
                            <strong>Email:</strong> <?php echo htmlspecialchars($reservation['email']); ?><br>
                            <strong>Hôtel:</strong> <?php echo htmlspecialchars($reservation['nom_hotel']); ?><br>
                            <strong>Type de chambre:</strong> <?php echo htmlspecialchars($reservation['type_chambre']); ?><br>
                            <strong>Date d'arrivée:</strong> <?php echo date('d/m/Y', strtotime($reservation['date_arrivee'])); ?><br>
                            <strong>Date de départ:</strong> <?php echo date('d/m/Y', strtotime($reservation['date_depart'])); ?><br>
                            <strong>Statut actuel:</strong> 
                            <span class="badge <?php echo $reservation['statut'] == 'confirmee' ? 'bg-success' : ($reservation['statut'] == 'refusee' ? 'bg-danger' : 'bg-warning'); ?>">
                                <?php 
                                echo $reservation['statut'] == 'confirmee' ? 'Confirmée' : 
                                    ($reservation['statut'] == 'refusee' ? 'Refusée' : 'En attente'); 
                                ?>
                            </span>
                        </p>
                        
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            Cette action est irréversible. La réservation sera définitivement supprimée.
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="reservations.php" class="btn btn-secondary">Annuler</a>
                            <form method="POST" action="" style="display: inline;">
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-trash"></i>
                                    Confirmer la suppression
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
