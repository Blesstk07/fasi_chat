<?php

require_once __DIR__ . '/../core/Database.php';

class ValveAnnonce
{
    public static function getById(int $id): ?array
    {
        return Database::fetch(
            "SELECT va.*, u.nom, u.prenom,
                    (SELECT nom_original FROM fichiers WHERE id = va.fichier_id) AS fichier_nom
             FROM valve_annonces va
             JOIN utilisateurs u ON va.apparitaire_id = u.id
             WHERE va.id = ?",
            [$id]
        );
    }

    public static function getAll(?string $categorie = null): array
    {
        $sql = "SELECT va.*, u.nom, u.prenom,
                       (SELECT nom_original FROM fichiers WHERE id = va.fichier_id) AS fichier_nom
                FROM valve_annonces va
                JOIN utilisateurs u ON va.apparitaire_id = u.id";
        $params = [];

        if ($categorie) {
            $sql .= " WHERE va.categorie = ?";
            $params[] = $categorie;
        }

        $sql .= " ORDER BY
                    CASE WHEN va.categorie = 'urgent' THEN 0 ELSE 1 END,
                    va.created_at DESC";

        return Database::fetchAll($sql, $params);
    }

    public static function incrementerVues(int $id): void
    {
        Database::execute("UPDATE valve_annonces SET vues = vues + 1 WHERE id = ?", [$id]);
    }

    public static function getStats(): array
    {
        $total = Database::fetch("SELECT COUNT(*) AS total FROM valve_annonces")['total'];
        $parCategorie = Database::fetchAll(
            "SELECT categorie, COUNT(*) AS total FROM valve_annonces GROUP BY categorie"
        );
        $vues = Database::fetch("SELECT COALESCE(SUM(vues), 0) AS total FROM valve_annonces")['total'];
        return compact('total', 'parCategorie', 'vues');
    }

    public static function getRecent(int $limit = 5): array
    {
        return Database::fetchAll(
            "SELECT va.*, u.nom, u.prenom
             FROM valve_annonces va
             JOIN utilisateurs u ON va.apparitaire_id = u.id
             ORDER BY va.created_at DESC
             LIMIT ?",
            [$limit]
        );
    }
}
