<?php
require_once __DIR__ . '/../config/connexion.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../connexion.php');
    exit;
}

if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
    add_flash_message('error', 'Jeton CSRF invalide.');
    header('Location: ../connexion.php');
    exit;
}

$email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
$motDePasse = $_POST['mot_de_passe'] ?? '';

if (!$email || !$motDePasse) {
    add_flash_message('error', 'Veuillez saisir votre e-mail et votre mot de passe.');
    header('Location: ../connexion.php');
    exit;
}

$stmt = $pdo->prepare('SELECT id, nom, prenom, mot_de_passe FROM utilisateurs WHERE email = :email');
$stmt->execute(['email' => $email]);
$user = $stmt->fetch();

if (!$user || !password_verify($motDePasse, $user['mot_de_passe'])) {
    add_flash_message('error', 'Identifiants incorrects.');
    header('Location: ../connexion.php');
    exit;
}

$_SESSION['user_id'] = $user['id'];
$_SESSION['user_nom'] = $user['prenom'] . ' ' . $user['nom'];

add_flash_message('success', 'Connexion réussie.');
header('Location: ../index.php');
exit;
