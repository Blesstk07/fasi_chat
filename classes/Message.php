<?php

abstract class Message
{
    protected PDO $pdo;
    protected int $expediteurId;
    protected ?int $destinataireId;
    protected ?int $coursId;
    protected string $contenu;
    protected string $typeMessage;

    public function __construct(
        PDO $pdo,
        int $expediteurId,
        ?int $destinataireId,
        ?int $coursId,
        string $contenu,
        string $typeMessage
    ) {
        $this->pdo = $pdo;
        $this->expediteurId = $expediteurId;
        $this->destinataireId = $destinataireId;
        $this->coursId = $coursId;
        $this->contenu = trim($contenu);
        $this->typeMessage = $typeMessage;
    }

    abstract public function envoyer(): int;

    protected function enregistrer(): int
    {
        $sql = "INSERT INTO messages
                (expediteur_id, destinataire_id, cours_id, contenu, type_message)
                VALUES
                (:expediteur_id, :destinataire_id, :cours_id, :contenu, :type_message)";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':expediteur_id' => $this->expediteurId,
            ':destinataire_id' => $this->destinataireId,
            ':cours_id' => $this->coursId,
            ':contenu' => $this->contenu,
            ':type_message' => $this->typeMessage
        ]);

        return (int) $this->pdo->lastInsertId();
    }
}
