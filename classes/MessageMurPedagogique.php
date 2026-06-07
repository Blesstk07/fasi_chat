<?php

require_once __DIR__ . '/Message.php';

class MessageMurPedagogique extends Message
{
    public function __construct(PDO $pdo, int $expediteurId, int $coursId, string $contenu)
    {
        parent::__construct($pdo, $expediteurId, null, $coursId, $contenu, 'mur_pedagogique');
    }

    public function envoyer(): int
    {
        if (empty($this->contenu)) {
            throw new Exception('Le contenu du mur pédagogique ne peut pas être vide.');
        }

        return $this->enregistrer();
    }
}
