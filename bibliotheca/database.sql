-- =====================================================
-- Bibliotheca — Script de création de la base de données
-- =====================================================

CREATE DATABASE IF NOT EXISTS `bibliotheca` 
DEFAULT CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE `bibliotheca`;

-- -----------------------------------------------------
-- Table : users
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nom` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table : livres
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `livres` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `titre` VARCHAR(255) NOT NULL,
    `auteur` VARCHAR(150) NOT NULL,
    `categorie` VARCHAR(100) NOT NULL DEFAULT 'Général',
    `annee` INT(4) DEFAULT NULL,
    `description` TEXT DEFAULT NULL,
    `couverture` VARCHAR(255) DEFAULT NULL,
    `statut` ENUM('disponible', 'emprunte') NOT NULL DEFAULT 'disponible',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table : emprunts
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `emprunts` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `livre_id` INT NOT NULL,
    `date_emprunt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `date_retour_prevue` DATETIME NOT NULL,
    `date_retour_effective` DATETIME DEFAULT NULL,
    `statut` ENUM('en_cours', 'retourne', 'en_retard') NOT NULL DEFAULT 'en_cours',
    CONSTRAINT `fk_emprunt_user` FOREIGN KEY (`user_id`) 
        REFERENCES `users`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_emprunt_livre` FOREIGN KEY (`livre_id`) 
        REFERENCES `livres`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Index pour les performances
-- -----------------------------------------------------
CREATE INDEX `idx_livres_titre` ON `livres`(`titre`);
CREATE INDEX `idx_livres_auteur` ON `livres`(`auteur`);
CREATE INDEX `idx_livres_categorie` ON `livres`(`categorie`);
CREATE INDEX `idx_emprunts_user` ON `emprunts`(`user_id`);
CREATE INDEX `idx_emprunts_livre` ON `emprunts`(`livre_id`);
CREATE INDEX `idx_emprunts_statut` ON `emprunts`(`statut`);

-- -----------------------------------------------------
-- Utilisateur admin par défaut
-- Mot de passe : admin123
-- -----------------------------------------------------
INSERT INTO `users` (`nom`, `email`, `password`, `role`) VALUES
('Administrateur', 'admin@bibliotheca.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');
