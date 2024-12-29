<?php
session_start();
require_once "../includes/config.php";

// Vérifier si l'utilisateur est connecté et est admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] !== true) {
    header("location: ../login.php");
    exit;
}

// Récupérer tous les clients avec leurs statistiques de réservation
$sql = "SELECT c.*, 
        COUNT(r.id_reservation) as total_reservations,
        SUM(CASE WHEN r.statut = 'confirmee' THEN 1 ELSE 0 END) as reservations_confirmees,
        SUM(CASE WHEN r.statut = 'en_attente' THEN 1 ELSE 0 END) as reservations_en_attente,
        SUM(CASE WHEN r.statut = 'annulee' THEN 1 ELSE 0 END) as reservations_annulees
        FROM tb_clients c
        LEFT JOIN tb_reservations r ON c.id_client = r.id_client
        GROUP BY c.id_client
        ORDER BY c.date_inscription DESC";

$result = mysqli_query($conn, $sql);
$clients = [];
if($result) {
    $clients = mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Traitement de la désactivation/réactivation d'un compte client
if(isset($_POST['action']) && isset($_POST['id_client'])) {
    $id_client = intval($_POST['id_client']);
    $action = $_POST['action'];
    $nouveau_statut = ($action === 'activer') ? 1 : 0;
    
    $update_sql = "UPDATE tb_clients SET actif = ? WHERE id_client = ?";
    if($stmt = mysqli_prepare($conn, $update_sql)) {
        mysqli_stmt_bind_param($stmt, "ii", $nouveau_statut, $id_client);
        if(mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "Le statut du client a été mis à jour avec succès";
            header("location: clients.php");
            exit;
        } else {
            $_SESSION['error'] = "Erreur lors de la mise à jour du statut";
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Clients - Admin</title>
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
                    <a class="nav-link" href="reservations.php">
                        Réservations
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="clients.php">
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
                    <h1 class="h3 mb-0">Gestion des Clients</h1>
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
                                        <th>ID</th>
                                        <th>Client</th>
                                        <th>Contact</th>
                                        <th>Inscription</th>
                                        <th>Réservations</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($clients as $client): ?>
                                    <tr>
                                        <td>#<?php echo $client['id_client']; ?></td>
                                        <td>
                                            <?php echo htmlspecialchars($client['nom']); ?>
                                            <?php if(!empty($client['prenom'])): ?>
                                                <?php echo htmlspecialchars($client['prenom']); ?>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($client['email']); ?>
                                            <?php if(!empty($client['telephone'])): ?>
                                                <br>
                                                <small class="text-muted"><?php echo htmlspecialchars($client['telephone']); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php echo date('d/m/Y', strtotime($client['date_inscription'])); ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary"><?php echo $client['total_reservations']; ?> totales</span>
                                            <br>
                                            <small>
                                                <span class="badge bg-success"><?php echo $client['reservations_confirmees']; ?> confirmées</span>
                                                <span class="badge bg-warning"><?php echo $client['reservations_en_attente']; ?> en attente</span>
                                                <span class="badge bg-danger"><?php echo $client['reservations_annulees']; ?> annulées</span>
                                            </small>
                                        </td>
                                        <td>
                                            <?php if($client['actif']): ?>
                                                <span class="badge bg-success">Actif</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Inactif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="view_client.php?id=<?php echo $client['id_client']; ?>" 
                                               class="btn btn-sm btn-info">Détails</a>
                                            
                                            <?php if($client['actif']): ?>
                                                <form method="post" action="" style="display: inline;">
                                                    <input type="hidden" name="id_client" value="<?php echo $client['id_client']; ?>">
                                                    <input type="hidden" name="action" value="desactiver">
                                                    <button type="submit" class="btn btn-sm btn-danger" 
                                                            onclick="return confirm('Êtes-vous sûr de vouloir désactiver ce compte client ?')">
                                                        Désactiver
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <form method="post" action="" style="display: inline;">
                                                    <input type="hidden" name="id_client" value="<?php echo $client['id_client']; ?>">
                                                    <input type="hidden" name="action" value="activer">
                                                    <button type="submit" class="btn btn-sm btn-success">Activer</button>
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
