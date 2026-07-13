<?php
require_once __DIR__ . '/../config/connexion.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../contact.php');
    exit;
}

if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
    add_flash_message('error', 'Jeton CSRF invalide.');
    header('Location: ../contact.php');
    exit;
}

$nom = trim($_POST['nom'] ?? '');
$email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
$sujet = trim($_POST['sujet'] ?? '');
$message = trim($_POST['message'] ?? '');

if (!$nom || !$email || !$sujet || !$message) {
    add_flash_message('error', 'Tous les champs du formulaire de contact sont requis.');
    header('Location: ../contact.php');
    exit;
}

$insert = $pdo->prepare('INSERT INTO contacts (nom, email, sujet, message) VALUES (:nom, :email, :sujet, :message)');
$insert->execute([
    'nom' => $nom,
    'email' => $email,
    'sujet' => $sujet,
    'message' => $message,
]);

add_flash_message('success', 'Votre message a bien été envoyé. Nous vous répondrons rapidement.');
header('Location: ../contact.php');
exit;
