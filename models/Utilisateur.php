<?php
// models/Utilisateur.php

abstract class Utilisateur
{
    protected PDO $db;
    protected ?int $id = null;
    protected string $nom;
    protected string $email;
    protected string $motDePasse;
    protected string $role;
    protected string $statut;

    public function __construct(PDO $database, array $data = [])
    {
        $this->db = $database;
        
        if (!empty($data)) {
            $this->id = $data['id'] ?? null;
            $this->nom = $data['nom'] ?? '';
            $this->email = $data['email'] ?? '';
            $this->motDePasse = $data['mot_de_passe'] ?? '';
            $this->role = $data['role'] ?? '';
            $this->statut = $data['statut'] ?? 'actif';
        }
    }

    /**
     * Méthode abstraite obligatoire : chaque rôle doit définir 
     * ses droits ou son menu spécifique dans l'application.
     */
    abstract public function getDroitsSpecifiques(): array;

    // =========================================================
    // MÉTHODES COMMUNES (Logique métier partagée)
    // =========================================================

    public function verifierMotDePasse(string $password): bool
    {
        return password_verify($password, $this->motDePasse);
    }

    public function estActif(): bool
    {
        return $this->statut === 'actif';
    }

    // =========================================================
    // GETTERS ET SETTERS
    // =========================================================

    public function getId(): ?int { return $this->id; }
    public function getNom(): string { return $this->nom; }
    public function setNom(string $nom): void { $this->nom = $nom; }
    public function getEmail(): string { return $this->email; }
    public function setEmail(string $email): void { $this->email = $email; }
    public function getRole(): string { return $this->role; }
    public function getStatut(): string { return $this->statut; }
    public function setStatut(string $statut): void { $this->statut = $statut; }
}