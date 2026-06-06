<?php

require_once __DIR__ . '/../config/init.php';

Session::start();
header('Content-Type: application/json');
Session::requireRole('etudiant');

$method = $_SERVER['REQUEST_METHOD'];
$userId = Session::getUserId();

match ($method) {
    'POST' => rejoindre(),
    'GET'  => getMesInscriptions(),
    default => http_response_code(405),
};

function rejoindre(): void
{
    global $userId;
    $coursId = (int)($_POST['cours_id'] ?? 0);
    if (!$coursId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'ID du cours requis.']);
        return;
    }
    $result = Cours::inscrire($userId, $coursId);
    echo json_encode(['success' => $result['success'], 'error' => $result['error'], 'message' => $result['success'] ? 'Inscription réussie.' : $result['error']]);
}

function getMesInscriptions(): void
{
    global $userId;
    $cours = Cours::getByEtudiant($userId);
    echo json_encode(['success' => true, 'cours' => $cours]);
}
