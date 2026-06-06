<?php

require_once __DIR__ . '/../core/Database.php';

class Reaction
{
    public static function toggle(int $messageId, int $userId, string $emoji): array
    {
        $existing = Database::fetch(
            "SELECT id FROM reactions WHERE message_id = ? AND utilisateur_id = ? AND emoji = ?",
            [$messageId, $userId, $emoji]
        );
        if ($existing) {
            Database::execute("DELETE FROM reactions WHERE id = ?", [$existing['id']]);
            return ['action' => 'removed'];
        }
        Database::insert(
            "INSERT INTO reactions (message_id, utilisateur_id, emoji) VALUES (?, ?, ?)",
            [$messageId, $userId, $emoji]
        );
        return ['action' => 'added'];
    }

    public static function getByMessage(int $messageId): array
    {
        return Database::fetchAll(
            "SELECT r.*, u.nom, u.prenom
             FROM reactions r
             JOIN utilisateurs u ON r.utilisateur_id = u.id
             WHERE r.message_id = ?
             ORDER BY r.created_at",
            [$messageId]
        );
    }

    public static function getByCours(int $coursId): array
    {
        return Database::fetchAll(
            "SELECT r.*, u.nom, u.prenom
             FROM reactions r
             JOIN messages m ON r.message_id = m.id
             JOIN utilisateurs u ON r.utilisateur_id = u.id
             WHERE m.cours_id = ?
             ORDER BY r.created_at",
            [$coursId]
        );
    }
}
