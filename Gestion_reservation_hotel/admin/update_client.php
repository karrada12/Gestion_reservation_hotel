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
if(!isset($_POST["id_client"]) || !isset($_POST["nom"]) || !isset($_POST["email"]) || !isset($_POST["telephone"]) || !isset($_POST["adresse"])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Données manquantes']);
    exit;
}

$id_client = intval($_POST["id_client"]);
$nom = $_POST["nom"];
$email = $_POST["email"];
$telephone = $_POST["telephone"];
$adresse = $_POST["adresse"];

// Update client
$sql = "UPDATE tb_clients SET 
        nom = ?, 
        email = ?, 
        telephone = ?, 
        adresse = ? 
        WHERE id_client = ?";

if($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "ssssi", $nom, $email, $telephone, $adresse, $id_client);
    
    if(mysqli_stmt_execute($stmt)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Le client a été mis à jour avec succès']);
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
