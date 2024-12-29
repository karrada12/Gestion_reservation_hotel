<?php
session_start();

// Vérifier si l'utilisateur est déjà connecté
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: index.php");
    exit;
}

require_once "includes/config.php";

$email = $password = "";
$email_err = $password_err = $login_err = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    if(empty(trim($_POST["email"]))){
        $email_err = "Veuillez entrer votre email.";
    } else{
        $email = trim($_POST["email"]);
    }
    
    if(empty(trim($_POST["password"]))){
        $password_err = "Veuillez entrer votre mot de passe.";
    } else{
        $password = trim($_POST["password"]);
    }
    
    if(empty($email_err) && empty($password_err)){
        // Vérifier d'abord dans la table admin
        $sql = "SELECT id_admin, email, password FROM tb_admin WHERE email = ?";
        
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "s", $param_email);
            $param_email = $email;
            
            if(mysqli_stmt_execute($stmt)){
                $result = mysqli_stmt_get_result($stmt);
                
                if(mysqli_num_rows($result) == 1){
                    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
                    
                    if(password_verify($password, $row["password"])){
                        // C'est un admin
                        $_SESSION["loggedin"] = true;
                        $_SESSION["id_admin"] = $row["id_admin"];
                        $_SESSION["email"] = $row["email"];
                        $_SESSION["is_admin"] = true;
                        
                        header("location: admin/index.php");
                        exit();
                    } else {
                        $login_err = "Mot de passe incorrect pour l'administrateur.";
                    }
                }
            } else {
                $login_err = "Erreur lors de l'exécution de la requête admin.";
            }
            mysqli_stmt_close($stmt);
        }

        if(empty($login_err)) {
            // Si ce n'est pas un admin, vérifier dans la table clients
            $sql = "SELECT id_client, nom, prenom, email, telephone, adresse, password FROM tb_clients WHERE email = ?";
            
            if($stmt = mysqli_prepare($conn, $sql)){
                mysqli_stmt_bind_param($stmt, "s", $param_email);
                $param_email = $email;
                
                if(mysqli_stmt_execute($stmt)){
                    $result = mysqli_stmt_get_result($stmt);
                    
                    if(mysqli_num_rows($result) == 1){
                        $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
                        
                        if(password_verify($password, $row["password"])){
                            // C'est un client
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id_client"] = $row["id_client"];
                            $_SESSION["nom"] = $row["nom"];
                            $_SESSION["prenom"] = $row["prenom"] ?? "";
                            $_SESSION["email"] = $row["email"];
                            $_SESSION["telephone"] = $row["telephone"] ?? "";
                            $_SESSION["adresse"] = $row["adresse"] ?? "";
                            $_SESSION["is_admin"] = false;
                            
                            // Redirection vers la page de réservation au lieu de index.php
                            header("location: hotel_details.php");
                            exit();
                        } else {
                            $login_err = "Mot de passe incorrect pour le client.";
                        }
                    } else {
                        $login_err = "Aucun compte trouvé avec cet email.";
                    }
                } else {
                    $login_err = "Erreur lors de l'exécution de la requête client.";
                }
                mysqli_stmt_close($stmt);
            }
        }
    }
    
    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Hôtel Réservation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #7793C2;
            --secondary-color: #9CADD8;
        }

        body {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            width: 100%;
            max-width: 500px;
            padding: 2rem;
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-header i {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .login-header h1 {
            color: var(--primary-color);
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }

        .form-control {
            border-radius: 10px;
            padding: 0.8rem 1rem;
            border: 1px solid #ddd;
            margin-bottom: 0.5rem;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(119, 147, 194, 0.25);
        }

        .input-group {
            margin-bottom: 1rem;
        }

        .input-group-text {
            border-radius: 10px 0 0 10px;
            border: 1px solid #ddd;
            background-color: #f8f9fa;
        }

        .btn-login {
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            border: none;
            border-radius: 10px;
            padding: 0.8rem;
            color: white;
            font-weight: 600;
            width: 100%;
            margin-top: 1rem;
            transition: transform 0.3s ease;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            background: linear-gradient(to right, var(--secondary-color), var(--primary-color));
        }

        .login-footer {
            text-align: center;
            margin-top: 1.5rem;
        }

        .login-footer a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }

        .login-footer a:hover {
            text-decoration: underline;
        }

        .alert {
            border-radius: 10px;
            margin-bottom: 1rem;
        }

        .invalid-feedback {
            font-size: 0.875rem;
            color: #dc3545;
            display: block;
            margin-top: -0.5rem;
            margin-bottom: 0.5rem;
        }

        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
            z-index: 10;
            background: none;
            border: none;
            padding: 0.25rem;
        }

        .password-toggle:hover {
            color: var(--primary-color);
        }

        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 1.5rem 0;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #ddd;
        }

        .divider span {
            padding: 0 1rem;
            color: #6c757d;
            font-size: 0.9rem;
        }

        .social-buttons {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .social-button {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            transition: transform 0.3s ease;
        }

        .social-button:hover {
            transform: translateY(-3px);
        }

        .facebook { background: #1877f2; }
        .google { background: #ea4335; }
        .twitter { background: #1da1f2; }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <i class="fas fa-hotel"></i>
            <h1>Connexion</h1>
            <p class="text-muted">Connectez-vous à votre compte</p>
        </div>

        <?php 
        if(!empty($login_err)){
            echo '<div class="alert alert-danger">' . $login_err . '</div>';
        }        
        ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                <input type="email" name="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" 
                       value="<?php echo $email; ?>" placeholder="Email">
                <?php if(!empty($email_err)): ?>
                    <div class="invalid-feedback d-block"><?php echo $email_err; ?></div>
                <?php endif; ?>
            </div>

            <div class="input-group mb-3">
                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" 
                       placeholder="Mot de passe" id="password">
                <button type="button" class="btn btn-outline-secondary password-toggle" onclick="togglePassword()">
                    <i class="fas fa-eye"></i>
                </button>
                <?php if(!empty($password_err)): ?>
                    <div class="invalid-feedback d-block"><?php echo $password_err; ?></div>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-login">
                <i class="fas fa-sign-in-alt me-2"></i>Se connecter
            </button>
        </form>

        <div class="divider">
            <span>ou connectez-vous avec</span>
        </div>

        <div class="social-buttons">
            <a href="#" class="social-button facebook">
                <i class="fab fa-facebook-f"></i>
            </a>
            <a href="#" class="social-button google">
                <i class="fab fa-google"></i>
            </a>
            <a href="#" class="social-button twitter">
                <i class="fab fa-twitter"></i>
            </a>
        </div>

        <div class="login-footer">
            <p>Vous n'avez pas de compte ? <a href="register.php">Inscrivez-vous</a></p>
            <a href="#" class="d-block mt-2">Mot de passe oublié ?</a>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.querySelector('.password-toggle i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
