<?php
namespace Classes\Modeles;

class Assistant extends Utilisateur {
    protected string $role = 'assistant';
    
    public function getPermissions(): array {
        return [
            'envoyer_message' => true,
            'voir_valve' => true,
            'publier_mur' => true,
            'voir_etudiants' => true
        ];
    }
    
    public function peutEnvoyerMessageA(Utilisateur $destinataire): bool {
        // Un assistant peut envoyer des messages aux enseignants et aux étudiants
        if ($destinataire instanceof Enseignant || $destinataire instanceof Etudiant) {
            return true;
        }
        return false;
    }
    
    public function getTableauBord(): array {
        return [
            'cours' => [],
            'etudiants' => [],
            'messages_non_lus' => 0
        ];
    }
}