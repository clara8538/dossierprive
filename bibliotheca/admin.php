<?php
require_once 'includes/header.php';
requireAdmin();

$activeTab = $_GET['tab'] ?? 'livres';

// --- Données pour Livres ---
$stmtLivres = $pdo->query("SELECT * FROM livres ORDER BY created_at DESC");
$livres = $stmtLivres->fetchAll();

// --- Données pour Emprunts ---
$stmtEmprunts = $pdo->query("SELECT e.*, l.titre, u.nom as user_nom, u.email 
                            FROM emprunts e 
                            JOIN livres l ON e.livre_id = l.id 
                            JOIN users u ON e.user_id = u.id 
                            ORDER BY e.date_emprunt DESC");
$emprunts = $stmtEmprunts->fetchAll();

// --- Données pour Membres ---
$stmtMembres = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
$membres = $stmtMembres->fetchAll();
?>

<main class="container page-section">
    <div class="admin-header">
        <h1>Espace bibliothécaire</h1>
        <p>Gérez le catalogue, les emprunts et les membres.</p>
    </div>

    <div class="admin-tabs">
        <button class="<?= $activeTab === 'livres' ? 'active' : '' ?>" data-tab="livres">Livres</button>
        <button class="<?= $activeTab === 'emprunts' ? 'active' : '' ?>" data-tab="emprunts">Emprunts</button>
        <button class="<?= $activeTab === 'membres' ? 'active' : '' ?>" data-tab="membres">Membres</button>
    </div>

    <!-- TAB: LIVRES -->
    <div id="tab-livres" class="tab-content <?= $activeTab === 'livres' ? 'active' : '' ?>">
        <div class="admin-toolbar">
            <button class="btn-add" onclick="openModal('modal-add-livre')">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Ajouter un livre
            </button>
        </div>
        
        <div class="data-table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Auteur</th>
                        <th>Catégorie</th>
                        <th>Année</th>
                        <th>Statut</th>
                        <th width="100">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($livres)): ?>
                        <tr class="empty-row"><td colspan="6">Aucun livre pour l'instant.</td></tr>
                    <?php else: ?>
                        <?php foreach ($livres as $livre): ?>
                            <tr>
                                <td><strong><?= e($livre['titre']) ?></strong></td>
                                <td><?= e($livre['auteur']) ?></td>
                                <td><?= e($livre['categorie']) ?></td>
                                <td><?= e($livre['annee']) ?></td>
                                <td><span class="badge badge-<?= $livre['statut'] ?>"><?= ucfirst($livre['statut']) ?></span></td>
                                <td>
                                    <div class="table-actions">
                                        <button class="btn-icon" title="Modifier" onclick="editLivre(<?= $livre['id'] ?>, '<?= addslashes(e($livre['titre'])) ?>', '<?= addslashes(e($livre['auteur'])) ?>', '<?= addslashes(e($livre['categorie'])) ?>', '<?= e($livre['annee']) ?>', '<?= addslashes(e($livre['description'])) ?>')">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        </button>
                                        <form action="admin_livre_action.php" method="POST" style="display:inline;" onsubmit="return confirmDelete(this, '<?= addslashes(e($livre['titre'])) ?>')">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $livre['id'] ?>">
                                            <button type="submit" class="btn-icon danger" title="Supprimer">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB: EMPRUNTS -->
    <div id="tab-emprunts" class="tab-content <?= $activeTab === 'emprunts' ? 'active' : '' ?>">
        <div class="data-table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Livre</th>
                        <th>Membre</th>
                        <th>Emprunté le</th>
                        <th>Retour prévu</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($emprunts)): ?>
                        <tr class="empty-row"><td colspan="5">Aucun emprunt.</td></tr>
                    <?php else: ?>
                        <?php foreach ($emprunts as $emprunt): ?>
                            <tr>
                                <td><strong><?= e($emprunt['titre']) ?></strong></td>
                                <td>
                                    <?= e($emprunt['user_nom']) ?><br>
                                    <small style="color:var(--text-muted)"><?= e($emprunt['email']) ?></small>
                                </td>
                                <td><?= date('d/m/Y', strtotime($emprunt['date_emprunt'])) ?></td>
                                <td><?= date('d/m/Y', strtotime($emprunt['date_retour_prevue'])) ?></td>
                                <td><span class="badge badge-<?= str_replace('_', '-', $emprunt['statut']) ?>"><?= str_replace('_', ' ', ucfirst($emprunt['statut'])) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB: MEMBRES -->
    <div id="tab-membres" class="tab-content <?= $activeTab === 'membres' ? 'active' : '' ?>">
        <div class="data-table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Date d'inscription</th>
                        <th>Rôle</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($membres as $membre): ?>
                        <tr>
                            <td><strong><?= e($membre['nom']) ?></strong></td>
                            <td><?= e($membre['email']) ?></td>
                            <td><?= date('d/m/Y', strtotime($membre['created_at'])) ?></td>
                            <td><span class="badge badge-<?= $membre['role'] ?>"><?= ucfirst($membre['role']) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- Modal Add Livre -->
<div id="modal-add-livre" class="modal-overlay">
    <div class="modal">
        <div class="modal-header">
            <h3>Ajouter un livre</h3>
            <button class="modal-close" onclick="closeModal('modal-add-livre')">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        
        <form action="admin_livre_action.php" method="POST">
            <input type="hidden" name="action" value="add">
            
            <div class="form-group">
                <label>Titre</label>
                <input type="text" name="titre" required>
            </div>
            
            <div class="form-group">
                <label>Auteur</label>
                <input type="text" name="auteur" required>
            </div>
            
            <div style="display:flex; gap:16px;">
                <div class="form-group" style="flex:1;">
                    <label>Catégorie</label>
                    <input type="text" name="categorie" placeholder="ex: Fiction, Sciences..." required>
                </div>
                <div class="form-group" style="width:120px;">
                    <label>Année</label>
                    <input type="number" name="annee" placeholder="ex: 2024">
                </div>
            </div>
            
            <div class="form-group">
                <label>Description</label>
                <textarea name="description"></textarea>
            </div>
            
            <div class="form-group">
                <label>URL Couverture (optionnel)</label>
                <input type="url" name="couverture" placeholder="https://...">
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeModal('modal-add-livre')">Annuler</button>
                <button type="submit" class="btn-submit" style="width:auto; margin-top:0;">Ajouter</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Livre -->
<div id="modal-edit-livre" class="modal-overlay">
    <div class="modal">
        <div class="modal-header">
            <h3>Modifier un livre</h3>
            <button class="modal-close" onclick="closeModal('modal-edit-livre')">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        
        <form action="admin_livre_action.php" method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="edit-livre-id">
            
            <div class="form-group">
                <label>Titre</label>
                <input type="text" name="titre" id="edit-titre" required>
            </div>
            
            <div class="form-group">
                <label>Auteur</label>
                <input type="text" name="auteur" id="edit-auteur" required>
            </div>
            
            <div style="display:flex; gap:16px;">
                <div class="form-group" style="flex:1;">
                    <label>Catégorie</label>
                    <input type="text" name="categorie" id="edit-categorie" required>
                </div>
                <div class="form-group" style="width:120px;">
                    <label>Année</label>
                    <input type="number" name="annee" id="edit-annee">
                </div>
            </div>
            
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" id="edit-description"></textarea>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeModal('modal-edit-livre')">Annuler</button>
                <button type="submit" class="btn-submit" style="width:auto; margin-top:0;">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
