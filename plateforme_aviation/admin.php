<?php
require_once 'db.php';
session_start();

// Protection de sécurité : uniquement pour les administrateurs
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'administrateur') {
    header("Location: login.php");
    exit;
}

$active_tab = $_GET['tab'] ?? 'dashboard';
$action = $_GET['action'] ?? '';
$edit_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

$pageTitle = "Administration - Mont Gabaon";
require_once 'includes/header.php';

// Messages flash en session
$admin_message = $_SESSION['admin_message'] ?? '';
$admin_msg_type = $_SESSION['admin_msg_type'] ?? '';
unset($_SESSION['admin_message'], $_SESSION['admin_msg_type']);

// Données d'édition si besoin
$edit_data = null;
if ($edit_id && $action === 'edit') {
    try {
        if ($active_tab === 'reservations') {
            $stmt = $pdo->prepare("SELECT * FROM reservations WHERE id = ?");
            $stmt->execute([$edit_id]);
            $edit_data = $stmt->fetch();
        } elseif ($active_tab === 'vols') {
            $stmt = $pdo->prepare("SELECT * FROM flights WHERE id = ?");
            $stmt->execute([$edit_id]);
            $edit_data = $stmt->fetch();
        } elseif ($active_tab === 'destinations') {
            $stmt = $pdo->prepare("SELECT * FROM destinations WHERE id = ?");
            $stmt->execute([$edit_id]);
            $edit_data = $stmt->fetch();
        } elseif ($active_tab === 'utilisateurs') {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$edit_id]);
            $edit_data = $stmt->fetch();
        }
    } catch (PDOException $e) {
        $admin_message = "Erreur lors du chargement des données : " . $e->getMessage();
        $admin_msg_type = "danger";
    }
}
?>

<section class="page-header">
    <div class="container">
        <h1>Console d'Administration</h1>
        <p>Pilotez l'ensemble de la plateforme d'aviation Mont Gabaon.</p>
    </div>
</section>

