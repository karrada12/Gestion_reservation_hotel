<?php
session_start();
require_once "../includes/config.php";

// Check if admin is logged in
if(!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Vérifier si l'ID et l'action sont présents
if(!isset($_GET['id']) || !isset($_GET['action'])) {
    $_SESSION['error'] = "Paramètres manquants";
    header("location: reservations.php");
    exit;
}

$id_reservation = $_GET['id'];
$action = $_GET['action'];

// Vérifier que l'action est valide
if($action !== 'accepter' && $action !== 'refuser') {
    $_SESSION['error'] = "Action non valide";
    header("location: reservations.php");
    exit;
}

// Vérifier que la réservation existe et est en attente
$sql = "SELECT statut FROM tb_reservations WHERE id_reservation = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id_reservation);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if(mysqli_num_rows($result) === 0) {
    $_SESSION['error'] = "Réservation non trouvée";
    header("location: reservations.php");
    exit;
}

$reservation = mysqli_fetch_assoc($result);
if($reservation['statut'] !== 'en_attente') {
    $_SESSION['error'] = "Cette réservation ne peut plus être modifiée";
    header("location: reservations.php");
    exit;
}

// Mettre à jour le statut de la réservation
$nouveau_statut = ($action === 'accepter') ? 'acceptee' : 'refusee';
$sql = "UPDATE tb_reservations SET statut = ? WHERE id_reservation = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "si", $nouveau_statut, $id_reservation);

if(mysqli_stmt_execute($stmt)) {
    $_SESSION['success'] = "La réservation a été " . ($action === 'accepter' ? "acceptée" : "refusée") . " avec succès";
} else {
    $_SESSION['error'] = "Une erreur est survenue lors du traitement de la réservation";
}

header("location: reservations.php");
exit;
?>
