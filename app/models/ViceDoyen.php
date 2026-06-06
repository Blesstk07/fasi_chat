<?php

require_once __DIR__ . '/Utilisateur.php';

class ViceDoyen extends Utilisateur
{
    public function __construct(array $data = [])
    {
        parent::__construct($data);
        $this->role = 'vice_doyen';
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

    public function getMessagesDoyen(): array
    {
        return Database::fetchAll(
            "SELECT m.*, u.nom, u.prenom
             FROM messages m
             JOIN utilisateurs u ON m.expediteur_id = u.id
             WHERE (m.expediteur_id = ? AND m.destinataire_id = (SELECT id FROM utilisateurs WHERE role = 'doyen' LIMIT 1))
                OR (m.expediteur_id = (SELECT id FROM utilisateurs WHERE role = 'doyen' LIMIT 1) AND m.destinataire_id = ?)
             ORDER BY m.created_at ASC",
            [$this->id, $this->id]
        );
    }

    public function getProjetsRecherche(): array
    {
        return Database::fetchAll(
            "SELECT * FROM fichiers WHERE uploader_id IN (
                SELECT id FROM utilisateurs WHERE role = 'enseignant'
            ) ORDER BY created_at DESC LIMIT 10"
        );
    }
}
