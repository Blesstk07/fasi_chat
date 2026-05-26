<?php
namespace Classes\Modeles;

use Classes\Services\BaseDeDonnees;

class Message {
    private ?int $id = null;
    private int $conversationId;
    private int $utilisateurId;
    private string $typeMessage = 'texte';
    private string $contenu;
    private \DateTime $creeLe;
    private ?Utilisateur $auteur = null;
    
    public function __construct(array $data = []) {
        $this->creeLe = new \DateTime();
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
    
    public function sauvegarder(): bool {
        $db = BaseDeDonnees::getInstance();
        $sql = "INSERT INTO messages (conversation_id, utilisateur_id, type_message, contenu) VALUES (?, ?, ?, ?)";
        $stmt = $db->prepare($sql);
        $result = $stmt->execute([$this->conversationId, $this->utilisateurId, $this->typeMessage, $this->contenu]);
        if ($result) {
            $this->id = $db->lastInsertId();
        }
        return $result;
    }
    
    public function getAuteur(): ?Utilisateur {
        if ($this->auteur === null && $this->utilisateurId) {
            $this->auteur = Utilisateur::findById($this->utilisateurId);
        }
        return $this->auteur;
    }
    
    // Getters
    public function getId(): ?int { return $this->id; }
    public function getContenu(): string { return $this->contenu; }
    public function getCreeLe(): \DateTime { return $this->creeLe; }
    public function getTypeMessage(): string { return $this->typeMessage; }
    
    public static function getMessagesByConversation(int $conversationId): array {
        $db = BaseDeDonnees::getInstance();
        $stmt = $db->query("
            SELECT * FROM messages 
            WHERE conversation_id = ? 
            ORDER BY cree_le ASC
        ", [$conversationId]);
        
        $messages = [];
        while ($data = $stmt->fetch()) {
            $messages[] = new self($data);
        }
        return $messages;
    }
}