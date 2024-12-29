<?php
session_start();
require_once "../includes/config.php";

// Vérifier si l'utilisateur est connecté et est admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] !== true) {
    header("location: ../login.php");
    exit;
}

// Marquer un message comme lu
if(isset($_POST['action']) && $_POST['action'] == 'mark_read' && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $sql = "UPDATE tb_messages SET lu = 1 WHERE id_message = ?";
    if($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        $_SESSION['success'] = "Message marqué comme lu.";
        header("location: messages.php");
        exit;
    }
}

// Supprimer un message
if(isset($_POST['action']) && $_POST['action'] == 'delete' && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $sql = "DELETE FROM tb_messages WHERE id_message = ?";
    if($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        $_SESSION['success'] = "Message supprimé avec succès.";
        header("location: messages.php");
        exit;
    }
}

// Récupérer tous les messages
$sql = "SELECT *, DATE_FORMAT(date_envoi, '%d/%m/%Y %H:%i') as date_formatee 
        FROM tb_messages 
        ORDER BY lu ASC, date_envoi DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Messages - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/admin-style.css" rel="stylesheet">
    <style>
        .message-card {
            transition: all 0.3s ease;
        }
        .message-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .message-unread {
            background-color: #f8f9fa;
            border-left: 4px solid #007bff;
        }
        .message-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        .message-meta {
            font-size: 0.9rem;
            color: #6c757d;
        }
        .message-actions {
            display: flex;
            gap: 10px;
        }
        .message-content {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #dee2e6;
        }
    </style>
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar">
            <a class="sidebar-brand" href="index.php">
                <div class="sidebar-brand-icon">
                    <i class="fas fa-hotel"></i>
                </div>
                <div class="sidebar-brand-text">Gloobin Admin</div>
            </a>

            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="hotels.php">
                        Hôtels
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="rooms.php">
                        Chambres
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="reservations.php">
                        Réservations
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="clients.php">
                        Clients
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="messages.php">
                        Messages
                        <?php 
                        $unread = mysqli_query($conn, "SELECT COUNT(*) as count FROM tb_messages WHERE lu = 0");
                        $unread_count = mysqli_fetch_assoc($unread)['count'];
                        if($unread_count > 0): 
                        ?>
                        <span class="badge bg-danger"><?php echo $unread_count; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li class="nav-item mt-3">
                    <a class="nav-link text-danger" href="../logout.php">
                        Déconnexion
                    </a>
                </li>
            </ul>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3 mb-0">Messages de Contact</h1>
                </div>

                <?php if(isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?php 
                        echo $_SESSION['success'];
                        unset($_SESSION['success']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if(isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?php 
                        echo $_SESSION['error'];
                        unset($_SESSION['error']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <?php if(mysqli_num_rows($result) > 0): ?>
                        <?php while($message = mysqli_fetch_assoc($result)): ?>
                            <div class="col-12 mb-4">
                                <div class="card message-card <?php echo !$message['lu'] ? 'message-unread' : ''; ?>">
                                    <div class="card-body">
                                        <div class="message-header">
                                            <div>
                                                <h5 class="card-title mb-1">
                                                    <?php echo htmlspecialchars($message['sujet']); ?>
                                                    <?php if(!$message['lu']): ?>
                                                        <span class="badge bg-primary">Nouveau</span>
                                                    <?php endif; ?>
                                                </h5>
                                                <div class="message-meta">
                                                    De: <strong><?php echo htmlspecialchars($message['nom']); ?></strong>
                                                    (<?php echo htmlspecialchars($message['email']); ?>)
                                                    <br>
                                                    Reçu le: <?php echo $message['date_formatee']; ?>
                                                </div>
                                            </div>
                                            <div class="message-actions">
                                                <?php if(!$message['lu']): ?>
                                                    <form method="post" style="display: inline;">
                                                        <input type="hidden" name="action" value="mark_read">
                                                        <input type="hidden" name="id" value="<?php echo $message['id_message']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-success">
                                                            <i class="fas fa-check"></i> Marquer comme lu
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                                <form method="post" style="display: inline;" 
                                                      onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce message ?');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?php echo $message['id_message']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                        <i class="fas fa-trash"></i> Supprimer
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                        <div class="message-content">
                                            <?php echo nl2br(htmlspecialchars($message['message'])); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="alert alert-info">
                                Aucun message reçu pour le moment.
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
