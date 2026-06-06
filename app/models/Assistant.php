<?php

require_once __DIR__ . '/Utilisateur.php';

class Assistant extends Utilisateur
{
    public function __construct(array $data = [])
    {
        parent::__construct($data);
        $this->role = 'assistant';
    }
}
