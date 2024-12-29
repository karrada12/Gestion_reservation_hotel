<?php
session_start();
require_once "includes/config.php";

// Initialiser la réponse
$response = [
    'success' => false,
    'message' => ''
];

// Vérifier si la requête est de type POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer et nettoyer les données du formulaire
    $name = trim(htmlspecialchars($_POST['name']));
    $email = trim(htmlspecialchars($_POST['email']));
    $subject = trim(htmlspecialchars($_POST['subject']));
    $message = trim(htmlspecialchars($_POST['message']));
    
    // Valider les données
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $response['message'] = "Tous les champs sont obligatoires.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = "L'adresse email n'est pas valide.";
    } else {
        // Préparer la requête SQL
        $sql = "INSERT INTO tb_contacts (nom, email, sujet, message, date_envoi) VALUES (?, ?, ?, ?, NOW())";
        
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "ssss", $name, $email, $subject, $message);
            
            if (mysqli_stmt_execute($stmt)) {
                $response['success'] = true;
                $response['message'] = "Votre message a été envoyé avec succès. Nous vous répondrons dans les plus brefs délais.";
                
                // Envoyer un email de confirmation (à configurer selon vos besoins)
                $to = $email;
                $email_subject = "Confirmation de réception - " . $subject;
                $email_message = "Bonjour " . $name . ",\n\n";
                $email_message .= "Nous avons bien reçu votre message et nous vous en remercions.\n";
                $email_message .= "Nous vous répondrons dans les plus brefs délais.\n\n";
                $email_message .= "Cordialement,\nL'équipe Gloobin";
                $headers = "From: contact@gloobin.com";
                
                mail($to, $email_subject, $email_message, $headers);
                
            } else {
                $response['message'] = "Une erreur est survenue lors de l'envoi du message.";
            }
            
            mysqli_stmt_close($stmt);
        } else {
            $response['message'] = "Une erreur est survenue lors de la préparation de la requête.";
        }
    }
    
    mysqli_close($conn);
} else {
    $response['message'] = "Méthode non autorisée.";
}

// Renvoyer la réponse en JSON
header('Content-Type: application/json');
echo json_encode($response);
?>
