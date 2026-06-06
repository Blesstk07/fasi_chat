<?php

require_once __DIR__ . '/../core/Database.php';

class Message
{
    public static function getById(int $id): ?array
    {
        return Database::fetch(
            "SELECT m.*, u.nom, u.prenom, u.role
             FROM messages m
             JOIN utilisateurs u ON m.expediteur_id = u.id
             WHERE m.id = ?",
            [$id]
        );
    }

    public static function getByCours(int $coursId, string $type = 'public', int $limit = 100): array
    {
        return Database::fetchAll(
            "SELECT m.*, u.nom, u.prenom, u.role, f.nom_original, f.taille, f.type AS fichier_type
             FROM messages m
             JOIN utilisateurs u ON m.expediteur_id = u.id
             LEFT JOIN fichiers f ON m.fichier_id = f.id
             WHERE m.cours_id = ? AND m.type = ?
             ORDER BY m.created_at ASC
             LIMIT ?",
            [$coursId, $type, $limit]
        );
    }

    public static function getPrive(int $userId1, int $userId2, int $limit = 100): array
    {
        return Database::fetchAll(
            "SELECT m.*, u.nom, u.prenom, u.role
             FROM messages m
             JOIN utilisateurs u ON m.expediteur_id = u.id
             WHERE (m.expediteur_id = ? AND m.destinataire_id = ?)
                OR (m.expediteur_id = ? AND m.destinataire_id = ?)
             ORDER BY m.created_at ASC
             LIMIT ?",
            [$userId1, $userId2, $userId2, $userId1, $limit]
        );
    }

    public static function getNonLus(int $userId): array
    {
        return Database::fetchAll(
            "SELECT COUNT(*) AS nb_non_lus, m.expediteur_id, u.nom, u.prenom
             FROM messages m
             JOIN utilisateurs u ON m.expediteur_id = u.id
             WHERE m.destinataire_id = ? AND m.lu = 0
             GROUP BY m.expediteur_id",
            [$userId]
        );
    }

    public static function marquerLu(int $messageId): int
    {
        return Database::execute("UPDATE messages SET lu = 1 WHERE id = ?", [$messageId]);
    }

    public static function marquerTousLus(int $userId, int $expediteurId): int
    {
        return Database::execute(
            "UPDATE messages SET lu = 1 WHERE destinataire_id = ? AND expediteur_id = ? AND lu = 0",
            [$userId, $expediteurId]
        );
    }
}
