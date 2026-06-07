<?php
// models/ViceDoyen.php
require_once __DIR__ . '/Utilisateur.php';

class ViceDoyen extends Utilisateur
{
    public function __construct(PDO $database, array $data = [])
    {
        parent::__construct($database, $data);
        $this->role = 'vice_doyen';
    }

    public function getDroitsSpecifiques(): array
    {
        return [
            'peut_publier_valve' => true,
            'peut_publier_mur' => false,
            'voir_tous_les_messages' => true, // Accès à la supervision comme le doyen
            'espace_accedation' => 'administration_global'
        ];
    }
}