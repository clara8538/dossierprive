<?php require_once 'includes/header.php'; ?>

<section class="section-overview container">
    <div class="section-heading">
        <h2>Contactez-nous</h2>
        <p>Une question sur une réservation ? Envoyez-nous un message et nous vous répondons rapidement.</p>
    </div>

    <div class="form-card">
        <div class="form-card-body">
            <form class="auth-form" action="backend/traitement_contact.php" method="POST">
                <?= csrf_input() ?>

                <div class="form-field">
                    <label for="nom">Nom complet</label>
                    <input id="nom" type="text" name="nom" required placeholder="Votre nom complet">
                </div>

                <div class="form-field">
                    <label for="email">Adresse e-mail</label>
                    <input id="email" type="email" name="email" required placeholder="votre@email.com">
                </div>

                <div class="form-field">
                    <label for="sujet">Sujet</label>
                    <input id="sujet" type="text" name="sujet" required placeholder="Objet de votre message">
                </div>

                <div class="form-field">
                    <label for="message">Message</label>
                    <textarea id="message" name="message" required placeholder="Écrivez votre message..."></textarea>
                </div>

                <div class="form-footer">
                    <button class="btn btn-primary" type="submit">Envoyer le message</button>
                </div>
            </form>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
