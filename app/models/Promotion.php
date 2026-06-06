<?php

require_once __DIR__ . '/../core/Database.php';

class Promotion
{
    public static function getById(int $id): ?array
    {
        return Database::fetch("SELECT * FROM promotions WHERE id = ?", [$id]);
    }

    public static function getAll(): array
    {
        return Database::fetchAll("SELECT * FROM promotions ORDER BY annee DESC, nom");
    }

    public static function creer(array $data): int
    {
        return Database::insert(
            "INSERT INTO promotions (nom, annee) VALUES (?, ?)",
            [$data['nom'], $data['annee']]
        );
    }

    public static function getEtudiants(int $promotionId): array
    {
        return Database::fetchAll(
            "SELECT DISTINCT u.* FROM utilisateurs u
             JOIN inscriptions i ON u.id = i.etudiant_id
             JOIN cours c ON i.cours_id = c.id
             WHERE c.promotion_id = ? AND u.role = 'etudiant'
             ORDER BY u.nom, u.prenom",
            [$promotionId]
        );
    }
}
