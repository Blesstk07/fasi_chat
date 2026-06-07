<?php
// models/Enseignant.php
require_once __DIR__ . '/Utilisateur.php';

class Enseignant extends Utilisateur
{
    public function __construct(PDO $database, array $data = [])
    {
        parent::__construct($database, $data);
        $this->role = 'enseignant';
    }

    public function getDroitsSpecifiques(): array
    {
        return [
            'peut_publier_valve' => true,
            'peut_publier_mur' => true,
            'voir_tous_les_messages' => false,
            'espace_accedation' => 'enseignant_dashboard'
        ];
    }
}