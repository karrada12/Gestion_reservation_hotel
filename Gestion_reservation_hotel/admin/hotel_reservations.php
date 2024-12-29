<?php
session_start();
require_once "../includes/config.php";

// Vérifier si l'utilisateur est connecté et est admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] !== true){
    header("location: ../login.php");
    exit;
}

// Vérifier si l'ID de l'hôtel est fourni
if(!isset($_GET['id'])) {
    header("location: hotels.php");
    exit;
}

$id_hotel = intval($_GET['id']);

// Récupérer les informations de l'hôtel
$sql_hotel = "SELECT * FROM tb_hotels WHERE id_hotel = ?";
if($stmt = mysqli_prepare($conn, $sql_hotel)) {
    mysqli_stmt_bind_param($stmt, "i", $id_hotel);
    mysqli_stmt_execute($stmt);
    $hotel_result = mysqli_stmt_get_result($stmt);
    $hotel = mysqli_fetch_assoc($hotel_result);
    mysqli_stmt_close($stmt);
    
    if(!$hotel) {
        $_SESSION['error'] = "Hôtel non trouvé.";
        header("location: hotels.php");
        exit;
    }
} else {
    $_SESSION['error'] = "Erreur lors de la récupération des informations de l'hôtel.";
    header("location: hotels.php");
    exit;
}

// Mettre à jour le statut d'une réservation si demandé
if(isset($_POST['update_status'])) {
    $id_reservation = intval($_POST['id_reservation']);
    $nouveau_statut = $_POST['nouveau_statut'];
    
    $update_sql = "UPDATE tb_reservations SET statut = ? WHERE id_reservation = ? AND id_chambre IN (SELECT id_chambre FROM tb_chambres WHERE id_hotel = ?)";
    if($update_stmt = mysqli_prepare($conn, $update_sql)) {
        mysqli_stmt_bind_param($update_stmt, "sii", $nouveau_statut, $id_reservation, $id_hotel);
        
        if(mysqli_stmt_execute($update_stmt)) {
            $_SESSION['success'] = "Le statut de la réservation a été mis à jour.";
        } else {
            $_SESSION['error'] = "Erreur lors de la mise à jour du statut.";
        }
        mysqli_stmt_close($update_stmt);
    } else {
        $_SESSION['error'] = "Erreur lors de la préparation de la requête de mise à jour.";
    }
    
    header("location: hotel_reservations.php?id=" . $id_hotel);
    exit;
}

// Récupérer toutes les réservations de l'hôtel
$sql = "SELECT r.*, c.nom as client_nom, c.email as client_email, 
        ch.type_chambre, ch.prix_nuit
        FROM tb_reservations r
        JOIN tb_clients c ON r.id_client = c.id_client
        JOIN tb_chambres ch ON r.id_chambre = ch.id_chambre
        WHERE ch.id_hotel = ?
        ORDER BY r.created_at DESC";

if($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $id_hotel);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $_SESSION['error'] = "Erreur lors de la récupération des réservations.";
    $result = false;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réservations - <?php echo htmlspecialchars($hotel['nom_hotel']); ?></title>
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
                    <a class="nav-link active" href="hotels.php">
                        Hôtels
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="rooms.php">
                        Chambres
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="reservations.php">
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
                    <div>
                        <h1 class="h3 mb-0">Réservations - <?php echo htmlspecialchars($hotel['nom_hotel']); ?></h1>
                        <p class="text-muted"><?php echo htmlspecialchars($hotel['adresse']); ?></p>
                    </div>
                    <a href="hotels.php" class="btn btn-secondary">Retour aux hôtels</a>
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
                            <?php if($result && mysqli_num_rows($result) > 0): ?>
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Client</th>
                                            <th>Type de chambre</th>
                                            <th>Prix/nuit</th>
                                            <th>Date d'arrivée</th>
                                            <th>Date de départ</th>
                                            <th>Statut</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($row = mysqli_fetch_assoc($result)): ?>
                                        <tr>
                                            <td><?php echo $row['id_reservation']; ?></td>
                                            <td>
                                                <?php echo htmlspecialchars($row['client_nom']); ?>
                                                <br>
                                                <small class="text-muted"><?php echo htmlspecialchars($row['client_email']); ?></small>
                                            </td>
                                            <td><?php echo htmlspecialchars($row['type_chambre']); ?></td>
                                            <td><?php echo number_format($row['prix_nuit'], 2); ?> €</td>
                                            <td><?php echo $row['date_arrivee']; ?></td>
                                            <td><?php echo $row['date_depart']; ?></td>
                                            <td>
                                                <?php
                                                $status_class = '';
                                                switch($row['statut']) {
                                                    case 'confirmee':
                                                        $status_class = 'bg-success';
                                                        break;
                                                    case 'en_attente':
                                                        $status_class = 'bg-warning';
                                                        break;
                                                    case 'annulee':
                                                        $status_class = 'bg-danger';
                                                        break;
                                                    default:
                                                        $status_class = 'bg-secondary';
                                                }
                                                ?>
                                                <span class="badge <?php echo $status_class; ?>">
                                                    <?php echo ucfirst(str_replace('_', ' ', $row['statut'])); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <form method="post" class="d-inline">
                                                    <input type="hidden" name="id_reservation" value="<?php echo $row['id_reservation']; ?>">
                                                    <select name="nouveau_statut" class="form-select form-select-sm d-inline-block w-auto">
                                                        <option value="en_attente" <?php echo $row['statut'] == 'en_attente' ? 'selected' : ''; ?>>En attente</option>
                                                        <option value="confirmee" <?php echo $row['statut'] == 'confirmee' ? 'selected' : ''; ?>>Confirmée</option>
                                                        <option value="annulee" <?php echo $row['statut'] == 'annulee' ? 'selected' : ''; ?>>Annulée</option>
                                                    </select>
                                                    <button type="submit" name="update_status" class="btn btn-sm btn-primary">Mettre à jour</button>
                                                </form>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <p class="text-muted">Aucune réservation trouvée pour cet hôtel.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
