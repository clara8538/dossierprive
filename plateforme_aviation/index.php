<?php
require_once 'db.php';

$pageTitle = "Accueil - Vol RDC";
require_once 'includes/header.php';

// Récupérer les villes de départ et d'arrivée uniques de la DB
$cities_depart = [];
$cities_arrivee = [];

try {
    $stmt = $pdo->query("SELECT DISTINCT depart FROM flights ORDER BY depart ASC");
    $cities_depart = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $stmt = $pdo->query("SELECT DISTINCT arrivee FROM flights ORDER BY arrivee ASC");
    $cities_arrivee = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    // Si la DB n'existe pas encore, init_db.php s'en occupe par redirection dans db.php
}

// Traiter la recherche
$searched = false;
$searchResults = [];
$type_vol = 'aller_retour';
$depart = '';
$arrivee = '';
$date_depart = '';
$date_retour = '';
$adultes = 1;
$enfants = 0;

if (isset($_GET['rechercher'])) {
    $searched = true;
    $type_vol = $_GET['type_vol'] ?? 'aller_retour';
    $depart = $_GET['depart'] ?? '';
    $arrivee = $_GET['arrivee'] ?? '';
    $date_depart = $_GET['date_depart'] ?? '';
    $date_retour = $_GET['date_retour'] ?? '';
    $adultes = (int)($_GET['adultes'] ?? 1);
    $enfants = (int)($_GET['enfants'] ?? 0);

    if ($date_depart) {
        // Obtenir le jour de la semaine en français
        $timestamp = strtotime($date_depart);
        $dayOfWeekEN = strtoupper(date('l', $timestamp));
        
        $daysMap = [
            'MONDAY'    => 'LUNDI',
            'TUESDAY'   => 'MARDI',
            'WEDNESDAY' => 'MERCREDI',
            'THURSDAY'  => 'JEUDI',
            'FRIDAY'    => 'VENDREDI',
            'SATURDAY'  => 'SAMEDI',
            'SUNDAY'    => 'DIMANCHE'
        ];
        $dayOfWeekFR = $daysMap[$dayOfWeekEN] ?? '';

        try {
            $stmt = $pdo->prepare("SELECT * FROM flights WHERE LOWER(depart) = LOWER(:depart) AND LOWER(arrivee) = LOWER(:arrivee) AND jour = :jour");
            $stmt->execute([
                'depart' => $depart,
                'arrivee' => $arrivee,
                'jour' => $dayOfWeekFR
            ]);
            $searchResults = $stmt->fetchAll();
        } catch (PDOException $e) {
            $error = $e->getMessage();
        }
    }
}
?>

<section class="hero">
    <div class="container">
        <h1>Découvrez le monde avec Mont Gabaon</h1>
        <p>Réservez vos vols en quelques clics vers vos destinations préférées en République Démocratique du Congo.</p>
    </div>
</section>

