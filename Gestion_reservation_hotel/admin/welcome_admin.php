<?php
session_start();
require_once('../includes/config.php');

// Verify if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration - Tableau de Bord</title>
    <link rel="stylesheet" href="css/common-tables.css">
    <link rel="stylesheet" href="css/login-style.css">
    <style>
        .admin-welcome-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }

        .stat-number {
            font-size: 2em;
            color: #2c3e50;
            font-weight: bold;
            margin: 10px 0;
        }

        .stat-label {
            color: #7f8c8d;
            font-size: 0.9em;
        }

        .recent-activity {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-top: 20px;
        }

        .activity-list {
            list-style: none;
            padding: 0;
        }

        .activity-item {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .welcome-header {
            text-align: center;
            margin-bottom: 30px;
            color: #2c3e50;
        }

        .launch-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: center;
        }
    </style>
</head>
<body>
    <?php include 'includes/admin_header.php'; ?>

    <div class="admin-welcome-container">
        <div class="welcome-header">
            <h1>Tableau de Bord Administrateur</h1>
            <p>Bienvenue dans l'interface d'administration</p>
        </div>

        <div class="launch-info">
            <h2>Information de Lancement</h2>
            <p>Système lancé le 28 décembre 2024</p>
            <p>Version actuelle: 1.0</p>
        </div>

        <div class="stats-grid">
            <?php
            // Get total number of hotels
            $sql_hotels = "SELECT COUNT(*) as total FROM hotels";
            $result_hotels = $conn->query($sql_hotels);
            $total_hotels = $result_hotels->fetch_assoc()['total'];

            // Get total number of reservations
            $sql_reservations = "SELECT COUNT(*) as total FROM reservations";
            $result_reservations = $conn->query($sql_reservations);
            $total_reservations = $result_reservations->fetch_assoc()['total'];

            // Get total number of users
            $sql_users = "SELECT COUNT(*) as total FROM users";
            $result_users = $conn->query($sql_users);
            $total_users = $result_users->fetch_assoc()['total'];
            ?>

            <div class="stat-card">
                <div class="stat-number"><?php echo $total_hotels; ?></div>
                <div class="stat-label">Hôtels Enregistrés</div>
            </div>

            <div class="stat-card">
                <div class="stat-number"><?php echo $total_reservations; ?></div>
                <div class="stat-label">Réservations Totales</div>
            </div>

            <div class="stat-card">
                <div class="stat-number"><?php echo $total_users; ?></div>
                <div class="stat-label">Utilisateurs Inscrits</div>
            </div>
        </div>

        <div class="recent-activity">
            <h2>Activités Récentes</h2>
            <ul class="activity-list">
                <?php
                // Get recent reservations
                $sql_recent = "SELECT r.*, u.username, h.nom as hotel_name 
                             FROM reservations r 
                             JOIN users u ON r.user_id = u.id 
                             JOIN hotels h ON r.hotel_id = h.id 
                             ORDER BY r.date_reservation DESC LIMIT 5";
                $result_recent = $conn->query($sql_recent);

                while($row = $result_recent->fetch_assoc()) {
                    echo "<li class='activity-item'>";
                    echo "Réservation par " . htmlspecialchars($row['username']) . " pour " . htmlspecialchars($row['hotel_name']);
                    echo " le " . date('d/m/Y', strtotime($row['date_reservation']));
                    echo "</li>";
                }
                ?>
            </ul>
        </div>
    </div>

    <?php include 'includes/admin_footer.php'; ?>
</body>
</html>
