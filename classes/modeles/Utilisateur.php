<?php
namespace Classes\Modeles;

use Classes\Services\BaseDeDonnees;

abstract class Utilisateur {
    protected ?int $id = null;
    protected string $role = '';
    protected string $nomComplet = '';
    protected string $identification = '';
    protected string $courriel = '';
    protected string $motDePasse = '';
    protected ?string $promotion = null;
    protected ?string $texteAvatar = null;
    protected string $statut = 'hors-ligne';
    protected \DateTime $creeLe;
    
    public function __construct(array $data = []) {
        $this->creeLe = new \DateTime();
        if (!empty($data)) {
            $this->hydrate($data);
        }
    }
    
    public function hydrate(array $data): self {
        $mapping = [
            'id' => 'id',
            'role' => 'role',
            'nom_complet' => 'nomComplet',
            'identification' => 'identification',
            'courriel' => 'courriel',
            'mot_de_passe' => 'motDePasse',
            'promotion' => 'promotion',
            'texte_avatar' => 'texteAvatar',
            'statut' => 'statut',
            'cree_le' => 'creeLe'
        ];
        
        foreach ($mapping as $dbField => $property) {
            if (isset($data[$dbField])) {
                if ($property === 'creeLe' && $data[$dbField]) {
                    $this->$property = new \DateTime($data[$dbField]);
                } else {
                    $this->$property = $data[$dbField];
                }
            }
        }
        return $this;
    }
    
    // Getters
    public function getId(): ?int { return $this->id; }
    public function getRole(): string { return $this->role; }
    public function getNomComplet(): string { return $this->nomComplet; }
    public function getIdentification(): string { return $this->identification; }
    public function getCourriel(): string { return $this->courriel; }
    public function getEmail(): string { return $this->courriel; }
    public function getPromotion(): ?string { return $this->promotion; }
    public function getTexteAvatar(): ?string { return $this->texteAvatar; }
    public function getStatut(): string { return $this->statut; }
    
    public function setMotDePasse(string $mdp): self {
        $this->motDePasse = $mdp;
        return $this;
    }
    
    /**
     * Vérification du mot de passe - Version pour mots de passe hashés
     * Utilise password_verify() car vos mots de passe sont hashés avec bcrypt
     */
    public function verifierMotDePasse(string $mdp): bool {
        // Le mot de passe stocké est déjà hashé (commence par $2y$)
        return password_verify($mdp, $this->motDePasse);
    }
    
    // Mise à jour du statut
    public function mettreAJourStatut(string $statut): bool {
        $db = BaseDeDonnees::getInstance();
        $sql = "UPDATE utilisateur SET statut = ? WHERE id = ?";
        $stmt = $db->prepare($sql);
        return $stmt->execute([$statut, $this->id]);
    }
    
    // Récupérer un utilisateur par ID
    public static function findById(int $id): ?self {
        $db = BaseDeDonnees::getInstance();
        $stmt = $db->query("SELECT * FROM utilisateur WHERE id = ?", [$id]);
        $data = $stmt->fetch();
        
        if (!$data) {
            return null;
        }
        
        return self::createFromData($data);
    }
    
    // Récupérer par email
    public static function findByEmail(string $email): ?self {
        $db = BaseDeDonnees::getInstance();
        $stmt = $db->query("SELECT * FROM utilisateur WHERE courriel = ?", [$email]);
        $data = $stmt->fetch();
        
        if (!$data) {
            return null;
        }
        
        return self::createFromData($data);
    }
    
    // Récupérer par identifiant
    public static function findByIdentification(string $identification): ?self {
        $db = BaseDeDonnees::getInstance();
        $stmt = $db->query("SELECT * FROM utilisateur WHERE identification = ?", [$identification]);
        $data = $stmt->fetch();
        
        if (!$data) {
            return null;
        }
        
        return self::createFromData($data);
    }
    
    // Factory
    private static function createFromData(array $data): ?self {
        $roleMap = [
            'etudiant' => 'Etudiant',
            'enseignant' => 'Enseignant',
            'assistant' => 'Assistant',
            'doyen' => 'Doyen',
            'vice_doyen' => 'ViceDoyen',
            'apparitaire' => 'Apparitaire'
        ];
        
        $className = "\\Classes\\Modeles\\" . ($roleMap[$data['role']] ?? 'Utilisateur');
        
        if (class_exists($className)) {
            return new $className($data);
        }
        
        return null;
    }
    
    // Méthodes abstraites pour les classes filles
    abstract public function getPermissions(): array;
    abstract public function peutEnvoyerMessageA(self $destinataire): bool;
    abstract public function getTableauBord(): array;
}