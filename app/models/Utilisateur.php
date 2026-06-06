<?php

require_once __DIR__ . '/../core/Database.php';

abstract class Utilisateur
{
    protected ?int $id;
    protected string $nom;
    protected string $prenom;
    protected string $email;
    protected string $motDePasse;
    protected string $role;
    protected ?string $matricule;
    protected ?int $promotionId;
    protected string $statut;
    protected ?string $derniereConnexion;
    protected PDO $db;

    public function __construct(array $data = [])
    {
        $this->db = Database::getInstance();
        $this->id = $data['id'] ?? null;
        $this->nom = $data['nom'] ?? '';
        $this->prenom = $data['prenom'] ?? '';
        $this->email = $data['email'] ?? '';
        $this->motDePasse = $data['mot_de_passe'] ?? '';
        $this->role = $data['role'] ?? '';
        $this->matricule = $data['matricule'] ?? null;
        $this->promotionId = $data['promotion_id'] ?? null;
        $this->statut = $data['statut'] ?? 'hors_ligne';
        $this->derniereConnexion = $data['derniere_connexion'] ?? null;
    }

    public static function getById(int $id): ?static
    {
        $row = Database::fetch("SELECT * FROM utilisateurs WHERE id = ?", [$id]);
        if (!$row) return null;
        return new static($row);
    }

    public static function getByEmail(string $email): ?static
    {
        $row = Database::fetch("SELECT * FROM utilisateurs WHERE email = ?", [$email]);
        if (!$row) return null;
        $class = self::getRoleClass($row['role']);
        return new $class($row);
    }

    protected static function getRoleClass(string $role): string
    {
        return match ($role) {
            'etudiant'    => Etudiant::class,
            'enseignant'  => Enseignant::class,
            'assistant'   => Assistant::class,
            'doyen'       => Doyen::class,
            'vice_doyen'  => ViceDoyen::class,
            'apparitaire' => Apparitaire::class,
            default       => throw new InvalidArgumentException("Rôle inconnu: $role"),
        };
    }

    public static function getAllByRole(string $role): array
    {
        return Database::fetchAll("SELECT * FROM utilisateurs WHERE role = ? ORDER BY nom, prenom", [$role]);
    }

    public static function getAll(): array
    {
        return Database::fetchAll("SELECT * FROM utilisateurs ORDER BY nom, prenom");
    }

    public function seConnecter(string $email, string $password): bool
    {
        $user = static::getByEmail($email);
        if (!$user || !password_verify($password, $user->motDePasse)) {
            return false;
        }
        Database::execute(
            "UPDATE utilisateurs SET statut = 'en_ligne', derniere_connexion = NOW() WHERE id = ?",
            [$user->id]
        );
        $_SESSION['user'] = [
            'id'        => $user->id,
            'nom'       => $user->nom,
            'prenom'    => $user->prenom,
            'email'     => $user->email,
            'role'      => $user->role,
            'matricule' => $user->matricule,
        ];
        return true;
    }

    public function seDeconnecter(): void
    {
        if ($this->id) {
            Database::execute(
                "UPDATE utilisateurs SET statut = 'hors_ligne' WHERE id = ?",
                [$this->id]
            );
        }
        Session::destroy();
    }

    public function envoyerMessage(array $data): int
    {
        return Database::insert(
            "INSERT INTO messages (expediteur_id, type, contenu, fichier_id, cours_id, destinataire_id)
             VALUES (?, ?, ?, ?, ?, ?)",
            [
                $this->id,
                $data['type'] ?? 'public',
                $data['contenu'] ?? '',
                $data['fichier_id'] ?? null,
                $data['cours_id'] ?? null,
                $data['destinataire_id'] ?? null,
            ]
        );
    }

    public function recevoirMessage(int $limit = 50): array
    {
        return Database::fetchAll(
            "SELECT m.*, u.nom, u.prenom, u.role
             FROM messages m
             JOIN utilisateurs u ON m.expediteur_id = u.id
             WHERE m.destinataire_id = ? OR m.cours_id IN (
                SELECT cours_id FROM inscriptions WHERE etudiant_id = ?
             )
             ORDER BY m.created_at DESC LIMIT ?",
            [$this->id, $this->id, $limit]
        );
    }

    public function getId(): ?int { return $this->id; }
    public function getNom(): string { return $this->nom; }
    public function getPrenom(): string { return $this->prenom; }
    public function getEmail(): string { return $this->email; }
    public function getRole(): string { return $this->role; }
    public function getMatricule(): ?string { return $this->matricule; }
    public function getPromotionId(): ?int { return $this->promotionId; }
    public function getStatut(): string { return $this->statut; }
    public function getNomComplet(): string { return $this->prenom . ' ' . $this->nom; }
    public function getMotDePasse(): string { return $this->motDePasse; }
}
