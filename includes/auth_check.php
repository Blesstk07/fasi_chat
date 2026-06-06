<?php

require_once __DIR__ . '/../app/core/Session.php';
Session::start();

if (!Session::isLoggedIn()) {
    header('Location: /FasiChatClassRoom/login.html');
    exit;
}

$currentUser = [
    'id'       => Session::getUserId(),
    'nom'      => Session::get('user_nom'),
    'prenom'   => Session::get('user_prenom'),
    'email'    => Session::get('user_email'),
    'role'     => Session::getUserRole(),
    'matricule'=> Session::get('user_matricule'),
];
