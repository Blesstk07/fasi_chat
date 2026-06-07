<?php
// models/Doyen.php
require_once __DIR__ . '/Utilisateur.php';

class Doyen extends Utilisateur
{
    public function __construct(PDO $database, array $data = [])
    {
        parent::__construct($database, $data);
        $this->role = 'doyen';
    }

    public function getDroitsSpecifiques(): array
    {
        return [
            'peut_publier_valve' => true,
            'peut_publier_mur' => false,
            'voir_tous_les_messages' => true, // Le Doyen contrôle tout
            'espace_accedation' => 'administration_global'
        ];
    }
}