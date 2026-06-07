<?php

require_once __DIR__ . '/MessagePrive.php';
require_once __DIR__ . '/MessagePublic.php';
require_once __DIR__ . '/MessageMurPedagogique.php';

class MessageService
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function envoyerMessagePrive(int $expediteurId, int $destinataireId, string $contenu): int
    {
        $expediteur = $this->getUtilisateur($expediteurId);
        $destinataire = $this->getUtilisateur($destinataireId);

        if (!$expediteur || !$destinataire) {
            throw new Exception('Utilisateur introuvable.');
        }

        if (!$this->peutEnvoyerMessagePrive($expediteur, $destinataire)) {
            throw new Exception("Vous n'avez pas le droit d'envoyer un message privé à cet utilisateur.");
        }

        $message = new MessagePrive($this->pdo, $expediteurId, $destinataireId, $contenu);
        return $message->envoyer();
    }

    public function envoyerMessagePublic(int $expediteurId, int $coursId, string $contenu): int
    {
        $expediteur = $this->getUtilisateur($expediteurId);

        if (!$expediteur) {
            throw new Exception('Utilisateur introuvable.');
        }

        if (!$this->coursExiste($coursId)) {
            throw new Exception('Cours introuvable.');
        }

        if (!$this->peutAccederCours($expediteur, $coursId)) {
            throw new Exception("Vous n'avez pas accès à ce cours.");
        }

        $message = new MessagePublic($this->pdo, $expediteurId, $coursId, $contenu);
        return $message->envoyer();
    }

    public function publierMurPedagogique(int $expediteurId, int $coursId, string $contenu): int
    {
        $expediteur = $this->getUtilisateur($expediteurId);

        if (!$expediteur) {
            throw new Exception('Utilisateur introuvable.');
        }

        if (!in_array($expediteur['role'], ['enseignant', 'assistant'], true)) {
            throw new Exception('Seuls les enseignants et assistants peuvent publier sur le mur pédagogique.');
        }

        if (!$this->peutAccederCours($expediteur, $coursId)) {
            throw new Exception("Vous n'avez pas accès à ce cours.");
        }

        $message = new MessageMurPedagogique($this->pdo, $expediteurId, $coursId, $contenu);
        return $message->envoyer();
    }

    public function getUtilisateursDisponibles(int $userId): array
    {
        $user = $this->getUtilisateur($userId);
        if (!$user) {
            return [];
        }

        $stmt = $this->pdo->prepare("SELECT id, nom, email, role, promotion_id FROM utilisateurs WHERE id != :id AND statut = 'actif' ORDER BY role, nom");
        $stmt->execute([':id' => $userId]);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_values(array_filter($users, fn($u) => $this->peutEnvoyerMessagePrive($user, $u)));
    }

    public function getCoursAccessibles(int $userId): array
    {
        $user = $this->getUtilisateur($userId);
        if (!$user) {
            return [];
        }

        if ($user['role'] === 'etudiant') {
            $stmt = $this->pdo->prepare("SELECT c.id, c.nom FROM cours c INNER JOIN cours_etudiants ce ON ce.cours_id = c.id WHERE ce.etudiant_id = :id ORDER BY c.nom");
            $stmt->execute([':id' => $userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        if (in_array($user['role'], ['enseignant', 'assistant'], true)) {
            $stmt = $this->pdo->prepare("SELECT id, nom FROM cours WHERE enseignant_id = :id ORDER BY nom");
            $stmt->execute([':id' => $userId]);
            $cours = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($user['role'] === 'assistant' && empty($cours)) {
                $stmt = $this->pdo->query("SELECT id, nom FROM cours ORDER BY nom");
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

            return $cours;
        }

        $stmt = $this->pdo->query("SELECT id, nom FROM cours ORDER BY nom");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMessagesVisibles(int $userId): array
    {
        $user = $this->getUtilisateur($userId);
        if (!$user) {
            return [];
        }

        $sql = "
            SELECT DISTINCT
                m.*,
                u.nom AS nom_expediteur,
                u.role AS role_expediteur,
                d.nom AS nom_destinataire,
                c.nom AS nom_cours
            FROM messages m
            INNER JOIN utilisateurs u ON m.expediteur_id = u.id
            LEFT JOIN utilisateurs d ON m.destinataire_id = d.id
            LEFT JOIN cours c ON m.cours_id = c.id
            LEFT JOIN cours_etudiants ce ON ce.cours_id = m.cours_id
            WHERE
                m.statut != 'supprime'
                AND (
                    m.destinataire_id = :user_id
                    OR m.expediteur_id = :user_id
                    OR ce.etudiant_id = :user_id
                    OR c.enseignant_id = :user_id
                    OR :role IN ('doyen', 'vice_doyen')
                )
            ORDER BY m.created_at DESC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':user_id' => $userId,
            ':role' => $user['role']
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getUtilisateur(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM utilisateurs WHERE id = :id AND statut = 'actif'");
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    private function peutEnvoyerMessagePrive(array $expediteur, array $destinataire): bool
    {
        $roleExp = $expediteur['role'];
        $roleDest = $destinataire['role'];

        if ($roleExp === 'etudiant' && $roleDest === 'etudiant') {
            return (int)$expediteur['promotion_id'] === (int)$destinataire['promotion_id'];
        }

        if (in_array($roleExp, ['enseignant', 'assistant'], true) && in_array($roleDest, ['enseignant', 'assistant'], true)) {
            return true;
        }

        if (($roleExp === 'doyen' && $roleDest === 'vice_doyen') || ($roleExp === 'vice_doyen' && $roleDest === 'doyen')) {
            return true;
        }

        return false;
    }

    private function coursExiste(int $coursId): bool
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM cours WHERE id = :id');
        $stmt->execute([':id' => $coursId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    private function peutAccederCours(array $user, int $coursId): bool
    {
        if ($user['role'] === 'etudiant') {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM cours_etudiants WHERE etudiant_id = :user_id AND cours_id = :cours_id");
            $stmt->execute([':user_id' => $user['id'], ':cours_id' => $coursId]);
            return (int)$stmt->fetchColumn() > 0;
        }

        if ($user['role'] === 'enseignant') {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM cours WHERE enseignant_id = :user_id AND id = :cours_id");
            $stmt->execute([':user_id' => $user['id'], ':cours_id' => $coursId]);
            return (int)$stmt->fetchColumn() > 0;
        }

        if ($user['role'] === 'assistant') {
            return true;
        }

        return in_array($user['role'], ['doyen', 'vice_doyen'], true);
    }
}
