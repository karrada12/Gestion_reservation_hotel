<?php
session_start();
require_once "../includes/config.php";

// Vérifier si l'utilisateur est connecté et est admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] !== true){
    header("location: ../login.php");
    exit;
}

// Récupérer toutes les chambres avec les informations de l'hôtel et le nombre de réservations
$sql = "SELECT c.*, h.nom_hotel, h.adresse,
        COUNT(r.id_reservation) as total_reservations,
        SUM(CASE WHEN r.statut = 'confirmee' THEN 1 ELSE 0 END) as reservations_confirmees,
        SUM(CASE WHEN r.statut = 'en_attente' THEN 1 ELSE 0 END) as reservations_en_attente
        FROM tb_chambres c 
        JOIN tb_hotels h ON c.id_hotel = h.id_hotel 
        LEFT JOIN tb_reservations r ON c.id_chambre = r.id_chambre
        GROUP BY c.id_chambre, h.nom_hotel, h.adresse, c.id_chambre, c.type_chambre, c.prix, c.disponibilite
        ORDER BY h.nom_hotel, c.type_chambre";

if($result = mysqli_query($conn, $sql)) {
    $rooms = mysqli_fetch_all($result, MYSQLI_ASSOC);
} else {
    $_SESSION['error'] = "Erreur lors de la récupération des chambres : " . mysqli_error($conn);
    $rooms = [];
}

// Récupérer la liste des hôtels pour le formulaire d'ajout
$hotels_sql = "SELECT id_hotel, nom_hotel FROM tb_hotels ORDER BY nom_hotel";
$hotels_result = mysqli_query($conn, $hotels_sql);
$hotels = mysqli_fetch_all($hotels_result, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Chambres - Admin</title>
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
                    <a class="nav-link active" href="rooms.php">
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
                    <h1 class="h3 mb-0">Gestion des Chambres</h1>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRoomModal">
                        Ajouter une chambre
                    </button>
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
                                        <th>Hôtel</th>
                                        <th>Type de chambre</th>
                                        <th>Prix/nuit</th>
                                        <th>Disponibilité</th>
                                        <th>Réservations</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($rooms as $room): ?>
                                    <tr>
                                        <td>
                                            <?php echo htmlspecialchars($room['nom_hotel']); ?>
                                            <br>
                                            <small class="text-muted"><?php echo htmlspecialchars($room['adresse']); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($room['type_chambre']); ?></td>
                                        <td><?php echo number_format($room['prix'], 2); ?> DHS</td>
                                        <td>
                                            <?php if($room['disponibilite']): ?>
                                                <span class="badge bg-success">Disponible</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Non disponible</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary"><?php echo $room['total_reservations']; ?> totales</span>
                                            <br>
                                            <small>
                                                <span class="badge bg-success"><?php echo $room['reservations_confirmees']; ?> confirmées</span>
                                                <span class="badge bg-warning"><?php echo $room['reservations_en_attente']; ?> en attente</span>
                                            </small>
                                        </td>
                                        <td>
                                            <a href="edit_room.php?id=<?php echo $room['id_chambre']; ?>" class="btn btn-sm btn-info">Modifier</a>
                                            <button type="button" class="btn btn-sm btn-danger" onclick="deleteRoom(<?php echo $room['id_chambre']; ?>)">
                                                Supprimer
                                            </button>
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

    <!-- Modal Ajout Chambre -->
    <div class="modal fade" id="addRoomModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ajouter une nouvelle chambre</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="post" action="add_room.php">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="id_hotel" class="form-label">Hôtel</label>
                            <select class="form-select" id="id_hotel" name="id_hotel" required>
                                <?php foreach($hotels as $hotel): ?>
                                    <option value="<?php echo $hotel['id_hotel']; ?>">
                                        <?php echo htmlspecialchars($hotel['nom_hotel']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="type_chambre" class="form-label">Type de chambre</label>
                            <select class="form-select" id="type_chambre" name="type_chambre" required>
                                <option value="Simple">Chambre Simple</option>
                                <option value="Double">Chambre Double</option>
                                <option value="Suite">Suite</option>
                                <option value="Suite Deluxe">Suite Deluxe</option>
                                <option value="Suite Présidentielle">Suite Présidentielle</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="prix" class="form-label">Prix par nuit (DHS)</label>
                            <input type="number" step="0.01" min="0" class="form-control" id="prix" name="prix" required>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="disponibilite" name="disponibilite" checked>
                            <label class="form-check-label" for="disponibilite">Disponible</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Ajouter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function deleteRoom(id) {
        if(confirm('Êtes-vous sûr de vouloir supprimer cette chambre ?')) {
            fetch('delete_room.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'id=' + id
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Une erreur est survenue lors de la suppression');
            });
        }
    }
    </script>
</body>
</html>
