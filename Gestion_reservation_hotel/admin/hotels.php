<?php
session_start();
require_once "../includes/config.php";

// Vérifier si l'utilisateur est connecté et est admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] !== true){
    header("location: ../login.php");
    exit;
}

// Traiter la suppression si demandée
if(isset($_POST['delete_hotel'])) {
    $id_hotel = $_POST['id_hotel'];
    
    // Vérifier s'il y a des réservations actives
    $check_sql = "SELECT COUNT(*) as res_count 
                 FROM tb_reservations r 
                 JOIN tb_chambres c ON r.id_chambre = c.id_chambre 
                 WHERE c.id_hotel = ?";
    
    $check_stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($check_stmt, "i", $id_hotel);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    $res_count = mysqli_fetch_assoc($check_result)['res_count'];
    
    if($res_count > 0) {
        $_SESSION['error'] = "Impossible de supprimer l'hôtel car il a des réservations actives.";
    } else {
        // Supprimer d'abord les chambres
        $delete_rooms = "DELETE FROM tb_chambres WHERE id_hotel = ?";
        $stmt = mysqli_prepare($conn, $delete_rooms);
        mysqli_stmt_bind_param($stmt, "i", $id_hotel);
        
        if(mysqli_stmt_execute($stmt)) {
            // Ensuite supprimer l'hôtel
            $delete_hotel = "DELETE FROM tb_hotels WHERE id_hotel = ?";
            $stmt = mysqli_prepare($conn, $delete_hotel);
            mysqli_stmt_bind_param($stmt, "i", $id_hotel);
            
            if(mysqli_stmt_execute($stmt)) {
                $_SESSION['success'] = "L'hôtel a été supprimé avec succès.";
            } else {
                $_SESSION['error'] = "Erreur lors de la suppression de l'hôtel.";
            }
        } else {
            $_SESSION['error'] = "Erreur lors de la suppression des chambres.";
        }
    }
    
    header("location: hotels.php");
    exit();
}

// Récupérer tous les hôtels avec leurs statistiques
$sql = "SELECT h.*, 
        COUNT(DISTINCT c.id_chambre) as nb_chambres,
        COUNT(DISTINCT r.id_reservation) as nb_reservations,
        SUM(CASE WHEN r.statut = 'confirmee' THEN 1 ELSE 0 END) as reservations_confirmees,
        SUM(CASE WHEN r.statut = 'en_attente' THEN 1 ELSE 0 END) as reservations_en_attente
        FROM tb_hotels h 
        LEFT JOIN tb_chambres c ON h.id_hotel = c.id_hotel 
        LEFT JOIN tb_reservations r ON c.id_chambre = r.id_chambre
        GROUP BY h.id_hotel 
        ORDER BY h.nom_hotel";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Hôtels - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/admin-style.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
                    <h1 class="h3 mb-0">Gestion des Hôtels</h1>
                    <a href="add_hotel.php" class="btn btn-primary">Ajouter un hôtel</a>
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
                                        <th>Nom de l'hôtel</th>
                                        <th>Adresse</th>
                                        <th>Nombre de chambres</th>
                                        <th>Réservations totales</th>
                                        <th>Réservations confirmées</th>
                                        <th>En attente</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['nom_hotel']); ?></td>
                                        <td><?php echo htmlspecialchars($row['adresse']); ?></td>
                                        <td><span class="badge bg-info"><?php echo $row['nb_chambres']; ?></span></td>
                                        <td><span class="badge bg-primary"><?php echo $row['nb_reservations']; ?></span></td>
                                        <td><span class="badge bg-success"><?php echo $row['reservations_confirmees']; ?></span></td>
                                        <td><span class="badge bg-warning"><?php echo $row['reservations_en_attente']; ?></span></td>
                                        <td>
                                            <a href="edit_hotel.php?id=<?php echo $row['id_hotel']; ?>" class="btn btn-sm btn-info">Modifier</a>
                                            <a href="hotel_reservations.php?id=<?php echo $row['id_hotel']; ?>" class="btn btn-sm btn-success">Réservations</a>
                                            <form method="post" style="display: inline;">
                                                <input type="hidden" name="id_hotel" value="<?php echo $row['id_hotel']; ?>">
                                                <button type="submit" name="delete_hotel" class="btn btn-sm btn-danger" 
                                                        onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet hôtel ?');">
                                                    Supprimer
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
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
