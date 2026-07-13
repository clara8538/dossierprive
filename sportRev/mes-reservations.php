<?php
require_once 'config/connexion.php';
require_once 'includes/functions.php';

if (empty($_SESSION['user_id'])) {
    add_flash_message('error', 'Vous devez être connecté pour consulter vos réservations.');
    header('Location: connexion.php');
    exit;
}

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['cancel_id'])) {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        add_flash_message('error', 'Jeton CSRF invalide.');
        header('Location: mes-reservations.php');
        exit;
    }

    $cancelId = filter_input(INPUT_POST, 'cancel_id', FILTER_VALIDATE_INT);
    if ($cancelId) {
        $stmt = $pdo->prepare('UPDATE reservations SET statut = :statut WHERE id = :id AND id_utilisateur = :userId');
        $stmt->execute([
            'statut' => 'annulé',
            'id' => $cancelId,
            'userId' => $userId,
        ]);
        add_flash_message('success', 'Votre réservation a bien été annulée.');
    }

    header('Location: mes-reservations.php');
    exit;
}

$stmt = $pdo->prepare('SELECT r.*, t.nom AS terrain_nom, t.sport, t.adresse FROM reservations r JOIN terrains t ON r.id_terrain = t.id WHERE r.id_utilisateur = :userId ORDER BY r.date_reservation DESC, r.heure_debut ASC');
$stmt->execute(['userId' => $userId]);
$reservations = $stmt->fetchAll();

require_once 'includes/header.php';
?>

<section class="section-overview container">
    <div class="section-heading">
        <h2>Mes réservations</h2>
        <p>Retrouvez toutes vos réservations et annulez un créneau si nécessaire.</p>
    </div>

    <?php if (empty($reservations)): ?>
        <div class="message-card">
            <div class="message-card-body">
                <h3>Aucune réservation trouvée</h3>
                <p>Vous n'avez pas encore réservé de terrain. Commencez par visiter la page <a href="terrains.php">Terrains</a>.</p>
            </div>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table-dashboard">
                <thead>
                    <tr>
                        <th>Terrain</th>
                        <th>Date</th>
                        <th>Créneau</th>
                        <th>Sport</th>
                        <th>Statut</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reservations as $reservation): ?>
                        <tr>
                            <td><?= h($reservation['terrain_nom']) ?><br><small><?= h($reservation['adresse']) ?></small></td>
                            <td><?= h($reservation['date_reservation']) ?></td>
                            <td><?= h(substr($reservation['heure_debut'], 0, 5)) ?> - <?= h(substr($reservation['heure_fin'], 0, 5)) ?></td>
                            <td><?= h($reservation['sport']) ?></td>
                            <td><?= h($reservation['statut']) ?></td>
                            <td>
                                <?php if ($reservation['statut'] !== 'annulé'): ?>
                                    <form method="POST" style="margin:0; display:inline-block;">
                                        <?= csrf_input() ?>
                                        <input type="hidden" name="cancel_id" value="<?= h($reservation['id']) ?>">
                                        <button class="btn btn-outline" type="submit">Annuler</button>
                                    </form>
                                <?php else: ?>
                                    <span style="color: var(--muted);">Aucune action</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<?php require_once 'includes/footer.php'; ?>
