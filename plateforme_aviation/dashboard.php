<?php
require_once 'db.php';
session_start();

// Si pas connecté, rediriger
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = "";
$msg_type = "";

// Traiter l'annulation d'une réservation par l'utilisateur
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel' && isset($_POST['reservation_id'])) {
    $res_id = (int)$_POST['reservation_id'];
    
    try {
        // Sécurité : s'assurer que la réservation appartient bien à l'utilisateur connecté et est toujours 'en attente'
        $stmtCheck = $pdo->prepare("SELECT status FROM reservations WHERE id = :id AND user_id = :user_id");
        $stmtCheck->execute(['id' => $res_id, 'user_id' => $user_id]);
        $status = $stmtCheck->fetchColumn();
        
        if ($status === 'en attente') {
            $stmtUpdate = $pdo->prepare("UPDATE reservations SET status = 'annulé' WHERE id = :id AND user_id = :user_id");
            $stmtUpdate->execute(['id' => $res_id, 'user_id' => $user_id]);
            $message = "Votre réservation #$res_id a été annulée avec succès.";
            $msg_type = "success";
        } else {
            $message = "Impossible d'annuler cette réservation. Seules les réservations en attente peuvent être annulées.";
            $msg_type = "danger";
        }
    } catch (PDOException $e) {
        $message = "Une erreur est survenue : " . $e->getMessage();
        $msg_type = "danger";
    }
}

$pageTitle = "Mon Espace Client - Mont Gabaon";
require_once 'includes/header.php';

