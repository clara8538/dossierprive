<?php
/**
 * Bibliotheca — Configuration centrale
 * Connexion PDO, session, fonctions utilitaires
 */

// Démarrer la session
session_start();

// --- Configuration Base de Données ---
define('DB_HOST', 'localhost');
define('DB_NAME', 'bibliotheca');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// --- Connexion PDO ---
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données. Vérifiez que MySQL est démarré et que la base 'bibliotheca' existe.");
}

// --- Fonctions utilitaires ---

/**
 * Vérifie si un utilisateur est connecté
 */
function estConnecte(): bool {
    return isset($_SESSION['user_id']);
}

/**
 * Vérifie si l'utilisateur connecté est admin
 */
function estAdmin(): bool {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Redirige vers une URL
 */
function rediriger(string $url): void {
    header("Location: $url");
    exit;
}

/**
 * Protège contre les injections XSS
 */
function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/**
 * Affiche un message flash et le supprime de la session
 */
function getFlash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Définit un message flash
 */
function setFlash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/**
 * Protège une page (requiert connexion)
 */
function requireLogin(): void {
    if (!estConnecte()) {
        setFlash('error', 'Vous devez être connecté pour accéder à cette page.');
        rediriger('auth.php');
    }
}

/**
 * Protège une page admin
 */
function requireAdmin(): void {
    requireLogin();
    if (!estAdmin()) {
        setFlash('error', 'Accès réservé aux administrateurs.');
        rediriger('index.php');
    }
}
