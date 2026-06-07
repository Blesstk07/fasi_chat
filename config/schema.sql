-- =========================================================
-- BASE DE DONNÉES : FasiChat Classroom
-- Projet PHP POO - Sciences Informatiques
-- =========================================================

CREATE DATABASE IF NOT EXISTS fasichat
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE fasichat;

-- =========================================================
-- TABLE : promotions
-- =========================================================

CREATE TABLE promotions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    description TEXT,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =========================================================
-- TABLE : utilisateurs
-- Tous les rôles du système
-- =========================================================

CREATE TABLE utilisateurs (

    id INT AUTO_INCREMENT PRIMARY KEY,

    nom VARCHAR(100) NOT NULL,

    email VARCHAR(150) NOT NULL UNIQUE,

    mot_de_passe VARCHAR(255) NOT NULL,

    role ENUM(
        'etudiant',
        'enseignant',
        'assistant',
        'doyen',
        'vice_doyen',
        'apparitaire'
    ) NOT NULL,

    promotion_id INT NULL,

    photo_profil VARCHAR(255) NULL,

    statut ENUM('actif', 'inactif') DEFAULT 'actif',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_utilisateur_promotion
    FOREIGN KEY (promotion_id)
    REFERENCES promotions(id)
    ON DELETE SET NULL

);

-- =========================================================
-- TABLE : cours
-- =========================================================

CREATE TABLE cours (

    id INT AUTO_INCREMENT PRIMARY KEY,

    nom VARCHAR(150) NOT NULL,

    description TEXT,

    enseignant_id INT NOT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_cours_enseignant
    FOREIGN KEY (enseignant_id)
    REFERENCES utilisateurs(id)
    ON DELETE CASCADE

);

-- =========================================================
-- TABLE : cours_etudiants
-- Relation MANY TO MANY
-- =========================================================

CREATE TABLE cours_etudiants (

    cours_id INT NOT NULL,

    etudiant_id INT NOT NULL,

    date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (cours_id, etudiant_id),

    CONSTRAINT fk_ce_cours
    FOREIGN KEY (cours_id)
    REFERENCES cours(id)
    ON DELETE CASCADE,

    CONSTRAINT fk_ce_etudiant
    FOREIGN KEY (etudiant_id)
    REFERENCES utilisateurs(id)
    ON DELETE CASCADE

);

-- =========================================================
-- TABLE : messages
-- =========================================================

CREATE TABLE messages (

    id INT AUTO_INCREMENT PRIMARY KEY,

    expediteur_id INT NOT NULL,

    destinataire_id INT NULL,

    cours_id INT NULL,

    contenu TEXT,

    type_message ENUM(
        'prive',
        'public',
        'convocation',
        'mur_pedagogique'
    ) NOT NULL,

    statut ENUM(
        'envoye',
        'lu',
        'supprime'
    ) DEFAULT 'envoye',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_message_expediteur
    FOREIGN KEY (expediteur_id)
    REFERENCES utilisateurs(id)
    ON DELETE CASCADE,

    CONSTRAINT fk_message_destinataire
    FOREIGN KEY (destinataire_id)
    REFERENCES utilisateurs(id)
    ON DELETE CASCADE,

    CONSTRAINT fk_message_cours
    FOREIGN KEY (cours_id)
    REFERENCES cours(id)
    ON DELETE CASCADE

);

-- =========================================================
-- TABLE : fichiers
-- =========================================================

CREATE TABLE fichiers (

    id INT AUTO_INCREMENT PRIMARY KEY,

    message_id INT NOT NULL,

    nom_original VARCHAR(255) NOT NULL,

    nom_stockage VARCHAR(255) NOT NULL,

    chemin VARCHAR(255) NOT NULL,

    type_mime VARCHAR(100) NOT NULL,

    taille BIGINT NOT NULL,

    extension VARCHAR(20),

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_fichier_message
    FOREIGN KEY (message_id)
    REFERENCES messages(id)
    ON DELETE CASCADE

);

-- =========================================================
-- TABLE : convocations
-- =========================================================

CREATE TABLE convocations (

    id INT AUTO_INCREMENT PRIMARY KEY,

    auteur_id INT NOT NULL,

    objet VARCHAR(255) NOT NULL,

    date_reunion DATETIME NOT NULL,

    lieu VARCHAR(255) NOT NULL,

    message TEXT,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_convocation_auteur
    FOREIGN KEY (auteur_id)
    REFERENCES utilisateurs(id)
    ON DELETE CASCADE

);

-- =========================================================
-- TABLE : convocation_destinataires
-- =========================================================

CREATE TABLE convocation_destinataires (

    convocation_id INT NOT NULL,

    utilisateur_id INT NOT NULL,

    statut_lecture ENUM(
        'non_lu',
        'lu'
    ) DEFAULT 'non_lu',

    PRIMARY KEY (
        convocation_id,
        utilisateur_id
    ),

    CONSTRAINT fk_cd_convocation
    FOREIGN KEY (convocation_id)
    REFERENCES convocations(id)
    ON DELETE CASCADE,

    CONSTRAINT fk_cd_utilisateur
    FOREIGN KEY (utilisateur_id)
    REFERENCES utilisateurs(id)
    ON DELETE CASCADE

);

-- =========================================================
-- TABLE : valve_annonces
-- =========================================================

CREATE TABLE valve_annonces (

    id INT AUTO_INCREMENT PRIMARY KEY,

    auteur_id INT NOT NULL,

    titre VARCHAR(255) NOT NULL,

    contenu TEXT NOT NULL,

    date_publication TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    date_expiration DATE NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_valve_auteur
    FOREIGN KEY (auteur_id)
    REFERENCES utilisateurs(id)
    ON DELETE CASCADE

);

-- =========================================================
-- INDEXES POUR PERFORMANCE
-- =========================================================

CREATE INDEX idx_user_role
ON utilisateurs(role);

CREATE INDEX idx_message_type
ON messages(type_message);

CREATE INDEX idx_message_date
ON messages(created_at);

CREATE INDEX idx_convocation_date
ON convocations(date_reunion);

-- =========================================================
-- DONNÉES DE TEST
-- =========================================================

-- Promotions

INSERT INTO promotions (nom, description)
VALUES
('L1 Info', 'Licence 1 Informatique'),
('L2 Info', 'Licence 2 Informatique');