// Récupérer les réservations de l'utilisateur
$my_reservations = [];
try {
    $stmt = $pdo->prepare("SELECT r.*, f.flight_number, f.depart, f.arrivee, f.heure_depart, f.heure_arrivee, f.price 
                           FROM reservations r 
                           JOIN flights f ON r.flight_id = f.id 
                           WHERE r.user_id = :user_id 
                           ORDER BY r.created_at DESC");
    $stmt->execute(['user_id' => $user_id]);
    $my_reservations = $stmt->fetchAll();
} catch (PDOException $e) {
    // Géré
}
?>

<section class="page-header">
    <div class="container">
        <h1>Mon Espace Personnel</h1>
        <p>Bienvenue, <?php echo htmlspecialchars($_SESSION['user_name']); ?>. Retrouvez ici vos réservations et gérez vos trajets.</p>
    </div>
</section>

<div class="container">
    <?php if ($message): ?>
        <div style="background-color: <?php echo ($msg_type === 'success') ? 'rgba(16, 185, 129, 0.1)' : 'rgba(239, 68, 68, 0.1)'; ?>; border: 1px solid <?php echo ($msg_type === 'success') ? 'var(--success)' : 'var(--danger)'; ?>; color: <?php echo ($msg_type === 'success') ? 'var(--success-hover)' : 'var(--danger)'; ?>; padding: 15px; border-radius: 8px; margin-bottom: 25px;">
            <i class="fa-solid <?php echo ($msg_type === 'success') ? 'fa-circle-check' : 'fa-circle-exclamation'; ?>"></i> <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div class="booking-grid" style="grid-template-columns: 1fr 3fr;">
        <!-- Profil info -->
        <div class="card-main" style="padding: 25px;">
            <h3 style="color: var(--primary); margin-bottom: 15px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">
                <i class="fa-solid fa-circle-user"></i> Mon Profil
            </h3>
            <div style="font-size: 0.95em; display: flex; flex-direction: column; gap: 15px;">
                <div>
                    <span style="color: var(--text-muted); display: block; font-size: 0.85em;">NOM</span>
                    <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong>
                </div>
                <div>
                    <span style="color: var(--text-muted); display: block; font-size: 0.85em;">ADRESSE EMAIL</span>
                    <strong><?php echo htmlspecialchars($_SESSION['user_email']); ?></strong>
                </div>
                <div>
                    <span style="color: var(--text-muted); display: block; font-size: 0.85em;">RÔLE DE COMPTE</span>
                    <strong style="text-transform: capitalize; color: var(--primary-light);">
                        <i class="fa-solid fa-user-shield" style="font-size: 0.85em;"></i> <?php echo htmlspecialchars($_SESSION['user_role']); ?>
                    </strong>
                </div>
            </div>
            
            <?php if ($_SESSION['user_role'] === 'administrateur'): ?>
                <a href="admin.php" class="btn-primary" style="margin-top: 25px; text-decoration: none;">
                    <i class="fa-solid fa-gears"></i> Portail Admin
                </a>
            <?php endif; ?>
        </div>

        <!-- Reservations list -->
        <div class="card-main" style="padding: 25px;">
            <h3 style="color: var(--primary); margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">
                <i class="fa-solid fa-plane-departure"></i> Historique de mes Réservations
            </h3>

            <?php if (!empty($my_reservations)): ?>
                <div class="flights-list">
                    <?php foreach ($my_reservations as $res): ?>
                        <div class="flight-card" style="box-shadow: none; border-color: var(--border-color); margin-bottom: 15px;">
                            <div class="flight-main-info" style="gap: 20px;">
                                <div class="flight-route" style="gap: 10px;">
                                    <div class="route-point">
                                        <div class="time" style="font-size: 1.1em;"><?php echo htmlspecialchars(date('H:i', strtotime($res['heure_depart']))); ?></div>
                                        <div class="city" style="font-size: 0.8em;"><?php echo htmlspecialchars($res['depart']); ?></div>
                                    </div>
                                    <div class="route-arrow" style="font-size: 1em;">
                                        <i class="fa-solid fa-plane"></i>
                                    </div>
                                    <div class="route-point">
                                        <div class="time" style="font-size: 1.1em;"><?php echo htmlspecialchars(date('H:i', strtotime($res['heure_arrivee']))); ?></div>
                                        <div class="city" style="font-size: 0.8em;"><?php echo htmlspecialchars($res['arrivee']); ?></div>
                                    </div>
                                </div>

                                <div class="flight-details" style="grid-template-columns: repeat(2, 1fr); gap: 5px 15px;">
                                    <div class="detail-item">
                                        <strong>Réf Réservation</strong>
                                        <span>#MG-<?php echo str_pad($res['id'], 5, '0', STR_PAD_LEFT); ?></span>
                                    </div>
                                    <div class="detail-item">
                                        <strong>Numéro de vol</strong>
                                        <span>MG-<?php echo htmlspecialchars($res['flight_number']); ?></span>
                                    </div>
                                    <div class="detail-item">
                                        <strong>Date de départ</strong>
                                        <span><?php echo date('d/m/Y', strtotime($res['date_depart'])); ?></span>
                                    </div>
                                    <div class="detail-item">
                                        <strong>Type</strong>
                                        <span><?php echo ($res['type_vol'] === 'aller_retour') ? 'Aller-Retour' : 'Aller simple'; ?></span>
                                    </div>
                                </div>
                            </div>

                            <div class="flight-price-action" style="gap: 15px;">
                                <div class="flight-price" style="text-align: right;">
                                    <span class="label">Total payé</span>
                                    <?php 
                                        $base_price = $res['price'];
                                        $passenger_multiplier = $res['adultes'] + ($res['enfants'] * 0.5);
                                        $trip_multiplier = ($res['type_vol'] === 'aller_retour') ? 2 : 1;
                                        $total_price = $base_price * $passenger_multiplier * $trip_multiplier;
                                    ?>
                                    <span class="amount" style="font-size: 1.3em;"><?php echo number_format($total_price, 2, ',', ' '); ?> $</span>
                                </div>
                                
                                <div style="display: flex; flex-direction: column; gap: 8px;">
                                    <span class="badge <?php 
                                        echo ($res['status'] === 'confirmé') ? 'badge-confirmed' : (($res['status'] === 'annulé') ? 'badge-cancelled' : 'badge-pending'); 
                                    ?>" style="text-align: center; width: 100%;"><?php echo htmlspecialchars($res['status']); ?></span>
                                    
                                    <?php if ($res['status'] === 'en attente'): ?>
                                        <form method="POST" action="dashboard.php" onsubmit="return confirm('Êtes-vous sûr de vouloir annuler cette réservation ?');" style="margin:0; padding:0; box-shadow:none; background:none;">
                                            <input type="hidden" name="reservation_id" value="<?php echo $res['id']; ?>">
                                            <input type="hidden" name="action" value="cancel">
                                            <button type="submit" class="btn-sm btn-cancel" style="width: 100%; justify-content: center;">
                                                <i class="fa-solid fa-ban"></i> Annuler
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div style="text-align: center; color: var(--text-muted); padding: 40px;">
                    <i class="fa-solid fa-plane-slash" style="font-size: 3em; margin-bottom: 15px;"></i>
                    <p>Vous n'avez pas encore effectué de réservations.</p>
                    <a href="index.php" class="btn-select" style="margin-top: 15px; display: inline-block;">Réserver mon premier vol</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>
