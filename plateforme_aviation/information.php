<?php
require_once 'db.php';
session_start();

$pageTitle = "Confirmation de réservation - Mont Gabaon";
require_once 'includes/header.php';

$reservation_id = isset($_GET['id']) ? (int)$_GET['id'] : ($_SESSION['last_reservation_id'] ?? null);
$reservation = null;

if ($reservation_id) {
    try {
        $stmt = $pdo->prepare("SELECT r.*, f.flight_number, f.aircraft, f.depart, f.arrivee, f.heure_depart, f.heure_arrivee, f.price 
                               FROM reservations r 
                               JOIN flights f ON r.flight_id = f.id 
                               WHERE r.id = :id");
        $stmt->execute(['id' => $reservation_id]);
        $reservation = $stmt->fetch();
    } catch (PDOException $e) {
        // Géré
    }
}
?>

<div class="container">
    <?php if ($reservation): ?>
        <div class="info-container">
            <div class="success-icon">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <h1>Merci de votre confiance !</h1>
            <p>Votre demande de réservation a été enregistrée avec succès. Notre équipe va la traiter dans les plus brefs délais.</p>

            <div class="receipt-card">
                <div class="receipt-title">Récapitulatif de Réservation</div>
                
                <div class="receipt-row">
                    <strong>Référence Réservation :</strong>
                    <span>#MG-<?php echo str_pad($reservation['id'], 5, '0', STR_PAD_LEFT); ?></span>
                </div>
                <div class="receipt-row">
                    <strong>Passager :</strong>
                    <span><?php echo htmlspecialchars($reservation['passenger_name']); ?></span>
                </div>
                <div class="receipt-row">
                    <strong>Téléphone :</strong>
                    <span><?php echo htmlspecialchars($reservation['phone']); ?></span>
                </div>
                <div class="receipt-row">
                    <strong>Email :</strong>
                    <span><?php echo htmlspecialchars($reservation['email']); ?></span>
                </div>
                
                <div style="border-top: 1px dashed var(--border-color); margin: 15px 0;"></div>
                
                <div class="receipt-row">
                    <strong>Vol :</strong>
                    <span>MG-<?php echo htmlspecialchars($reservation['flight_number']); ?> (<?php echo htmlspecialchars($reservation['aircraft']); ?>)</span>
                </div>
                <div class="receipt-row">
                    <strong>Itinéraire :</strong>
                    <span><?php echo htmlspecialchars(ucfirst($reservation['depart'])); ?> &rarr; <?php echo htmlspecialchars(ucfirst($reservation['arrivee'])); ?></span>
                </div>
                <div class="receipt-row">
                    <strong>Départ :</strong>
                    <span><?php echo htmlspecialchars(date('d/m/Y', strtotime($reservation['date_depart']))); ?> à <?php echo htmlspecialchars(date('H:i', strtotime($reservation['heure_depart']))); ?></span>
                </div>
                <?php if ($reservation['type_vol'] === 'aller_retour' && $reservation['date_retour']): ?>
                    <div class="receipt-row">
                        <strong>Retour :</strong>
                        <span><?php echo htmlspecialchars(date('d/m/Y', strtotime($reservation['date_retour']))); ?></span>
                    </div>
                <?php endif; ?>
                <div class="receipt-row">
                    <strong>Voyageurs :</strong>
                    <span><?php echo $reservation['adultes']; ?> Adulte(s) <?php echo ($reservation['enfants'] > 0) ? ", " . $reservation['enfants'] . " Enfant(s)" : ""; ?></span>
                </div>
                <div class="receipt-row">
                    <strong>Statut :</strong>
                    <span class="badge <?php 
                        echo ($reservation['status'] === 'confirmé') ? 'badge-confirmed' : (($reservation['status'] === 'annulé') ? 'badge-cancelled' : 'badge-pending'); 
                    ?>"><?php echo htmlspecialchars($reservation['status']); ?></span>
                </div>

                <div style="border-top: 1px dashed var(--border-color); margin: 15px 0;"></div>

                <div class="receipt-row" style="font-size: 1.15em; font-weight: 700;">
                    <strong>Total estimé :</strong>
                    <?php 
                        $base_price = $reservation['price'];
                        $passenger_multiplier = $reservation['adultes'] + ($reservation['enfants'] * 0.5);
                        $trip_multiplier = ($reservation['type_vol'] === 'aller_retour') ? 2 : 1;
                        $total_price = $base_price * $passenger_multiplier * $trip_multiplier;
                    ?>
                    <span style="color: var(--primary); font-size: 1.2em;"><?php echo number_format($total_price, 2, ',', ' '); ?> $</span>
                </div>
            </div>

            <div class="info-alert">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <p>
                    <b>Important :</b> Votre réservation est actuellement <b>en attente de confirmation</b>. Un agent de Mont Gabaon va vous contacter par téléphone ou email dans les 2 prochaines heures pour valider votre billet et organiser le paiement.
                </p>
            </div>

            <div style="display: flex; gap: 15px; justify-content: center; margin-top: 20px;">
                <button onclick="window.print()" class="btn-select" style="background-color: var(--primary); color: white; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-print"></i> Imprimer le reçu
                </button>
                <a href="index.php" class="btn-select" style="background-color: var(--bg-body); color: var(--text-main); border: 1px solid var(--border-color);">
                    <i class="fa-solid fa-house"></i> Retour à l'accueil
                </a>
            </div>
        </div>
    <?php else: ?>
        <!-- Affichage standard si aucun reçu récent n'est chargé (ancienne page information.html) -->
        <div class="info-container">
            <div class="success-icon" style="color: var(--primary-light);">
                <i class="fa-solid fa-hands-clapping"></i>
            </div>
            <h1>Merci de nous faire confiance !</h1>
            <p>À la prochaine sur nos lignes !</p>
            
            <div style="margin: 30px 0; text-align: left;">
                <div class="info-contact" style="background-color: var(--bg-body); padding: 20px; border-radius: 12px; border: 1px solid var(--border-color); margin-bottom: 20px;">
                    <h3 style="color: var(--primary); margin-bottom: 12px;"><i class="fa-solid fa-address-book"></i> Nos Contacts</h3>
                    <p style="margin-bottom: 8px;"><i class="fa-solid fa-phone" style="color: var(--primary-light);"></i> Téléphone : <a href="tel:+243826940352" style="color: var(--primary); text-decoration: none; font-weight: 600;">+243 826 940 352</a></p>
                    <p><i class="fa-solid fa-envelope" style="color: var(--primary-light);"></i> Email : <a href="mailto:clarayks.26@gmail.com" style="color: var(--primary); text-decoration: none; font-weight: 600;">clarayks.26@gmail.com</a></p>
                </div>

                <div class="info-alert" style="background-color: rgba(239, 68, 68, 0.08); border-left: 4px solid var(--danger);">
                    <i class="fa-solid fa-triangle-exclamation" style="color: var(--danger);"></i>
                    <p style="color: var(--text-main);">
                        <b>En cas d'urgence :</b> Pour toute modification urgente ou problème lié à votre vol, veuillez nous contacter directement au : <b>099 400 0060</b>.
                    </p>
                </div>

                <div style="background-color: var(--bg-body); padding: 20px; border-radius: 12px; border: 1px solid var(--border-color);">
                    <h3 style="color: var(--primary); margin-bottom: 8px;"><i class="fa-solid fa-ban"></i> Conditions d'annulation</h3>
                    <p style="font-size: 0.95em; color: var(--text-muted);">
                        Pour toute annulation, veuillez nous contacter au moins <b>12 heures avant l'heure de départ prévue</b>. Des frais d'annulation peuvent s'appliquer en fonction du type de billet.
                    </p>
                </div>
            </div>
            
            <a href="index.php" class="btn-select" style="display: inline-block; width: auto; padding: 12px 30px;">
                <i class="fa-solid fa-plane-departure"></i> Réserver un vol
            </a>
        </div>
    <?php endif; ?>
</div>

<?php
require_once 'includes/footer.php';
?>
