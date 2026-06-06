<?php

require_once __DIR__ . '/../config/init.php';

$method = $_SERVER['REQUEST_METHOD'];

match ($method) {
    'POST'   => MessageController::send(),
    'GET'    => match ($_GET['action'] ?? '') {
        'cours'  => MessageController::getByCours(),
        'prive'  => MessageController::getPrive(),
        'nonlus' => MessageController::getNonLus(),
        default  => MessageController::getByCours(),
    },
    default  => http_response_code(405),
};
