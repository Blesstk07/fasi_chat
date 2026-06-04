<?php

class AnnonceValve
{
    private $id;
    private $titre;
    private $contenu;
    private $date_publication;
    private $date_expiration;

    public function __construct($titre, $contenu, $date_expiration = null)
    {
        $this->titre = $titre;
        $this->contenu = $contenu;
        $this->date_expiration = $date_expiration;
    }

    public function getTitre()
    {
        return $this->titre;
    }

    public function getContenu()
    {
        return $this->contenu;
    }

    public function getDatePublication()
    {
        return $this->date_publication;
    }

    public function getDateExpiration()
    {
        return $this->date_expiration;
    }
}