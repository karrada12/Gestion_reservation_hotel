<?php
session_start();
require_once "../includes/config.php";

// Check if admin is logged in
if(!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true) {
    die("Accès non autorisé");
}

// Check if request is POST and has required data
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id']) && isset($_POST['status'])) {
    $id = intval($_POST['id']);
    $status = $_POST['status'];
    
    // Validate status
    $valid_statuses = ['en_attente', 'confirmee', 'annulee'];
    if (!in_array($status, $valid_statuses)) {
        die("Statut invalide");
    }
    
    // Update the reservation status
    $sql = "UPDATE tb_reservations SET statut = ? WHERE id_reservation = ?";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "si", $status, $id);
        
        if (mysqli_stmt_execute($stmt)) {
            echo "success";
        } else {
            echo "Erreur lors de la mise à jour: " . mysqli_error($conn);
        }
        
        mysqli_stmt_close($stmt);
    } else {
        echo "Erreur de préparation de la requête: " . mysqli_error($conn);
    }
} else {
    echo "Données manquantes";
}

mysqli_close($conn);
?>
