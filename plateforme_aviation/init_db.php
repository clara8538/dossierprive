<?php
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'plateforme_aviation';

$message = "";
$status = "info";

try {
    // 1. Connexion initiale à MySQL sans base de données
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // 2. Création de la base de données
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    $pdo->exec("USE `$dbname`;");

    // 3. Suppression propre des tables existantes pour repartir à zéro (ordre inverse des contraintes)
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
    $pdo->exec("DROP TABLE IF EXISTS `reservations`;");
    $pdo->exec("DROP TABLE IF EXISTS `flights`;");
    $pdo->exec("DROP TABLE IF EXISTS `destinations`;");
    $pdo->exec("DROP TABLE IF EXISTS `users`;");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

    // 4. Création de la table des utilisateurs (users)
    $pdo->exec("CREATE TABLE `users` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(150) NOT NULL,
        `email` VARCHAR(150) NOT NULL UNIQUE,
        `password` VARCHAR(255) NOT NULL,
        `role` VARCHAR(30) NOT NULL DEFAULT 'utilisateur', -- utilisateur / administrateur
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;");

    // 5. Création de la table des destinations
    $pdo->exec("CREATE TABLE `destinations` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(100) NOT NULL UNIQUE,
        `description` TEXT NOT NULL,
        `image_path` VARCHAR(255) NOT NULL,
        `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00
    ) ENGINE=InnoDB;");

    // 6. Création de la table des vols (flights)
    $pdo->exec("CREATE TABLE `flights` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `flight_number` VARCHAR(20) NOT NULL,
        `aircraft` VARCHAR(50) NOT NULL,
        `depart` VARCHAR(100) NOT NULL,
        `arrivee` VARCHAR(100) NOT NULL,
        `jour` VARCHAR(20) NOT NULL, -- LUNDI, MARDI, etc.
        `heure_depart` TIME NOT NULL,
        `heure_arrivee` TIME NOT NULL,
        `block_time` VARCHAR(20) NOT NULL,
        `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        `remarks` VARCHAR(255) DEFAULT ''
    ) ENGINE=InnoDB;");

    // 7. Création de la table des réservations (avec liaison utilisateur)
    $pdo->exec("CREATE TABLE `reservations` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `passenger_name` VARCHAR(150) NOT NULL,
        `email` VARCHAR(150) NOT NULL,
        `phone` VARCHAR(50) NOT NULL,
        `flight_id` INT NOT NULL,
        `user_id` INT DEFAULT NULL, -- L'utilisateur connecté (optionnel si invité)
        `type_vol` VARCHAR(20) NOT NULL, -- aller_retour / aller_simple
        `date_depart` DATE NOT NULL,
        `date_retour` DATE DEFAULT NULL,
        `adultes` INT NOT NULL DEFAULT 1,
        `enfants` INT NOT NULL DEFAULT 0,
        `status` VARCHAR(20) NOT NULL DEFAULT 'en attente', -- en attente / confirmé / annulé
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`flight_id`) REFERENCES `flights`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB;");

    // 8. Insérer les utilisateurs par défaut
    $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
    $userPassword = password_hash('user123', PASSWORD_DEFAULT);
    
    $stmtUser = $pdo->prepare("INSERT INTO `users` (`name`, `email`, `password`, `role`) VALUES (?, ?, ?, ?)");
    $stmtUser->execute(["Administrateur Gabaon", "admin@gabaon.com", $adminPassword, "administrateur"]);
    $stmtUser->execute(["Utilisateur Test", "user@gabaon.com", $userPassword, "utilisateur"]);

    // 9. Insérer les destinations par défaut
    $stmtDest = $pdo->prepare("INSERT INTO `destinations` (`name`, `description`, `image_path`, `price`) VALUES (?, ?, ?, ?)");
    $destinations = [
        ["Kinshasa", "Capitale de la RDC, riche en culture et énergie urbaine.", "images/kinshasa.jpeg", 250.00],
        ["Lubumbashi", "Centre minier et économique du sud de la RDC.", "images/lubumbashi.jpeg", 300.00],
        ["Goma", "Ville volcanique au bord du lac Kivu, proche du Rwanda.", "images/goma.jpeg", 350.00],
        ["Boma", "Ville portuaire historique sur le fleuve Congo.", "images/boma.jpeg", 200.00]
    ];
    foreach ($destinations as $dest) {
        $stmtDest->execute($dest);
    }

    // 10. Insérer les vols par défaut
    $stmtFlight = $pdo->prepare("INSERT INTO `flights` (`flight_number`, `aircraft`, `depart`, `arrivee`, `jour`, `heure_depart`, `heure_arrivee`, `block_time`, `price`, `remarks`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $flights = [
        ["1612", "A330-2", "Lubumbashi", "Kinshasa", "LUNDI", "08:30:00", "10:00:00", "02:30", 280.00, ""],
        ["1671", "A320", "Kinshasa", "Lubumbashi", "LUNDI", "10:30:00", "14:00:00", "02:30", 290.00, ""],
        ["1611", "A330-2", "Kinshasa", "Lubumbashi", "MARDI", "12:30:00", "16:00:00", "02:30", 280.00, ""],
        ["1612", "A330-2", "Lubumbashi", "Kinshasa", "MERCREDI", "08:00:00", "09:30:00", "02:30", 300.00, ""],
        ["1612", "A330-2", "Lubumbashi", "Kinshasa", "JEUDI", "08:30:00", "10:00:00", "02:30", 280.00, ""],
        ["1821", "A320", "Kinshasa", "Goma", "MARDI", "09:00:00", "11:30:00", "02:30", 320.00, "Escale technique"],
        ["1410", "Q400", "Kinshasa", "Boma", "MERCREDI", "14:00:00", "15:00:00", "01:00", 180.00, ""],
        ["1822", "A320", "Goma", "Kinshasa", "JEUDI", "12:00:00", "14:30:00", "02:30", 310.00, ""],
        ["1411", "Q400", "Boma", "Kinshasa", "LUNDI", "16:00:00", "17:00:00", "01:00", 170.00, ""],
        ["1901", "A320", "Lubumbashi", "Goma", "MERCREDI", "11:00:00", "13:45:00", "02:45", 340.00, "Vol direct"],
        ["1902", "A320", "Goma", "Lubumbashi", "VENDREDI", "15:00:00", "17:45:00", "02:45", 340.00, "Vol direct"]
    ];
    foreach ($flights as $flight) {
        $stmtFlight->execute($flight);
    }

    $message = "La base de données professionnelle a été créée et réinitialisée avec succès ! Comptes par défaut configurés.";
    $status = "success";

} catch (PDOException $e) {
    $message = "Une erreur est survenue lors de l'initialisation de la base de données : " . $e->getMessage();
    $status = "error";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Initialisation DB Professionnelle - Mont Gabaon</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #f1f5f9;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
            box-sizing: border-box;
        }
        .container {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            max-width: 600px;
            width: 100%;
            padding: 40px;
            text-align: center;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            box-sizing: border-box;
        }
        .logo {
            font-size: 3em;
            color: #fbbf24;
            margin-bottom: 20px;
        }
        h1 {
            font-size: 1.8em;
            margin-bottom: 20px;
            font-weight: 700;
        }
        .status-box {
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            font-size: 0.95em;
            line-height: 1.6;
            text-align: left;
        }
        .status-success {
            background: rgba(16, 185, 129, 0.15);
            border: 1px solid #10b981;
            color: #34d399;
        }
        .status-error {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid #ef4444;
            color: #f87171;
        }
        .btn {
            display: inline-block;
            background: #2563eb;
            color: white;
            padding: 12px 30px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s ease;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }
        .btn:hover {
            background: #1d4ed8;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(37, 99, 235, 0.4);
        }
        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-top: 25px;
            text-align: left;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 20px;
            font-size: 0.85em;
        }
        .details-box {
            background: rgba(255, 255, 255, 0.05);
            padding: 15px;
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .details-box h3 {
            margin-top: 0;
            color: #fbbf24;
            font-size: 1.1em;
            margin-bottom: 8px;
        }
        .details-box code {
            background: rgba(0, 0, 0, 0.2);
            padding: 2px 6px;
            border-radius: 4px;
            color: #38bdf8;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <i class="fa-solid fa-shield-halved"></i>
        </div>
        <h1>Configuration Professionnelle</h1>
        
        <div class="status-box status-<?php echo $status; ?>">
            <p>
                <?php if ($status == 'success'): ?>
                    <i class="fa-solid fa-circle-check" style="margin-right: 8px;"></i>
                <?php else: ?>
                    <i class="fa-solid fa-triangle-exclamation" style="margin-right: 8px;"></i>
                <?php endif; ?>
                <?php echo $message; ?>
            </p>
        </div>
        
        <?php if ($status == 'success'): ?>
            <a href="login.php" class="btn">Aller à la connexion</a>
            
            <div class="details-grid">
                <div class="details-box">
                    <h3>Compte Admin</h3>
                    Email: <code>admin@gabaon.com</code><br>
                    Mot de passe: <code>admin123</code>
                </div>
                <div class="details-box">
                    <h3>Compte Utilisateur</h3>
                    Email: <code>user@gabaon.com</code><br>
                    Mot de passe: <code>user123</code>
                </div>
            </div>
        <?php else: ?>
            <a href="init_db.php" class="btn" style="background: #ef4444; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);">Réessayer</a>
        <?php endif; ?>
    </div>
</body>
</html>
