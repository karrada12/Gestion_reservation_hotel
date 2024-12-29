<?php
session_start();
require_once "../includes/config.php";

// Check if admin is logged in
if(!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true) {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

if(!isset($_POST['id']) || empty($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID manquant']);
    exit;
}

$id = intval($_POST['id']);

$sql = "SELECT * FROM tb_hotels WHERE id_hotel = ?";
if($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if($hotel = mysqli_fetch_assoc($result)) {
        echo json_encode(['success' => true, 'hotel' => $hotel]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Hôtel non trouvé']);
    }
    mysqli_stmt_close($stmt);
} else {
    echo json_encode(['success' => false, 'message' => 'Erreur de requête']);
}

mysqli_close($conn);
?>
