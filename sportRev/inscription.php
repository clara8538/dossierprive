<?php require_once 'includes/header.php'; ?>

<section class="section-overview container">
    <div class="section-heading">
        <h2>Inscription</h2>
        <p>Créez votre compte pour réserver facilement et accéder à votre espace personnel.</p>
    </div>

    <div class="form-card">
        <div class="form-card-body">
            <form class="auth-form" action="backend/traitement_inscription.php" method="POST">
                <?= csrf_input() ?>

                <div class="form-field">
                    <label for="nom">Nom</label>
                    <input id="nom" type="text" name="nom" required placeholder="Votre nom">
                </div>

                <div class="form-field">
                    <label for="prenom">Prénom</label>
                    <input id="prenom" type="text" name="prenom" required placeholder="Votre prénom">
                </div>

                <div class="form-field">
                    <label for="email">Adresse e-mail</label>
                    <input id="email" type="email" name="email" required placeholder="votre@email.com">
                </div>

                <div class="form-field">
                    <label for="telephone">Téléphone</label>
                    <input id="telephone" type="tel" name="telephone" placeholder="+243 99 123 4567">
                </div>

                <div class="form-field">
                    <label for="mot_de_passe">Mot de passe</label>
                    <input id="mot_de_passe" type="password" name="mot_de_passe" required placeholder="Mot de passe">
                </div>

                <div class="form-field">
                    <label for="mot_de_passe_confirm">Confirmation du mot de passe</label>
                    <input id="mot_de_passe_confirm" type="password" name="mot_de_passe_confirm" required placeholder="Confirmez le mot de passe">
                </div>

                <div class="form-footer">
                    <button class="btn btn-primary" type="submit">Créer mon compte</button>
                </div>
            </form>
            <p style="margin-top:1rem; color: var(--muted);">Vous avez déjà un compte ? <a href="connexion.php">Connectez-vous</a>.</p>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
