<?php
require_once 'db.php';
session_start();

$pageTitle = "Réservation de Vol - Mont Gabaon";
require_once 'includes/header.php';

$flight_id = isset($_GET['flight_id']) ? (int)$_GET['flight_id'] : null;
$type_vol = $_GET['type_vol'] ?? 'aller_retour';
$date_depart = $_GET['date_depart'] ?? '';
$date_retour = $_GET['date_retour'] ?? '';
$adultes = isset($_GET['adultes']) ? (int)$_GET['adultes'] : 1;
$enfants = isset($_GET['enfants']) ? (int)$_GET['enfants'] : 0;

$selected_flight = null;
$all_flights = [];

try {
    if ($flight_id) {
        $stmt = $pdo->prepare("SELECT * FROM flights WHERE id = :id");
        $stmt->execute(['id' => $flight_id]);
        $selected_flight = $stmt->fetch();
    }
    
    // Si aucun vol n'est sélectionné via GET, récupérer la liste complète des vols pour un sélecteur
    if (!$selected_flight) {
        $stmt = $pdo->query("SELECT * FROM flights ORDER BY depart ASC, arrivee ASC");
        $all_flights = $stmt->fetchAll();
    }
} catch (PDOException $e) {
    // Géré par redirection dans db.php
}

$error_message = "";
$success_message = "";

// Traiter la soumission du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $passenger_name = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['Numero'] ?? '');
    $post_flight_id = isset($_POST['flight_id']) ? (int)$_POST['flight_id'] : null;
    $post_type_vol = $_POST['type_vol'] ?? 'aller_simple';
    $post_date_depart = $_POST['date_depart'] ?? '';
    $post_date_retour = $_POST['date_retour'] !== "" ? $_POST['date_retour'] : null;
    $post_adultes = isset($_POST['adultes']) ? (int)$_POST['adultes'] : 1;
    $post_enfants = isset($_POST['enfants']) ? (int)$_POST['enfants'] : 0;

    // Validation simple
    if (empty($passenger_name) || empty($email) || empty($phone) || !$post_flight_id || empty($post_date_depart)) {
        $error_message = "Veuillez remplir tous les champs obligatoires.";
    } else {
        try {
            $user_id = $_SESSION['user_id'] ?? null;
            $stmt = $pdo->prepare("INSERT INTO reservations 
                (passenger_name, email, phone, flight_id, user_id, type_vol, date_depart, date_retour, adultes, enfants, status) 
                VALUES (:name, :email, :phone, :flight_id, :user_id, :type_vol, :date_depart, :date_retour, :adultes, :enfants, 'en attente')");
            
            $stmt->execute([
                'name' => $passenger_name,
                'email' => $email,
                'phone' => $phone,
                'flight_id' => $post_flight_id,
                'user_id' => $user_id,
                'type_vol' => $post_type_vol,
                'date_depart' => $post_date_depart,
                'date_retour' => $post_date_retour,
                'adultes' => $post_adultes,
                'enfants' => $post_enfants
            ]);
            
            $reservation_id = $pdo->lastInsertId();
            $_SESSION['last_reservation_id'] = $reservation_id;
            
            // Redirection vers la page d'information/remerciement
            header("Location: information.php?id=" . $reservation_id);
            exit;
        } catch (PDOException $e) {
            $error_message = "Erreur lors de l'enregistrement de votre réservation : " . $e->getMessage();
        }
    }
}
?>

<section class="page-header">
    <div class="container">
        <h1>Finaliser votre réservation</h1>
        <p>Veuillez entrer vos coordonnées de contact pour bloquer vos places.</p>
    </div>
</section>

