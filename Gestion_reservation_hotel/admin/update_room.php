<?php
session_start();
require_once "../includes/config.php";

// Check if admin is logged in
if(!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

// Validate input
if(!isset($_POST["id_chambre"]) || !isset($_POST["id_hotel"]) || !isset($_POST["type_chambre"]) || !isset($_POST["prix"]) || !isset($_POST["nombre_lits"])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Données manquantes']);
    exit;
}

$id_chambre = intval($_POST["id_chambre"]);
$id_hotel = intval($_POST["id_hotel"]);
$type_chambre = $_POST["type_chambre"];
$prix = floatval($_POST["prix"]);
$nombre_lits = intval($_POST["nombre_lits"]);
$disponibilite = isset($_POST["disponibilite"]) ? 1 : 0;

// Update room
$sql = "UPDATE tb_chambres SET 
        id_hotel = ?, 
        type_chambre = ?, 
        prix = ?, 
        nombre_lits = ?, 
        disponibilite = ? 
        WHERE id_chambre = ?";

if($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "isdiis", $id_hotel, $type_chambre, $prix, $nombre_lits, $disponibilite, $id_chambre);
    
    if(mysqli_stmt_execute($stmt)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'La chambre a été mise à jour avec succès']);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour : ' . mysqli_error($conn)]);
    }
    mysqli_stmt_close($stmt);
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Erreur de préparation de la requête : ' . mysqli_error($conn)]);
}

mysqli_close($conn);
?>
