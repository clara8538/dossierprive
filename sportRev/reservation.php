<?php
require_once 'config/connexion.php';
require_once 'includes/functions.php';

if (empty($_SESSION['user_id'])) {
    add_flash_message('error', 'Vous devez être connecté pour réserver un terrain.');
    header('Location: connexion.php');
    exit;
}

$terrainId = filter_input(INPUT_GET, 'terrain', FILTER_VALIDATE_INT);

$query = 'SELECT id, nom, sport, adresse, prix_min, prix_max, horaire_ouverture, horaire_fermeture FROM terrains WHERE statut = :statut ORDER BY nom';
$stmt = $pdo->prepare($query);
$stmt->execute(['statut' => 'actif']);
$terrains = $stmt->fetchAll();

if ($terrainId) {
    $selectedTerrain = null;
    foreach ($terrains as $terrain) {
        if ($terrain['id'] === $terrainId) {
            $selectedTerrain = $terrain;
            break;
        }
    }
}

$selectedTerrainId = $selectedTerrain['id'] ?? '';

require_once 'includes/header.php';
?>

<section class="section-overview container">
    <div class="section-heading">
        <h2>Formulaire de réservation</h2>
        <p>Choisissez votre terrain, une date et un créneau horaire sécurisé.</p>
    </div>

    <div class="form-card">
        <div class="form-card-body">
            <form class="auth-form" action="backend/traitement_reservation.php" method="POST">
                <?= csrf_input() ?>

                <div class="form-field">
                    <label for="terrain">Terrain</label>
                    <select id="terrain" name="terrain_id" required>
                        <option value="">-- Sélectionner un terrain --</option>
                        <?php foreach ($terrains as $terrain): ?>
                            <option value="<?= h($terrain['id']) ?>" <?= $terrain['id'] == $selectedTerrainId ? 'selected' : '' ?>>
                                <?= h($terrain['nom']) ?> — <?= h($terrain['sport']) ?> (<?= h($terrain['ville']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-field">
                    <label for="date_reservation">Date de réservation</label>
                    <input id="date_reservation" type="date" name="date_reservation" required>
                </div>

                <div class="form-field">
                    <label for="heure_debut">Heure de début</label>
                    <input id="heure_debut" type="time" name="heure_debut" required>
                </div>

                <div class="form-field">
                    <label for="heure_fin">Heure de fin</label>
                    <input id="heure_fin" type="time" name="heure_fin" required>
                </div>

                <div class="form-footer">
                    <button class="btn btn-primary" type="submit">Confirmer la réservation</button>
                </div>
            </form>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
