<?php
namespace Classes\Traits;

trait ConvocableTrait {
    
    public function convoquer(string $objet, \DateTime $dateReunion, string $lieu, ?string $message = null): array {
        $db = \Classes\Services\BaseDeDonnees::getInstance();
        
        // Récupérer tous les enseignants et assistants
        $stmt = $db->query("SELECT id FROM utilisateur WHERE role IN ('enseignant', 'assistant')");
        $destinataires = $stmt->fetchAll();
        
        $convocations = [];
        
        foreach ($destinataires as $dest) {
            $stmt = $db->prepare("
                INSERT INTO reunions (expediteur_id, titre, audience, date_reunion, heure_reunion, lieu, note)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $result = $stmt->execute([
                $this->id,
                $objet,
                'enseignants_assistants',
                $dateReunion->format('Y-m-d'),
                $dateReunion->format('H:i:s'),
                $lieu,
                $message
            ]);
            
            if ($result) {
                $convocations[] = $db->lastInsertId();
            }
        }
        
        return $convocations;
    }
}