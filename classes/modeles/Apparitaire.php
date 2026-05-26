<?php
namespace Classes\Modeles;

class Apparitaire extends Utilisateur {
    protected string $role = 'apparitaire';
    
    public function getPermissions(): array {
        return [
            'gerer_valve' => true,
            'voir_valve' => true,
            'publier_annonces' => true,
            'modifier_annonces' => true,
            'supprimer_annonces' => true
        ];
    }
    
    public function peutEnvoyerMessageA(Utilisateur $destinataire): bool {
        // L'apparitaire ne participe pas aux conversations pédagogiques
        return false;
    }
    
    public function getTableauBord(): array {
        return [
            'annonces' => [],
            'statistiques_valve' => []
        ];
    }
}