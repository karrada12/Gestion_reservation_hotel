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
    $_SESSION['error'] = "ID de la chambre manquant";
    header("location: rooms.php");
    exit;
}

$id = intval($_GET["id"]);
$room = null;

// Get room info with hotel name and reservation count
$sql = "SELECT c.id_chambre, c.type_chambre, c.prix as prix_nuit, c.disponibilite, h.nom_hotel,
        COUNT(DISTINCT r.id_reservation) as reservation_count
        FROM tb_chambres c 
        LEFT JOIN tb_hotels h ON c.id_hotel = h.id_hotel
        LEFT JOIN tb_reservations r ON c.id_chambre = r.id_chambre
        WHERE c.id_chambre = ?
        GROUP BY c.id_chambre, c.type_chambre, c.prix, c.disponibilite, h.nom_hotel";

if($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $id);
    
    if(mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        
        if($room = mysqli_fetch_assoc($result)) {
            // Room found, continue
        } else {
            $_SESSION['error'] = "Chambre non trouvée";
            header("location: rooms.php");
            exit;
        }
    } else {
        $_SESSION['error'] = "Erreur d'exécution de la requête : " . mysqli_error($conn);
        header("location: rooms.php");
        exit;
    }
    mysqli_stmt_close($stmt);
} else {
    $_SESSION['error'] = "Erreur de préparation de la requête : " . mysqli_error($conn);
    header("location: rooms.php");
    exit;
}

// If form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST" && $room !== null) {
    try {
        // Check for active reservations
        if($room['reservation_count'] > 0) {
            throw new Exception("Impossible de supprimer la chambre car elle a des réservations actives.");
        }
        
        // Delete the room
        $delete_sql = "DELETE FROM tb_chambres WHERE id_chambre = ?";
        if($stmt = mysqli_prepare($conn, $delete_sql)) {
            mysqli_stmt_bind_param($stmt, "i", $id);
            if(!mysqli_stmt_execute($stmt)) {
                throw new Exception("Erreur lors de la suppression de la chambre : " . mysqli_error($conn));
            }
            mysqli_stmt_close($stmt);
            
            $_SESSION['success'] = "La chambre a été supprimée avec succès.";
            header("location: rooms.php");
            exit;
        }
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header("location: rooms.php");
        exit;
    }
}

// If we get here without a room, redirect with error
if($room === null) {
    $_SESSION['error'] = "Données de la chambre non disponibles";
    header("location: rooms.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Confirmer la suppression - Admin</title>
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
                    <h1 class="h2">Confirmer la suppression</h1>
                    <a href="rooms.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Retour
                    </a>
                </div>

                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Êtes-vous sûr de vouloir supprimer cette chambre ?</h5>
                        <p class="card-text">
                            <strong>Type de chambre:</strong> <?php echo htmlspecialchars($room['type_chambre']); ?><br>
                            <strong>Hôtel:</strong> <?php echo htmlspecialchars($room['nom_hotel']); ?><br>
                            <strong>Prix par nuit:</strong> <?php echo number_format($room['prix_nuit'], 2); ?> DHS<br>
                            <strong>Statut:</strong> 
                            <span class="badge <?php echo $room['disponibilite'] ? 'bg-success' : 'bg-danger'; ?>">
                                <?php echo $room['disponibilite'] ? 'Disponible' : 'Occupée'; ?>
                            </span><br>
                            <?php if($room['reservation_count'] > 0): ?>
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    Attention: Cette chambre a <?php echo $room['reservation_count']; ?> réservation(s) active(s).
                                    La suppression ne sera pas possible.
                                </div>
                            <?php endif; ?>
                        </p>
                        
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i>
                            Cette action est irréversible.
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="rooms.php" class="btn btn-secondary">Annuler</a>
                            <?php if($room['reservation_count'] == 0): ?>
                                <form method="POST" action="" style="display: inline;">
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fas fa-trash"></i> Confirmer la suppression
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
