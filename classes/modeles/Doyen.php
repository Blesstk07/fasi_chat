<?php
namespace Classes\Modeles;

class Doyen extends Utilisateur {
    protected string $role = 'doyen';
    
    public function getPermissions(): array {
        return [
            'envoyer_message' => true,
            'voir_valve' => true,
            'convoquer' => true,
            'vision_globale' => true,
            'gerer_utilisateurs' => true
        ];
    }
    
    public function peutEnvoyerMessageA(Utilisateur $destinataire): bool {
        // Le doyen peut envoyer des messages à tout le monde
        return true;
    }
    
    public function getTableauBord(): array {
        return [
            'statistiques' => [],
            'reunions' => [],
            'annonces' => []
        ];
    }
}