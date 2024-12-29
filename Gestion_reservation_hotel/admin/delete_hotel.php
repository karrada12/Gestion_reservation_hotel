<?php
session_start();
require_once "../includes/config.php";

// Check if admin is logged in
if(!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true) {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

// Check if ID was provided
if(!isset($_POST['id']) || empty($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID manquant']);
    exit;
}

$id = intval($_POST['id']);

// Start transaction
mysqli_begin_transaction($conn);

try {
    // First check if hotel has any reservations through its rooms
    $check_sql = "SELECT COUNT(*) as res_count 
                 FROM tb_reservations r 
                 JOIN tb_chambres c ON r.id_chambre = c.id_chambre 
                 WHERE c.id_hotel = ?";
    
    if($check_stmt = mysqli_prepare($conn, $check_sql)) {
        mysqli_stmt_bind_param($check_stmt, "i", $id);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);
        $res_count = mysqli_fetch_assoc($check_result)['res_count'];
        
        if($res_count > 0) {
            throw new Exception("Impossible de supprimer l'hôtel car certaines chambres ont des réservations actives.");
        }
        mysqli_stmt_close($check_stmt);
    }
    
    // Delete all rooms of the hotel first
    $delete_rooms_sql = "DELETE FROM tb_chambres WHERE id_hotel = ?";
    if($stmt = mysqli_prepare($conn, $delete_rooms_sql)) {
        mysqli_stmt_bind_param($stmt, "i", $id);
        if(!mysqli_stmt_execute($stmt)) {
            throw new Exception("Erreur lors de la suppression des chambres : " . mysqli_error($conn));
        }
        mysqli_stmt_close($stmt);
    }
    
    // Then delete the hotel
    $delete_hotel_sql = "DELETE FROM tb_hotels WHERE id_hotel = ?";
    if($stmt = mysqli_prepare($conn, $delete_hotel_sql)) {
        mysqli_stmt_bind_param($stmt, "i", $id);
        if(!mysqli_stmt_execute($stmt)) {
            throw new Exception("Erreur lors de la suppression de l'hôtel : " . mysqli_error($conn));
        }
        mysqli_stmt_close($stmt);
    }
    
    // If we got here, commit the transaction
    mysqli_commit($conn);
    echo json_encode(['success' => true, 'message' => "L'hôtel et toutes ses chambres ont été supprimés avec succès."]);
    
} catch (Exception $e) {
    // Something went wrong, rollback the transaction
    mysqli_rollback($conn);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

mysqli_close($conn);
?>
