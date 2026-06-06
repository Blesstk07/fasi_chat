<?php

require_once __DIR__ . '/config/init.php';

$method = $_SERVER['REQUEST_METHOD'];

match ($method) {
    'POST' => UploadController::upload(),
    'GET'  => UploadController::list(),
    'DELETE' => UploadController::delete(),
    default  => http_response_code(405),
};
