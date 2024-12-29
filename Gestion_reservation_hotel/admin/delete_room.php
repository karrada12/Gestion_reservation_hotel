<?php
session_start();
require_once "../includes/config.php";

// Vérifier si l'utilisateur est connecté et est admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

// Vérifier si l'ID est présent
if(!isset($_POST["id"])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'ID manquant']);
    exit;
}

$id = intval($_POST["id"]);

// Vérifier si la chambre a des réservations
$check_sql = "SELECT COUNT(*) as res_count FROM tb_reservations WHERE id_chambre = ?";
if($check_stmt = mysqli_prepare($conn, $check_sql)) {
    mysqli_stmt_bind_param($check_stmt, "i", $id);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    $res_count = mysqli_fetch_assoc($check_result)['res_count'];
    
    if($res_count > 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Impossible de supprimer la chambre car elle a des réservations associées']);
        exit;
    }
    mysqli_stmt_close($check_stmt);
}

// Si pas de réservations, procéder à la suppression
$sql = "DELETE FROM tb_chambres WHERE id_chambre = ?";
if($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $id);
    if(mysqli_stmt_execute($stmt)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'La chambre a été supprimée avec succès']);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression : ' . mysqli_error($conn)]);
    }
    mysqli_stmt_close($stmt);
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Erreur de préparation de la requête : ' . mysqli_error($conn)]);
}

mysqli_close($conn);
?>
