<?php
require_once 'db.php';

$pageTitle = "Destinations - Vol RDC";
require_once 'includes/header.php';

$destinations = [];
try {
    $stmt = $pdo->query("SELECT * FROM destinations ORDER BY name ASC");
    $destinations = $stmt->fetchAll();
} catch (PDOException $e) {
    // Géré par redirection dans db.php si la base n'existe pas
}
?>

<section class="page-header">
    <div class="container">
        <h1>Nos Destinations Disponibles</h1>
        <p>Explorez les plus grandes villes de la République Démocratique du Congo avec notre flotte moderne et confortable.</p>
    </div>
</section>

<div class="container">
    <?php if (!empty($destinations)): ?>
        <div class="destinations-grid">
            <?php foreach ($destinations as $dest): ?>
                <div class="dest-card">
                    <div class="dest-img-container">
                        <img src="<?php echo htmlspecialchars($dest['image_path']); ?>" alt="<?php echo htmlspecialchars($dest['name']); ?>">
                        <div class="dest-price-badge">À partir de <?php echo number_format($dest['price'], 0, ',', ' '); ?> $</div>
                    </div>
                    <div class="dest-content">
                        <h3><?php echo htmlspecialchars($dest['name']); ?></h3>
                        <p><?php echo htmlspecialchars($dest['description']); ?></p>
                        <a href="index.php?arrivee=<?php echo urlencode(strtolower($dest['name'])); ?>" class="btn-select">
                            <i class="fa-solid fa-plane-departure"></i> Réserver un vol
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p style="text-align: center; color: var(--text-muted); padding: 40px;">Aucune destination disponible pour le moment.</p>
    <?php endif; ?>
</div>

<?php
require_once 'includes/footer.php';
?>
