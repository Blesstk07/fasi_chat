<?php
// models/Etudiant.php
require_once __DIR__ . '/Utilisateur.php';

class Etudiant extends Utilisateur
{
    private ?int $promotionId = null;

    public function __construct(PDO $database, array $data = [])
    {
        parent::__construct($database, $data);
        $this->promotionId = $data['promotion_id'] ?? null;
        $this->role = 'etudiant';
    }

    public function getDroitsSpecifiques(): array
    {
        return [
            'peut_publier_valve' => false,
            'peut_publier_mur' => false,
            'voir_tous_les_messages' => false,
            'espace_accedation' => 'etudiant_dashboard'
        ];
    }

    public function getPromotionId(): ?int { return $this->promotionId; }
    public function setPromotionId(int $promotionId): void { $this->promotionId = $promotionId; }
}