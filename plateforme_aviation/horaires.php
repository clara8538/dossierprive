<?php
require_once 'db.php';

$pageTitle = "Horaires des vols - Mont Gabaon";
require_once 'includes/header.php';

// Filtres
$filtre_jour = $_GET['jour'] ?? '';
$filtre_depart = $_GET['depart'] ?? '';
$filtre_arrivee = $_GET['arrivee'] ?? '';

// Récupérer les jours uniques présents dans la base de données
$jours = ["LUNDI", "MARDI", "MERCREDI", "JEUDI", "VENDREDI", "SAMEDI", "DIMANCHE"];

// Construire la requête SQL avec filtres
$queryStr = "SELECT * FROM flights WHERE 1=1";
$params = [];

if ($filtre_jour !== '') {
    $queryStr .= " AND jour = :jour";
    $params['jour'] = $filtre_jour;
}
if ($filtre_depart !== '') {
    $queryStr .= " AND LOWER(depart) LIKE :depart";
    $params['depart'] = '%' . strtolower($filtre_depart) . '%';
}
if ($filtre_arrivee !== '') {
    $queryStr .= " AND LOWER(arrivee) LIKE :arrivee";
    $params['arrivee'] = '%' . strtolower($filtre_arrivee) . '%';
}

$queryStr .= " ORDER BY FIELD(jour, 'LUNDI', 'MARDI', 'MERCREDI', 'JEUDI', 'VENDREDI', 'SAMEDI', 'DIMANCHE'), heure_depart ASC";

$flights = [];
try {
    $stmt = $pdo->prepare($queryStr);
    $stmt->execute($params);
    $flights = $stmt->fetchAll();
} catch (PDOException $e) {
    // Géré par redirection dans db.php si la base n'existe pas
}

// Grouper les vols par jour
$flights_by_day = [];
foreach ($flights as $flight) {
    $flights_by_day[$flight['jour']][] = $flight;
}
?>

<section class="page-header">
    <div class="container">
        <h1>Programme Hebdomadaire des Vols</h1>
        <p>Consultez les jours d'opération et les horaires de notre réseau intérieur en RDC.</p>
    </div>
</section>

