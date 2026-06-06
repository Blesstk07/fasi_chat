<?php

require_once __DIR__ . '/../core/Database.php';

class Convocation
{
    public static function getById(int $id): ?array
    {
        return Database::fetch(
            "SELECT c.*, u.nom, u.prenom, u.role AS expediteur_role
             FROM convocations c
             JOIN utilisateurs u ON c.expediteur_id = u.id
             WHERE c.id = ?",
            [$id]
        );
    }

    public static function getAll(int $limit = 50): array
    {
        return Database::fetchAll(
            "SELECT c.*, u.nom, u.prenom, u.role AS expediteur_role,
                    (SELECT COUNT(*) FROM convocation_destinataires WHERE convocation_id = c.id) AS nb_destinataires
             FROM convocations c
             JOIN utilisateurs u ON c.expediteur_id = u.id
             ORDER BY c.date_reunion DESC, c.heure_reunion DESC
             LIMIT ?",
            [$limit]
        );
    }

    public static function getByDestinataire(int $userId): array
    {
        return Database::fetchAll(
            "SELECT c.*, u.nom, u.prenom, u.role AS expediteur_role, cd.statut AS statut_convocation
             FROM convocations c
             JOIN utilisateurs u ON c.expediteur_id = u.id
             JOIN convocation_destinataires cd ON c.id = cd.convocation_id
             WHERE cd.utilisateur_id = ?
             ORDER BY c.date_reunion DESC",
            [$userId]
        );
    }

    public static function getByExpediteur(int $userId): array
    {
        return Database::fetchAll(
            "SELECT c.*,
                    (SELECT COUNT(*) FROM convocation_destinataires WHERE convocation_id = c.id) AS nb_destinataires
             FROM convocations c
             WHERE c.expediteur_id = ?
             ORDER BY c.date_reunion DESC",
            [$userId]
        );
    }

    public static function repondre(int $convocationId, int $userId, string $statut): int
    {
        return Database::execute(
            "UPDATE convocation_destinataires SET statut = ? WHERE convocation_id = ? AND utilisateur_id = ?",
            [$statut, $convocationId, $userId]
        );
    }
}
