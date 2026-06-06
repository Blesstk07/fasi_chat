<?php

require_once __DIR__ . '/../config/init.php';

$method = $_SERVER['REQUEST_METHOD'];

match ($method) {
    'POST' => match ($_POST['action'] ?? '') {
        'create' => ValveController::create(),
        'update' => ValveController::update(),
        'delete' => ValveController::delete(),
        default  => ValveController::create(),
    },
    'GET'  => match ($_GET['action'] ?? '') {
        'get'    => ValveController::get(),
        'stats'  => ValveController::stats(),
        default  => ValveController::list(),
    },
    default => http_response_code(405),
};
