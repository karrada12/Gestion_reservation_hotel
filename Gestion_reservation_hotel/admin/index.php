<?php
session_start();

// Vérifier si l'utilisateur est connecté et est admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] !== true){
    header("location: ../login.php");
    exit;
}

require_once "../includes/config.php";

// Récupérer les statistiques
$sql_hotels = "SELECT COUNT(*) as total_hotels FROM tb_hotels";
$sql_rooms = "SELECT COUNT(*) as total_rooms FROM tb_chambres";
$sql_reservations = "SELECT COUNT(*) as total_reservations FROM tb_reservations";
$sql_clients = "SELECT COUNT(*) as total_clients FROM tb_clients";

$hotels_count = mysqli_fetch_assoc(mysqli_query($conn, $sql_hotels))['total_hotels'];
$rooms_count = mysqli_fetch_assoc(mysqli_query($conn, $sql_rooms))['total_rooms'];
$reservations_count = mysqli_fetch_assoc(mysqli_query($conn, $sql_reservations))['total_reservations'];
$clients_count = mysqli_fetch_assoc(mysqli_query($conn, $sql_clients))['total_clients'];
?>
 
 
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Gloobin</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #858796;
            --success-color: #1cc88a;
            --info-color: #36b9cc;
            --warning-color: #f6c23e;
            --danger-color: #e74a3b;
            --light-color: #f8f9fc;
            --dark-color: #5a5c69;
        }

        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f8f9fc;
        }

        .sidebar {
            min-height: 100vh;
            width: 250px;
            background-color: #4e73df;
            box-shadow: 0 0.15rem 1.75rem rgba(0, 0, 0, 0.15);
            position: fixed;
            left: 0;
            top: 0;
            z-index: 1000;
            padding: 1rem;
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 0.8rem 1rem;
            margin: 0.2rem 0;
            border-radius: 0.35rem;
            transition: all 0.2s ease-in-out;
        }

        .sidebar .nav-link:hover, 
        .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
        }

        .content {
            margin-left: 250px;
            padding: 1.5rem;
        }

        /* Style des statistiques */
        .stats-container {
            display: flex;
            justify-content: flex-end;
            gap: 20px;
            padding: 20px;
            margin-top: -60px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 200px;
            text-align: center;
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card.primary { background: linear-gradient(135deg, #4e73df, #3756a4); }
        .stat-card.success { background: linear-gradient(135deg, #1cc88a, #169a67); }
        .stat-card.info { background: linear-gradient(135deg, #36b9cc, #258391); }
        .stat-card.warning { background: linear-gradient(135deg, #f6c23e, #dfa82a); }

        .stat-value {
            font-size: 28px;
            font-weight: bold;
            color: white;
            margin: 10px 0;
        }

        .stat-label {
            color: white;
            font-size: 16px;
            opacity: 0.9;
        }

        /* Style des cartes */
        .card {
            border: none;
            border-radius: 0.35rem;
            box-shadow: 0 0.15rem 1.75rem rgba(0, 0, 0, 0.05);
            margin-bottom: 1.5rem;
        }

        .card-header {
            background-color: #f8f9fc;
            border-bottom: 1px solid #e3e6f0;
            padding: 1rem 1.25rem;
        }

        .card-header h6 {
            font-weight: 700;
            color: #4e73df;
            margin: 0;
        }

        .table thead th {
            background-color: #f8f9fc;
            color: #4e73df;
            border-bottom: 2px solid #e3e6f0;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.8rem;
        }
    </style>
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link active" href="index.php">
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
                    <a class="nav-link" href="clients.php">
                        Clients
                    </a>
                </li>
                <li class="nav-item mt-3">
                    <a class="nav-link text-danger" href="../logout.php">
                        Déconnexion
                    </a>
                </li>
            </ul>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="container-fluid">
                <!-- Page Header -->
                <div class="page-header">
                    <h1 class="page-title">Dashboard</h1>
                </div>

                <!-- Stats Cards -->
                <div class="stats-container">
                    <div class="stat-card primary">
                        <div class="stat-label">Hôtels</div>
                        <div class="stat-value"><?php echo $hotels_count; ?></div>
                    </div>
                    <div class="stat-card success">
                        <div class="stat-label">Chambres</div>
                        <div class="stat-value"><?php echo $rooms_count; ?></div>
                    </div>
                    <div class="stat-card info">
                        <div class="stat-label">Réservations</div>
                        <div class="stat-value"><?php echo $reservations_count; ?></div>
                    </div>
                    <div class="stat-card warning">
                        <div class="stat-label">Clients</div>
                        <div class="stat-value"><?php echo $clients_count; ?></div>
                    </div>
                </div>

                <!-- Recent Reservations -->
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold">Réservations Récentes</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Client</th>
                                        <th>Hôtel</th>
                                        <th>Chambre</th>
                                        <th>Date d'arrivée</th>
                                        <th>Date de départ</th>
                                        <th>Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sql = "SELECT r.id_reservation, c.nom as client_nom, h.nom_hotel, 
                                           ch.type_chambre, r.date_arrivee, r.date_depart, r.statut
                                           FROM tb_reservations r
                                           JOIN tb_clients c ON r.id_client = c.id_client
                                           JOIN tb_chambres ch ON r.id_chambre = ch.id_chambre
                                           JOIN tb_hotels h ON ch.id_hotel = h.id_hotel
                                           ORDER BY r.created_at DESC LIMIT 5";
                                    $result = mysqli_query($conn, $sql);
                                    
                                    if ($result) {
                                        while($row = mysqli_fetch_assoc($result)) {
                                            echo "<tr>";
                                            echo "<td>" . $row['id_reservation'] . "</td>";
                                            echo "<td>" . htmlspecialchars($row['client_nom']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['nom_hotel']) . "</td>";
                                            echo "<td>" . htmlspecialchars($row['type_chambre']) . "</td>";
                                            echo "<td>" . $row['date_arrivee'] . "</td>";
                                            echo "<td>" . $row['date_depart'] . "</td>";
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
                                            echo "<td><span class='badge " . $status_class . "'>" . ucfirst(str_replace('_', ' ', $row['statut'])) . "</span></td>";
                                            echo "</tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='7' class='text-center'>Erreur lors de la récupération des réservations : " . mysqli_error($conn) . "</td></tr>";
                                    }
                                    ?>
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
