<?php

require_once __DIR__ . '/Utilisateur.php';

class Doyen extends Utilisateur
{
    public function __construct(array $data = [])
    {
        parent::__construct($data);
        $this->role = 'doyen';
    }

    public function convoquerReunion(array $data): int
    {
        $convId = Database::insert(
            "INSERT INTO convocations (expediteur_id, objet, date_reunion, heure_reunion, lieu, message)
             VALUES (?, ?, ?, ?, ?, ?)",
            [
                $this->id,
                $data['objet'],
                $data['date_reunion'],
                $data['heure_reunion'],
                $data['lieu'],
                $data['message'] ?? null,
            ]
        );
        $destinataires = Database::fetchAll(
            "SELECT id FROM utilisateurs WHERE role IN ('enseignant', 'assistant')"
        );
        foreach ($destinataires as $dest) {
            Database::execute(
                "INSERT INTO convocation_destinataires (convocation_id, utilisateur_id) VALUES (?, ?)",
                [$convId, $dest['id']]
            );
        }
        return $convId;
    }

    public function getConvocations(): array
    {
        return Database::fetchAll(
            "SELECT c.*,
                    (SELECT COUNT(*) FROM convocation_destinataires WHERE convocation_id = c.id) AS nb_destinataires
             FROM convocations c
             WHERE c.expediteur_id = ?
             ORDER BY c.created_at DESC",
            [$this->id]
        );
    }

    public function getTousUtilisateurs(): array
    {
        return Database::fetchAll(
            "SELECT * FROM utilisateurs ORDER BY role, nom, prenom"
        );
    }

    public function getStats(): array
    {
        $stats = [];
        $stats['etudiants'] = Database::fetch(
            "SELECT COUNT(*) AS total FROM utilisateurs WHERE role = 'etudiant'"
        )['total'];
        $stats['enseignants'] = Database::fetch(
            "SELECT COUNT(*) AS total FROM utilisateurs WHERE role = 'enseignant'"
        )['total'];
        $stats['cours'] = Database::fetch("SELECT COUNT(*) AS total FROM cours")['total'];
        $stats['convocations'] = Database::fetch(
            "SELECT COUNT(*) AS total FROM convocations WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())"
        )['total'];
        return $stats;
    }
}
