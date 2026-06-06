<?php

require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../models/Message.php';

class MessageController
{
    public static function send(): void
    {
        Session::start();
        header('Content-Type: application/json');
        Session::requireAuth();

        $userId      = Session::getUserId();
        $type        = $_POST['type'] ?? 'public';
        $contenu     = $_POST['contenu'] ?? '';
        $cours_id    = $_POST['cours_id'] ?? null;
        $destinataire_id = $_POST['destinataire_id'] ?? null;
        $fichier_id  = $_POST['fichier_id'] ?? null;

        if (empty($contenu) && !$fichier_id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Message ou fichier requis.']);
            return;
        }

        $msgId = Database::insert(
            "INSERT INTO messages (expediteur_id, type, contenu, fichier_id, cours_id, destinataire_id)
             VALUES (?, ?, ?, ?, ?, ?)",
            [$userId, $type, $contenu, $fichier_id, $cours_id, $destinataire_id]
        );

        echo json_encode([
            'success' => true,
            'message_id' => $msgId,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public static function getByCours(): void
    {
        Session::start();
        header('Content-Type: application/json');
        Session::requireAuth();

        $coursId = $_GET['cours_id'] ?? 0;
        $type    = $_GET['type'] ?? 'public';

        $messages = Message::getByCours((int)$coursId, $type);

        echo json_encode(['success' => true, 'messages' => $messages]);
    }

    public static function getPrive(): void
    {
        Session::start();
        header('Content-Type: application/json');
        Session::requireAuth();

        $autreId = $_GET['avec'] ?? 0;
        $userId  = Session::getUserId();

        $messages = Message::getPrive($userId, (int)$autreId);

        echo json_encode(['success' => true, 'messages' => $messages]);
    }

    public static function getNonLus(): void
    {
        Session::start();
        header('Content-Type: application/json');
        Session::requireAuth();

        $nonLus = Message::getNonLus(Session::getUserId());
        echo json_encode(['success' => true, 'non_lus' => $nonLus]);
    }

    public static function marquerLu(): void
    {
        Session::start();
        header('Content-Type: application/json');
        Session::requireAuth();

        $messageId = $_POST['message_id'] ?? 0;
        Message::marquerLu((int)$messageId);
        echo json_encode(['success' => true]);
    }
}
