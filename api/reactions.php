<?php

require_once __DIR__ . '/../config/init.php';

Session::start();
header('Content-Type: application/json');
Session::requireAuth();

$method = $_SERVER['REQUEST_METHOD'];
$userId = Session::getUserId();

match ($method) {
    'POST' => toggleReaction(),
    'GET'  => getReactions(),
    default => http_response_code(405),
};

function toggleReaction(): void
{
    global $userId;
    $messageId = (int)($_POST['message_id'] ?? 0);
    $emoji     = $_POST['emoji'] ?? '';

    if (!$messageId || !$emoji) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'message_id et emoji requis.']);
        return;
    }

    $result = Reaction::toggle($messageId, $userId, $emoji);
    echo json_encode(['success' => true, 'action' => $result['action']]);
}

function getReactions(): void
{
    $coursId = (int)($_GET['cours_id'] ?? 0);
    if (!$coursId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'cours_id requis.']);
        return;
    }
    $reactions = Reaction::getByCours($coursId);
    echo json_encode(['success' => true, 'reactions' => $reactions]);
}
