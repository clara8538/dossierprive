<?php
require_once __DIR__ . '/../config/connexion.php';
require_once __DIR__ . '/../includes/functions.php';

if (empty($_SESSION['user_id'])) {
    add_flash_message('error', 'Vous devez être connecté pour effectuer une réservation.');
    header('Location: ../connexion.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../reservation.php');
    exit;
}

if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
    add_flash_message('error', 'Jeton CSRF invalide.');
    header('Location: ../reservation.php');
    exit;
}

$terrainId = filter_input(INPUT_POST, 'terrain_id', FILTER_VALIDATE_INT);
date_default_timezone_set('Africa/Kinshasa');
$dateReservation = $_POST['date_reservation'] ?? '';
$heureDebut = $_POST['heure_debut'] ?? '';
$heureFin = $_POST['heure_fin'] ?? '';

$errors = [];

if (!$terrainId) {
    $errors[] = 'Veuillez sélectionner un terrain.';
}

if (!$dateReservation || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateReservation)) {
    $errors[] = 'Date de réservation invalide.';
}

if (!$heureDebut || !$heureFin || $heureDebut >= $heureFin) {
    $errors[] = 'Veuillez saisir un créneau horaire valide.';
}

if ($dateReservation < date('Y-m-d')) {
    $errors[] = 'La date de réservation doit être aujourd\'hui ou ultérieure.';
}

if ($errors) {
    add_flash_message('error', implode(' ', $errors));
    header('Location: ../reservation.php?terrain=' . ($terrainId ?: ''));
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM terrains WHERE id = :id AND statut = :statut');
$stmt->execute(['id' => $terrainId, 'statut' => 'actif']);
$terrain = $stmt->fetch();

if (!$terrain) {
    add_flash_message('error', 'Terrain introuvable ou inaccessible.');
    header('Location: ../reservation.php');
    exit;
}

if ($heureDebut < $terrain['horaire_ouverture'] || $heureFin > $terrain['horaire_fermeture']) {
    add_flash_message('error', 'La réservation doit être dans les horaires d\'ouverture du terrain.');
    header('Location: ../reservation.php?terrain=' . $terrainId);
    exit;
}

$conflictStmt = $pdo->prepare('SELECT COUNT(*) FROM reservations WHERE id_terrain = :id_terrain AND date_reservation = :date_reservation AND statut != :annule AND ((heure_debut < :heure_fin AND heure_fin > :heure_debut))');
$conflictStmt->execute([
    'id_terrain' => $terrainId,
    'date_reservation' => $dateReservation,
    'heure_debut' => $heureDebut,
    'heure_fin' => $heureFin,
    'annule' => 'annulé',
]);

if ($conflictStmt->fetchColumn() > 0) {
    add_flash_message('error', 'Ce créneau est déjà réservé pour ce terrain.');
    header('Location: ../reservation.php?terrain=' . $terrainId);
    exit;
}

$insert = $pdo->prepare('INSERT INTO reservations (id_utilisateur, id_terrain, date_reservation, heure_debut, heure_fin) VALUES (:id_utilisateur, :id_terrain, :date_reservation, :heure_debut, :heure_fin)');
$insert->execute([
    'id_utilisateur' => $_SESSION['user_id'],
    'id_terrain' => $terrainId,
    'date_reservation' => $dateReservation,
    'heure_debut' => $heureDebut,
    'heure_fin' => $heureFin,
]);

add_flash_message('success', 'Votre réservation a été enregistrée avec succès.');
header('Location: ../mes-reservations.php');
exit;
