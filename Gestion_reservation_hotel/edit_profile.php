<?php
session_start();

// Vérifier si l'utilisateur est connecté
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

require_once "includes/config.php";

$nom = $prenom = $email = $telephone = $adresse = "";
$nom_err = $prenom_err = $email_err = $telephone_err = $adresse_err = "";
$success_message = $error_message = "";

// Traitement du formulaire lors de la soumission
if($_SERVER["REQUEST_METHOD"] == "POST"){
    // Validation du nom
    if(empty(trim($_POST["nom"]))){
        $nom_err = "Veuillez entrer votre nom.";
    } else {
        $nom = trim($_POST["nom"]);
    }

    // Validation du prénom
    $prenom = trim($_POST["prenom"]); // Optionnel

    // Validation de l'email
    if(empty(trim($_POST["email"]))){
        $email_err = "Veuillez entrer votre email.";
    } else {
        $email = trim($_POST["email"]);
        // Vérifier si l'email existe déjà (sauf pour l'utilisateur actuel)
        $sql = "SELECT id_client FROM tb_clients WHERE email = ? AND id_client != ?";
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "si", $email, $_SESSION["id_client"]);
            if(mysqli_stmt_execute($stmt)){
                mysqli_stmt_store_result($stmt);
                if(mysqli_stmt_num_rows($stmt) > 0){
                    $email_err = "Cet email est déjà utilisé.";
                }
            }
            mysqli_stmt_close($stmt);
        }
    }

    // Validation du téléphone
    $telephone = trim($_POST["telephone"]); // Optionnel

    // Validation de l'adresse
    $adresse = trim($_POST["adresse"]); // Optionnel

    // Vérifier les erreurs avant la mise à jour
    if(empty($nom_err) && empty($email_err)){
        $sql = "UPDATE tb_clients SET nom = ?, prenom = ?, email = ?, telephone = ?, adresse = ? WHERE id_client = ?";
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "sssssi", $nom, $prenom, $email, $telephone, $adresse, $_SESSION["id_client"]);
            if(mysqli_stmt_execute($stmt)){
                // Mettre à jour les variables de session
                $_SESSION["nom"] = $nom;
                $_SESSION["prenom"] = $prenom;
                $_SESSION["email"] = $email;
                $_SESSION["telephone"] = $telephone;
                $_SESSION["adresse"] = $adresse;
                
                // Rediriger vers la page de profil avec un message de succès
                $_SESSION["success_message"] = "Votre profil a été mis à jour avec succès.";
                header("location: profile.php");
                exit();
            } else {
                $error_message = "Une erreur est survenue lors de la mise à jour du profil.";
            }
            mysqli_stmt_close($stmt);
        }
    }
}

// Récupérer les informations actuelles de l'utilisateur
if(empty($nom) && empty($prenom) && empty($email) && empty($telephone) && empty($adresse)){
    $nom = $_SESSION["nom"] ?? "";
    $prenom = $_SESSION["prenom"] ?? "";
    $email = $_SESSION["email"] ?? "";
    $telephone = $_SESSION["telephone"] ?? "";
    $adresse = $_SESSION["adresse"] ?? "";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier mon profil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/main-style.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #7793C2;
            --secondary-color: #9CADD8;
        }

        body {
            background-color: #f8f9fa;
        }

        .edit-profile-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2.5rem;
            background: white;
            border-radius: 20px;
            box-shadow: 0 0 25px rgba(0,0,0,0.1);
        }

        .form-group {
            margin-bottom: 1.8rem;
            position: relative;
        }

        .form-label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }

        .form-control {
            border-radius: 12px;
            border: 2px solid #e0e0e0;
            padding: 0.8rem 1.2rem;
            transition: all 0.3s ease;
            font-size: 1rem;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(119, 147, 194, 0.25);
        }

        .btn {
            padding: 0.8rem 2rem;
            border-radius: 12px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            border: none;
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(119, 147, 194, 0.4);
        }

        .btn-outline-secondary {
            border: 2px solid #6c757d;
            background: transparent;
            color: #6c757d;
        }

        .btn-outline-secondary:hover {
            background: #6c757d;
            color: white;
            transform: translateY(-2px);
        }

        .alert {
            border-radius: 12px;
            padding: 1rem 1.5rem;
            margin-bottom: 2rem;
            border: none;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
        }

        .invalid-feedback {
            font-size: 0.85rem;
            color: #dc3545;
            margin-top: 0.5rem;
        }

        .form-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .form-header h2 {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .form-header p {
            color: #6c757d;
            font-size: 1.1rem;
        }

        .required-field::after {
            content: "*";
            color: #dc3545;
            margin-left: 4px;
        }

        .button-group {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        @media (max-width: 768px) {
            .edit-profile-container {
                margin: 1rem;
                padding: 1.5rem;
            }

            .button-group {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container py-5">
        <div class="edit-profile-container">
            <div class="form-header">
                <h2>Modifier mon profil</h2>
                <p>Mettez à jour vos informations personnelles</p>
            </div>

            <?php if(!empty($error_message)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-group">
                    <label class="form-label required-field">Nom</label>
                    <input type="text" name="nom" class="form-control <?php echo (!empty($nom_err)) ? 'is-invalid' : ''; ?>" 
                           value="<?php echo htmlspecialchars($nom); ?>" placeholder="Entrez votre nom">
                    <?php if(!empty($nom_err)): ?>
                        <div class="invalid-feedback">
                            <i class="fas fa-exclamation-circle me-1"></i>
                            <?php echo $nom_err; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label class="form-label">Prénom</label>
                    <input type="text" name="prenom" class="form-control" 
                           value="<?php echo htmlspecialchars($prenom); ?>" placeholder="Entrez votre prénom">
                </div>

                <div class="form-group">
                    <label class="form-label required-field">Email</label>
                    <input type="email" name="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" 
                           value="<?php echo htmlspecialchars($email); ?>" placeholder="Entrez votre email">
                    <?php if(!empty($email_err)): ?>
                        <div class="invalid-feedback">
                            <i class="fas fa-exclamation-circle me-1"></i>
                            <?php echo $email_err; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label class="form-label">Téléphone</label>
                    <input type="tel" name="telephone" class="form-control" 
                           value="<?php echo htmlspecialchars($telephone); ?>" placeholder="Entrez votre numéro de téléphone">
                </div>

                <div class="form-group">
                    <label class="form-label">Adresse</label>
                    <textarea name="adresse" class="form-control" rows="3" 
                              placeholder="Entrez votre adresse"><?php echo htmlspecialchars($adresse); ?></textarea>
                </div>

                <div class="button-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Enregistrer les modifications
                    </button>
                    <a href="profile.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Retour au profil
                    </a>
                </div>
            </form>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
