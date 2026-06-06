<?php
// controllers/MessageController.php

class MessageController
{
    private PDO $db;
    private MessageService $messageService;

    public function __construct(PDO $database)
    {
        $this->db = $database;
        $this->messageService = new MessageService($this->db);
    }

    /**
     * API JSON qui récupère TOUS les messages et les trie de façon conditionnelle
     */
    public function getMessagesAjax()
    {
        header('Content-Type: application/json; charset=utf-8');

        // 1. Sécurité : Vérifier que l'utilisateur est connecté
        if (!isset($_SESSION['user']['id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Non authentifié.']);
            exit;
        }

        $userId = (int)$_SESSION['user']['id'];
        $type = $_GET['type'] ?? ''; // 'prive', 'public', ou 'mur_pedagogique'

        try {
            // 2. On récupère le tableau GLOBAL grâce à la fonction de ton pote
            $tousLesMessages = $this->messageService->getMessagesVisibles($userId);

            // 3. TRI CONDITIONNEL DU TABLEAU PHP
            if ($type === 'prive') {
                $contactId = (int)($_GET['contact_id'] ?? 0);
                
                // On ne garde que les messages privés entre l'utilisateur et ce contact précis
                $messagesFiltres = array_filter($tousLesMessages, function($msg) use ($userId, $contactId) {
                    return $msg['type_message'] === 'prive' && (
                        ((int)$msg['expediteur_id'] === $userId && (int)$msg['destinataire_id'] === $contactId) ||
                        ((int)$msg['expediteur_id'] === $contactId && (int)$msg['destinataire_id'] === $userId)
                    );
                });
            } 
            elseif ($type === 'public' || $type === 'mur_pedagogique') {
                $coursId = (int)($_GET['cours_id'] ?? 0);
                
                // On ne garde que les messages du cours et du type demandé
                $messagesFiltres = array_filter($tousLesMessages, function($msg) use ($coursId, $type) {
                    return $msg['type_message'] === $type && (int)$msg['cours_id'] === $coursId;
                });
            } 
            else {
                // Si aucun paramètre (Barre latérale ou flux d'accueil), on garde tout
                $messagesFiltres = $tousLesMessages;
            }

            // array_filter préserve les clés numériques (ex: index 4, 12, 19). 
            // array_values réindexe proprement de 0 à N pour le JSON du Front.
            $messagesFiltres = array_values($messagesFiltres);

            // 4. On inverse le tableau pour le Front (getMessagesVisibles trie par DESC, 
            // mais dans un chat on veut souvent du plus ancien au plus récent ASC)
            if ($type !== '') {
                $messagesFiltres = array_reverse($messagesFiltres);
            }

            // Réponse finale envoyée au JS
            echo json_encode(['success' => true, 'messages' => $messagesFiltres], JSON_UNESCAPED_UNICODE);

        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }
}