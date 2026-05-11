-- ============================================================
-- BASE DE DONNÉES : famicare_evaluation
-- Application d'évaluation des intervenantes FamiCare
-- Version : 1.0
-- Date    : 2026
-- ============================================================

CREATE DATABASE IF NOT EXISTS famicare_evaluation
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE famicare_evaluation;

-- ============================================================
-- TABLE : utilisateurs
-- Tous les comptes (admin + intervenantes)
-- ============================================================
CREATE TABLE utilisateurs (
  id           INT          NOT NULL AUTO_INCREMENT,
  nom          VARCHAR(100) NOT NULL,
  prenom       VARCHAR(100) NOT NULL,
  email        VARCHAR(150) NOT NULL UNIQUE,
  mot_de_passe VARCHAR(255) NOT NULL,
  role         ENUM('admin','intervenante') NOT NULL DEFAULT 'intervenante',
  actif        TINYINT(1)   NOT NULL DEFAULT 1,
  cree_le      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE : intervenantes
-- Profil étendu des intervenantes
-- ============================================================
CREATE TABLE intervenantes (
  id               INT NOT NULL AUTO_INCREMENT,
  id_utilisateur   INT NOT NULL,
  telephone        VARCHAR(20)  DEFAULT NULL,
  categorie        ENUM('menage','garde_enfant','repassage','accompagnement') DEFAULT NULL,
  date_inscription DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  CONSTRAINT fk_interv_utilisateur
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE : tests
-- Les tests d'évaluation créés par l'admin
-- ============================================================
CREATE TABLE tests (
  id           INT          NOT NULL AUTO_INCREMENT,
  titre        VARCHAR(150) NOT NULL,
  description  TEXT         DEFAULT NULL,
  categorie    ENUM('menage','garde_enfant','repassage','accompagnement') NOT NULL,
  duree_limite INT          DEFAULT NULL COMMENT 'Durée en minutes, NULL = pas de limite',
  actif        TINYINT(1)   NOT NULL DEFAULT 1,
  id_createur  INT          NOT NULL,
  cree_le      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  CONSTRAINT fk_test_createur
    FOREIGN KEY (id_createur) REFERENCES utilisateurs(id)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE : questions
-- Les questions de chaque test
-- ============================================================
CREATE TABLE questions (
  id         INT  NOT NULL AUTO_INCREMENT,
  id_test    INT  NOT NULL,
  texte      TEXT NOT NULL,
  type       ENUM('qcm','mise_en_situation') NOT NULL DEFAULT 'qcm',
  image_path VARCHAR(255) DEFAULT NULL COMMENT 'Chemin vers /uploads/questions/',
  points     INT NOT NULL DEFAULT 1,
  ordre      INT NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  CONSTRAINT fk_question_test
    FOREIGN KEY (id_test) REFERENCES tests(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE : choix_reponses
-- Les choix proposés pour chaque question
-- ============================================================
CREATE TABLE choix_reponses (
  id           INT          NOT NULL AUTO_INCREMENT,
  id_question  INT          NOT NULL,
  texte        VARCHAR(255) NOT NULL,
  est_correcte TINYINT(1)   NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  CONSTRAINT fk_choix_question
    FOREIGN KEY (id_question) REFERENCES questions(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE : resultats
-- Résultat global d'un passage de test
-- ============================================================
CREATE TABLE resultats (
  id               INT   NOT NULL AUTO_INCREMENT,
  id_intervenante  INT   NOT NULL,
  id_test          INT   NOT NULL,
  score            INT   NOT NULL DEFAULT 0,
  pourcentage      FLOAT NOT NULL DEFAULT 0,
  mention          ENUM('insuffisant','satisfaisant','bien','excellent') NOT NULL,
  duree_sec        INT   DEFAULT NULL COMMENT 'Durée du passage en secondes',
  passe_le         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  CONSTRAINT fk_resultat_intervenante
    FOREIGN KEY (id_intervenante) REFERENCES intervenantes(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_resultat_test
    FOREIGN KEY (id_test) REFERENCES tests(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE : reponses_detail
-- Détail question par question de chaque résultat
-- ============================================================
CREATE TABLE reponses_detail (
  id           INT NOT NULL AUTO_INCREMENT,
  id_resultat  INT NOT NULL,
  id_question  INT NOT NULL,
  id_choix     INT NOT NULL,
  est_correcte TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  CONSTRAINT fk_repdet_resultat
    FOREIGN KEY (id_resultat) REFERENCES resultats(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_repdet_question
    FOREIGN KEY (id_question) REFERENCES questions(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_repdet_choix
    FOREIGN KEY (id_choix) REFERENCES choix_reponses(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE : notifications
-- Notifications internes de l'application
-- ============================================================
CREATE TABLE notifications (
  id               INT  NOT NULL AUTO_INCREMENT,
  id_destinataire  INT  NOT NULL,
  message          TEXT NOT NULL,
  lien             VARCHAR(255) DEFAULT NULL,
  lue              TINYINT(1)   NOT NULL DEFAULT 0,
  cree_le          DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  CONSTRAINT fk_notif_destinataire
    FOREIGN KEY (id_destinataire) REFERENCES utilisateurs(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- DONNÉES DE TEST
-- ============================================================

-- Compte admin
INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role) VALUES
('Admin', 'FamiCare', 'admin@famicare.fr', '$2y$12$exampleHashedPasswordAdmin', 'admin');

-- 3 intervenantes fictives
INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role) VALUES
('Dupont',  'Marie',  'marie.dupont@email.fr',  '$2y$12$exampleHashedPassword1', 'intervenante'),
('Martin',  'Sophie', 'sophie.martin@email.fr', '$2y$12$exampleHashedPassword2', 'intervenante'),
('Diallo',  'Fatima', 'fatima.diallo@email.fr', '$2y$12$exampleHashedPassword3', 'intervenante');

INSERT INTO intervenantes (id_utilisateur, telephone, categorie) VALUES
(2, '0601020304', 'menage'),
(3, '0605060708', 'garde_enfant'),
(4, '0609101112', 'repassage');

-- 1 test exemple
INSERT INTO tests (titre, description, categorie, duree_limite, id_createur) VALUES
('Évaluation ménage débutant',
 'Test de base pour évaluer les connaissances en entretien ménager.',
 'menage', 20, 1);

-- 3 questions pour ce test
INSERT INTO questions (id_test, texte, type, points, ordre) VALUES
(1, 'Quel produit utilisez-vous pour nettoyer les vitres sans laisser de traces ?', 'qcm', 2, 1),
(1, 'Dans quel ordre faut-il nettoyer une pièce ?', 'qcm', 2, 2),
(1, 'Quelle est la température recommandée pour laver le linge délicat ?', 'qcm', 1, 3);

-- Choix de réponses question 1
INSERT INTO choix_reponses (id_question, texte, est_correcte) VALUES
(1, 'De l''eau avec du vinaigre blanc', 1),
(1, 'Du savon de Marseille dilué', 0),
(1, 'De l''eau de Javel', 0),
(1, 'Du liquide vaisselle', 0);

-- Choix de réponses question 2
INSERT INTO choix_reponses (id_question, texte, est_correcte) VALUES
(2, 'Du haut vers le bas, et du fond vers la sortie', 1),
(2, 'Du bas vers le haut, en commençant par le sol', 0),
(2, 'Par les fenêtres en premier, puis le sol', 0),
(2, 'L''ordre n''a pas d''importance', 0);

-- Choix de réponses question 3
INSERT INTO choix_reponses (id_question, texte, est_correcte) VALUES
(3, '30°C', 1),
(3, '60°C', 0),
(3, '90°C', 0),
(3, '40°C', 0);
