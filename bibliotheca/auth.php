<?php
require_once 'config.php';

if (estConnecte()) {
    rediriger('index.php');
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'login') {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($email) || empty($password)) {
            $error = "Tous les champs sont requis.";
        } else {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_nom'] = $user['nom'];
                $_SESSION['user_role'] = $user['role'];
                
                setFlash('success', 'Bienvenue, ' . e($user['nom']) . ' !');
                rediriger('index.php');
            } else {
                $error = "Identifiants incorrects.";
            }
        }
    } elseif ($action === 'register') {
        $nom = trim($_POST['nom'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($nom) || empty($email) || empty($password)) {
            $error = "Tous les champs sont requis.";
        } else {
            // Vérifier si l'email existe déjà
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = "Cet email est déjà utilisé.";
            } else {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (nom, email, password) VALUES (?, ?, ?)");
                
                if ($stmt->execute([$nom, $email, $hashed])) {
                    setFlash('success', 'Compte créé avec succès. Vous pouvez maintenant vous connecter.');
                    // Switch to login tab automatically in UI via query param maybe? Or just show login form
                } else {
                    $error = "Erreur lors de la création du compte.";
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — Bibliotheca</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="auth-page">
    <div class="auth-left">
        <a href="index.php" class="auth-logo" style="text-decoration:none;">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>
            Bibliotheca
        </a>
        
        <div class="auth-quote">
            <blockquote>« Une bibliothèque est un hôpital pour l'esprit. »</blockquote>
            <cite>— Anonyme grec</cite>
        </div>
        
        <div class="auth-footer-text">
            &copy; <?= date('Y') ?> Bibliotheca
        </div>
    </div>
    
    <div class="auth-right">
        <div class="auth-form-container">
            <h2>Bienvenue</h2>
            <p class="auth-subtitle">Connectez-vous pour emprunter et lire.</p>
            
            <?php if ($error): ?>
                <div class="flash-message error" style="margin: 0 0 20px 0;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <?= e($error) ?>
                </div>
            <?php endif; ?>
            <?php $flash = getFlash(); if ($flash): ?>
                <div class="flash-message <?= $flash['type'] ?>" style="margin: 0 0 20px 0;">
                    <?= e($flash['message']) ?>
                </div>
            <?php endif; ?>
            
            <button class="btn-google" type="button">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48"><path fill="#FFC107" d="M43.611,20.083H42V20H24v8h11.303c-1.649,4.657-6.08,8-11.303,8c-6.627,0-12-5.373-12-12c0-6.627,5.373-12,12-12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C12.955,4,4,12.955,4,24c0,11.045,8.955,20,20,20c11.045,0,20-8.955,20-20C44,22.659,43.862,21.35,43.611,20.083z"/><path fill="#FF3D00" d="M6.306,14.691l6.571,4.819C14.655,15.108,18.961,12,24,12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C16.318,4,9.656,8.337,6.306,14.691z"/><path fill="#4CAF50" d="M24,44c5.166,0,9.86-1.977,13.409-5.192l-6.19-5.238C29.211,35.091,26.715,36,24,36c-5.202,0-9.619-3.317-11.283-7.946l-6.522,5.025C9.505,39.556,16.227,44,24,44z"/><path fill="#1976D2" d="M43.611,20.083H42V20H24v8h11.303c-0.792,2.237-2.231,4.166-4.087,5.571c0.001-0.001,0.002-0.001,0.003-0.002l6.19,5.238C36.971,39.205,44,34,44,24C44,22.659,43.862,21.35,43.611,20.083z"/></svg>
                Continuer avec Google
            </button>
            
            <div class="auth-divider">
                <span>ou</span>
            </div>
            
            <div class="auth-tabs">
                <button class="active" data-tab="login">Connexion</button>
                <button data-tab="register">Inscription</button>
            </div>
            
            <!-- Login Form -->
            <form id="login-form" action="auth.php" method="POST">
                <input type="hidden" name="action" value="login">
                
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label>Mot de passe</label>
                    <input type="password" name="password" required>
                </div>
                
                <button type="submit" class="btn-submit">Se connecter</button>
            </form>
            
            <!-- Register Form -->
            <form id="register-form" action="auth.php" method="POST" class="hidden">
                <input type="hidden" name="action" value="register">
                
                <div class="form-group">
                    <label>Nom complet</label>
                    <input type="text" name="nom" required>
                </div>
                
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label>Mot de passe</label>
                    <input type="password" name="password" required>
                </div>
                
                <button type="submit" class="btn-submit">Créer un compte</button>
            </form>
        </div>
    </div>
</div>

<script src="js/main.js"></script>
</body>
</html>
