<?php

require_once __DIR__ . '/../config/init.php';

$method = $_SERVER['REQUEST_METHOD'];

match ($method) {
    'POST' => match ($_POST['action'] ?? 'create') {
        'create'  => ConvocationController::create(),
        'repondre'=> ConvocationController::repondre(),
        default   => ConvocationController::create(),
    },
    'GET'  => ConvocationController::list(),
    default => http_response_code(405),
};
