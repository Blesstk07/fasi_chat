-- =============================================================
-- FasiChat Classroom — Schéma de la base de données
-- =============================================================

CREATE DATABASE IF NOT EXISTS fasichat
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;

USE fasichat;

-- ---------------------------------------------------------
-- 1. UTILISATEURS
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS utilisateurs (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    nom             VARCHAR(100) NOT NULL,
    prenom          VARCHAR(100) NOT NULL,
    email           VARCHAR(255) NOT NULL UNIQUE,
    mot_de_passe    VARCHAR(255) NOT NULL,
    role            ENUM('etudiant','enseignant','assistant','doyen','vice_doyen','apparitaire') NOT NULL,
    promotion_id    INT,
    matricule       VARCHAR(50) UNIQUE,
    statut          ENUM('en_ligne','hors_ligne') DEFAULT 'hors_ligne',
    derniere_connexion DATETIME,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- 2. PROMOTIONS
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS promotions (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nom         VARCHAR(255) NOT NULL,
    annee       VARCHAR(20) NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- 3. COURS
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS cours (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    nom             VARCHAR(255) NOT NULL,
    code            VARCHAR(50) NOT NULL UNIQUE,
    description     TEXT,
    promotion_id    INT,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (promotion_id) REFERENCES promotions(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- 4. COURS — ENSEIGNANTS (relation Many-to-Many)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS cours_enseignants (
    cours_id        INT NOT NULL,
    enseignant_id   INT NOT NULL,
    PRIMARY KEY (cours_id, enseignant_id),
    FOREIGN KEY (cours_id)      REFERENCES cours(id) ON DELETE CASCADE,
    FOREIGN KEY (enseignant_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- 5. INSCRIPTIONS (étudiants inscrits à un cours)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS inscriptions (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    etudiant_id     INT NOT NULL,
    cours_id        INT NOT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_inscription (etudiant_id, cours_id),
    FOREIGN KEY (etudiant_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (cours_id)    REFERENCES cours(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- 11. RÉACTIONS (messages)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS reactions (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    message_id      INT NOT NULL,
    utilisateur_id  INT NOT NULL,
    emoji           VARCHAR(50) NOT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_reaction (message_id, utilisateur_id, emoji),
    FOREIGN KEY (message_id)     REFERENCES messages(id) ON DELETE CASCADE,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- 10. FICHIERS (créé avant messages pour la FK)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS fichiers (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    nom_original    VARCHAR(255) NOT NULL,
    nom_stocke      VARCHAR(255) NOT NULL,
    type            VARCHAR(100) NOT NULL,
    taille          INT NOT NULL,
    chemin          VARCHAR(500) NOT NULL,
    uploader_id     INT NOT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (uploader_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- 6. MESSAGES
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS messages (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    expediteur_id   INT NOT NULL,
    type            ENUM('prive','public','mur') NOT NULL DEFAULT 'public',
    contenu         TEXT,
    fichier_id      INT,
    cours_id        INT,
    destinataire_id INT,
    lu              TINYINT(1) DEFAULT 0,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (expediteur_id)   REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (fichier_id)      REFERENCES fichiers(id) ON DELETE SET NULL,
    FOREIGN KEY (cours_id)        REFERENCES cours(id) ON DELETE CASCADE,
    FOREIGN KEY (destinataire_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- 7. CONVOCATIONS
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS convocations (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    expediteur_id   INT NOT NULL,
    objet           VARCHAR(255) NOT NULL,
    date_reunion    DATE NOT NULL,
    heure_reunion   TIME NOT NULL,
    lieu            VARCHAR(255),
    message         TEXT,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (expediteur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- 8. CONVOCATION — DESTINATAIRES
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS convocation_destinataires (
    convocation_id  INT NOT NULL,
    utilisateur_id  INT NOT NULL,
    statut          ENUM('en_attente','accepte','refuse') DEFAULT 'en_attente',
    PRIMARY KEY (convocation_id, utilisateur_id),
    FOREIGN KEY (convocation_id)  REFERENCES convocations(id) ON DELETE CASCADE,
    FOREIGN KEY (utilisateur_id)  REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- 9. VALVE (annonces institutionnelles)
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS valve_annonces (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    titre           VARCHAR(255) NOT NULL,
    contenu         TEXT NOT NULL,
    categorie       ENUM('urgent','convocation','information','academique') NOT NULL DEFAULT 'information',
    apparitaire_id  INT NOT NULL,
    fichier_id      INT,
    date_expiration DATE,
    vues            INT DEFAULT 0,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (apparitaire_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (fichier_id)     REFERENCES fichiers(id) ON DELETE SET NULL
) ENGINE=InnoDB;


