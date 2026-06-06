<?php

require_once __DIR__ . '/Utilisateur.php';

class Enseignant extends Utilisateur
{
    public function __construct(array $data = [])
    {
        parent::__construct($data);
        $this->role = 'enseignant';
    }

    public function getCours(): array
    {
        return Database::fetchAll(
            "SELECT c.*, p.nom AS promotion_nom
             FROM cours c
             JOIN cours_enseignants ce ON c.id = ce.cours_id
             LEFT JOIN promotions p ON c.promotion_id = p.id
             WHERE ce.enseignant_id = ?
             ORDER BY c.nom",
            [$this->id]
        );
    }

    public function getEtudiants(): array
    {
        return Database::fetchAll(
            "SELECT DISTINCT u.*
             FROM utilisateurs u
             JOIN inscriptions i ON u.id = i.etudiant_id
             JOIN cours_enseignants ce ON i.cours_id = ce.cours_id
             WHERE ce.enseignant_id = ? AND u.role = 'etudiant'
             ORDER BY u.nom, u.prenom",
            [$this->id]
        );
    }

    public function getEtudiantsByCours(int $coursId): array
    {
        return Database::fetchAll(
            "SELECT u.*
             FROM utilisateurs u
             JOIN inscriptions i ON u.id = i.etudiant_id
             WHERE i.cours_id = ? AND u.role = 'etudiant'
             ORDER BY u.nom, u.prenom",
            [$coursId]
        );
    }

    public function publierMurPedagogique(string $contenu, int $coursId): int
    {
        return $this->envoyerMessage([
            'type'     => 'mur',
            'contenu'  => $contenu,
            'cours_id' => $coursId,
        ]);
    }

    public function getMessagesMur(int $coursId): array
    {
        return Database::fetchAll(
            "SELECT m.*, u.nom, u.prenom, u.role, f.nom_original, f.taille
             FROM messages m
             JOIN utilisateurs u ON m.expediteur_id = u.id
             LEFT JOIN fichiers f ON m.fichier_id = f.id
             WHERE m.cours_id = ? AND m.type = 'mur'
             ORDER BY m.created_at DESC",
            [$coursId]
        );
    }

    public function getCollegues(): array
    {
        return Database::fetchAll(
            "SELECT * FROM utilisateurs
             WHERE (role = 'enseignant' OR role = 'assistant') AND id != ?
             ORDER BY nom, prenom",
            [$this->id]
        );
    }
}
