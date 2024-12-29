<?php
session_start();

// Vérifier si l'utilisateur est connecté
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

require_once "includes/config.php";

// Récupérer les réservations du client
$reservations = array();
$sql = "SELECT r.*, c.type_chambre, h.nom_hotel, h.adresse 
        FROM tb_reservations r 
        JOIN tb_chambres c ON r.id_chambre = c.id_chambre 
        JOIN tb_hotels h ON c.id_hotel = h.id_hotel 
        WHERE r.id_client = ? 
        ORDER BY r.date_debut DESC";

if($stmt = mysqli_prepare($conn, $sql)){
    mysqli_stmt_bind_param($stmt, "i", $_SESSION["id_client"]);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while($row = mysqli_fetch_assoc($result)){
        $reservations[] = $row;
    }
    mysqli_stmt_close($stmt);
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/main-style.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #7793C2;
            --secondary-color: #9CADD8;
            --background-color: #f8f9fa;
            --text-color: #2c3e50;
            --border-radius: 20px;
            --box-shadow: 0 0 25px rgba(0,0,0,0.1);
        }

        body {
            background-color: var(--background-color);
            color: var(--text-color);
        }

        .profile-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }

        .profile-section {
            background: white;
            border-radius: var(--border-radius);
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--box-shadow);
        }

        .profile-header {
            display: flex;
            align-items: center;
            margin-bottom: 2rem;
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 2rem;
            box-shadow: 0 5px 15px rgba(119, 147, 194, 0.3);
        }

        .profile-avatar i {
            font-size: 3.5rem;
            color: white;
        }

        .profile-info h1 {
            margin: 0;
            color: var(--primary-color);
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .info-item {
            background: var(--background-color);
            border-radius: 15px;
            padding: 1.2rem;
            margin-bottom: 1rem;
            transition: transform 0.3s ease;
        }

        .info-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .info-label {
            color: #6c757d;
            font-weight: 600;
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }

        .info-value {
            color: var(--text-color);
            font-size: 1.1rem;
            font-weight: 500;
        }

        .btn-edit-profile {
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn-edit-profile:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(119, 147, 194, 0.4);
            color: white;
        }

        .reservation-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 3px 10px rgba(0,0,0,0.05);
            transition: transform 0.3s ease;
            border: 1px solid #eee;
        }

        .reservation-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .reservation-status {
            padding: 0.5rem 1.2rem;
            border-radius: 25px;
            font-size: 0.9rem;
            font-weight: 600;
            display: inline-block;
        }

        .status-active {
            background: #e3f2fd;
            color: #1976d2;
        }

        .status-completed {
            background: #e8f5e9;
            color: #388e3c;
        }

        .alert {
            border-radius: var(--border-radius);
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
            border: none;
            box-shadow: 0 3px 10px rgba(0,0,0,0.05);
        }

        .alert-success {
            background: linear-gradient(45deg, #d4edda, #c3e6cb);
            color: #155724;
        }

        .reservation-details {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #eee;
        }

        .reservation-details p {
            margin-bottom: 0.5rem;
            color: var(--text-color);
        }

        .reservation-details strong {
            color: var(--primary-color);
            font-weight: 600;
        }

        .section-title {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 1.5rem;
            font-size: 1.8rem;
        }

        .empty-reservations {
            text-align: center;
            padding: 3rem;
            color: #6c757d;
        }

        .empty-reservations i {
            font-size: 3rem;
            color: var(--secondary-color);
            margin-bottom: 1rem;
        }

        @media (max-width: 768px) {
            .profile-header {
                flex-direction: column;
                text-align: center;
            }

            .profile-avatar {
                margin: 0 auto 1.5rem;
            }

            .profile-info h1 {
                font-size: 1.8rem;
            }

            .btn-edit-profile {
                width: 100%;
            }

            .reservation-card {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="profile-container">
        <div class="row">
            <div class="col-lg-4">
                <?php if(isset($_SESSION["success_message"])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <?php 
                        echo $_SESSION["success_message"];
                        unset($_SESSION["success_message"]); 
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="profile-section">
                    <div class="profile-header">
                        <div class="profile-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="profile-info">
                            <h1>
                                <?php 
                                $nom_complet = trim(
                                    (isset($_SESSION["nom"]) ? htmlspecialchars($_SESSION["nom"]) : "") . " " . 
                                    (isset($_SESSION["prenom"]) ? htmlspecialchars($_SESSION["prenom"]) : "")
                                );
                                echo !empty($nom_complet) ? $nom_complet : "Client";
                                ?>
                            </h1>
                            <p class="text-muted mb-0">Client</p>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Email</div>
                        <div class="info-value">
                            <?php echo isset($_SESSION["email"]) ? htmlspecialchars($_SESSION["email"]) : ""; ?>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Téléphone</div>
                        <div class="info-value">
                            <?php echo isset($_SESSION["telephone"]) ? htmlspecialchars($_SESSION["telephone"]) : "Non renseigné"; ?>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-label">Adresse</div>
                        <div class="info-value">
                            <?php echo isset($_SESSION["adresse"]) ? htmlspecialchars($_SESSION["adresse"]) : "Non renseignée"; ?>
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <a href="edit_profile.php" class="btn-edit-profile">
                            <i class="fas fa-edit me-2"></i>Modifier mon profil
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="profile-section">
                    <h2 class="section-title">Mes Réservations</h2>
                    
                    <?php if (empty($reservations)): ?>
                        <div class="empty-reservations">
                            <i class="fas fa-calendar-times mb-3"></i>
                            <p class="text-muted">Vous n'avez pas encore de réservations.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($reservations as $reservation): ?>
                            <div class="reservation-card">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h5 class="mb-1"><?php echo htmlspecialchars($reservation['nom_hotel']); ?></h5>
                                        <p class="text-muted mb-2"><?php echo htmlspecialchars($reservation['adresse']); ?></p>
                                    </div>
                                    <?php
                                    $status_class = '';
                                    $status_text = '';
                                    $today = new DateTime();
                                    $debut = new DateTime($reservation['date_debut']);
                                    $fin = new DateTime($reservation['date_fin']);
                                    
                                    if ($today < $debut) {
                                        $status_class = 'status-active';
                                        $status_text = 'À venir';
                                    } elseif ($today <= $fin) {
                                        $status_class = 'status-active';
                                        $status_text = 'En cours';
                                    } else {
                                        $status_class = 'status-completed';
                                        $status_text = 'Terminée';
                                    }
                                    ?>
                                    <span class="reservation-status <?php echo $status_class; ?>">
                                        <?php echo $status_text; ?>
                                    </span>
                                </div>
                                <div class="reservation-details">
                                    <p><strong>Type de chambre:</strong> <?php echo htmlspecialchars($reservation['type_chambre']); ?></p>
                                    <p><strong>Date d'arrivée:</strong> <?php echo date('d/m/Y', strtotime($reservation['date_debut'])); ?></p>
                                    <p><strong>Date de départ:</strong> <?php echo date('d/m/Y', strtotime($reservation['date_fin'])); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
