<?php
session_start();
require_once "includes/config.php";

// Vérifier si l'utilisateur est connecté
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Vérifier si l'ID de réservation est fourni
if(!isset($_GET["id"]) || empty($_GET["id"])) {
    $_SESSION["error"] = "ID de réservation invalide.";
    header("location: mes-reservations.php");
    exit;
}

$id_reservation = intval($_GET["id"]);
$id_client = $_SESSION["id_client"];

// Vérifier si la réservation appartient à l'utilisateur connecté
$sql = "SELECT r.*, c.disponibilite 
        FROM tb_reservations r 
        JOIN tb_chambres c ON r.id_chambre = c.id_chambre 
        WHERE r.id_reservation = ? AND r.id_client = ?";

if($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "ii", $id_reservation, $id_client);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if(mysqli_num_rows($result) == 1) {
        $reservation = mysqli_fetch_assoc($result);
        
        // Vérifier si la date d'arrivée n'est pas dépassée
        if(strtotime($reservation['date_arrivee']) > time()) {
            // Supprimer la réservation
            $sql_delete = "DELETE FROM tb_reservations WHERE id_reservation = ?";
            if($stmt_delete = mysqli_prepare($conn, $sql_delete)) {
                mysqli_stmt_bind_param($stmt_delete, "i", $id_reservation);
                if(mysqli_stmt_execute($stmt_delete)) {
                    // Mettre à jour la disponibilité de la chambre
                    $sql_update = "UPDATE tb_chambres SET disponibilite = 1 WHERE id_chambre = ?";
                    if($stmt_update = mysqli_prepare($conn, $sql_update)) {
                        mysqli_stmt_bind_param($stmt_update, "i", $reservation['id_chambre']);
                        mysqli_stmt_execute($stmt_update);
                    }
                    $_SESSION["success"] = "La réservation a été annulée avec succès.";
                } else {
                    $_SESSION["error"] = "Une erreur est survenue lors de l'annulation de la réservation.";
                }
                mysqli_stmt_close($stmt_delete);
            }
        } else {
            $_SESSION["error"] = "Impossible d'annuler une réservation dont la date est dépassée.";
        }
    } else {
        $_SESSION["error"] = "Réservation non trouvée ou non autorisée.";
    }
    mysqli_stmt_close($stmt);
}

header("location: mes-reservations.php");
exit;
?>
