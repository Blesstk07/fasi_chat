<?php
// models/Apparitaire.php
require_once __DIR__ . '/Utilisateur.php';

class Apparitaire extends Utilisateur
{
    public function __construct(PDO $database, array $data = [])
    {
        parent::__construct($database, $data);
        $this->role = 'apparitaire';
    }

    public function getDroitsSpecifiques(): array
    {
        return [
            'peut_publier_valve' => true, // Il affiche les communiqués officiels sur le Valve
            'peut_publier_mur' => false,
            'voir_tous_les_messages' => false,
            'espace_accedation' => 'apparitaire_dashboard'
        ];
    }
}