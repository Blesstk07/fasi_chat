<?php

require_once __DIR__ . '/config/init.php';

Session::start();

if (!Session::isLoggedIn()) {
    header('Location: login.html');
    exit;
}

$role = Session::getUserRole();

$redirects = [
    'etudiant'    => 'dashboard_etudiant.html',
    'enseignant'  => 'dashboard_enseignant.html',
    'assistant'   => 'dashboard_enseignant.html',
    'doyen'       => 'dashboard_admin.html',
    'vice_doyen'  => 'dashboard_vicedoyen.html',
    'apparitaire' => 'dashboard_apparitaire.html',
];

$page = $redirects[$role] ?? 'login.html';
header("Location: $page");
exit;
