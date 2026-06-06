<?php

require_once __DIR__ . '/Utilisateur.php';

class Apparitaire extends Utilisateur
{
    public function __construct(array $data = [])
    {
        parent::__construct($data);
        $this->role = 'apparitaire';
    }

    public function publierAnnonce(array $data): int
    {
        return Database::insert(
            "INSERT INTO valve_annonces (titre, contenu, categorie, apparitaire_id, fichier_id, date_expiration)
             VALUES (?, ?, ?, ?, ?, ?)",
            [
                $data['titre'],
                $data['contenu'],
                $data['categorie'] ?? 'information',
                $this->id,
                $data['fichier_id'] ?? null,
                $data['date_expiration'] ?? null,
            ]
        );
    }

    public function modifierAnnonce(int $annonceId, array $data): int
    {
        return Database::execute(
            "UPDATE valve_annonces
             SET titre = ?, contenu = ?, categorie = ?, fichier_id = ?, date_expiration = ?
             WHERE id = ? AND apparitaire_id = ?",
            [
                $data['titre'],
                $data['contenu'],
                $data['categorie'] ?? 'information',
                $data['fichier_id'] ?? null,
                $data['date_expiration'] ?? null,
                $annonceId,
                $this->id,
            ]
        );
    }

    public function supprimerAnnonce(int $annonceId): int
    {
        return Database::execute(
            "DELETE FROM valve_annonces WHERE id = ? AND apparitaire_id = ?",
            [$annonceId, $this->id]
        );
    }

    public function getAnnonces(): array
    {
        return Database::fetchAll(
            "SELECT va.*,
                    (SELECT COUNT(*) FROM fichiers WHERE id = va.fichier_id) AS a_fichier
             FROM valve_annonces va
             WHERE va.apparitaire_id = ?
             ORDER BY va.created_at DESC",
            [$this->id]
        );
    }

    public function getStatsValve(): array
    {
        $total = Database::fetch("SELECT COUNT(*) AS total FROM valve_annonces")['total'];
        $urgences = Database::fetch(
            "SELECT COUNT(*) AS total FROM valve_annonces WHERE categorie = 'urgent'"
        )['total'];
        $vues = Database::fetch("SELECT COALESCE(SUM(vues), 0) AS total FROM valve_annonces")['total'];
        $convocations = Database::fetch(
            "SELECT COUNT(*) AS total FROM valve_annonces WHERE categorie = 'convocation'"
        )['total'];
        return compact('total', 'urgences', 'vues', 'convocations');
    }
}
