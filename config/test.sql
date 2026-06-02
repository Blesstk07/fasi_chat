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
    '$2y$10$wH7K8K9xKj2vK1k6Qn5q0e8nB4r4j5D9xM1lG7YyQWmK5vX8RjY9C',
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
    '$2y$10$wH7K8K9xKj2vK1k6Qn5q0e8nB4r4j5D9xM1lG7YyQWmK5vX8RjY9C',
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
    '$2y$10$wH7K8K9xKj2vK1k6Qn5q0e8nB4r4j5D9xM1lG7YyQWmK5vX8RjY9C',
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
    '$2y$10$wH7K8K9xKj2vK1k6Qn5q0e8nB4r4j5D9xM1lG7YyQWmK5vX8RjY9C',
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
    '$2y$10$wH7K8K9xKj2vK1k6Qn5q0e8nB4r4j5D9xM1lG7YyQWmK5vX8RjY9C',
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
    '$2y$10$wH7K8K9xKj2vK1k6Qn5q0e8nB4r4j5D9xM1lG7YyQWmK5vX8RjY9C',
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
    '$2y$10$wH7K8K9xKj2vK1k6Qn5q0e8nB4r4j5D9xM1lG7YyQWmK5vX8RjY9C',
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
    '$2y$10$wH7K8K9xKj2vK1k6Qn5q0e8nB4r4j5D9xM1lG7YyQWmK5vX8RjY9C',
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
    '$2y$10$wH7K8K9xKj2vK1k6Qn5q0e8nB4r4j5D9xM1lG7YyQWmK5vX8RjY9C',
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
    '$2y$10$wH7K8K9xKj2vK1k6Qn5q0e8nB4r4j5D9xM1lG7YyQWmK5vX8RjY9C',
    'apparitaire',
    NULL,
    'default.jpg',
    'actif',
    NOW(),
    NOW()
);