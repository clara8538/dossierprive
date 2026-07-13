<?php require_once 'includes/header.php'; ?>

<section class="hero container">
    <div class="hero-content">
        <span class="eyebrow">Réservez votre terrain</span>
        <h1>SportBooking, la réservation sportive à Kinshasa en quelques clics</h1>
        <p>Football, basketball, tennis, volley, golf et plus encore. Trouvez le terrain idéal, choisissez votre créneau et confirmez votre réservation rapidement.</p>
        <div class="hero-actions">
            <a href="terrains.php" class="btn btn-primary">Voir les terrains</a>
            <a href="reservation.php" class="btn btn-secondary">Réserver maintenant</a>
        </div>
    </div>
</section>

<section class="section-overview container">
    <div class="section-heading">
        <h2>Nos terrains populaires</h2>
    </div>
    <div class="card-grid">
        <article class="terrain-card">
            <img src="assets/images/B.jpeg" alt="Terrain de football">
            <div class="terrain-card-body">
                <h3>Terrain de l'institut Lisanga</h3>
                <div class="terrain-meta">
                    <span>Football</span>
                    <span>Gombe, Kinshasa</span>
                    <span>8h00 - 22h00</span>
                </div>
                <p>Terrain moderne avec éclairage et vestiaires, parfait pour matchs et entraînements.</p>
                <a href="reservation.php?terrain=1" class="btn btn-secondary">Réserver</a>
            </div>
        </article>
        <article class="terrain-card">
            <img src="assets/images/A.jpeg" alt="Terrain de tennis">
            <div class="terrain-card-body">
                <h3>Utexafrica Tennis Club</h3>
                <div class="terrain-meta">
                    <span>Tennis</span>
                    <span>Gombe, Kinshasa</span>
                    <span>8h00 - 17h00</span>
                </div>
                <p>Courts de tennis couverts et service professionnel pour joueurs individuels et groupes.</p>
                <a href="reservation.php?terrain=6" class="btn btn-secondary">Réserver</a>
            </div>
        </article>
        <article class="terrain-card">
            <img src="assets/images/C.jpeg" alt="Terrain de volley-ball">
            <div class="terrain-card-body">
                <h3>Gymnase du stade des Martyrs</h3>
                <div class="terrain-meta">
                    <span>Volley-ball</span>
                    <span>Lingwala, Kinshasa</span>
                    <span>8h00 - 22h00</span>
                </div>
                <p>Gymnase spacieux pour entraînements, tournois et événements sportifs.</p>
                <a href="reservation.php?terrain=8" class="btn btn-secondary">Réserver</a>
            </div>
        </article>
    </div>
</section>

<section class="section-overview container">
    <div class="section-heading">
        <h2>Pourquoi SportBooking ?</h2>
    </div>
    <div class="card-grid">
        <article class="message-card">
            <div class="message-card-body">
                <h3>Réservation simple</h3>
                <p>Une interface claire pour choisir un terrain, un créneau et confirmer votre réservation rapidement.</p>
            </div>
        </article>
        <article class="message-card">
            <div class="message-card-body">
                <h3>Support local</h3>
                <p>Nous travaillons avec les meilleurs terrains de Kinshasa pour proposer des espaces sûrs et modernes.</p>
            </div>
        </article>
        <article class="message-card">
            <div class="message-card-body">
                <h3>Gestion en ligne</h3>
                <p>Consultez vos réservations, annulez une séance et contactez facilement le support.</p>
            </div>
        </article>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>

