<?php
session_start();
require_once "includes/config.php";

if(!isset($_GET["id"])) {
    header("location: index.php");
    exit;
}

$id_hotel = $_GET["id"];

// Get hotel details
$sql = "SELECT h.*, COUNT(c.id_chambre) as room_count 
        FROM tb_hotels h 
        LEFT JOIN tb_chambres c ON h.id_hotel = c.id_hotel 
        WHERE h.id_hotel = ? 
        GROUP BY h.id_hotel";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id_hotel);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$hotel = mysqli_fetch_assoc($result);

if (!$hotel) {
    header("location: index.php");
    exit;
}

// Get available rooms
$sql = "SELECT * FROM tb_chambres WHERE id_hotel = ? AND disponibilite = 1";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id_hotel);
mysqli_stmt_execute($stmt);
$rooms = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($hotel['nom_hotel']); ?> - Détails de l'hôtel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        .hotel-banner {
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), 
                        url('<?php echo !empty($hotel['image']) ? $hotel['image'] : 'css/hotel-default.jpg'; ?>');
            background-size: cover;
            background-position: center;
            height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin-bottom: 30px;
        }

        .hotel-info {
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .room-card {
            border: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            transition: transform 0.3s;
        }

        .room-card:hover {
            transform: translateY(-5px);
        }

        .room-image {
            height: 200px;
            object-fit: cover;
        }

        .price {
            font-size: 24px;
            color: #007bff;
            font-weight: bold;
        }

        .features {
            list-style: none;
            padding: 0;
        }

        .features li {
            margin-bottom: 10px;
        }

        .features i {
            margin-right: 10px;
            color: #28a745;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="hotel-banner">
        <div class="container text-center">
            <h1><?php echo htmlspecialchars($hotel['nom_hotel']); ?></h1>
            <p class="lead">
                <i class="fas fa-map-marker-alt"></i> 
                <?php echo htmlspecialchars($hotel['adresse']); ?>
            </p>
        </div>
    </div>

    <div class="container">
        <div class="row">
            <div class="col-md-8">
                <div class="hotel-info">
                    <h2>Description</h2>
                    <p><?php echo nl2br(htmlspecialchars($hotel['description'])); ?></p>

                    <h3 class="mt-4">Informations de contact</h3>
                    <ul class="features">
                        <?php if(!empty($hotel['email'])): ?>
                        <li><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($hotel['email']); ?></li>
                        <?php endif; ?>
                        <?php if(!empty($hotel['telephone'])): ?>
                        <li><i class="fas fa-phone"></i> <?php echo htmlspecialchars($hotel['telephone']); ?></li>
                        <?php endif; ?>
                        <?php if(!empty($hotel['site_web'])): ?>
                        <li>
                            <i class="fas fa-globe"></i>
                            <a href="<?php echo htmlspecialchars($hotel['site_web']); ?>" target="_blank">
                                Visiter le site web
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

            <div class="col-md-4">
                <div class="hotel-info">
                    <h3>Chambres disponibles</h3>
                    <?php if(mysqli_num_rows($rooms) > 0): ?>
                        <?php while($room = mysqli_fetch_assoc($rooms)): ?>
                        <div class="card room-card">
                            <img src="<?php echo !empty($room['image']) ? $room['image'] : 'css/room-default.jpg'; ?>" 
                                 class="card-img-top room-image" 
                                 alt="<?php echo htmlspecialchars($room['type_chambre']); ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($room['type_chambre']); ?></h5>
                                <ul class="features">
                                    <li><i class="fas fa-users"></i> <?php echo $room['capacite']; ?> personnes max</li>
                                    <li><i class="fas fa-bed"></i> <?php echo $room['nombre_lits']; ?> lits</li>
                                </ul>
                                <p class="price mb-3"><?php echo number_format($room['prix'], 0, ',', ' '); ?> MAD <small>/ nuit</small></p>
                                <?php if(isset($_SESSION['user_id'])): ?>
                                <a href="reservation.php?chambre=<?php echo $room['id_chambre']; ?>" class="btn btn-primary w-100">
                                    Réserver maintenant
                                </a>
                                <?php else: ?>
                                <a href="login.php" class="btn btn-outline-primary w-100">
                                    Connectez-vous pour réserver
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="alert alert-info">
                            Aucune chambre disponible pour le moment.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
