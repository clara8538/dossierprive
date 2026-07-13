<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . " - Mont Gabaon" : "Mont Gabaon - Réservation de Vols"; ?></title>
    <!-- Google Font Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- FontAwesome icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Main Style -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header class="main-header">
        <nav class="navbar">
            <div class="logo">
                <a href="index.php">
                    <img src="images/logo.jpeg.jpg" alt="Logo Mont Gabaon">
                    <span>Mont Gabaon</span>
                </a>
            </div>
            <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation">
                <i class="fa-solid fa-bars"></i>
            </button>
            <ul class="nav-menu" id="navMenu">
                <li><a href="index.php" class="<?php echo ($currentPage == 'index.php' || $currentPage == '') ? 'active' : ''; ?>"><i class="fa-solid fa-house"></i> Accueil</a></li>
                <li><a href="horaires.php" class="<?php echo ($currentPage == 'horaires.php') ? 'active' : ''; ?>"><i class="fa-solid fa-calendar-days"></i> Horaires</a></li>
                <li><a href="destinations.php" class="<?php echo ($currentPage == 'destinations.php') ? 'active' : ''; ?>"><i class="fa-solid fa-map-location-dot"></i> Destinations</a></li>
                <li><a href="information.php" class="<?php echo ($currentPage == 'information.php') ? 'active' : ''; ?>"><i class="fa-solid fa-circle-info"></i> Infos</a></li>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="dashboard.php" class="<?php echo ($currentPage == 'dashboard.php') ? 'active' : ''; ?>"><i class="fa-solid fa-circle-user"></i> Mon Espace</a></li>
                    <?php if ($_SESSION['user_role'] === 'administrateur'): ?>
                        <li><a href="admin.php" class="<?php echo ($currentPage == 'admin.php') ? 'active' : ''; ?>"><i class="fa-solid fa-gears"></i> Admin</a></li>
                    <?php endif; ?>
                    <li><a href="logout.php" style="color: var(--danger);"><i class="fa-solid fa-power-off"></i> Quitter</a></li>
                <?php else: ?>
                    <li><a href="login.php" class="<?php echo ($currentPage == 'login.php') ? 'active' : ''; ?>"><i class="fa-solid fa-arrow-right-to-bracket"></i> Connexion</a></li>
                    <li><a href="register.php" class="<?php echo ($currentPage == 'register.php') ? 'active' : ''; ?>"><i class="fa-solid fa-user-plus"></i> Inscription</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
    <main class="main-content">
