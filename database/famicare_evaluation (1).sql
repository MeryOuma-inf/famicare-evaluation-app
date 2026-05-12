-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : mar. 12 mai 2026 à 09:18
-- Version du serveur : 8.4.7
-- Version de PHP : 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `famicare_evaluation`
--

-- --------------------------------------------------------

--
-- Structure de la table `choix_reponses`
--

DROP TABLE IF EXISTS `choix_reponses`;
CREATE TABLE IF NOT EXISTS `choix_reponses` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_question` int NOT NULL,
  `texte` varchar(255) NOT NULL,
  `est_correcte` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_choix_question` (`id_question`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `choix_reponses`
--

INSERT INTO `choix_reponses` (`id`, `id_question`, `texte`, `est_correcte`) VALUES
(1, 1, 'Eau avec du vinaigre blanc', 1),
(2, 1, 'Savon de Marseille dilué', 0),
(3, 1, 'Eau de Javel', 0),
(4, 1, 'Liquide vaisselle', 0),
(5, 2, 'Du haut vers le bas, fond vers la sortie', 1),
(6, 2, 'Du bas vers le haut, sol en premier', 0),
(7, 2, 'Les fenêtres en premier, puis le sol', 0),
(8, 2, 'L\'ordre n\'a pas d\'importance', 0),
(9, 3, '30°C', 1),
(10, 3, '60°C', 0),
(11, 3, '90°C', 0),
(12, 3, '40°C', 0),
(13, 1, 'Du sol vers le plafond', 0),
(14, 1, 'Du haut vers le bas (plafond → sol)', 1),
(15, 1, 'Commencer par les poubelles', 0),
(16, 1, 'L ordre n a pas d importance', 0),
(17, 2, 'Eau de Javel pure', 0),
(18, 2, 'Savon noir dilué ou nettoyant parquet', 1),
(19, 2, 'Eau bouillante', 0),
(20, 2, 'Alcool ménager', 0),
(21, 3, '2 minutes', 0),
(22, 3, '30 secondes', 0),
(23, 3, '15 minutes minimum', 1),
(24, 3, 'Pas besoin de laisser agir', 0);

-- --------------------------------------------------------

--
-- Structure de la table `intervenantes`
--

DROP TABLE IF EXISTS `intervenantes`;
CREATE TABLE IF NOT EXISTS `intervenantes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_utilisateur` int NOT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `categorie` enum('menage','garde_enfant','repassage','accompagnement') DEFAULT NULL,
  `date_inscription` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_interv_utilisateur` (`id_utilisateur`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `intervenantes`
--

INSERT INTO `intervenantes` (`id`, `id_utilisateur`, `telephone`, `categorie`, `date_inscription`) VALUES
(1, 2, '0601020304', 'menage', '2026-05-12 10:57:39'),
(2, 3, '0605060708', 'garde_enfant', '2026-05-12 10:57:39'),
(3, 4, '0609101112', 'repassage', '2026-05-12 10:57:39');

-- --------------------------------------------------------

--
-- Structure de la table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_destinataire` int NOT NULL,
  `message` text NOT NULL,
  `lien` varchar(255) DEFAULT NULL,
  `lue` tinyint(1) NOT NULL DEFAULT '0',
  `cree_le` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_notif_destinataire` (`id_destinataire`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `questions`
--

DROP TABLE IF EXISTS `questions`;
CREATE TABLE IF NOT EXISTS `questions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_test` int NOT NULL,
  `texte` text NOT NULL,
  `type` enum('qcm','mise_en_situation') NOT NULL DEFAULT 'qcm',
  `image_path` varchar(255) DEFAULT NULL,
  `points` int NOT NULL DEFAULT '1',
  `ordre` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `fk_question_test` (`id_test`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `questions`
--

INSERT INTO `questions` (`id`, `id_test`, `texte`, `type`, `image_path`, `points`, `ordre`) VALUES
(1, 1, 'Quel produit pour nettoyer les vitres sans laisser de traces ?', 'qcm', 'uploads/questions/q1_vitres.jpg', 2, 1),
(2, 1, 'Dans quel ordre faut-il nettoyer une pièce ?', 'qcm', 'uploads/questions/q2_ordre.jpg', 2, 2),
(3, 1, 'Température recommandée pour laver le linge délicat ?', 'qcm', NULL, 1, 3);

-- --------------------------------------------------------

--
-- Structure de la table `reponses_detail`
--

DROP TABLE IF EXISTS `reponses_detail`;
CREATE TABLE IF NOT EXISTS `reponses_detail` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_resultat` int NOT NULL,
  `id_question` int NOT NULL,
  `id_choix` int NOT NULL,
  `est_correcte` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_repdet_resultat` (`id_resultat`),
  KEY `fk_repdet_question` (`id_question`),
  KEY `fk_repdet_choix` (`id_choix`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `resultats`
--

DROP TABLE IF EXISTS `resultats`;
CREATE TABLE IF NOT EXISTS `resultats` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_intervenante` int NOT NULL,
  `id_test` int NOT NULL,
  `score` int NOT NULL DEFAULT '0',
  `pourcentage` float NOT NULL DEFAULT '0',
  `mention` enum('insuffisant','satisfaisant','bien','excellent') NOT NULL,
  `duree_sec` int DEFAULT NULL,
  `passe_le` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_resultat_intervenante` (`id_intervenante`),
  KEY `fk_resultat_test` (`id_test`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `resultats`
--

INSERT INTO `resultats` (`id`, `id_intervenante`, `id_test`, `score`, `pourcentage`, `mention`, `duree_sec`, `passe_le`) VALUES
(1, 1, 1, 4, 80, 'bien', 720, '2026-05-12 10:57:39'),
(2, 2, 1, 2, 50, 'satisfaisant', 900, '2026-05-12 10:57:39'),
(3, 3, 1, 1, 40, 'insuffisant', 1080, '2026-05-12 10:57:39');

-- --------------------------------------------------------

--
-- Structure de la table `tests`
--

DROP TABLE IF EXISTS `tests`;
CREATE TABLE IF NOT EXISTS `tests` (
  `id` int NOT NULL AUTO_INCREMENT,
  `titre` varchar(150) NOT NULL,
  `description` text,
  `categorie` enum('menage','garde_enfant','repassage','accompagnement') NOT NULL,
  `duree_limite` int DEFAULT NULL,
  `actif` tinyint(1) NOT NULL DEFAULT '1',
  `id_createur` int NOT NULL,
  `cree_le` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_test_createur` (`id_createur`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `tests`
--

INSERT INTO `tests` (`id`, `titre`, `description`, `categorie`, `duree_limite`, `actif`, `id_createur`, `cree_le`) VALUES
(1, 'Évaluation ménage débutant', 'Test de base pour évaluer les connaissances en entretien ménager.', 'menage', 20, 1, 1, '2026-05-12 10:57:39');

-- --------------------------------------------------------

--
-- Structure de la table `utilisateurs`
--

DROP TABLE IF EXISTS `utilisateurs`;
CREATE TABLE IF NOT EXISTS `utilisateurs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `role` enum('admin','intervenante') NOT NULL DEFAULT 'intervenante',
  `actif` tinyint(1) NOT NULL DEFAULT '1',
  `cree_le` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `utilisateurs`
--

INSERT INTO `utilisateurs` (`id`, `nom`, `prenom`, `email`, `mot_de_passe`, `role`, `actif`, `cree_le`) VALUES
(1, 'Admin', 'FamiCare', 'admin@famicare.fr', '$2y$12$exampleHashedPasswordAdmin', 'admin', 1, '2026-05-12 10:57:38'),
(2, 'Dupont', 'Marie', 'marie.dupont@email.fr', '$2y$12$exampleHashedPassword1', 'intervenante', 1, '2026-05-12 10:57:39'),
(3, 'Martin', 'Sophie', 'sophie.martin@email.fr', '$2y$12$exampleHashedPassword2', 'intervenante', 1, '2026-05-12 10:57:39'),
(4, 'Diallo', 'Fatima', 'fatima.diallo@email.fr', '$2y$12$exampleHashedPassword3', 'intervenante', 1, '2026-05-12 10:57:39');

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `choix_reponses`
--
ALTER TABLE `choix_reponses`
  ADD CONSTRAINT `fk_choix_question` FOREIGN KEY (`id_question`) REFERENCES `questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `intervenantes`
--
ALTER TABLE `intervenantes`
  ADD CONSTRAINT `fk_interv_utilisateur` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notif_destinataire` FOREIGN KEY (`id_destinataire`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `questions`
--
ALTER TABLE `questions`
  ADD CONSTRAINT `fk_question_test` FOREIGN KEY (`id_test`) REFERENCES `tests` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `reponses_detail`
--
ALTER TABLE `reponses_detail`
  ADD CONSTRAINT `fk_repdet_choix` FOREIGN KEY (`id_choix`) REFERENCES `choix_reponses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_repdet_question` FOREIGN KEY (`id_question`) REFERENCES `questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_repdet_resultat` FOREIGN KEY (`id_resultat`) REFERENCES `resultats` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `resultats`
--
ALTER TABLE `resultats`
  ADD CONSTRAINT `fk_resultat_intervenante` FOREIGN KEY (`id_intervenante`) REFERENCES `intervenantes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_resultat_test` FOREIGN KEY (`id_test`) REFERENCES `tests` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `tests`
--
ALTER TABLE `tests`
  ADD CONSTRAINT `fk_test_createur` FOREIGN KEY (`id_createur`) REFERENCES `utilisateurs` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