<div class="container search-container">
    <div class="card-main">
        <h2 class="form-title"><i class="fa-solid fa-plane-searching"></i> Rechercher un vol</h2>
        
        <form method="GET" action="index.php" id="reservation-form-main">
            <div class="form-radio-row">
                <input type="radio" id="aller_retour" name="type_vol" value="aller_retour" <?php echo ($type_vol === 'aller_retour') ? 'checked' : ''; ?> style="display:none;">
                <label for="aller_retour"><i class="fa-solid fa-arrows-left-right"></i> Aller-Retour</label>

                <input type="radio" id="aller_simple" name="type_vol" value="aller_simple" <?php echo ($type_vol === 'aller_simple') ? 'checked' : ''; ?> style="display:none;">
                <label for="aller_simple"><i class="fa-solid fa-arrow-right"></i> Aller simple</label>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label for="depart"><i class="fa-solid fa-plane-departure"></i> De :</label>
                    <select id="depart" name="depart" required>
                        <option value="" disabled <?php echo empty($depart) ? 'selected' : ''; ?>>Ville de départ</option>
                        <?php foreach ($cities_depart as $city): ?>
                            <option value="<?php echo htmlspecialchars($city); ?>" <?php echo (strtolower($depart) === strtolower($city)) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars(ucfirst($city)); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="arrivee"><i class="fa-solid fa-plane-arrival"></i> À :</label>
                    <select id="arrivee" name="arrivee" required>
                        <option value="" disabled <?php echo empty($arrivee) ? 'selected' : ''; ?>>Ville de destination</option>
                        <?php foreach ($cities_arrivee as $city): ?>
                            <option value="<?php echo htmlspecialchars($city); ?>" <?php echo (strtolower($arrivee) === strtolower($city)) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars(ucfirst($city)); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label for="date_depart"><i class="fa-solid fa-calendar-day"></i> Date de départ :</label>
                    <input type="date" id="date_depart" name="date_depart" value="<?php echo htmlspecialchars($date_depart); ?>" required>
                </div>

                <div class="form-group" id="date-retour-div">
                    <label for="date_retour"><i class="fa-solid fa-calendar-day"></i> Date de retour :</label>
                    <input type="date" id="date_retour" name="date_retour" value="<?php echo htmlspecialchars($date_retour); ?>">
                </div>

                <div class="form-group">
                    <label for="passagers"><i class="fa-solid fa-users"></i> Passagers :</label>
                    <div style="display: flex; gap: 10px;">
                        <select id="adultes" name="adultes" style="flex: 1;">
                            <option value="1" <?php echo ($adultes == 1) ? 'selected' : ''; ?>>1 Adulte</option>
                            <option value="2" <?php echo ($adultes == 2) ? 'selected' : ''; ?>>2 Adultes</option>
                            <option value="3" <?php echo ($adultes == 3) ? 'selected' : ''; ?>>3 Adultes</option>
                            <option value="4" <?php echo ($adultes == 4) ? 'selected' : ''; ?>>4 Adultes</option>
                        </select>
                        <select id="enfants" name="enfants" style="flex: 1;">
                            <option value="0" <?php echo ($enfants == 0) ? 'selected' : ''; ?>>0 Enfant</option>
                            <option value="1" <?php echo ($enfants == 1) ? 'selected' : ''; ?>>1 Enfant</option>
                            <option value="2" <?php echo ($enfants == 2) ? 'selected' : ''; ?>>2 Enfants</option>
                        </select>
                    </div>
                </div>
            </div>

            <button type="submit" name="rechercher" value="1" class="btn-primary">
                <i class="fa-solid fa-magnifying-glass"></i> Rechercher des vols
            </button>
        </form>
    </div>

    <?php if ($searched): ?>
        <section class="results-section">
            <h2 class="results-title">
                <i class="fa-solid fa-clipboard-list"></i> 
                Vols disponibles le <?php echo htmlspecialchars(date('d/m/Y', strtotime($date_depart))); ?> 
                (<?php echo htmlspecialchars($dayOfWeekFR); ?>)
            </h2>

            <?php if (!empty($searchResults)): ?>
                <div class="flights-list">
                    <?php foreach ($searchResults as $flight): ?>
                        <div class="flight-card">
                            <div class="flight-main-info">
                                <div class="flight-route">
                                    <div class="route-point">
                                        <div class="time"><?php echo htmlspecialchars(date('H:i', strtotime($flight['heure_depart']))); ?></div>
                                        <div class="city"><?php echo htmlspecialchars($flight['depart']); ?></div>
                                    </div>
                                    <div class="route-arrow">
                                        <i class="fa-solid fa-plane"></i>
                                        <span><?php echo htmlspecialchars($flight['block_time']); ?></span>
                                    </div>
                                    <div class="route-point">
                                        <div class="time"><?php echo htmlspecialchars(date('H:i', strtotime($flight['heure_arrivee']))); ?></div>
                                        <div class="city"><?php echo htmlspecialchars($flight['arrivee']); ?></div>
                                    </div>
                                </div>

                                <div class="flight-details">
                                    <div class="detail-item">
                                        <strong>Appareil</strong>
                                        <span><?php echo htmlspecialchars($flight['aircraft']); ?></span>
                                    </div>
                                    <div class="detail-item">
                                        <strong>Numéro de vol</strong>
                                        <span>MG-<?php echo htmlspecialchars($flight['flight_number']); ?></span>
                                    </div>
                                </div>
                            </div>

                            <div class="flight-price-action">
                                <div class="flight-price">
                                    <span class="label">Prix par adulte</span>
                                    <span class="amount"><?php echo number_format($flight['price'], 2, ',', ' '); ?> $</span>
                                </div>
                                <a href="reservation.php?flight_id=<?php echo $flight['id']; ?>&type_vol=<?php echo $type_vol; ?>&date_depart=<?php echo $date_depart; ?>&date_retour=<?php echo $date_retour; ?>&adultes=<?php echo $adultes; ?>&enfants=<?php echo $enfants; ?>" class="btn-select">
                                    Réserver <i class="fa-solid fa-chevron-right"></i>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div style="background-color: #ffffff; border-radius: 12px; padding: 40px; text-align: center; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm);">
                    <i class="fa-solid fa-plane-slash" style="font-size: 3em; color: var(--text-muted); margin-bottom: 15px;"></i>
                    <h3>Aucun vol disponible pour cette date</h3>
                    <p style="color: var(--text-muted); margin-top: 10px;">
                        Nous ne proposons pas de vol direct entre <strong><?php echo htmlspecialchars(ucfirst($depart)); ?></strong> et <strong><?php echo htmlspecialchars(ucfirst($arrivee)); ?></strong> les <strong><?php echo htmlspecialchars(ucfirst(strtolower($dayOfWeekFR))); ?>s</strong>.
                    </p>
                    <p style="color: var(--primary-light); font-weight: 600; margin-top: 15px;">
                        <a href="horaires.php" style="text-decoration: none; color: inherit;"><i class="fa-solid fa-calendar-days"></i> Consulter le calendrier complet des horaires hebdomadaires</a>
                    </p>
                </div>
            <?php endif; ?>
        </section>
    <?php endif; ?>
</div>

<?php
require_once 'includes/footer.php';
?>
