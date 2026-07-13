<?php
require_once 'includes/header.php';
requireLogin();

// Récupérer les emprunts de l'utilisateur
$sql = "SELECT e.*, l.titre, l.auteur, l.couverture 
        FROM emprunts e 
        JOIN livres l ON e.livre_id = l.id 
        WHERE e.user_id = ? 
        ORDER BY e.date_emprunt DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION['user_id']]);
$emprunts = $stmt->fetchAll();
?>

<main class="container page-section">
    <div class="admin-header">
        <h1>Mes emprunts</h1>
        <p>Gérez vos livres en cours et votre historique.</p>
    </div>

    <?php if (empty($emprunts)): ?>
        <div class="empty-state">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>
            <p>Aucun emprunt pour l'instant.</p>
        </div>
    <?php else: ?>
        <div class="emprunts-list">
            <?php foreach ($emprunts as $emprunt): ?>
                <div class="emprunt-card">
                    <div class="emprunt-cover">
                        <?php if ($emprunt['couverture']): ?>
                            <img src="<?= e($emprunt['couverture']) ?>" alt="Couverture">
                        <?php else: ?>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>
                        <?php endif; ?>
                    </div>
                    
                    <div class="emprunt-info">
                        <h3><?= e($emprunt['titre']) ?></h3>
                        <p class="emprunt-author"><?= e($emprunt['auteur']) ?></p>
                        
                        <div class="emprunt-dates">
                            Emprunté le : <?= date('d/m/Y', strtotime($emprunt['date_emprunt'])) ?><br>
                            Retour prévu : 
                            <?php 
                                $datePrevue = strtotime($emprunt['date_retour_prevue']);
                                $class = ($emprunt['statut'] === 'en_cours' && $datePrevue < time()) ? 'color: #C62828; font-weight: bold;' : '';
                            ?>
                            <span style="<?= $class ?>"><?= date('d/m/Y', $datePrevue) ?></span>
                            
                            <?php if ($emprunt['statut'] === 'retourne'): ?>
                                <br>Retourné le : <?= date('d/m/Y', strtotime($emprunt['date_retour_effective'])) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="emprunt-actions">
                        <span class="badge badge-<?= str_replace('_', '-', $emprunt['statut']) ?>">
                            <?= str_replace('_', ' ', ucfirst($emprunt['statut'])) ?>
                        </span>
                        
                        <?php if ($emprunt['statut'] !== 'retourne'): ?>
                            <form action="action_emprunt.php" method="POST">
                                <input type="hidden" name="action" value="retourner">
                                <input type="hidden" name="emprunt_id" value="<?= $emprunt['id'] ?>">
                                <button type="submit" class="btn-retourner">Retourner</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<?php require_once 'includes/footer.php'; ?>
