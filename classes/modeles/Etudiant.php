<?php
namespace Classes\Modeles;

class Etudiant extends Utilisateur {
    protected string $role = 'etudiant';
    
    public function getPermissions(): array {
        return [
            'envoyer_message' => true,
            'voir_valve' => true,
            'voir_mur' => true
        ];
    }
    
    public function peutEnvoyerMessageA(Utilisateur $destinataire): bool {
        // Étudiant à étudiant de même promotion
        if ($destinataire instanceof Etudiant) {
            return $this->promotion === $destinataire->getPromotion();
        }
        // Étudiant à enseignant
        if ($destinataire instanceof Enseignant) {
            return true;
        }
        return false;
    }
    
    public function getTableauBord(): array {
        return [
            'cours' => [],
            'messages_non_lus' => 0,
            'annonces' => []
        ];
    }
}