<?php

class Valve
{
    private $id;
    private $titre;
    private $contenu;
    private $date_publication;
    private $date_expiration;
    private $auteur_id;

    public function __construct($titre, $contenu, $date_expiration, $auteur_id)
    {
        $this->titre = $titre;
        $this->contenu = $contenu;
        $this->date_expiration = $date_expiration;
        $this->auteur_id = $auteur_id;
    }

    public function getTitre()
    {
        return $this->titre;
    }

    public function getContenu()
    {
        return $this->contenu;
    }

    public function getDateExpiration()
    {
        return $this->date_expiration;
    }

    public function getAuteurId()
    {
        return $this->auteur_id;
    }
}