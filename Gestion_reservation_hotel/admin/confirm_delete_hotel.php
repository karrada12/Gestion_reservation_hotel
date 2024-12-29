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
    $_SESSION['error'] = "ID de l'hôtel manquant";
    header("location: hotels.php");
    exit;
}

$id = intval($_GET["id"]);

// Get hotel info
$sql = "SELECT h.*, 
        COUNT(c.id_chambre) as room_count,
        COUNT(DISTINCT r.id_reservation) as reservation_count
        FROM tb_hotels h 
        LEFT JOIN tb_chambres c ON h.id_hotel = c.id_hotel 
        LEFT JOIN tb_reservations r ON c.id_chambre = r.id_chambre
        WHERE h.id_hotel = ?
        GROUP BY h.id_hotel";

if($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if($hotel = mysqli_fetch_assoc($result)) {
        // Hotel found
    } else {
        $_SESSION['error'] = "Hôtel non trouvé";
        header("location: hotels.php");
        exit;
    }
    mysqli_stmt_close($stmt);
}

// If form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST") {
    // Start transaction
    mysqli_begin_transaction($conn);
    
    try {
        // First check if hotel has any reservations
        if($hotel['reservation_count'] > 0) {
            throw new Exception("Impossible de supprimer l'hôtel car certaines chambres ont des réservations actives.");
        }
        
        // Delete all rooms of the hotel first
        $delete_rooms_sql = "DELETE FROM tb_chambres WHERE id_hotel = ?";
        if($stmt = mysqli_prepare($conn, $delete_rooms_sql)) {
            mysqli_stmt_bind_param($stmt, "i", $id);
            if(!mysqli_stmt_execute($stmt)) {
                throw new Exception("Erreur lors de la suppression des chambres : " . mysqli_error($conn));
            }
            mysqli_stmt_close($stmt);
        }
        
        // Then delete the hotel
        $delete_hotel_sql = "DELETE FROM tb_hotels WHERE id_hotel = ?";
        if($stmt = mysqli_prepare($conn, $delete_hotel_sql)) {
            mysqli_stmt_bind_param($stmt, "i", $id);
            if(!mysqli_stmt_execute($stmt)) {
                throw new Exception("Erreur lors de la suppression de l'hôtel : " . mysqli_error($conn));
            }
            mysqli_stmt_close($stmt);
        }
        
        // If we got here, commit the transaction
        mysqli_commit($conn);
        $_SESSION['success'] = "L'hôtel et toutes ses chambres ont été supprimés avec succès.";
        header("location: hotels.php");
        exit;
        
    } catch (Exception $e) {
        // Something went wrong, rollback the transaction
        mysqli_rollback($conn);
        $_SESSION['error'] = $e->getMessage();
        header("location: hotels.php");
        exit;
    }
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
                    <a href="hotels.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Retour
                    </a>
                </div>

                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Êtes-vous sûr de vouloir supprimer cet hôtel ?</h5>
                        <p class="card-text">
                            <strong>Nom de l'hôtel:</strong> <?php echo htmlspecialchars($hotel['nom_hotel']); ?><br>
                            <strong>Adresse:</strong> <?php echo htmlspecialchars($hotel['adresse']); ?><br>
                            <strong>Nombre de chambres:</strong> <?php echo $hotel['room_count']; ?><br>
                            <?php if($hotel['reservation_count'] > 0): ?>
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    Attention: Cet hôtel a <?php echo $hotel['reservation_count']; ?> réservation(s) active(s).
                                    La suppression ne sera pas possible.
                                </div>
                            <?php endif; ?>
                        </p>
                        
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i>
                            Cette action est irréversible. Toutes les chambres de cet hôtel seront également supprimées.
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="hotels.php" class="btn btn-secondary">Annuler</a>
                            <?php if($hotel['reservation_count'] == 0): ?>
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
