<?php
namespace Classes\Modeles;

use Classes\Services\BaseDeDonnees;

abstract class Fichier {
    protected ?int $id = null;
    protected int $messageId;
    protected string $nomOriginal;
    protected string $nomStockage;
    protected string $chemin;
    protected string $typeMime;
    protected int $taille;
    protected ?int $tailleOriginale = null;
    protected bool $estCompresse = false;
    protected \DateTime $dateUpload;
    
    public function __construct(array $data = []) {
        $this->dateUpload = new \DateTime();
        if (!empty($data)) {
            $this->hydrate($data);
        }
    }
    
    public function hydrate(array $data): self {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
        return $this;
    }
    
    // Getters
    public function getId(): ?int { return $this->id; }
    public function getMessageId(): int { return $this->messageId; }
    public function getNomOriginal(): string { return $this->nomOriginal; }
    public function getNomStockage(): string { return $this->nomStockage; }
    public function getChemin(): string { return $this->chemin; }
    public function getTypeMime(): string { return $this->typeMime; }
    public function getTaille(): int { return $this->taille; }
    public function getTailleFormatee(): string { return $this->formatTaille($this->taille); }
    public function getTailleOriginale(): ?int { return $this->tailleOriginale; }
    public function isEstCompresse(): bool { return $this->estCompresse; }
    public function getDateUpload(): \DateTime { return $this->dateUpload; }
    
    // Setter
    public function setMessageId(int $messageId): self {
        $this->messageId = $messageId;
        return $this;
    }
    
    // Méthodes abstraites
    abstract public function compresser(): bool;
    abstract public function estValide(): bool;
    
    // Upload du fichier
    public function uploader(array $file, int $messageId): bool {
        // Vérifier la taille
        if ($file['size'] > MAX_FILE_SIZE) {
            throw new \Exception("Fichier trop volumineux. Maximum " . (MAX_FILE_SIZE / 1024 / 1024) . " Mo");
        }
        
        // Vérifier le type
        if (!$this->estValide()) {
            throw new \Exception("Type de fichier non autorisé");
        }
        
        $this->messageId = $messageId;
        $this->nomOriginal = $file['name'];
        $this->typeMime = $file['type'];
        $this->tailleOriginale = $file['size'];
        
        // Générer un nom unique
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $this->nomStockage = uniqid() . '_' . time() . '.' . $extension;
        $this->chemin = UPLOAD_PATH . $this->nomStockage;
        
        // Déplacer le fichier
        if (!move_uploaded_file($file['tmp_name'], $this->chemin)) {
            throw new \Exception("Erreur lors de l'upload");
        }
        
        $this->taille = filesize($this->chemin);
        
        // Compresser si nécessaire
        if ($this->compresser()) {
            $this->estCompresse = true;
            $this->taille = filesize($this->chemin);
        }
        
        return $this->sauvegarder();
    }
    
    // Sauvegarde en BDD
    public function sauvegarder(): bool {
        $db = BaseDeDonnees::getInstance();
        $sql = "INSERT INTO fichiers (message_id, nom_original, nom_stockage, chemin, type_mime, taille, taille_originale, est_compresse) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($sql);
        $result = $stmt->execute([
            $this->messageId,
            $this->nomOriginal,
            $this->nomStockage,
            $this->chemin,
            $this->typeMime,
            $this->taille,
            $this->tailleOriginale,
            $this->estCompresse ? 1 : 0
        ]);
        
        if ($result) {
            $this->id = $db->lastInsertId();
        }
        return $result;
    }
    
    // Récupérer les fichiers d'un message
    public static function getByMessageId(int $messageId): array {
        $db = BaseDeDonnees::getInstance();
        $stmt = $db->query("SELECT * FROM fichiers WHERE message_id = ?", [$messageId]);
        
        $fichiers = [];
        while ($data = $stmt->fetch()) {
            $fichiers[] = self::createFromData($data);
        }
        return $fichiers;
    }
    
    // Factory
    private static function createFromData(array $data): ?self {
        $typeMime = $data['type_mime'];
        
        if (in_array($typeMime, ALLOWED_IMAGES)) {
            return new Image($data);
        } elseif (in_array($typeMime, ALLOWED_VIDEOS)) {
            return new Video($data);
        } else {
            return new Document($data);
        }
    }
    
    // Formatage taille
    private function formatTaille(int $bytes): string {
        $units = ['o', 'Ko', 'Mo', 'Go'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}