<?php

require_once __DIR__ . '/Message.php';

class MessagePrive extends Message
{
    public function __construct(PDO $pdo, int $expediteurId, int $destinataireId, string $contenu)
    {
        parent::__construct($pdo, $expediteurId, $destinataireId, null, $contenu, 'prive');
    }

    public function envoyer(): int
    {
        if (empty($this->contenu)) {
            throw new Exception('Le message ne peut pas être vide.');
        }

        return $this->enregistrer();
    }
}
