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

// If form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate input
    if(empty($_POST['nom_hotel']) || empty($_POST['adresse']) || empty($_POST['email']) || empty($_POST['telephone'])) {
        $_SESSION['error'] = "Tous les champs sont obligatoires";
    } else {
        $nom = $_POST['nom_hotel'];
        $adresse = $_POST['adresse'];
        $email = $_POST['email'];
        $telephone = $_POST['telephone'];

        // Update hotel
        $sql = "UPDATE tb_hotels SET 
                nom_hotel = ?, 
                adresse = ?, 
                email = ?, 
                telephone = ? 
                WHERE id_hotel = ?";

        if($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "ssssi", $nom, $adresse, $email, $telephone, $id);
            
            if(mysqli_stmt_execute($stmt)) {
                $_SESSION['success'] = "L'hôtel a été mis à jour avec succès";
                header("location: hotels.php");
                exit;
            } else {
                $_SESSION['error'] = "Erreur lors de la mise à jour : " . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt);
        }
    }
}

// Get hotel data
$sql = "SELECT * FROM tb_hotels WHERE id_hotel = ?";
if($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if($hotel = mysqli_fetch_assoc($result)) {
        // Hotel found, continue
    } else {
        $_SESSION['error'] = "Hôtel non trouvé";
        header("location: hotels.php");
        exit;
    }
    mysqli_stmt_close($stmt);
} else {
    $_SESSION['error'] = "Erreur de requête";
    header("location: hotels.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier l'hôtel - Admin</title>
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
                    <h1 class="h2">Modifier l'hôtel</h1>
                    <a href="hotels.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Retour
                    </a>
                </div>

                <?php if(isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?php 
                        echo $_SESSION['error']; 
                        unset($_SESSION['error']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="nom_hotel" class="form-label">Nom de l'hôtel</label>
                                <input type="text" class="form-control" id="nom_hotel" name="nom_hotel" 
                                       value="<?php echo htmlspecialchars($hotel['nom_hotel']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="adresse" class="form-label">Adresse</label>
                                <input type="text" class="form-control" id="adresse" name="adresse" 
                                       value="<?php echo htmlspecialchars($hotel['adresse']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($hotel['email']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="telephone" class="form-label">Téléphone</label>
                                <input type="tel" class="form-control" id="telephone" name="telephone" 
                                       value="<?php echo htmlspecialchars($hotel['telephone']); ?>" required>
                            </div>
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="hotels.php" class="btn btn-secondary me-md-2">Annuler</a>
                                <button type="submit" class="btn btn-primary">Enregistrer</button>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
