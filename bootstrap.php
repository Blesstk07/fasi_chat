<?php
// Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configuration des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Définition des constantes
define('ROOT_PATH', __DIR__);
define('CLASSES_PATH', ROOT_PATH . '/classes');
define('CONFIG_PATH', ROOT_PATH . '/config');

// Autoloader simple et robuste
spl_autoload_register(function ($className) {
    // Si la classe commence par "Classes\\"
    if (strpos($className, 'Classes\\') === 0) {
        // Enlever "Classes\" du début
        $relativeClass = substr($className, 8);
        // Convertir les backslashes en slashes
        $filePath = CLASSES_PATH . '/' . str_replace('\\', '/', $relativeClass) . '.php';
        
        if (file_exists($filePath)) {
            require_once $filePath;
            return true;
        }
    }
    
    return false;
});

// Fonctions globales
function redirect($url) {
    header("Location: " . $url);
    exit;
}

function dd($var) {
    echo '<pre>';
    var_dump($var);
    echo '</pre>';
    die();
}