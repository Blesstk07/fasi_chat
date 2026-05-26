<?php
namespace Classes\Modeles;

class ViceDoyen extends Utilisateur {
    protected string $role = 'vice_doyen';
    
    public function getPermissions(): array {
        return [
            'envoyer_message' => true,
            'voir_valve' => true,
            'convoquer' => true,
            'affecter_cours' => true
        ];
    }
    
    public function peutEnvoyerMessageA(Utilisateur $destinataire): bool {
        // Le vice-doyen peut envoyer des messages à tout le monde
        return true;
    }
    
    public function getTableauBord(): array {
        return [
            'cours' => [],
            'reunions' => [],
            'affectations' => []
        ];
    }
}