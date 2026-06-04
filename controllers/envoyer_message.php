<?php

session_start();

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../classes/MessageService.php';
require_once __DIR__ . '/../classes/FichierService.php';

if (!isset($_SESSION['user']['id'])) {
    header('Location: ../login.php');
    exit;
}

$pdo = (new Database())->getConnection();
$messageService = new MessageService($pdo);
$fichierService = new FichierService($pdo);

$expediteurId = (int) $_SESSION['user']['id'];
$type = $_POST['type'] ?? '';
$contenu = trim($_POST['contenu'] ?? '');
$files = $_FILES['fichiers'] ?? null;
$aDesFichiers = $fichierService->hasFiles($files);

if ($contenu === '' && $aDesFichiers) {
    $contenu = '[Fichier joint]';
}

try {
    $pdo->beginTransaction();

    if ($type === 'prive') {
        $destinataireId = (int) ($_POST['destinataire_id'] ?? 0);
        if ($destinataireId <= 0) {
            throw new Exception('Veuillez choisir un destinataire.');
        }
        $messageId = $messageService->envoyerMessagePrive($expediteurId, $destinataireId, $contenu);
    } elseif ($type === 'public') {
        $coursId = (int) ($_POST['cours_id'] ?? 0);
        if ($coursId <= 0) {
            throw new Exception('Veuillez choisir un cours.');
        }
        $messageId = $messageService->envoyerMessagePublic($expediteurId, $coursId, $contenu);
    } elseif ($type === 'mur_pedagogique') {
        $coursId = (int) ($_POST['cours_id'] ?? 0);
        if ($coursId <= 0) {
            throw new Exception('Veuillez choisir un cours.');
        }
        $messageId = $messageService->publierMurPedagogique($expediteurId, $coursId, $contenu);
    } else {
        throw new Exception('Type de message invalide.');
    }

    if ($aDesFichiers) {
        $fichierService->enregistrerFichiersMessage($messageId, $files);
    }

    $pdo->commit();

    header('Location: ../messagerie.php?success=1');
    exit;
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    header('Location: ../messagerie.php?error=' . urlencode($e->getMessage()));
    exit;
}
