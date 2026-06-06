<?php

require_once __DIR__ . '/../config/init.php';

Session::start();
header('Content-Type: application/json');
Session::requireAuth();

$action = $_GET['action'] ?? '';

match ($action) {
    'by_role' => getByRole(),
    'search'  => search(),
    default   => getAll(),
};

function getAll(): void
{
    $users = Database::fetchAll("SELECT id, nom, prenom, email, role, matricule, statut FROM utilisateurs ORDER BY nom, prenom");
    echo json_encode(['success' => true, 'users' => $users]);
}

function getByRole(): void
{
    $role  = $_GET['role'] ?? '';
    $users = Database::fetchAll(
        "SELECT id, nom, prenom, email, role, matricule, statut FROM utilisateurs WHERE role = ? ORDER BY nom, prenom",
        [$role]
    );
    echo json_encode(['success' => true, 'users' => $users]);
}

function search(): void
{
    $q    = $_GET['q'] ?? '';
    $role = $_GET['role'] ?? '';

    $sql = "SELECT id, nom, prenom, email, role, matricule, statut FROM utilisateurs WHERE (nom LIKE ? OR prenom LIKE ? OR email LIKE ?)";
    $params = ["%$q%", "%$q%", "%$q%"];

    if ($role) {
        $sql .= " AND role = ?";
        $params[] = $role;
    }

    $users = Database::fetchAll($sql . " ORDER BY nom, prenom LIMIT 20", $params);
    echo json_encode(['success' => true, 'users' => $users]);
}