<div class="container">
    <?php if ($admin_message): ?>
        <div style="background-color: <?php echo ($admin_msg_type === 'success' || $admin_msg_type === '') ? 'rgba(16, 185, 129, 0.1)' : 'rgba(239, 68, 68, 0.1)'; ?>; border: 1px solid <?php echo ($admin_msg_type === 'success' || $admin_msg_type === '') ? 'var(--success)' : 'var(--danger)'; ?>; color: <?php echo ($admin_msg_type === 'success' || $admin_msg_type === '') ? 'var(--success-hover)' : 'var(--danger)'; ?>; padding: 15px; border-radius: 8px; margin-bottom: 25px;">
            <i class="fa-solid <?php echo ($admin_msg_type === 'success' || $admin_msg_type === '') ? 'fa-circle-check' : 'fa-circle-exclamation'; ?>"></i> <?php echo htmlspecialchars($admin_message); ?>
        </div>
    <?php endif; ?>

    <!-- Navigation par Onglets -->
    <div class="admin-tabs-nav">
        <a href="admin.php?tab=dashboard" class="btn-tab <?php echo ($active_tab === 'dashboard') ? 'active' : ''; ?>"><i class="fa-solid fa-chart-pie"></i> Dashboard</a>
        <a href="admin.php?tab=reservations" class="btn-tab <?php echo ($active_tab === 'reservations') ? 'active' : ''; ?>"><i class="fa-solid fa-receipt"></i> Réservations</a>
        <a href="admin.php?tab=vols" class="btn-tab <?php echo ($active_tab === 'vols') ? 'active' : ''; ?>"><i class="fa-solid fa-plane"></i> Vols</a>
        <a href="admin.php?tab=destinations" class="btn-tab <?php echo ($active_tab === 'destinations') ? 'active' : ''; ?>"><i class="fa-solid fa-map-location-dot"></i> Destinations</a>
        <a href="admin.php?tab=utilisateurs" class="btn-tab <?php echo ($active_tab === 'utilisateurs') ? 'active' : ''; ?>"><i class="fa-solid fa-users-gear"></i> Rôles & Users</a>
    </div>

    <!-- ========================================== -->
    <!-- ONGLET 1 : DASHBOARD (STATISTIQUES & DERNIERS VOLS) -->
    <!-- ========================================== -->
    <?php if ($active_tab === 'dashboard'): ?>
        <?php
        // Récupérer les stats
        $stats = ['total' => 0, 'pending' => 0, 'confirmed' => 0, 'flights' => 0, 'users' => 0];
        $recent_reservations = [];
        try {
            $stats['total'] = $pdo->query("SELECT COUNT(*) FROM reservations")->fetchColumn();
            $stats['pending'] = $pdo->query("SELECT COUNT(*) FROM reservations WHERE status = 'en attente'")->fetchColumn();
            $stats['confirmed'] = $pdo->query("SELECT COUNT(*) FROM reservations WHERE status = 'confirmé'")->fetchColumn();
            $stats['flights'] = $pdo->query("SELECT COUNT(*) FROM flights")->fetchColumn();
            $stats['users'] = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
            
            $stmt = $pdo->query("SELECT r.*, f.flight_number, f.depart, f.arrivee, f.price 
                                 FROM reservations r 
                                 JOIN flights f ON r.flight_id = f.id 
                                 ORDER BY r.created_at DESC LIMIT 5");
            $recent_reservations = $stmt->fetchAll();
        } catch (PDOException $e) {}
        ?>
        <div class="admin-summary">
            <div class="summary-box">
                <div class="summary-box-info">
                    <h4>Réservations</h4>
                    <div class="value"><?php echo $stats['total']; ?></div>
                </div>
                <div class="summary-box-icon"><i class="fa-solid fa-receipt"></i></div>
            </div>
            <div class="summary-box warning-box">
                <div class="summary-box-info">
                    <h4>En Attente</h4>
                    <div class="value"><?php echo $stats['pending']; ?></div>
                </div>
                <div class="summary-box-icon"><i class="fa-solid fa-hourglass-half"></i></div>
            </div>
            <div class="summary-box success-box">
                <div class="summary-box-info">
                    <h4>Confirmées</h4>
                    <div class="value"><?php echo $stats['confirmed']; ?></div>
                </div>
                <div class="summary-box-icon"><i class="fa-solid fa-circle-check"></i></div>
            </div>
            <div class="summary-box">
                <div class="summary-box-info">
                    <h4>Utilisateurs</h4>
                    <div class="value"><?php echo $stats['users']; ?></div>
                </div>
                <div class="summary-box-icon"><i class="fa-solid fa-users"></i></div>
            </div>
        </div>

        <div class="card-main" style="padding: 25px; margin-top: 30px;">
            <h3 style="color: var(--primary); margin-bottom: 20px; font-weight: 700;">
                <i class="fa-solid fa-clock-rotate-left"></i> Réservations Récentes
            </h3>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Réf</th>
                            <th>Passager</th>
                            <th>Itinéraire</th>
                            <th>Date</th>
                            <th>Total</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_reservations as $res): ?>
                            <tr>
                                <td><strong>#MG-<?php echo str_pad($res['id'], 5, '0', STR_PAD_LEFT); ?></strong></td>
                                <td><?php echo htmlspecialchars($res['passenger_name']); ?></td>
                                <td>MG-<?php echo htmlspecialchars($res['flight_number']); ?> (<?php echo htmlspecialchars($res['depart']); ?> &rarr; <?php echo htmlspecialchars($res['arrivee']); ?>)</td>
                                <td><?php echo date('d/m/Y', strtotime($res['date_depart'])); ?></td>
                                <td>
                                    <?php 
                                        $passenger_multiplier = $res['adultes'] + ($res['enfants'] * 0.5);
                                        $trip_multiplier = ($res['type_vol'] === 'aller_retour') ? 2 : 1;
                                        echo number_format($res['price'] * $passenger_multiplier * $trip_multiplier, 2, ',', ' ') . ' $';
                                    ?>
                                </td>
                                <td>
                                    <span class="badge <?php 
                                        echo ($res['status'] === 'confirmé') ? 'badge-confirmed' : (($res['status'] === 'annulé') ? 'badge-cancelled' : 'badge-pending'); 
                                    ?>"><?php echo htmlspecialchars($res['status']); ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <a href="admin.php?tab=reservations" class="btn-select" style="margin-top: 20px; display: inline-block; width: auto;">Voir toutes les réservations <i class="fa-solid fa-circle-arrow-right"></i></a>
        </div>
    <?php endif; ?>


    <!-- ========================================== -->
    <!-- ONGLET 2 : CRUD RÉSERVATIONS -->
    <!-- ========================================== -->
    <?php if ($active_tab === 'reservations'): ?>
        <?php if ($action === 'edit' && $edit_data): ?>
            <!-- Formulaire d'édition de réservation -->
            <div class="card-main" style="max-width: 600px; margin: 0 auto 30px auto;">
                <h3 class="form-title"><i class="fa-solid fa-pen-to-square"></i> Modifier la Réservation #<?php echo $edit_data['id']; ?></h3>
                
                <form method="POST" action="admin_actions.php">
                    <input type="hidden" name="action" value="edit_reservation">
                    <input type="hidden" name="tab" value="reservations">
                    <input type="hidden" name="id" value="<?php echo $edit_data['id']; ?>">
                    
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label for="passenger_name">Nom du passager :</label>
                        <input type="text" id="passenger_name" name="passenger_name" value="<?php echo htmlspecialchars($edit_data['passenger_name']); ?>" required>
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="email">Email :</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($edit_data['email']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="phone">Téléphone :</label>
                            <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($edit_data['phone']); ?>" required>
                        </div>
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="type_vol">Type de vol :</label>
                            <select id="type_vol" name="type_vol">
                                <option value="aller_simple" <?php echo ($edit_data['type_vol'] === 'aller_simple') ? 'selected' : ''; ?>>Aller simple</option>
                                <option value="aller_retour" <?php echo ($edit_data['type_vol'] === 'aller_retour') ? 'selected' : ''; ?>>Aller-Retour</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="status">Statut :</label>
                            <select id="status" name="status">
                                <option value="en attente" <?php echo ($edit_data['status'] === 'en attente') ? 'selected' : ''; ?>>En attente</option>
                                <option value="confirmé" <?php echo ($edit_data['status'] === 'confirmé') ? 'selected' : ''; ?>>Confirmé</option>
                                <option value="annulé" <?php echo ($edit_data['status'] === 'annulé') ? 'selected' : ''; ?>>Annulé</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="date_depart">Date départ :</label>
                            <input type="date" id="date_depart" name="date_depart" value="<?php echo $edit_data['date_depart']; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="date_retour">Date retour :</label>
                            <input type="date" id="date_retour" name="date_retour" value="<?php echo $edit_data['date_retour']; ?>">
                        </div>
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="adultes">Adultes :</label>
                            <input type="number" id="adultes" name="adultes" value="<?php echo $edit_data['adultes']; ?>" min="1" required>
                        </div>
                        <div class="form-group">
                            <label for="enfants">Enfants :</label>
                            <input type="number" id="enfants" name="enfants" value="<?php echo $edit_data['enfants']; ?>" min="0" required>
                        </div>
                    </div>
                    
                    <div style="display: flex; gap: 10px; margin-top: 20px;">
                        <button type="submit" class="btn-primary" style="flex:1;"><i class="fa-solid fa-save"></i> Enregistrer</button>
                        <a href="admin.php?tab=reservations" class="btn-select" style="flex:1; background-color: var(--bg-body); color: var(--text-main); border: 1px solid var(--border-color); text-align: center; line-height: 24px; padding: 14px 0;">Annuler</a>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <!-- Liste complète des réservations -->
            <?php
            $reservations = [];
            try {
                $stmt = $pdo->query("SELECT r.*, f.flight_number, f.depart, f.arrivee, f.price 
                                     FROM reservations r 
                                     JOIN flights f ON r.flight_id = f.id 
                                     ORDER BY r.created_at DESC");
                $reservations = $stmt->fetchAll();
            } catch (PDOException $e) {}
            ?>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Réf</th>
                            <th>Passager</th>
                            <th>Contact</th>
                            <th>Vol</th>
                            <th>Départ</th>
                            <th>Pax</th>
                            <th>Total</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reservations as $res): ?>
                            <tr>
                                <td><strong>#MG-<?php echo str_pad($res['id'], 5, '0', STR_PAD_LEFT); ?></strong></td>
                                <td><?php echo htmlspecialchars($res['passenger_name']); ?></td>
                                <td>
                                    <div style="font-size: 0.9em;"><?php echo htmlspecialchars($res['phone']); ?></div>
                                    <div style="font-size: 0.8em; color: var(--text-muted);"><?php echo htmlspecialchars($res['email']); ?></div>
                                </td>
                                <td>MG-<?php echo htmlspecialchars($res['flight_number']); ?></td>
                                <td>
                                    <div><?php echo date('d/m/Y', strtotime($res['date_depart'])); ?></div>
                                    <div style="font-size: 0.85em; color: var(--text-muted); text-transform: capitalize;"><?php echo htmlspecialchars($res['depart']); ?> &rarr; <?php echo htmlspecialchars($res['arrivee']); ?></div>
                                </td>
                                <td><?php echo $res['adultes']; ?>A <?php echo ($res['enfants'] > 0) ? "/ " . $res['enfants'] . "E" : ""; ?></td>
                                <td>
                                    <?php 
                                        $passenger_multiplier = $res['adultes'] + ($res['enfants'] * 0.5);
                                        $trip_multiplier = ($res['type_vol'] === 'aller_retour') ? 2 : 1;
                                        echo number_format($res['price'] * $passenger_multiplier * $trip_multiplier, 2, ',', ' ') . ' $';
                                    ?>
                                </td>
                                <td>
                                    <span class="badge <?php 
                                        echo ($res['status'] === 'confirmé') ? 'badge-confirmed' : (($res['status'] === 'annulé') ? 'badge-cancelled' : 'badge-pending'); 
                                    ?>"><?php echo htmlspecialchars($res['status']); ?></span>
                                </td>
                                <td>
                                    <div class="actions-cell">
                                        <a href="admin.php?tab=reservations&action=edit&id=<?php echo $res['id']; ?>" class="btn-sm btn-confirm" style="background-color: var(--primary-light);" title="Modifier"><i class="fa-solid fa-pen"></i></a>
                                        <form method="POST" action="admin_actions.php" onsubmit="return confirm('Supprimer définitivement cette réservation ?');" style="margin:0; padding:0; box-shadow:none; background:none;">
                                            <input type="hidden" name="action" value="delete_reservation">
                                            <input type="hidden" name="tab" value="reservations">
                                            <input type="hidden" name="id" value="<?php echo $res['id']; ?>">
                                            <button type="submit" class="btn-sm btn-cancel" title="Supprimer"><i class="fa-solid fa-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    <?php endif; ?>


    <!-- ========================================== -->
    <!-- ONGLET 3 : CRUD VOLS (FLIGHTS) -->
    <!-- ========================================== -->
    <?php if ($active_tab === 'vols'): ?>
        <?php if (($action === 'add') || ($action === 'edit' && $edit_data)): ?>
            <!-- Formulaire d'ajout / édition de vol -->
            <div class="card-main" style="max-width: 600px; margin: 0 auto 30px auto;">
                <h3 class="form-title">
                    <i class="fa-solid fa-plane"></i> 
                    <?php echo ($action === 'add') ? "Ajouter un Vol" : "Modifier le Vol MG-" . $edit_data['flight_number']; ?>
                </h3>
                
                <form method="POST" action="admin_actions.php">
                    <input type="hidden" name="action" value="<?php echo ($action === 'add') ? 'add_flight' : 'edit_flight'; ?>">
                    <input type="hidden" name="tab" value="vols">
                    <?php if ($action === 'edit'): ?>
                        <input type="hidden" name="id" value="<?php echo $edit_data['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="flight_number">Numéro de vol :</label>
                            <input type="text" id="flight_number" name="flight_number" placeholder="Ex: 1612" value="<?php echo $edit_data ? htmlspecialchars($edit_data['flight_number']) : ''; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="aircraft">Appareil (Aircraft) :</label>
                            <input type="text" id="aircraft" name="aircraft" placeholder="Ex: A330-2" value="<?php echo $edit_data ? htmlspecialchars($edit_data['aircraft']) : ''; ?>" required>
                        </div>
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="depart">Départ (Ville) :</label>
                            <input type="text" id="depart" name="depart" placeholder="Ex: Kinshasa" value="<?php echo $edit_data ? htmlspecialchars($edit_data['depart']) : ''; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="arrivee">Arrivée (Ville) :</label>
                            <input type="text" id="arrivee" name="arrivee" placeholder="Ex: Lubumbashi" value="<?php echo $edit_data ? htmlspecialchars($edit_data['arrivee']) : ''; ?>" required>
                        </div>
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="jour">Jour d'opération :</label>
                            <select id="jour" name="jour" required>
                                <option value="LUNDI" <?php echo ($edit_data && $edit_data['jour'] === 'LUNDI') ? 'selected' : ''; ?>>Lundi</option>
                                <option value="MARDI" <?php echo ($edit_data && $edit_data['jour'] === 'MARDI') ? 'selected' : ''; ?>>Mardi</option>
                                <option value="MERCREDI" <?php echo ($edit_data && $edit_data['jour'] === 'MERCREDI') ? 'selected' : ''; ?>>Mercredi</option>
                                <option value="JEUDI" <?php echo ($edit_data && $edit_data['jour'] === 'JEUDI') ? 'selected' : ''; ?>>Jeudi</option>
                                <option value="VENDREDI" <?php echo ($edit_data && $edit_data['jour'] === 'VENDREDI') ? 'selected' : ''; ?>>Vendredi</option>
                                <option value="SAMEDI" <?php echo ($edit_data && $edit_data['jour'] === 'SAMEDI') ? 'selected' : ''; ?>>Samedi</option>
                                <option value="DIMANCHE" <?php echo ($edit_data && $edit_data['jour'] === 'DIMANCHE') ? 'selected' : ''; ?>>Dimanche</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="block_time">Durée de vol (ex: 02:30) :</label>
                            <input type="text" id="block_time" name="block_time" placeholder="02:30" value="<?php echo $edit_data ? htmlspecialchars($edit_data['block_time']) : ''; ?>" required>
                        </div>
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="heure_depart">Heure départ :</label>
                            <input type="time" id="heure_depart" name="heure_depart" value="<?php echo $edit_data ? $edit_data['heure_depart'] : '08:00'; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="heure_arrivee">Heure arrivée :</label>
                            <input type="time" id="heure_arrivee" name="heure_arrivee" value="<?php echo $edit_data ? $edit_data['heure_arrivee'] : '10:30'; ?>" required>
                        </div>
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="price">Prix unitaire (USD) :</label>
                            <input type="number" id="price" name="price" step="0.01" min="0" placeholder="Ex: 280.00" value="<?php echo $edit_data ? $edit_data['price'] : ''; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="remarks">Remarques (Optionnel) :</label>
                            <input type="text" id="remarks" name="remarks" placeholder="Ex: Escale" value="<?php echo $edit_data ? htmlspecialchars($edit_data['remarks']) : ''; ?>">
                        </div>
                    </div>
                    
                    <div style="display: flex; gap: 10px; margin-top: 20px;">
                        <button type="submit" class="btn-primary" style="flex:1;"><i class="fa-solid fa-save"></i> Sauvegarder</button>
                        <a href="admin.php?tab=vols" class="btn-select" style="flex:1; background-color: var(--bg-body); color: var(--text-main); border: 1px solid var(--border-color); text-align: center; line-height: 24px; padding: 14px 0;">Annuler</a>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <!-- Liste complète des vols -->
            <div style="margin-bottom: 20px; text-align: right;">
                <a href="admin.php?tab=vols&action=add" class="btn-select" style="background-color: var(--success); color: white; display: inline-flex; align-items: center; gap: 8px; width: auto;">
                    <i class="fa-solid fa-plus"></i> Ajouter un Vol
                </a>
            </div>
            
            <?php
            $vols = [];
            try {
                $stmt = $pdo->query("SELECT * FROM flights ORDER BY FIELD(jour, 'LUNDI', 'MARDI', 'MERCREDI', 'JEUDI', 'VENDREDI', 'SAMEDI', 'DIMANCHE'), heure_depart ASC");
                $vols = $stmt->fetchAll();
            } catch (PDOException $e) {}
            ?>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>N° Vol</th>
                            <th>Appareil</th>
                            <th>Itinéraire</th>
                            <th>Jour</th>
                            <th>Départ / Arrivée</th>
                            <th>Prix</th>
                            <th>Remarques</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($vols as $v): ?>
                            <tr>
                                <td><strong>MG-<?php echo htmlspecialchars($v['flight_number']); ?></strong></td>
                                <td><?php echo htmlspecialchars($v['aircraft']); ?></td>
                                <td style="text-transform: capitalize;"><?php echo htmlspecialchars($v['depart']); ?> &rarr; <?php echo htmlspecialchars($v['arrivee']); ?></td>
                                <td><?php echo htmlspecialchars($v['jour']); ?></td>
                                <td><?php echo date('H:i', strtotime($v['heure_depart'])); ?> &rarr; <?php echo date('H:i', strtotime($v['heure_arrivee'])); ?> (<?php echo htmlspecialchars($v['block_time']); ?>h)</td>
                                <td><strong><?php echo number_format($v['price'], 2, ',', ' '); ?> $</strong></td>
                                <td><span style="color: var(--accent-hover); font-size: 0.9em;"><?php echo htmlspecialchars($v['remarks']); ?></span></td>
                                <td>
                                    <div class="actions-cell">
                                        <a href="admin.php?tab=vols&action=edit&id=<?php echo $v['id']; ?>" class="btn-sm btn-confirm" style="background-color: var(--primary-light);" title="Modifier"><i class="fa-solid fa-pen"></i></a>
                                        <form method="POST" action="admin_actions.php" onsubmit="return confirm('Voulez-vous vraiment supprimer ce vol ? Toutes les réservations associées seront supprimées.');" style="margin:0; padding:0; box-shadow:none; background:none;">
                                            <input type="hidden" name="action" value="delete_flight">
                                            <input type="hidden" name="tab" value="vols">
                                            <input type="hidden" name="id" value="<?php echo $v['id']; ?>">
                                            <button type="submit" class="btn-sm btn-cancel" title="Supprimer"><i class="fa-solid fa-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    <?php endif; ?>


    <!-- ========================================== -->
    <!-- ONGLET 4 : CRUD DESTINATIONS -->
    <!-- ========================================== -->
    <?php if ($active_tab === 'destinations'): ?>
        <?php if (($action === 'add') || ($action === 'edit' && $edit_data)): ?>
            <!-- Formulaire d'ajout / édition de destination -->
            <div class="card-main" style="max-width: 600px; margin: 0 auto 30px auto;">
                <h3 class="form-title">
                    <i class="fa-solid fa-map-location-dot"></i> 
                    <?php echo ($action === 'add') ? "Ajouter une Destination" : "Modifier la Destination " . $edit_data['name']; ?>
                </h3>
                
                <form method="POST" action="admin_actions.php">
                    <input type="hidden" name="action" value="<?php echo ($action === 'add') ? 'add_destination' : 'edit_destination'; ?>">
                    <input type="hidden" name="tab" value="destinations">
                    <?php if ($action === 'edit'): ?>
                        <input type="hidden" name="id" value="<?php echo $edit_data['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label for="name">Nom de la ville :</label>
                        <input type="text" id="name" name="name" placeholder="Ex: Goma" value="<?php echo $edit_data ? htmlspecialchars($edit_data['name']) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label for="description">Description touristique/économique :</label>
                        <textarea id="description" name="description" rows="4" style="width:100%; border: 2px solid var(--border-color); border-radius: 8px; padding:12px; font-family:var(--font-family);" required><?php echo $edit_data ? htmlspecialchars($edit_data['description']) : ''; ?></textarea>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="image_path">Chemin image locale (ou URL) :</label>
                            <input type="text" id="image_path" name="image_path" placeholder="Ex: images/goma.jpeg" value="<?php echo $edit_data ? htmlspecialchars($edit_data['image_path']) : 'images/background.jpeg'; ?>">
                        </div>
                        <div class="form-group">
                            <label for="price">Prix d'appel (USD) :</label>
                            <input type="number" id="price" name="price" step="0.01" min="0" placeholder="Ex: 250.00" value="<?php echo $edit_data ? $edit_data['price'] : ''; ?>" required>
                        </div>
                    </div>
                    
                    <div style="display: flex; gap: 10px; margin-top: 20px;">
                        <button type="submit" class="btn-primary" style="flex:1;"><i class="fa-solid fa-save"></i> Sauvegarder</button>
                        <a href="admin.php?tab=destinations" class="btn-select" style="flex:1; background-color: var(--bg-body); color: var(--text-main); border: 1px solid var(--border-color); text-align: center; line-height: 24px; padding: 14px 0;">Annuler</a>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <!-- Liste complète des destinations -->
            <div style="margin-bottom: 20px; text-align: right;">
                <a href="admin.php?tab=destinations&action=add" class="btn-select" style="background-color: var(--success); color: white; display: inline-flex; align-items: center; gap: 8px; width: auto;">
                    <i class="fa-solid fa-plus"></i> Ajouter une Destination
                </a>
            </div>
            
            <?php
            $destinations = [];
            try {
                $stmt = $pdo->query("SELECT * FROM destinations ORDER BY name ASC");
                $destinations = $stmt->fetchAll();
            } catch (PDOException $e) {}
            ?>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Aperçu</th>
                            <th>Ville</th>
                            <th>Description</th>
                            <th>Prix d'appel</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($destinations as $d): ?>
                            <tr>
                                <td>
                                    <img src="<?php echo htmlspecialchars($d['image_path']); ?>" alt="img" style="height: 50px; width: 80px; object-fit: cover; border-radius: 4px; border: 1px solid var(--border-color);">
                                </td>
                                <td><strong><?php echo htmlspecialchars($d['name']); ?></strong></td>
                                <td style="max-width: 400px; font-size: 0.9em; color: var(--text-muted);"><?php echo htmlspecialchars($d['description']); ?></td>
                                <td><strong><?php echo number_format($d['price'], 2, ',', ' '); ?> $</strong></td>
                                <td>
                                    <div class="actions-cell">
                                        <a href="admin.php?tab=destinations&action=edit&id=<?php echo $d['id']; ?>" class="btn-sm btn-confirm" style="background-color: var(--primary-light);" title="Modifier"><i class="fa-solid fa-pen"></i></a>
                                        <form method="POST" action="admin_actions.php" onsubmit="return confirm('Voulez-vous vraiment supprimer cette destination ?');" style="margin:0; padding:0; box-shadow:none; background:none;">
                                            <input type="hidden" name="action" value="delete_destination">
                                            <input type="hidden" name="tab" value="destinations">
                                            <input type="hidden" name="id" value="<?php echo $d['id']; ?>">
                                            <button type="submit" class="btn-sm btn-cancel" title="Supprimer"><i class="fa-solid fa-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    <?php endif; ?>


    <!-- ========================================== -->
    <!-- ONGLET 5 : GESTION DES UTILISATEURS / ROLES -->
    <!-- ========================================== -->
    <?php if ($active_tab === 'utilisateurs'): ?>
        <?php if ($action === 'edit' && $edit_data): ?>
            <!-- Modification du rôle d'un utilisateur -->
            <div class="card-main" style="max-width: 500px; margin: 0 auto 30px auto;">
                <h3 class="form-title"><i class="fa-solid fa-user-shield"></i> Modifier le Rôle</h3>
                <p style="margin-bottom: 20px; font-size:0.95em;">Modifier le rôle de l'utilisateur : <strong><?php echo htmlspecialchars($edit_data['name']); ?></strong> (<?php echo htmlspecialchars($edit_data['email']); ?>).</p>
                
                <form method="POST" action="admin_actions.php">
                    <input type="hidden" name="action" value="edit_user_role">
                    <input type="hidden" name="tab" value="utilisateurs">
                    <input type="hidden" name="id" value="<?php echo $edit_data['id']; ?>">
                    
                    <div class="form-group" style="margin-bottom: 25px;">
                        <label for="role">Attribuer le rôle :</label>
                        <select id="role" name="role" required>
                            <option value="utilisateur" <?php echo ($edit_data['role'] === 'utilisateur') ? 'selected' : ''; ?>>Utilisateur standard</option>
                            <option value="administrateur" <?php echo ($edit_data['role'] === 'administrateur') ? 'selected' : ''; ?>>Administrateur</option>
                        </select>
                    </div>
                    
                    <div style="display: flex; gap: 10px;">
                        <button type="submit" class="btn-primary" style="flex:1;"><i class="fa-solid fa-save"></i> Enregistrer</button>
                        <a href="admin.php?tab=utilisateurs" class="btn-select" style="flex:1; background-color: var(--bg-body); color: var(--text-main); border: 1px solid var(--border-color); text-align: center; line-height: 24px; padding: 14px 0;">Annuler</a>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <!-- Liste des utilisateurs -->
            <?php
            $users = [];
            try {
                $stmt = $pdo->query("SELECT * FROM users ORDER BY name ASC");
                $users = $stmt->fetchAll();
            } catch (PDOException $e) {}
            ?>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Adresse Email</th>
                            <th>Créé le</th>
                            <th>Rôle</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($u['name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($u['email']); ?></td>
                                <td><?php echo date('d/m/Y à H:i', strtotime($u['created_at'])); ?></td>
                                <td>
                                    <span class="badge <?php echo ($u['role'] === 'administrateur') ? 'badge-confirmed' : 'badge-pending'; ?>">
                                        <i class="fa-solid <?php echo ($u['role'] === 'administrateur') ? 'fa-user-shield' : 'fa-user'; ?>"></i> 
                                        <?php echo htmlspecialchars($u['role']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="actions-cell">
                                        <!-- Modifier le rôle -->
                                        <a href="admin.php?tab=utilisateurs&action=edit&id=<?php echo $u['id']; ?>" class="btn-sm btn-confirm" style="background-color: var(--primary-light);" title="Modifier rôle"><i class="fa-solid fa-user-gear"></i></a>
                                        
                                        <!-- Supprimer l'utilisateur -->
                                        <?php if ($u['id'] !== $_SESSION['user_id']): ?>
                                            <form method="POST" action="admin_actions.php" onsubmit="return confirm('Voulez-vous supprimer définitivement cet utilisateur ? Toutes ses réservations resteront en base mais ne seront plus rattachées.');" style="margin:0; padding:0; box-shadow:none; background:none;">
                                                <input type="hidden" name="action" value="delete_user">
                                                <input type="hidden" name="tab" value="utilisateurs">
                                                <input type="hidden" name="id" value="<?php echo $u['id']; ?>">
                                                <button type="submit" class="btn-sm btn-cancel" title="Supprimer"><i class="fa-solid fa-trash"></i></button>
                                            </form>
                                        <?php else: ?>
                                            <span style="font-size:0.85em; color:var(--text-muted); font-style:italic;">Votre compte</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php
require_once 'includes/footer.php';
?>
