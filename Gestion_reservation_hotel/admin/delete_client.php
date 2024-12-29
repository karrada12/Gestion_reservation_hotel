<?php
session_start();
require_once "../includes/config.php";

// Check if admin is logged in
if(!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

// Ensure we have an ID
if(!isset($_POST["id"])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'ID manquant']);
    exit;
}

$id = intval($_POST["id"]);

// First check if client has any reservations
$check_sql = "SELECT COUNT(*) as res_count FROM tb_reservations WHERE id_client = ?";
if($check_stmt = mysqli_prepare($conn, $check_sql)) {
    mysqli_stmt_bind_param($check_stmt, "i", $id);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    $res_count = mysqli_fetch_assoc($check_result)['res_count'];
    
    if($res_count > 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Impossible de supprimer le client car il a des réservations associées']);
        exit;
    }
    mysqli_stmt_close($check_stmt);
}

// If no reservations, proceed with deletion
$sql = "DELETE FROM tb_clients WHERE id_client = ?";
if($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $id);
    if(mysqli_stmt_execute($stmt)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Le client a été supprimé avec succès']);
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
