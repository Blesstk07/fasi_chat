<?php

require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../models/ValveAnnonce.php';
require_once __DIR__ . '/../models/Apparitaire.php';

class ValveController
{
    public static function list(): void
    {
        header('Content-Type: application/json');

        $categorie = $_GET['categorie'] ?? null;
        $annonces  = ValveAnnonce::getAll($categorie);

        echo json_encode(['success' => true, 'annonces' => $annonces]);
    }

    public static function get(): void
    {
        header('Content-Type: application/json');

        $id = $_GET['id'] ?? 0;
        $annonce = ValveAnnonce::getById((int)$id);

        if (!$annonce) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Annonce introuvable.']);
            return;
        }

        ValveAnnonce::incrementerVues((int)$id);
        echo json_encode(['success' => true, 'annonce' => $annonce]);
    }

    public static function create(): void
    {
        Session::start();
        header('Content-Type: application/json');
        Session::requireRole('apparitaire');

        $titre   = $_POST['titre'] ?? '';
        $contenu = $_POST['contenu'] ?? '';
        $categorie = $_POST['categorie'] ?? 'information';

        if (empty($titre) || empty($contenu)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Titre et contenu requis.']);
            return;
        }

        $user = new Apparitaire(['id' => Session::getUserId()]);
        $annId = $user->publierAnnonce([
            'titre'    => $titre,
            'contenu'  => $contenu,
            'categorie'=> $categorie,
        ]);

        echo json_encode([
            'success' => true,
            'annonce_id' => $annId,
            'message' => 'Annonce publiée avec succès sur le Valve.',
        ]);
    }

    public static function update(): void
    {
        Session::start();
        header('Content-Type: application/json');
        Session::requireRole('apparitaire');

        $id      = $_POST['id'] ?? 0;
        $titre   = $_POST['titre'] ?? '';
        $contenu = $_POST['contenu'] ?? '';
        $categorie = $_POST['categorie'] ?? 'information';

        $user = new Apparitaire(['id' => Session::getUserId()]);
        $user->modifierAnnonce((int)$id, [
            'titre'    => $titre,
            'contenu'  => $contenu,
            'categorie'=> $categorie,
        ]);

        echo json_encode(['success' => true, 'message' => 'Annonce modifiée.']);
    }

    public static function delete(): void
    {
        Session::start();
        header('Content-Type: application/json');
        Session::requireRole('apparitaire');

        $id = $_POST['id'] ?? 0;

        $user = new Apparitaire(['id' => Session::getUserId()]);
        $user->supprimerAnnonce((int)$id);

        echo json_encode(['success' => true, 'message' => 'Annonce supprimée.']);
    }

    public static function stats(): void
    {
        header('Content-Type: application/json');
        $stats = ValveAnnonce::getStats();
        echo json_encode(['success' => true, 'stats' => $stats]);
    }
}
