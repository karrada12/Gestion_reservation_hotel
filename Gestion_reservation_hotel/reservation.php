<?php
session_start();
require_once "includes/config.php";

// Check if user is logged in
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

if(!isset($_GET["id_chambre"])) {
    header("location: index.php");
    exit;
}

$id_chambre = $_GET["id_chambre"];
$id_client = $_SESSION["id_client"];

// Get room details
$sql = "SELECT c.*, h.nom_hotel 
        FROM tb_chambres c 
        JOIN tb_hotels h ON c.id_hotel = h.id_hotel 
        WHERE c.id_chambre = ?";
if($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $id_chambre);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $room = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
}

// Process reservation
if(isset($_POST["reserve"])) {
    $date_arrivee = $_POST["date_arrivee"];
    $date_depart = $_POST["date_depart"];
    
    // Check if room is available for these dates
    $sql = "CALL add_reservation(?, ?, ?, ?)";
    if($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "iiss", $id_client, $id_chambre, $date_arrivee, $date_depart);
        if(mysqli_stmt_execute($stmt)) {
            header("location: mes-reservations.php");
            exit;
        } else {
            $error = "Erreur lors de la réservation. Veuillez réessayer.";
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Réservation - Hotel Reservation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3>Réservation - <?php echo $room['nom_hotel']; ?></h3>
                    </div>
                    <div class="card-body">
                        <?php if(isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <div class="room-details mb-4">
                            <h4>Détails de la chambre</h4>
                            <p>
                                Type: <?php echo $room['type_chambre']; ?><br>
                                Prix: <?php echo $room['prix']; ?> MAD/nuit<br>
                                Nombre de lits: <?php echo $room['nombre_lits']; ?>
                            </p>
                        </div>
                        
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?id_chambre=" . $id_chambre; ?>" method="post">
                            <div class="form-group mb-3">
                                <label>Date d'arrivée</label>
                                <input type="date" name="date_arrivee" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
                            </div>
                            
                            <div class="form-group mb-3">
                                <label>Date de départ</label>
                                <input type="date" name="date_depart" class="form-control" required min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                            </div>
                            
                            <div class="form-group mb-3">
                                <input type="submit" name="reserve" class="btn btn-primary" value="Confirmer la réservation">
                                <a href="hotel_detail.php?id=<?php echo $room['id_hotel']; ?>" class="btn btn-secondary">Annuler</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add client-side date validation
        document.addEventListener('DOMContentLoaded', function() {
            const dateArrivee = document.querySelector('input[name="date_arrivee"]');
            const dateDepart = document.querySelector('input[name="date_depart"]');
            
            dateArrivee.addEventListener('change', function() {
                const minDepart = new Date(this.value);
                minDepart.setDate(minDepart.getDate() + 1);
                dateDepart.min = minDepart.toISOString().split('T')[0];
                
                if(dateDepart.value && dateDepart.value <= this.value) {
                    dateDepart.value = minDepart.toISOString().split('T')[0];
                }
            });
        });
    </script>
</body>
</html>
