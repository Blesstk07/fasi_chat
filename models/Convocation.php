<?php

class Convocation
{
    private $id;
    private $objet;
    private $date_reunion;
    private $lieu;
    private $message;
    private $expediteur_id;

    public function __construct($objet, $date_reunion, $lieu, $message, $expediteur_id)
    {
        $this->objet = $objet;
        $this->date_reunion = $date_reunion;
        $this->lieu = $lieu;
        $this->message = $message;
        $this->expediteur_id = $expediteur_id;
    }

    public function getObjet()
    {
        return $this->objet;
    }

    public function getDateReunion()
    {
        return $this->date_reunion;
    }

    public function getLieu()
    {
        return $this->lieu;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getExpediteurId()
    {
        return $this->expediteur_id;
    }
}