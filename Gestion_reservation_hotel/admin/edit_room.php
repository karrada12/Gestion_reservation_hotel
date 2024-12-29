<?php
session_start();
require_once "../includes/config.php";

// Vérifier si l'utilisateur est connecté et est admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] !== true) {
    header("location: ../login.php");
    exit;
}

// Vérifier si l'ID est fourni
if(!isset($_GET["id"])) {
    $_SESSION['error'] = "ID de la chambre manquant";
    header("location: rooms.php");
    exit;
}

$id = intval($_GET["id"]);

// Si le formulaire est soumis
if($_SERVER["REQUEST_METHOD"] == "POST") {
    // Valider les entrées
    if(empty($_POST['type_chambre']) || empty($_POST['prix']) || !isset($_POST['disponibilite']) || empty($_POST['id_hotel'])) {
        $_SESSION['error'] = "Tous les champs sont obligatoires";
    } else {
        $type_chambre = $_POST['type_chambre'];
        $prix = floatval($_POST['prix']);
        $disponibilite = intval($_POST['disponibilite']);
        $id_hotel = intval($_POST['id_hotel']);

        // Mettre à jour la chambre
        $sql = "UPDATE tb_chambres SET 
                type_chambre = ?, 
                prix = ?, 
                disponibilite = ?,
                id_hotel = ?
                WHERE id_chambre = ?";

        if($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "sdiii", $type_chambre, $prix, $disponibilite, $id_hotel, $id);
            
            if(mysqli_stmt_execute($stmt)) {
                $_SESSION['success'] = "La chambre a été mise à jour avec succès";
                header("location: rooms.php");
                exit;
            } else {
                $_SESSION['error'] = "Erreur lors de la mise à jour : " . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt);
        }
    }
}

// Récupérer les données de la chambre
$sql = "SELECT c.*, h.nom_hotel 
        FROM tb_chambres c 
        LEFT JOIN tb_hotels h ON c.id_hotel = h.id_hotel 
        WHERE c.id_chambre = ?";

if($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if($room = mysqli_fetch_assoc($result)) {
        // Chambre trouvée
    } else {
        $_SESSION['error'] = "Chambre non trouvée";
        header("location: rooms.php");
        exit;
    }
    mysqli_stmt_close($stmt);
} else {
    $_SESSION['error'] = "Erreur de requête";
    header("location: rooms.php");
    exit;
}

// Récupérer tous les hôtels pour le menu déroulant
$hotels_sql = "SELECT id_hotel, nom_hotel FROM tb_hotels ORDER BY nom_hotel";
$hotels_result = mysqli_query($conn, $hotels_sql);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier la chambre - Admin</title>
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
                    <h1 class="h3 mb-0">Modifier la chambre</h1>
                    <a href="rooms.php" class="btn btn-secondary">
                        Retour
                    </a>
                </div>

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
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="id_hotel" class="form-label">Hôtel</label>
                                <select class="form-select" id="id_hotel" name="id_hotel" required>
                                    <?php while($hotel = mysqli_fetch_assoc($hotels_result)): ?>
                                        <option value="<?php echo $hotel['id_hotel']; ?>" 
                                                <?php echo ($hotel['id_hotel'] == $room['id_hotel']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($hotel['nom_hotel']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="type_chambre" class="form-label">Type de chambre</label>
                                <select class="form-select" id="type_chambre" name="type_chambre" required>
                                    <option value="Simple" <?php echo ($room['type_chambre'] == 'Simple') ? 'selected' : ''; ?>>Chambre Simple</option>
                                    <option value="Double" <?php echo ($room['type_chambre'] == 'Double') ? 'selected' : ''; ?>>Chambre Double</option>
                                    <option value="Suite" <?php echo ($room['type_chambre'] == 'Suite') ? 'selected' : ''; ?>>Suite</option>
                                    <option value="Suite Deluxe" <?php echo ($room['type_chambre'] == 'Suite Deluxe') ? 'selected' : ''; ?>>Suite Deluxe</option>
                                    <option value="Suite Présidentielle" <?php echo ($room['type_chambre'] == 'Suite Présidentielle') ? 'selected' : ''; ?>>Suite Présidentielle</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="prix" class="form-label">Prix par nuit (DHS)</label>
                                <input type="number" step="0.01" min="0" class="form-control" id="prix" name="prix" 
                                       value="<?php echo htmlspecialchars($room['prix']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="disponibilite" class="form-label">Disponibilité</label>
                                <select class="form-select" id="disponibilite" name="disponibilite" required>
                                    <option value="1" <?php echo ($room['disponibilite'] == 1) ? 'selected' : ''; ?>>Disponible</option>
                                    <option value="0" <?php echo ($room['disponibilite'] == 0) ? 'selected' : ''; ?>>Non disponible</option>
                                </select>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="rooms.php" class="btn btn-secondary me-md-2">Annuler</a>
                                <button type="submit" class="btn btn-primary">Enregistrer</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
