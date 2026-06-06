<?php

require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../models/Convocation.php';
require_once __DIR__ . '/../models/Doyen.php';
require_once __DIR__ . '/../models/ViceDoyen.php';

class ConvocationController
{
    public static function create(): void
    {
        Session::start();
        header('Content-Type: application/json');
        Session::requireRole(['doyen', 'vice_doyen']);

        $userId = Session::getUserId();
        $role   = Session::getUserRole();

        $objet        = $_POST['objet'] ?? '';
        $date_reunion = $_POST['date_reunion'] ?? '';
        $heure_reunion = $_POST['heure_reunion'] ?? '';
        $lieu         = $_POST['lieu'] ?? '';
        $message      = $_POST['message'] ?? '';

        if (empty($objet) || empty($date_reunion) || empty($heure_reunion)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Objet, date et heure requis.']);
            return;
        }

        $user = $role === 'doyen'
            ? new Doyen(['id' => $userId])
            : new ViceDoyen(['id' => $userId]);

        $convId = $user->convoquerReunion([
            'objet'        => $objet,
            'date_reunion' => $date_reunion,
            'heure_reunion'=> $heure_reunion,
            'lieu'         => $lieu,
            'message'      => $message,
        ]);

        // Envoyer un message à chaque destinataire
        $destinataires = Database::fetchAll(
            "SELECT id FROM utilisateurs WHERE role IN ('enseignant', 'assistant')"
        );
        foreach ($destinataires as $dest) {
            Database::insert(
                "INSERT INTO messages (expediteur_id, type, contenu, destinataire_id)
                 VALUES (?, 'prive', ?, ?)",
                [$userId, "📅 CONVOCATION: $objet\nDate: $date_reunion à $heure_reunion\nLieu: $lieu\n\n$message", $dest['id']]
            );
        }

        echo json_encode([
            'success' => true,
            'convocation_id' => $convId,
            'message' => 'Convocation envoyée avec succès à ' . count($destinataires) . ' destinataires.',
        ]);
    }

    public static function list(): void
    {
        Session::start();
        header('Content-Type: application/json');
        Session::requireAuth();

        $userId = Session::getUserId();
        $role   = Session::getUserRole();

        if (in_array($role, ['doyen', 'vice_doyen'])) {
            $convocations = Convocation::getByExpediteur($userId);
        } else {
            $convocations = Convocation::getByDestinataire($userId);
        }

        echo json_encode(['success' => true, 'convocations' => $convocations]);
    }

    public static function repondre(): void
    {
        Session::start();
        header('Content-Type: application/json');
        Session::requireAuth();

        $convId = $_POST['convocation_id'] ?? 0;
        $statut = $_POST['statut'] ?? '';

        if (!in_array($statut, ['accepte', 'refuse'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Statut invalide.']);
            return;
        }

        Convocation::repondre((int)$convId, Session::getUserId(), $statut);
        echo json_encode(['success' => true]);
    }
}
