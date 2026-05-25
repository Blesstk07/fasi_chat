<?php
namespace Classes\Modeles;

class Enseignant extends Utilisateur {
    protected string $role = 'enseignant';
    
    public function getPermissions(): array {
        return [
            'envoyer_message' => true,
            'voir_valve' => true,
            'publier_mur' => true,
            'voir_etudiants' => true,
            'recevoir_convocations' => true
        ];
    }
    
    public function peutEnvoyerMessageA(Utilisateur $destinataire): bool {
        // Enseignant à enseignant : privé
        if ($destinataire instanceof Enseignant || $destinataire instanceof Assistant) {
            return true;
        }
        // Enseignant à étudiant : public
        if ($destinataire instanceof Etudiant) {
            return true;
        }
        return false;
    }
    
    public function getTableauBord(): array {
        return [
            'cours' => [],
            'etudiants' => [],
            'messages_non_lus' => 0,
            'convocations' => []
        ];
    }
}