<div class="container">
    <?php if ($error_message): ?>
        <div style="background-color: rgba(239, 68, 68, 0.1); border: 1px solid var(--danger); color: var(--danger); padding: 15px; border-radius: 8px; margin-bottom: 25px;">
            <i class="fa-solid fa-circle-exclamation"></i> <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>

    <div class="booking-grid">
        <!-- Formulaire de réservation -->
        <div class="card-main">
            <h2 class="form-title"><i class="fa-solid fa-address-card"></i> Informations Passager</h2>
            
            <form method="POST" action="reservation.php" class="reservation-form-flow">
                <!-- Garder les informations de vol cachées si déjà sélectionnées -->
                <?php if ($selected_flight): ?>
                    <input type="hidden" name="flight_id" value="<?php echo $selected_flight['id']; ?>">
                    <input type="hidden" name="type_vol" value="<?php echo htmlspecialchars($type_vol); ?>">
                    <input type="hidden" name="date_depart" value="<?php echo htmlspecialchars($date_depart); ?>">
                    <input type="hidden" name="date_retour" value="<?php echo htmlspecialchars($date_retour); ?>">
                    <input type="hidden" name="adultes" value="<?php echo $adultes; ?>">
                    <input type="hidden" name="enfants" value="<?php echo $enfants; ?>">
                <?php else: ?>
                    <!-- Sélection dynamique si aucune sélection de vol préalable -->
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="flight_id"><i class="fa-solid fa-plane"></i> Choisir un Vol :</label>
                        <select id="flight_id" name="flight_id" required>
                            <option value="" disabled selected>-- Sélectionnez un itinéraire --</option>
                            <?php foreach ($all_flights as $f): ?>
                                <option value="<?php echo $f['id']; ?>">
                                    MG-<?php echo $f['flight_number']; ?> | <?php echo htmlspecialchars(ucfirst($f['depart'])); ?> -> <?php echo htmlspecialchars(ucfirst($f['arrivee'])); ?> (<?php echo htmlspecialchars($f['jour']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-radio-row">
                        <input type="radio" id="aller_retour" name="type_vol" value="aller_retour" checked style="display:none;">
                        <label for="aller_retour"><i class="fa-solid fa-arrows-left-right"></i> Aller-Retour</label>

                        <input type="radio" id="aller_simple" name="type_vol" value="aller_simple" style="display:none;">
                        <label for="aller_simple"><i class="fa-solid fa-arrow-right"></i> Aller simple</label>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="date_depart">Date de départ :</label>
                            <input type="date" id="date_depart" name="date_depart" required>
                        </div>
                        <div class="form-group" id="date-retour-div">
                            <label for="date_retour">Date de retour :</label>
                            <input type="date" id="date_retour" name="date_retour">
                        </div>
                    </div>

                    <div class="form-grid" style="grid-template-columns: 1fr 1fr;">
                        <div class="form-group">
                            <label for="adultes">Adultes :</label>
                            <select id="adultes" name="adultes">
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="enfants">Enfants :</label>
                            <select id="enfants" name="enfants">
                                <option value="0">0</option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                            </select>
                        </div>
                    </div>
                <?php endif; ?>

                 <div class="form-group" style="margin-bottom: 20px;">
                    <label for="nom"><i class="fa-solid fa-user"></i> Nom Complet :</label>
                    <input type="text" name="nom" id="nom" placeholder="Entrez votre nom complet" value="<?php echo htmlspecialchars($_SESSION['user_name'] ?? ''); ?>" required>
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="email"><i class="fa-solid fa-envelope"></i> Adresse Email :</label>
                    <input type="email" name="email" id="email" placeholder="entrez votre adresse email" value="<?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?>" required>
                </div>

                <div class="form-group" style="margin-bottom: 30px;">
                    <label for="Numero"><i class="fa-solid fa-phone"></i> Numéro de Téléphone :</label>
                    <input type="tel" name="Numero" id="Numero" placeholder="Ex: +243826940352" required>
                </div>

                <button type="submit" class="btn-primary">
                    <i class="fa-solid fa-circle-check"></i> Soumettre la réservation
                </button>
            </form>
        </div>

        <!-- Récapitulatif du vol sélectionné -->
        <div>
            <?php if ($selected_flight): ?>
                <div class="flight-summary-card">
                    <h3><i class="fa-solid fa-ticket"></i> Détails du Vol Sélectionné</h3>
                    
                    <div class="summary-route">
                        <div class="summary-point">
                            <div class="time"><?php echo htmlspecialchars(date('H:i', strtotime($selected_flight['heure_depart']))); ?></div>
                            <div class="city"><?php echo htmlspecialchars($selected_flight['depart']); ?></div>
                        </div>
                        <div style="color: var(--accent); font-size: 1.5em;">
                            <i class="fa-solid fa-plane"></i>
                        </div>
                        <div class="summary-point">
                            <div class="time"><?php echo htmlspecialchars(date('H:i', strtotime($selected_flight['heure_arrivee']))); ?></div>
                            <div class="city"><?php echo htmlspecialchars($selected_flight['arrivee']); ?></div>
                        </div>
                    </div>

                    <div class="summary-details">
                        <div>
                            <span>Numéro de Vol</span>
                            <strong>MG-<?php echo htmlspecialchars($selected_flight['flight_number']); ?></strong>
                        </div>
                        <div>
                            <span>Appareil</span>
                            <strong><?php echo htmlspecialchars($selected_flight['aircraft']); ?></strong>
                        </div>
                        <div>
                            <span>Type de vol</span>
                            <strong><?php echo ($type_vol === 'aller_retour') ? 'Aller-Retour' : 'Aller Simple'; ?></strong>
                        </div>
                        <div>
                            <span>Passagers</span>
                            <strong><?php echo $adultes; ?> Adulte(s) <?php echo ($enfants > 0) ? ", $enfants Enfant(s)" : ""; ?></strong>
                        </div>
                        <div style="grid-column: span 2;">
                            <span>Date de départ</span>
                            <strong><?php echo htmlspecialchars(date('d/m/Y', strtotime($date_depart))); ?> (<?php echo htmlspecialchars($selected_flight['jour']); ?>)</strong>
                        </div>
                        <?php if ($type_vol === 'aller_retour' && $date_retour): ?>
                            <div style="grid-column: span 2;">
                                <span>Date de retour</span>
                                <strong><?php echo htmlspecialchars(date('d/m/Y', strtotime($date_retour))); ?></strong>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="summary-price">
                        <span>Total estimé :</span>
                        <?php 
                            $base_price = $selected_flight['price'];
                            $passenger_multiplier = $adultes + ($enfants * 0.5); // Enfant paye demi-tarif
                            $trip_multiplier = ($type_vol === 'aller_retour') ? 2 : 1;
                            $total_price = $base_price * $passenger_multiplier * $trip_multiplier;
                        ?>
                        <strong><?php echo number_format($total_price, 2, ',', ' '); ?> $</strong>
                    </div>
                </div>
            <?php else: ?>
                <div style="background-color: #ffffff; border: 1px solid var(--border-color); border-radius: 16px; padding: 25px; text-align: center; color: var(--text-muted);">
                    <i class="fa-solid fa-receipt" style="font-size: 3em; color: var(--text-muted); margin-bottom: 15px;"></i>
                    <h4>Aucun vol présélectionné</h4>
                    <p style="font-size: 0.9em; margin-top: 10px;">
                        Choisissez un itinéraire dans la liste à gauche pour voir les détails de votre voyage et le prix estimé.
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>
