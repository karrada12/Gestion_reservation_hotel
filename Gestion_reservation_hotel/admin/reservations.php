<?php
session_start();
require_once "../includes/config.php";

// Vérifier si l'utilisateur est connecté et est admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] !== true) {
    header("location: ../login.php");
    exit;
}

// Récupérer toutes les réservations avec les informations associées
$sql = "SELECT r.*, c.nom as client_nom, c.email as client_email, 
        h.nom_hotel, ch.type_chambre, ch.prix
        FROM tb_reservations r
        JOIN tb_clients c ON r.id_client = c.id_client
        JOIN tb_chambres ch ON r.id_chambre = ch.id_chambre
        JOIN tb_hotels h ON ch.id_hotel = h.id_hotel
        ORDER BY r.date_reservation DESC";

$result = mysqli_query($conn, $sql);
$reservations = [];
if($result) {
    $reservations = mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Traitement des actions (confirmer/annuler réservation)
if(isset($_POST['action']) && isset($_POST['id_reservation'])) {
    $id_reservation = intval($_POST['id_reservation']);
    $action = $_POST['action'];
    $nouveau_statut = '';
    
    if($action === 'confirmer') {
        $nouveau_statut = 'confirmee';
    } elseif($action === 'annuler') {
        $nouveau_statut = 'annulee';
    }
    
    if($nouveau_statut) {
        $update_sql = "UPDATE tb_reservations SET statut = ? WHERE id_reservation = ?";
        if($stmt = mysqli_prepare($conn, $update_sql)) {
            mysqli_stmt_bind_param($stmt, "si", $nouveau_statut, $id_reservation);
            if(mysqli_stmt_execute($stmt)) {
                $_SESSION['success'] = "Le statut de la réservation a été mis à jour avec succès";
                header("location: reservations.php");
                exit;
            } else {
                $_SESSION['error'] = "Erreur lors de la mise à jour du statut";
            }
            mysqli_stmt_close($stmt);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Réservations - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/admin-style.css" rel="stylesheet">
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar">
            <a class="sidebar-brand" href="index.php">
                <div class="sidebar-brand-text">Gloobin Admin</div>
            </a>

            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="hotels.php">
                        Hôtels
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="rooms.php">
                        Chambres
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="reservations.php">
                        Réservations
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="clients.php">
                        Clients
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../logout.php">
                        Déconnexion
                    </a>
                </li>
            </ul>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3 mb-0">Gestion des Réservations</h1>
                </div>

                <?php if(isset($_SESSION['success'])): ?>
                    <div class="alert alert-success">
                        <?php 
                        echo $_SESSION['success'];
                        unset($_SESSION['success']);
                        ?>
                    </div>
                <?php endif; ?>

                <?php if(isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger">
                        <?php 
                        echo $_SESSION['error'];
                        unset($_SESSION['error']);
                        ?>
                    </div>
                <?php endif; ?>

                <div class="card shadow">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Réf.</th>
                                        <th>Client</th>
                                        <th>Hôtel</th>
                                        <th>Chambre</th>
                                        <th>Dates</th>
                                        <th>Prix Total</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($reservations as $reservation): ?>
                                    <tr>
                                        <td>#<?php echo $reservation['id_reservation']; ?></td>
                                        <td>
                                            <?php echo htmlspecialchars($reservation['client_nom']); ?>
                                            <br>
                                            <small class="text-muted"><?php echo htmlspecialchars($reservation['client_email']); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($reservation['nom_hotel']); ?></td>
                                        <td>
                                            <?php echo htmlspecialchars($reservation['type_chambre']); ?>
                                            <br>
                                            <small class="text-muted"><?php echo number_format($reservation['prix'], 2); ?> DHS/nuit</small>
                                        </td>
                                        <td>
                                            Du <?php echo date('d/m/Y', strtotime($reservation['date_debut'])); ?>
                                            <br>
                                            Au <?php echo date('d/m/Y', strtotime($reservation['date_fin'])); ?>
                                        </td>
                                        <td>
                                            <?php
                                            $debut = new DateTime($reservation['date_debut']);
                                            $fin = new DateTime($reservation['date_fin']);
                                            $nbJours = $debut->diff($fin)->days;
                                            $prixTotal = $nbJours * $reservation['prix'];
                                            echo number_format($prixTotal, 2);
                                            ?> DHS
                                        </td>
                                        <td>
                                            <?php
                                            $badgeClass = 'bg-secondary';
                                            switch($reservation['statut']) {
                                                case 'confirmee':
                                                    $badgeClass = 'bg-success';
                                                    break;
                                                case 'en_attente':
                                                    $badgeClass = 'bg-warning';
                                                    break;
                                                case 'annulee':
                                                    $badgeClass = 'bg-danger';
                                                    break;
                                            }
                                            ?>
                                            <span class="badge <?php echo $badgeClass; ?>">
                                                <?php echo ucfirst($reservation['statut']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if($reservation['statut'] === 'en_attente'): ?>
                                                <form method="post" action="" style="display: inline;">
                                                    <input type="hidden" name="id_reservation" value="<?php echo $reservation['id_reservation']; ?>">
                                                    <input type="hidden" name="action" value="confirmer">
                                                    <button type="submit" class="btn btn-sm btn-success">Confirmer</button>
                                                </form>
                                                <form method="post" action="" style="display: inline;">
                                                    <input type="hidden" name="id_reservation" value="<?php echo $reservation['id_reservation']; ?>">
                                                    <input type="hidden" name="action" value="annuler">
                                                    <button type="submit" class="btn btn-sm btn-danger">Annuler</button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
