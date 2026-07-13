<?php
require_once 'config/connexion.php';
require_once 'includes/header.php';

$sport = filter_input(INPUT_GET, 'sport', FILTER_SANITIZE_STRING);

if ($sport) {
    $stmt = $pdo->prepare('SELECT * FROM terrains WHERE statut = :statut AND sport = :sport ORDER BY nom');
    $stmt->execute(['statut' => 'actif', 'sport' => $sport]);
} else {
    $stmt = $pdo->query('SELECT * FROM terrains WHERE statut = "actif" ORDER BY nom');
}
$terrains = $stmt->fetchAll();
?>

<section class="hero container">
    <div class="hero-inner">
        <span class="eyebrow">Terrains</span>
        <h1>Choisissez votre terrain et réservez rapidement</h1>
        <p>Découvrez tous les espaces sportifs disponibles à Kinshasa avec les meilleurs horaires et tarifs.</p>
    </div>
</section>

<section class="section-overview container">
    <div class="section-heading">
        <h2>Filtrer par sport</h2>
        <div>
            <a class="btn btn-outline" href="terrains.php">Tous</a>
            <a class="btn btn-outline" href="terrains.php?sport=Football">Football</a>
            <a class="btn btn-outline" href="terrains.php?sport=Basketball">Basketball</a>
            <a class="btn btn-outline" href="terrains.php?sport=Tennis">Tennis</a>
            <a class="btn btn-outline" href="terrains.php?sport=Volley-ball">Volley-ball</a>
            <a class="btn btn-outline" href="terrains.php?sport=Golf">Golf</a>
        </div>
    </div>

    <?php if (empty($terrains)): ?>
        <p>Aucun terrain trouvé pour ce filtre.</p>
    <?php else: ?>
        <div class="card-grid">
            <?php foreach ($terrains as $terrain): ?>
                <article class="terrain-card">
                    <img src="<?= h($terrain['image'] ?: 'assets/images/B.jpeg') ?>" alt="<?= h($terrain['nom']) ?>">
                    <div class="terrain-card-body">
                        <h3><?= h($terrain['nom']) ?></h3>
                        <div class="terrain-meta">
                            <span><?= h($terrain['sport']) ?></span>
                            <span><?= h($terrain['ville']) ?></span>
                            <span><?= date('H:i', strtotime($terrain['horaire_ouverture'])) ?> - <?= date('H:i', strtotime($terrain['horaire_fermeture'])) ?></span>
                        </div>
                        <p><?= h($terrain['description']) ?></p>
                        <p><strong>Prix :</strong> <?= number_format($terrain['prix_min'], 0, ',', ' ') ?>$ - <?= number_format($terrain['prix_max'], 0, ',', ' ') ?>$</p>
                        <a href="reservation.php?terrain=<?= h($terrain['id']) ?>" class="btn btn-secondary">Réserver</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?php require_once 'includes/footer.php'; ?>
