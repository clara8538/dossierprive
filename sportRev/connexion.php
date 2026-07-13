<?php require_once 'includes/header.php'; ?>

<section class="section-overview container">
    <div class="section-heading">
        <h2>Connexion</h2>
        <p>Connectez-vous pour gérer vos réservations et accéder à vos informations.</p>
    </div>

    <div class="form-card">
        <div class="form-card-body">
            <form class="auth-form" action="backend/traitement_connexion.php" method="POST">
                <?= csrf_input() ?>

                <div class="form-field">
                    <label for="email">Adresse e-mail</label>
                    <input id="email" type="email" name="email" required placeholder="votre@email.com">
                </div>

                <div class="form-field">
                    <label for="mot_de_passe">Mot de passe</label>
                    <input id="mot_de_passe" type="password" name="mot_de_passe" required placeholder="Mot de passe">
                </div>

                <div class="form-footer">
                    <button class="btn btn-primary" type="submit">Se connecter</button>
                </div>
            </form>
            <p style="margin-top:1rem; color: var(--muted);">Pas encore de compte ? <a href="inscription.php">Inscrivez-vous ici</a>.</p>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
