<?php
require_once __DIR__ . '/../config/connexion.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../inscription.php');
    exit;
}

if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
    add_flash_message('error', 'Jeton CSRF invalide.');
    header('Location: ../inscription.php');
    exit;
}

$nom = trim($_POST['nom'] ?? '');
$prenom = trim($_POST['prenom'] ?? '');
$email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
$telephone = trim($_POST['telephone'] ?? '');
$motDePasse = $_POST['mot_de_passe'] ?? '';
$confirmPassword = $_POST['mot_de_passe_confirm'] ?? '';

if (!$nom || !$prenom || !$email || !$motDePasse || !$confirmPassword) {
    add_flash_message('error', 'Tous les champs obligatoires doivent être remplis.');
    header('Location: ../inscription.php');
    exit;
}

if (strlen($motDePasse) < 6) {
    add_flash_message('error', 'Le mot de passe doit contenir au moins 6 caractères.');
    header('Location: ../inscription.php');
    exit;
}

if ($motDePasse !== $confirmPassword) {
    add_flash_message('error', 'La confirmation du mot de passe ne correspond pas.');
    header('Location: ../inscription.php');
    exit;
}

$stmt = $pdo->prepare('SELECT id FROM utilisateurs WHERE email = :email');
$stmt->execute(['email' => $email]);
if ($stmt->fetch()) {
    add_flash_message('error', 'Cette adresse e-mail est déjà utilisée.');
    header('Location: ../inscription.php');
    exit;
}

$hash = password_hash($motDePasse, PASSWORD_DEFAULT);
$stmt = $pdo->prepare('INSERT INTO utilisateurs (nom, prenom, email, telephone, mot_de_passe) VALUES (:nom, :prenom, :email, :telephone, :mot_de_passe)');
$stmt->execute([
    'nom' => $nom,
    'prenom' => $prenom,
    'email' => $email,
    'telephone' => $telephone,
    'mot_de_passe' => $hash,
]);

add_flash_message('success', 'Votre compte a bien été créé. Vous pouvez maintenant vous connecter.');
header('Location: ../connexion.php');
exit;
