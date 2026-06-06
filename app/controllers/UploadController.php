<?php

require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../models/Fichier.php';

class UploadController
{
    public static function upload(): void
    {
        Session::start();
        header('Content-Type: application/json');
        Session::requireAuth();

        if (!isset($_FILES['fichier'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Aucun fichier envoyé.']);
            return;
        }

        try {
            $fichierId = Fichier::upload($_FILES['fichier'], Session::getUserId());
            $fichier   = Fichier::getById($fichierId);

            echo json_encode([
                'success'  => true,
                'fichier'  => $fichier,
                'message'  => 'Fichier uploadé avec succès.',
            ]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public static function list(): void
    {
        Session::start();
        header('Content-Type: application/json');
        Session::requireAuth();

        $fichiers = Fichier::getByUploader(Session::getUserId());
        echo json_encode(['success' => true, 'fichiers' => $fichiers]);
    }

    public static function delete(): void
    {
        Session::start();
        header('Content-Type: application/json');
        Session::requireAuth();

        $id = $_POST['id'] ?? 0;
        Fichier::supprimer((int)$id);
        echo json_encode(['success' => true, 'message' => 'Fichier supprimé.']);
    }
}
