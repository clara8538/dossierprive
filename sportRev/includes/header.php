<?php
require_once __DIR__ . '/functions.php';

$currentRoute = basename($_SERVER['PHP_SELF']);
function navActive($page)
{
    global $currentRoute;
    return $currentRoute === $page ? 'active' : '';
}

$userLoggedIn = !empty($_SESSION['user_id']);
$userName = $_SESSION['user_nom'] ?? '';
$flashSuccess = flash_message('success');
$flashError = flash_message('error');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SportBooking</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<header class="site-header">
    <div class="container header-inner">
        <a class="brand" href="index.php">SPORTBOOKING</a>
        <nav class="site-nav">
            <a href="index.php" class="<?= navActive('index.php') ?>">Accueil</a>
            <a href="terrains.php" class="<?= navActive('terrains.php') ?>">Terrains</a>
            <a href="reservation.php" class="<?= navActive('reservation.php') ?>">Réserver</a>
            <a href="contact.php" class="<?= navActive('contact.php') ?>">Contact</a>
        </nav>
        <div class="header-actions">
            <?php if ($userLoggedIn): ?>
                <span class="user-badge">Bonjour, <?= h($userName) ?></span>
                <a class="btn btn-secondary" href="mes-reservations.php">Mes réservations</a>
                <a class="btn btn-outline" href="backend/traitement_deconnexion.php">Déconnexion</a>
            <?php else: ?>
                <a class="btn btn-primary" href="connexion.php">Se connecter</a>
            <?php endif; ?>
        </div>
    </div>
</header>

<?php if ($flashSuccess || $flashError): ?>
    <div class="flash-messages container">
        <?php if ($flashSuccess): ?>
            <div class="flash flash-success"><?= h($flashSuccess) ?></div>
        <?php endif; ?>
        <?php if ($flashError): ?>
            <div class="flash flash-error"><?= h($flashError) ?></div>
        <?php endif; ?>
    </div>
<?php endif; ?>
<main class="site-content">
