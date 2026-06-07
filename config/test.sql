INSERT INTO utilisateurs
(
    nom,
    email,
    mot_de_passe,
    role,
    promotion_id,
    photo_profil,
    statut,
    created_at,
    updated_at
)

VALUES

-- ==================================================
-- ETUDIANTS
-- Mot de passe : 123456
-- ==================================================

(
    'Jean Kabongo',
    'jean@student.com',
    '$2y$12$ZbG7I8DM57KNs11vgioWMeF8JOr7nSdCgP.zE52VinQgbX1XNWJH6',
    'etudiant',
    1,
    'default.jpg',
    'actif',
    NOW(),
    NOW()
),

(
    'Sarah Ilunga',
    'sarah@student.com',
    '$2y$12$ZbG7I8DM57KNs11vgioWMeF8JOr7nSdCgP.zE52VinQgbX1XNWJH6',
    'etudiant',
    1,
    'default.jpg',
    'actif',
    NOW(),
    NOW()
),

(
    'David Mukendi',
    'david@student.com',
    '$2y$12$ZbG7I8DM57KNs11vgioWMeF8JOr7nSdCgP.zE52VinQgbX1XNWJH6',
    'etudiant',
    2,
    'default.jpg',
    'actif',
    NOW(),
    NOW()
),


-- ==================================================
-- ENSEIGNANTS
-- Mot de passe : 123456
-- ==================================================

(
    'Professeur Malu',
    'malu@fasi.com',
    '$2y$12$ZbG7I8DM57KNs11vgioWMeF8JOr7nSdCgP.zE52VinQgbX1XNWJH6',
    'enseignant',
    NULL,
    'default.jpg',
    'actif',
    NOW(),
    NOW()
),

(
    'Professeur Nzambe',
    'nzambe@fasi.com',
    '$2y$12$ZbG7I8DM57KNs11vgioWMeF8JOr7nSdCgP.zE52VinQgbX1XNWJH6',
    'enseignant',
    NULL,
    'default.jpg',
    'actif',
    NOW(),
    NOW()
),


-- ==================================================
-- ASSISTANTS
-- Mot de passe : 123456
-- ==================================================

(
    'Assistant Kevin',
    'kevin@fasi.com',
    '$2y$12$ZbG7I8DM57KNs11vgioWMeF8JOr7nSdCgP.zE52VinQgbX1XNWJH6',
    'assistant',
    NULL,
    'default.jpg',
    'actif',
    NOW(),
    NOW()
),

(
    'Assistant Grâce',
    'grace@fasi.com',
    '$2y$12$ZbG7I8DM57KNs11vgioWMeF8JOr7nSdCgP.zE52VinQgbX1XNWJH6',
    'assistant',
    NULL,
    'default.jpg',
    'actif',
    NOW(),
    NOW()
),


-- ==================================================
-- ADMINISTRATION
-- Mot de passe : 123456
-- ==================================================

(
    'Doyen Tshibangu',
    'doyen@fasi.com',
    '$2y$12$ZbG7I8DM57KNs11vgioWMeF8JOr7nSdCgP.zE52VinQgbX1XNWJH6',
    'doyen',
    NULL,
    'default.jpg',
    'actif',
    NOW(),
    NOW()
),

(
    'Vice Doyen Mbuyi',
    'vice@fasi.com',
    '$2y$12$ZbG7I8DM57KNs11vgioWMeF8JOr7nSdCgP.zE52VinQgbX1XNWJH6',
    'vice_doyen',
    NULL,
    'default.jpg',
    'actif',
    NOW(),
    NOW()
),

(
    'Apparitaire Kanku',
    'apparitaire@fasi.com',
    '$2y$12$ZbG7I8DM57KNs11vgioWMeF8JOr7nSdCgP.zE52VinQgbX1XNWJH6',
    'apparitaire',
    NULL,
    'default.jpg',
    'actif',
    NOW(),
    NOW()
);

-- ==================================================
-- COURS DE TEST
-- ==================================================

INSERT INTO cours (nom, description, enseignant_id)
VALUES
('Programmation Web en PHP', 'Cours de PHP orienté objet et PDO', 4),
('Base de données', 'Modélisation relationnelle et SQL', 5);

-- ==================================================
-- INSCRIPTIONS AUX COURS
-- ==================================================

INSERT INTO cours_etudiants (cours_id, etudiant_id)
VALUES
(1, 1),
(1, 2),
(2, 1),
(2, 3);

-- ==================================================
-- MESSAGES DE TEST
-- ==================================================

INSERT INTO messages (expediteur_id, destinataire_id, cours_id, contenu, type_message)
VALUES
(1, 2, NULL, 'Salut Sarah, tu as compris le TP de PHP ?', 'prive'),
(4, NULL, 1, 'Bonjour à tous, le TP FasiChat doit respecter la POO native.', 'public'),
(4, NULL, 1, 'Annonce du mur pédagogique : préparez votre schéma de base de données.', 'mur_pedagogique');
