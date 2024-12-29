<?php
session_start();
require_once "../includes/config.php";

// Vérifier la connexion
if (!$conn) {
    die("La connexion a échoué : " . mysqli_connect_error());
}

// Check if admin is logged in
if(!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Check if ID and action are provided
if(!isset($_GET["id"]) || !isset($_GET["action"])) {
    $_SESSION['error'] = "Paramètres manquants";
    header("location: reservations.php");
    exit;
}

$id = intval($_GET["id"]);
$action = $_GET["action"];

if($action !== 'accept' && $action !== 'refuse') {
    $_SESSION['error'] = "Action non valide";
    header("location: reservations.php");
    exit;
}

try {
    // Get reservation info with error reporting
    $sql = "SELECT r.id_reservation, r.date_arrivee, r.date_depart, r.statut,
                   h.nom_hotel, c.type_chambre, cl.nom as client_nom
            FROM tb_reservations r
            INNER JOIN tb_chambres c ON r.id_chambre = c.id_chambre
            INNER JOIN tb_hotels h ON c.id_hotel = h.id_hotel
            INNER JOIN tb_clients cl ON r.id_client = cl.id_client
            WHERE r.id_reservation = ?";

    if(!$stmt = mysqli_prepare($conn, $sql)) {
        throw new Exception("Erreur de préparation : " . mysqli_error($conn));
    }

    if(!mysqli_stmt_bind_param($stmt, "i", $id)) {
        throw new Exception("Erreur de liaison des paramètres : " . mysqli_stmt_error($stmt));
    }

    if(!mysqli_stmt_execute($stmt)) {
        throw new Exception("Erreur d'exécution : " . mysqli_stmt_error($stmt));
    }

    $result = mysqli_stmt_get_result($stmt);
    
    if(!$result) {
        throw new Exception("Erreur de récupération du résultat : " . mysqli_error($conn));
    }

    if(!$reservation = mysqli_fetch_assoc($result)) {
        throw new Exception("Réservation non trouvée");
    }

    mysqli_stmt_close($stmt);

    // Si on arrive ici, on a la réservation
    // Traitement du formulaire POST
    if($_SERVER["REQUEST_METHOD"] == "POST") {
        $new_status = ($action === 'accept') ? 'confirmee' : 'refusee';
        
        $update_sql = "UPDATE tb_reservations SET statut = ? WHERE id_reservation = ?";
        
        if(!$update_stmt = mysqli_prepare($conn, $update_sql)) {
            throw new Exception("Erreur de préparation de la mise à jour : " . mysqli_error($conn));
        }
        
        if(!mysqli_stmt_bind_param($update_stmt, "si", $new_status, $id)) {
            throw new Exception("Erreur de liaison des paramètres de mise à jour : " . mysqli_stmt_error($update_stmt));
        }
        
        if(!mysqli_stmt_execute($update_stmt)) {
            throw new Exception("Erreur d'exécution de la mise à jour : " . mysqli_stmt_error($update_stmt));
        }
        
        mysqli_stmt_close($update_stmt);
        
        $_SESSION['success'] = "La réservation a été " . ($action === 'accept' ? 'acceptée' : 'refusée');
        header("location: reservations.php");
        exit;
    }

} catch (Exception $e) {
    $_SESSION['error'] = "Erreur : " . $e->getMessage();
    header("location: reservations.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?php echo $action === 'accept' ? 'Accepter' : 'Refuser'; ?> la réservation - Admin</title>
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
                    <h1 class="h2"><?php echo $action === 'accept' ? 'Accepter' : 'Refuser'; ?> la réservation</h1>
                    <a href="reservations.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Retour
                    </a>
                </div>

                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas <?php echo $action === 'accept' ? 'fa-check text-success' : 'fa-ban text-warning'; ?>"></i>
                            Êtes-vous sûr de vouloir <?php echo $action === 'accept' ? 'accepter' : 'refuser'; ?> cette réservation ?
                        </h5>
                        <p class="card-text">
                            <strong>Client:</strong> <?php echo htmlspecialchars($reservation['client_nom']); ?><br>
                            <strong>Hôtel:</strong> <?php echo htmlspecialchars($reservation['nom_hotel']); ?><br>
                            <strong>Type de chambre:</strong> <?php echo htmlspecialchars($reservation['type_chambre']); ?><br>
                            <strong>Date d'arrivée:</strong> <?php echo date('d/m/Y', strtotime($reservation['date_arrivee'])); ?><br>
                            <strong>Date de départ:</strong> <?php echo date('d/m/Y', strtotime($reservation['date_depart'])); ?><br>
                        </p>
                        
                        <?php if($action === 'refuse'): ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                Le refus de la réservation libérera la chambre pour d'autres réservations.
                            </div>
                        <?php endif; ?>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="reservations.php" class="btn btn-secondary">Annuler</a>
                            <form method="POST" action="" style="display: inline;">
                                <button type="submit" class="btn <?php echo $action === 'accept' ? 'btn-success' : 'btn-warning'; ?>">
                                    <i class="fas <?php echo $action === 'accept' ? 'fa-check' : 'fa-ban'; ?>"></i>
                                    Confirmer <?php echo $action === 'accept' ? 'l\'acceptation' : 'le refus'; ?>
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
