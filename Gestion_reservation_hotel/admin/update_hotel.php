<?php
session_start();
require_once "../includes/config.php";

// Check if admin is logged in
if(!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true) {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

// Validate input
if(!isset($_POST['id_hotel']) || !isset($_POST['nom_hotel']) || !isset($_POST['adresse']) || !isset($_POST['email']) || !isset($_POST['telephone'])) {
    echo json_encode(['success' => false, 'message' => 'Données manquantes']);
    exit;
}

$id = intval($_POST['id_hotel']);
$nom = $_POST['nom_hotel'];
$adresse = $_POST['adresse'];
$email = $_POST['email'];
$telephone = $_POST['telephone'];

// Update hotel
$sql = "UPDATE tb_hotels SET 
        nom_hotel = ?, 
        adresse = ?, 
        email = ?, 
        telephone = ? 
        WHERE id_hotel = ?";

if($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "ssssi", $nom, $adresse, $email, $telephone, $id);
    
    if(mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true, 'message' => 'Hôtel mis à jour avec succès']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour : ' . mysqli_error($conn)]);
    }
    mysqli_stmt_close($stmt);
} else {
    echo json_encode(['success' => false, 'message' => 'Erreur de préparation de la requête']);
}

mysqli_close($conn);
?>
