<?php require_once 'includes/header.php'; ?>

<main class="hero">
    <div class="container">
        <div class="hero-badge">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 21 1.9-5.7a8.5 8.5 0 1 1 3.8 3.8z"/></svg>
            Bibliothèque numérique
        </div>
        
        <h1>Toute la connaissance,<br>à portée de clic.</h1>
        <p>Parcourez le catalogue, empruntez et lisez en ligne des milliers d'ouvrages numériques depuis n'importe quel appareil.</p>
        
        <form action="catalogue.php" method="GET" class="search-bar">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            <input type="text" name="q" placeholder="Rechercher un titre, un auteur..." required>
            <button type="submit" class="btn-search">Chercher</button>
        </form>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
