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
if(!isset($_GET["id"])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'ID manquant']);
    exit;
}

$id = intval($_GET["id"]);

// Get room details
$sql = "SELECT * FROM tb_chambres WHERE id_chambre = ?";
if($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $id);
    if(mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        if($room = mysqli_fetch_assoc($result)) {
            header('Content-Type: application/json');
            echo json_encode($room);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Chambre non trouvée']);
        }
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la récupération : ' . mysqli_error($conn)]);
    }
    mysqli_stmt_close($stmt);
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Erreur de préparation de la requête : ' . mysqli_error($conn)]);
}

mysqli_close($conn);
?>