<div class="container">
    <!-- Formulaire de filtrage -->
    <div class="filter-card">
        <form method="GET" action="horaires.php" class="filter-form">
            <div class="form-group">
                <label for="jour"><i class="fa-solid fa-calendar-day"></i> Jour :</label>
                <select id="jour" name="jour">
                    <option value="">Tous les jours</option>
                    <option value="LUNDI" <?php echo ($filtre_jour === 'LUNDI') ? 'selected' : ''; ?>>Lundi</option>
                    <option value="MARDI" <?php echo ($filtre_jour === 'MARDI') ? 'selected' : ''; ?>>Mardi</option>
                    <option value="MERCREDI" <?php echo ($filtre_jour === 'MERCREDI') ? 'selected' : ''; ?>>Mercredi</option>
                    <option value="JEUDI" <?php echo ($filtre_jour === 'JEUDI') ? 'selected' : ''; ?>>Jeudi</option>
                    <option value="VENDREDI" <?php echo ($filtre_jour === 'VENDREDI') ? 'selected' : ''; ?>>Vendredi</option>
                    <option value="SAMEDI" <?php echo ($filtre_jour === 'SAMEDI') ? 'selected' : ''; ?>>Samedi</option>
                    <option value="DIMANCHE" <?php echo ($filtre_jour === 'DIMANCHE') ? 'selected' : ''; ?>>Dimanche</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="depart"><i class="fa-solid fa-plane-departure"></i> Ville de départ :</label>
                <input type="text" id="depart" name="depart" placeholder="Ex: Kinshasa" value="<?php echo htmlspecialchars($filtre_depart); ?>">
            </div>
            
            <div class="form-group">
                <label for="arrivee"><i class="fa-solid fa-plane-arrival"></i> Ville d'arrivée :</label>
                <input type="text" id="arrivee" name="arrivee" placeholder="Ex: Lubumbashi" value="<?php echo htmlspecialchars($filtre_arrivee); ?>">
            </div>
            
            <button type="submit" class="btn-primary">
                <i class="fa-solid fa-filter"></i> Filtrer
            </button>
        </form>
    </div>

    <!-- Affichage des horaires -->
    <?php if (!empty($flights_by_day)): ?>
        <?php foreach ($jours as $jourName): ?>
            <?php if (isset($flights_by_day[$jourName])): ?>
                <div class="horaire-section">
                    <h2 class="horaire-day-title">
                        <i class="fa-solid fa-circle-play"></i> 
                        <?php 
                            $translation = [
                                'LUNDI' => 'Lundi / Monday',
                                'MARDI' => 'Mardi / Tuesday',
                                'MERCREDI' => 'Mercredi / Wednesday',
                                'JEUDI' => 'Jeudi / Thursday',
                                'VENDREDI' => 'Vendredi / Friday',
                                'SAMEDI' => 'Samedi / Saturday',
                                'DIMANCHE' => 'Dimanche / Sunday'
                            ];
                            echo htmlspecialchars($translation[$jourName]);
                        ?>
                        <span><?php echo count($flights_by_day[$jourName]); ?> vol(s)</span>
                    </h2>
                    
                    <div class="flights-list">
                        <?php foreach ($flights_by_day[$jourName] as $flight): ?>
                            <div class="flight-card">
                                <div class="flight-main-info">
                                    <div class="flight-route">
                                        <div class="route-point">
                                            <div class="time"><?php echo htmlspecialchars(date('H:i', strtotime($flight['heure_depart']))); ?></div>
                                            <div class="city"><?php echo htmlspecialchars($flight['depart']); ?></div>
                                        </div>
                                        <div class="route-arrow">
                                            <i class="fa-solid fa-plane"></i>
                                            <span><?php echo htmlspecialchars($flight['block_time']); ?> h</span>
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
                                            <strong>Vol N°</strong>
                                            <span>MG-<?php echo htmlspecialchars($flight['flight_number']); ?></span>
                                        </div>
                                        <?php if ($flight['remarks']): ?>
                                            <div class="detail-item" style="grid-column: span 2;">
                                                <strong>Remarque</strong>
                                                <span style="color: var(--accent-hover);"><i class="fa-solid fa-triangle-exclamation"></i> <?php echo htmlspecialchars($flight['remarks']); ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="flight-price-action">
                                    <div class="flight-price">
                                        <span class="label">Prix</span>
                                        <span class="amount"><?php echo number_format($flight['price'], 2, ',', ' '); ?> $</span>
                                    </div>
                                    <a href="reservation.php?flight_id=<?php echo $flight['id']; ?>&date_depart=<?php 
                                        // Calculer la date la plus proche correspondant à ce jour de la semaine
                                        $d = new DateTime();
                                        $engDays = ['LUNDI'=>'Monday', 'MARDI'=>'Tuesday', 'MERCREDI'=>'Wednesday', 'JEUDI'=>'Thursday', 'VENDREDI'=>'Friday', 'SAMEDI'=>'Saturday', 'DIMANCHE'=>'Sunday'];
                                        $d->modify('next ' . $engDays[$flight['jour']]);
                                        echo $d->format('Y-m-d');
                                    ?>" class="btn-select">
                                        Réserver
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php else: ?>
        <div style="background-color: #ffffff; border-radius: 12px; padding: 40px; text-align: center; border: 1px solid var(--border-color); box-shadow: var(--shadow-sm);">
            <i class="fa-solid fa-filter-circle-xmark" style="font-size: 3em; color: var(--text-muted); margin-bottom: 15px;"></i>
            <h3>Aucun vol ne correspond à vos critères de recherche</h3>
            <p style="color: var(--text-muted); margin-top: 10px;">Veuillez élargir vos filtres de recherche.</p>
            <a href="horaires.php" class="btn-select" style="margin-top: 20px;">Afficher tous les horaires</a>
        </div>
    <?php endif; ?>
</div>

<?php
require_once 'includes/footer.php';
?>
