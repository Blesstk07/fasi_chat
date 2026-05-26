<?php
namespace Classes\Modeles;

use Classes\Services\BaseDeDonnees;

class Conversation {
    private ?int $id = null;
    private string $type;
    private string $titre;
    private ?string $sousTitre = null;
    private ?int $coursId = null;
    private array $participants = [];
    private array $messages = [];
    private \DateTime $creeLe;
    
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
    
    // Getters
    public function getId(): ?int { return $this->id; }
    public function getType(): string { return $this->type; }
    public function getTitre(): string { return $this->titre; }
    public function getSousTitre(): ?string { return $this->sousTitre; }
    public function getCoursId(): ?int { return $this->coursId; }
    public function getParticipants(): array { return $this->participants; }
    public function getMessages(): array { return $this->messages; }
    public function getCreeLe(): \DateTime { return $this->creeLe; }
    
    // Setters
    public function setType(string $type): self {
        $this->type = $type;
        return $this;
    }
    
    public function setTitre(string $titre): self {
        $this->titre = $titre;
        return $this;
    }
    
    public function setParticipants(array $participants): self {
        $this->participants = $participants;
        return $this;
    }
    
    public function sauvegarder(): bool {
        $db = BaseDeDonnees::getInstance();
        
        if ($this->id) {
            $sql = "UPDATE conversations SET titre = ?, sous_titre = ? WHERE id = ?";
            $stmt = $db->prepare($sql);
            return $stmt->execute([$this->titre, $this->sousTitre, $this->id]);
        } else {
            $sql = "INSERT INTO conversations (type, titre, sous_titre, cours_id) VALUES (?, ?, ?, ?)";
            $stmt = $db->prepare($sql);
            $result = $stmt->execute([$this->type, $this->titre, $this->sousTitre, $this->coursId]);
            if ($result) {
                $this->id = $db->lastInsertId();
                // Ajouter les participants
                $this->ajouterParticipants();
            }
            return $result;
        }
    }
    
    private function ajouterParticipants(): bool {
        $db = BaseDeDonnees::getInstance();
        $sql = "INSERT INTO participants_conversation (conversation_id, utilisateur_id) VALUES (?, ?)";
        $stmt = $db->prepare($sql);
        
        foreach ($this->participants as $participantId) {
            $stmt->execute([$this->id, $participantId]);
        }
        return true;
    }
    
    public static function getConversationsByUser(int $userId): array {
        $db = BaseDeDonnees::getInstance();
        $stmt = $db->query("
            SELECT c.* FROM conversations c
            JOIN participants_conversation pc ON c.id = pc.conversation_id
            WHERE pc.utilisateur_id = ?
            ORDER BY c.cree_le DESC
        ", [$userId]);
        
        $conversations = [];
        while ($data = $stmt->fetch()) {
            $conversations[] = new self($data);
        }
        return $conversations;
    }
}