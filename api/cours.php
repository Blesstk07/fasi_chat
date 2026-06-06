<?php

require_once __DIR__ . '/../config/init.php';

Session::start();
header('Content-Type: application/json');
Session::requireAuth();

$action = $_GET['action'] ?? '';
$userId = Session::getUserId();
$role   = Session::getUserRole();

match ($action) {
    'enseignant' => getByEnseignant(),
    'etudiant'   => getByEtudiant(),
    'details'    => getDetails(),
    default      => getAllCours(),
};

function getAllCours(): void
{
    echo json_encode(['success' => true, 'cours' => Cours::getAll()]);
}

function getByEnseignant(): void
{
    echo json_encode(['success' => true, 'cours' => Cours::getByEnseignant($GLOBALS['userId'])]);
}

function getByEtudiant(): void
{
    echo json_encode(['success' => true, 'cours' => Cours::getByEtudiant($GLOBALS['userId'])]);
}

function getDetails(): void
{
    $coursId = (int)($_GET['cours_id'] ?? 0);
    $cours    = Cours::getById($coursId);
    $enseignants = Cours::getEnseignants($coursId);
    $etudiants   = Cours::getEtudiants($coursId);

    echo json_encode([
        'success' => true,
        'cours'   => $cours,
        'enseignants' => $enseignants,
        'etudiants'   => $etudiants,
    ]);
}
