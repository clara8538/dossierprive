CREATE DATABASE IF NOT EXISTS reservation_sport CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE reservation_sport;

CREATE TABLE IF NOT EXISTS utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(180) NOT NULL UNIQUE,
    telephone VARCHAR(30) DEFAULT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    date_inscription DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS terrains (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(150) NOT NULL,
    adresse VARCHAR(255) NOT NULL,
    ville VARCHAR(100) NOT NULL,
    sport VARCHAR(80) NOT NULL,
    prix_min DECIMAL(10,2) NOT NULL DEFAULT 0,
    prix_max DECIMAL(10,2) NOT NULL DEFAULT 0,
    horaire_ouverture TIME NOT NULL,
    horaire_fermeture TIME NOT NULL,
    description TEXT,
    image VARCHAR(255) DEFAULT NULL,
    statut ENUM('actif','inactif') NOT NULL DEFAULT 'actif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_utilisateur INT NOT NULL,
    id_terrain INT NOT NULL,
    date_reservation DATE NOT NULL,
    heure_debut TIME NOT NULL,
    heure_fin TIME NOT NULL,
    statut ENUM('en attente','confirmé','annulé') NOT NULL DEFAULT 'en attente',
    date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (id_terrain) REFERENCES terrains(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(120) NOT NULL,
    email VARCHAR(180) NOT NULL,
    sujet VARCHAR(180) NOT NULL,
    message TEXT NOT NULL,
    date_envoi DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO terrains (nom, adresse, ville, sport, prix_min, prix_max, horaire_ouverture, horaire_fermeture, description, image, statut) VALUES
('Terrain de l\'institut Lisanga', 'Av. de la Libération, Gombe', 'Kinshasa', 'Football', 80.00, 80.00, '08:00:00', '22:00:00', 'Terrain idéal pour matchs amicaux et entraînements.', 'assets/images/B.jpeg', 'actif'),
('Terrain Shark Club', 'Av. de la Gombe, Gombe', 'Kinshasa', 'Football', 50.00, 100.00, '08:00:00', '22:00:00', 'Centre sportif moderne pour équipes locales.', 'assets/images/B.jpeg', 'actif'),
('Terrain synthétique de l\'UNIKIN', 'Université de Kinshasa, Lemba', 'Kinshasa', 'Football', 20.00, 40.00, '08:00:00', '22:00:00', 'Terrain synthétique sécurisé avec éclairage.', 'assets/images/B.jpeg', 'actif'),
('Gymnase de l\'institut Boboto', 'Av. de l\'Université, Gombe', 'Kinshasa', 'Basketball', 15.00, 25.00, '13:00:00', '22:00:00', 'Gymnase couvert pour entraînements et matchs.', 'assets/images/B.jpeg', 'actif'),
('Terrain de basketball de l\'YMCA', 'Av. de la Victoire, Kasa-Vubu', 'Kinshasa', 'Basketball', 10.00, 10.00, '08:00:00', '22:00:00', 'Terrain intérieur avec vestiaires proches.', 'assets/images/B.jpeg', 'actif'),
('Utexafrica Tennis Club', 'Av. Colonel Mondjiba, Gombe', 'Kinshasa', 'Tennis', 10.00, 15.00, '08:00:00', '17:00:00', 'Club de tennis haut de gamme avec courts couverts.', 'assets/images/A.jpeg', 'actif'),
('Courts du cercle de Kinshasa', 'Av. du Cercle, Gombe', 'Kinshasa', 'Tennis', 15.00, 20.00, '08:00:00', '17:00:00', 'Accès journalier pour membres et visiteurs.', 'assets/images/A.jpeg', 'actif'),
('Gymnase jumelé du stade des Martyrs', 'Av. des Huilleries, Lingwala', 'Kinshasa', 'Volley-ball', 20.00, 30.00, '08:00:00', '22:00:00', 'Structure adaptée pour entraînements de volley.', 'assets/images/C.jpeg', 'actif'),
('Terrain Multi-sports de Elaïs', 'Av. de la Gombe, Gombe', 'Kinshasa', 'Volley-ball', 15.00, 20.00, '08:00:00', '18:00:00', 'Complexe multisports avec piscine et restauration.', 'assets/images/C.jpeg', 'actif'),
('Golf Club de Kinshasa', 'Av. 414 du Cercle, Gombe', 'Kinshasa', 'Golf', 60.00, 80.00, '10:00:00', '17:00:00', 'Parcours de 18 trous pour joueurs de tous niveaux.', 'assets/images/C.jpeg', 'actif');