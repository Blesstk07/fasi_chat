<?php

require_once __DIR__ . '/../config/init.php';

$type = $_GET['type'] ?? '';

match ($type) {
    'admin'      => DashboardController::admin(),
    'enseignant' => DashboardController::enseignant(),
    'etudiant'   => DashboardController::etudiant(),
    'apparitaire'=> DashboardController::apparitaire(),
    'vicedoyen'  => DashboardController::vicedoyen(),
    default      => http_response_code(400),
};
