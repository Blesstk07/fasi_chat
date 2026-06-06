<?php

require_once __DIR__ . '/../core/Database.php';

class Cours
{
    public static function getById(int $id): ?array
    {
        return Database::fetch(
            "SELECT c.*, p.nom AS promotion_nom
             FROM cours c
             LEFT JOIN promotions p ON c.promotion_id = p.id
             WHERE c.id = ?",
            [$id]
        );
    }

    public static function getAll(): array
    {
        return Database::fetchAll(
            "SELECT c.*, p.nom AS promotion_nom
             FROM cours c
             LEFT JOIN promotions p ON c.promotion_id = p.id
             ORDER BY c.nom"
        );
    }

    public static function getByEnseignant(int $enseignantId): array
    {
        return Database::fetchAll(
            "SELECT c.*, p.nom AS promotion_nom
             FROM cours c
             JOIN cours_enseignants ce ON c.id = ce.cours_id
             LEFT JOIN promotions p ON c.promotion_id = p.id
             WHERE ce.enseignant_id = ?
             ORDER BY c.nom",
            [$enseignantId]
        );
    }

    public static function getByEtudiant(int $etudiantId): array
    {
        return Database::fetchAll(
            "SELECT c.*, p.nom AS promotion_nom
             FROM cours c
             JOIN inscriptions i ON c.id = i.cours_id
             LEFT JOIN promotions p ON c.promotion_id = p.id
             WHERE i.etudiant_id = ?
             ORDER BY c.nom",
            [$etudiantId]
        );
    }

    public static function getByPromotion(int $promotionId): array
    {
        return Database::fetchAll(
            "SELECT * FROM cours WHERE promotion_id = ? ORDER BY nom",
            [$promotionId]
        );
    }

    public static function creer(array $data): int
    {
        return Database::insert(
            "INSERT INTO cours (nom, code, description, promotion_id) VALUES (?, ?, ?, ?)",
            [$data['nom'], $data['code'], $data['description'] ?? null, $data['promotion_id'] ?? null]
        );
    }

    public static function inscrire(int $etudiantId, int $coursId): array
    {
        $cours = self::getById($coursId);
        if (!$cours) {
            return ['success' => false, 'error' => 'Cours introuvable.'];
        }

        $promoId = $cours['promotion_id'];

        $existingPromo = Database::fetch(
            "SELECT DISTINCT c.promotion_id FROM inscriptions i
             JOIN cours c ON i.cours_id = c.id
             WHERE i.etudiant_id = ? AND c.promotion_id IS NOT NULL
             LIMIT 1",
            [$etudiantId]
        );

        if ($existingPromo && $existingPromo['promotion_id'] != $promoId) {
            return ['success' => false, 'error' => 'Ce cours n\'est pas dans votre promotion.'];
        }

        try {
            Database::insert(
                "INSERT IGNORE INTO inscriptions (etudiant_id, cours_id) VALUES (?, ?)",
                [$etudiantId, $coursId]
            );
            return ['success' => true, 'error' => ''];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => 'Erreur lors de l\'inscription.'];
        }
    }

    public static function estInscrit(int $etudiantId, int $coursId): bool
    {
        $row = Database::fetch(
            "SELECT 1 FROM inscriptions WHERE etudiant_id = ? AND cours_id = ?",
            [$etudiantId, $coursId]
        );
        return (bool)$row;
    }

    public static function getEnseignants(int $coursId): array
    {
        return Database::fetchAll(
            "SELECT u.* FROM utilisateurs u
             JOIN cours_enseignants ce ON u.id = ce.enseignant_id
             WHERE ce.cours_id = ?",
            [$coursId]
        );
    }

    public static function getEtudiants(int $coursId): array
    {
        return Database::fetchAll(
            "SELECT u.* FROM utilisateurs u
             JOIN inscriptions i ON u.id = i.etudiant_id
             WHERE i.cours_id = ? AND u.role = 'etudiant'
             ORDER BY u.nom, u.prenom",
            [$coursId]
        );
    }
}
