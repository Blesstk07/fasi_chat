<?php

require_once __DIR__ . '/Utilisateur.php';

class Etudiant extends Utilisateur
{
    public function __construct(array $data = [])
    {
        parent::__construct($data);
        $this->role = 'etudiant';
    }

    public function getCours(): array
    {
        return Database::fetchAll(
            "SELECT c.*, p.nom AS promotion_nom
             FROM cours c
             JOIN inscriptions i ON c.id = i.cours_id
             LEFT JOIN promotions p ON c.promotion_id = p.id
             WHERE i.etudiant_id = ?
             ORDER BY c.nom",
            [$this->id]
        );
    }

    public function getMessagesPublics(int $coursId): array
    {
        return Database::fetchAll(
            "SELECT m.*, u.nom, u.prenom, u.role
             FROM messages m
             JOIN utilisateurs u ON m.expediteur_id = u.id
             WHERE m.cours_id = ? AND m.type = 'public'
             ORDER BY m.created_at ASC",
            [$coursId]
        );
    }

    public function getMessagesPrives(int $autreId): array
    {
        return Database::fetchAll(
            "SELECT m.*, u.nom, u.prenom, u.role
             FROM messages m
             JOIN utilisateurs u ON m.expediteur_id = u.id
             WHERE (m.expediteur_id = ? AND m.destinataire_id = ?)
                OR (m.expediteur_id = ? AND m.destinataire_id = ?)
             ORDER BY m.created_at ASC",
            [$this->id, $autreId, $autreId, $this->id]
        );
    }

    public function getCamaradesPromotion(): array
    {
        return Database::fetchAll(
            "SELECT DISTINCT u.* FROM utilisateurs u
             JOIN inscriptions i1 ON u.id = i1.etudiant_id
             WHERE i1.cours_id IN (
                SELECT cours_id FROM inscriptions WHERE etudiant_id = ?
             ) AND u.id != ? AND u.role = 'etudiant'
             ORDER BY u.nom, u.prenom",
            [$this->id, $this->id]
        );
    }

    public function getEnseignants(): array
    {
        return Database::fetchAll(
            "SELECT DISTINCT u.* FROM utilisateurs u
             JOIN cours_enseignants ce ON u.id = ce.enseignant_id
             JOIN inscriptions i ON ce.cours_id = i.cours_id
             WHERE i.etudiant_id = ?
             ORDER BY u.nom, u.prenom",
            [$this->id]
        );
    }
}
