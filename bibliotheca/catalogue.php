<?php
require_once 'includes/header.php';

// Gestion de la recherche et des filtres
$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$categorie = isset($_GET['categorie']) ? $_GET['categorie'] : '';

$params = [];
$sql = "SELECT * FROM livres WHERE 1=1";

if ($search !== '') {
    $sql .= " AND (titre LIKE :search OR auteur LIKE :search)";
    $params[':search'] = "%$search%";
}

if ($categorie !== '') {
    $sql .= " AND categorie = :categorie";
    $params[':categorie'] = $categorie;
}

$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$livres = $stmt->fetchAll();

// Récupérer les catégories pour les filtres
$stmtCats = $pdo->query("SELECT DISTINCT categorie FROM livres WHERE categorie IS NOT NULL ORDER BY categorie");
$categories = $stmtCats->fetchAll(PDO::FETCH_COLUMN);
?>

<main class="container page-section">
    <h1 class="page-title">Catalogue</h1>
    <p class="page-subtitle"><?= count($livres) ?> ouvrage<?= count($livres) > 1 ? 's' : '' ?> disponible<?= count($livres) > 1 ? 's' : '' ?></p>

    <!-- Filtres -->
    <div class="catalogue-filters">
        <button class="filter-btn <?= $categorie === '' ? 'active' : '' ?>" data-category="all">Tous</button>
        <?php foreach ($categories as $cat): ?>
            <button class="filter-btn <?= $categorie === $cat ? 'active' : '' ?>" data-category="<?= e($cat) ?>">
                <?= e($cat) ?>
            </button>
        <?php endforeach; ?>
    </div>

    <?php if (empty($livres)): ?>
        <div class="empty-state">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>
            <p>Aucun livre trouvé pour l'instant. Les bibliothécaires peuvent en ajouter depuis l'espace admin.</p>
        </div>
    <?php else: ?>
        <div class="books-grid">
            <?php foreach ($livres as $livre): ?>
                <div class="book-card">
                    <div class="book-card-cover">
                        <?php if ($livre['couverture']): ?>
                            <img src="<?= e($livre['couverture']) ?>" alt="Couverture de <?= e($livre['titre']) ?>">
                        <?php else: ?>
                            <div class="cover-placeholder">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>
                            </div>
                        <?php endif; ?>
                        
                        <span class="book-card-badge <?= $livre['statut'] ?>">
                            <?= ucfirst($livre['statut']) ?>
                        </span>
                    </div>
                    
                    <div class="book-card-body">
                        <h3><?= e($livre['titre']) ?></h3>
                        <p class="book-author"><?= e($livre['auteur']) ?></p>
                        
                        <div class="book-meta">
                            <span><?= e($livre['categorie']) ?></span>
                            <span><?= e($livre['annee']) ?></span>
                        </div>
                        
                        <?php if (estConnecte()): ?>
                            <?php if ($livre['statut'] === 'disponible'): ?>
                                <form action="action_emprunt.php" method="POST">
                                    <input type="hidden" name="action" value="emprunter">
                                    <input type="hidden" name="livre_id" value="<?= $livre['id'] ?>">
                                    <button type="submit" class="btn-emprunter disponible">Emprunter</button>
                                </form>
                            <?php else: ?>
                                <button class="btn-emprunter indisponible" disabled>Indisponible</button>
                            <?php endif; ?>
                        <?php else: ?>
                            <a href="auth.php" class="btn-emprunter disponible" style="text-decoration:none;">Connectez-vous pour emprunter</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<?php require_once 'includes/footer.php'; ?>
