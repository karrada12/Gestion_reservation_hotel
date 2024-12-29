<?php
session_start();
include 'includes/config.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenue - Système de Réservation d'Hôtel</title>
    <link rel="stylesheet" href="css/main-style.css">
    <style>
        .welcome-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 30px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .welcome-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .welcome-header h1 {
            color: #2c3e50;
            font-size: 2.5em;
            margin-bottom: 15px;
        }

        .launch-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }

        .features-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }

        .feature-item {
            padding: 15px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .cta-button {
            display: inline-block;
            padding: 12px 25px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s;
        }

        .cta-button:hover {
            background: #2980b9;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="welcome-container">
        <div class="welcome-header">
            <h1>Bienvenue sur notre Système de Réservation d'Hôtel</h1>
            <p>Votre destination pour une expérience de réservation simple et efficace</p>
        </div>

        <div class="launch-info">
            <h2>À propos de notre lancement</h2>
            <p>Notre système a été lancé le 28 décembre 2024, marquant le début d'une nouvelle ère dans la réservation d'hôtel en ligne.</p>
            <p>Nous sommes fiers de vous offrir une plateforme moderne et intuitive pour gérer vos réservations d'hôtel.</p>
        </div>

        <div class="features-list">
            <div class="feature-item">
                <h3>Réservation Facile</h3>
                <p>Interface intuitive pour réserver votre chambre en quelques clics</p>
            </div>
            <div class="feature-item">
                <h3>Large Sélection</h3>
                <p>Accès à une variété d'hôtels et de chambres</p>
            </div>
            <div class="feature-item">
                <h3>Gestion Simple</h3>
                <p>Suivez et gérez vos réservations facilement</p>
            </div>
        </div>

        <div style="text-align: center; margin-top: 30px;">
            <a href="hotel.php" class="cta-button">Découvrir nos hôtels</a>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
