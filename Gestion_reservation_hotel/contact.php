<?php
session_start();
require_once "includes/config.php";

$message = "";
$messageClass = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = trim($_POST["nom"]);
    $email = trim($_POST["email"]);
    $sujet = trim($_POST["sujet"]);
    $message_text = trim($_POST["message"]);

    if (empty($nom) || empty($email) || empty($sujet) || empty($message_text)) {
        $message = "Veuillez remplir tous les champs.";
        $messageClass = "alert-danger";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Veuillez entrer une adresse email valide.";
        $messageClass = "alert-danger";
    } else {
        $sql = "INSERT INTO tb_messages (nom, email, sujet, message, date_envoi) VALUES (?, ?, ?, ?, NOW())";
        
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "ssss", $nom, $email, $sujet, $message_text);
            
            if (mysqli_stmt_execute($stmt)) {
                $message = "Votre message a été envoyé avec succès. Nous vous répondrons dans les plus brefs délais.";
                $messageClass = "alert-success";
                
                // Réinitialiser les champs
                $nom = $email = $sujet = $message_text = "";
            } else {
                $message = "Une erreur est survenue. Veuillez réessayer plus tard.";
                $messageClass = "alert-danger";
            }
            mysqli_stmt_close($stmt);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact - Gloobin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="css/main-style.css" rel="stylesheet">
    <style>
        .contact-section {
            padding: 80px 0;
            background-color: #f8f9fa;
        }
        .contact-info {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            height: 100%;
        }
        .contact-form {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .contact-info i {
            color: #007bff;
            font-size: 24px;
            margin-right: 10px;
        }
        .contact-info-item {
            margin-bottom: 20px;
        }
        .contact-info-item h5 {
            margin-bottom: 5px;
            color: #333;
        }
        .social-links {
            margin-top: 30px;
        }
        .social-links a {
            display: inline-block;
            width: 40px;
            height: 40px;
            line-height: 40px;
            text-align: center;
            border-radius: 50%;
            background-color: #007bff;
            color: white;
            margin-right: 10px;
            transition: all 0.3s ease;
        }
        .social-links a:hover {
            background-color: #0056b3;
            transform: translateY(-3px);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php include 'includes/navbar.php'; ?>

    <!-- Contact Section -->
    <section class="contact-section" id="contact">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-10">
                    <div class="text-center mb-5">
                        <h2>Contactez-nous</h2>
                        <p>Nous sommes là pour répondre à toutes vos questions</p>
                    </div>
                </div>
            </div>

            <?php if(!empty($message)): ?>
                <div class="row justify-content-center mb-4">
                    <div class="col-md-10">
                        <div class="alert <?php echo $messageClass; ?>"><?php echo $message; ?></div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="row justify-content-center">
                <div class="col-md-6 mb-4 mb-md-0">
                    <div class="contact-form">
                        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                            <div class="mb-3">
                                <label for="nom" class="form-label">Nom complet</label>
                                <input type="text" class="form-control" id="nom" name="nom" required 
                                       value="<?php echo isset($nom) ? htmlspecialchars($nom) : ''; ?>">
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required
                                       value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
                            </div>
                            <div class="mb-3">
                                <label for="sujet" class="form-label">Sujet</label>
                                <input type="text" class="form-control" id="sujet" name="sujet" required
                                       value="<?php echo isset($sujet) ? htmlspecialchars($sujet) : ''; ?>">
                            </div>
                            <div class="mb-3">
                                <label for="message" class="form-label">Message</label>
                                <textarea class="form-control" id="message" name="message" rows="5" required><?php echo isset($message_text) ? htmlspecialchars($message_text) : ''; ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Envoyer le message</button>
                        </form>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="contact-info">
                        <div class="contact-info-item">
                            <h5><i class="fas fa-map-marker-alt"></i> Adresse</h5>
                            <p>123 Rue Example, Ville, Maroc</p>
                        </div>
                        <div class="contact-info-item">
                            <h5><i class="fas fa-phone"></i> Téléphone</h5>
                            <p>+212 123 456 789</p>
                        </div>
                        <div class="contact-info-item">
                            <h5><i class="fas fa-envelope"></i> Email</h5>
                            <p>contact@hotelbooking.com</p>
                        </div>
                        <div class="contact-info-item">
                            <h5><i class="fas fa-clock"></i> Heures d'ouverture</h5>
                            <p>Lundi - Vendredi: 9h00 - 18h00<br>
                               Samedi: 9h00 - 14h00<br>
                               Dimanche: Fermé</p>
                        </div>
                        <div class="social-links">
                            <a href="#"><i class="fab fa-facebook-f"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fab fa-instagram"></i></a>
                            <a href="#"><i class="fab fa-linkedin-in"></i></a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row justify-content-center">
                <div class="col-md-10">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h4 class="mb-4">Notre emplacement</h4>
                            <div class="map-container">
                                <iframe 
                                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3323.846447471348!2d-7.589843785271385!3d33.57382048073799!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMzPCsDM0JzI1LjgiTiA3wrAzNScyMy40Ilc!5e0!3m2!1sfr!2sma!4v1635789012345!5m2!1sfr!2sma" 
                                    allowfullscreen="" 
                                    loading="lazy"
                                    referrerpolicy="no-referrer-when-downgrade">
                                </iframe>
                            </div>
                            <div class="map-actions mt-3">
                                <div class="row">
                                    <div class="col-md-6">
                                        <a href="https://www.google.com/maps/dir//33.5738205,-7.5898438/@33.5738205,-7.5898438,17z" 
                                           target="_blank" 
                                           class="btn btn-outline-primary w-100 mb-2">
                                            <i class="fas fa-directions"></i> Obtenir l'itinéraire
                                        </a>
                                    </div>
                                    <div class="col-md-6">
                                        <button type="button" 
                                                class="btn btn-outline-secondary w-100 mb-2" 
                                                onclick="window.open('https://www.google.com/maps/@33.5738205,-7.5898438,18z', '_blank')">
                                            <i class="fas fa-expand-arrows-alt"></i> Voir en plein écran
                                        </button>
                                    </div>
                                </div>
                                <div class="map-info mt-3">
                                    <h5><i class="fas fa-info-circle"></i> À proximité :</h5>
                                    <ul class="list-unstyled">
                                        <li><i class="fas fa-subway"></i> Station de métro (500m)</li>
                                        <li><i class="fas fa-parking"></i> Parking public (200m)</li>
                                        <li><i class="fas fa-utensils"></i> Restaurants (dans un rayon de 1km)</li>
                                        <li><i class="fas fa-shopping-bag"></i> Centre commercial (1.5km)</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
