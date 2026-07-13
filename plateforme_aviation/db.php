<?php
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'plateforme_aviation';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    // Code 1049 = Unknown database. Redirect to init_db.php if it exists
    if ($e->getCode() == 1049) {
        $scriptName = basename($_SERVER['PHP_SELF']);
        if ($scriptName !== 'init_db.php') {
            header("Location: init_db.php");
            exit;
        }
    } else {
        die("Erreur de connexion à la base de données : " . $e->getMessage());
    }
}
