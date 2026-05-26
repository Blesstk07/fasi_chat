<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('HTTP/1.0 403 Forbidden');
    exit('Accès non autorisé');
}

$host = 'localhost';
$dbname = 'campus_relay';
$username = 'root';
$password = '1234';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $fileId = (int)$_GET['id'];
    
    $stmt = $pdo->prepare("
        SELECT f.* FROM fichiers f
        JOIN messages m ON f.message_id = m.id
        JOIN participants_conversation pc ON m.conversation_id = pc.conversation_id
        WHERE f.id = ? AND pc.utilisateur_id = ?
    ");
    $stmt->execute([$fileId, $_SESSION['user_id']]);
    $file = $stmt->fetch();
    
    if ($file && file_exists($file['chemin'])) {
        header('Content-Type: ' . $file['type_mime']);
        header('Content-Disposition: attachment; filename="' . $file['nom_original'] . '"');
        header('Content-Length: ' . $file['taille']);
        readfile($file['chemin']);
        exit;
    } else {
        header('HTTP/1.0 404 Not Found');
        exit('Fichier non trouvé');
    }
    
} catch (PDOException $e) {
    header('HTTP/1.0 500 Internal Server Error');
    exit('Erreur technique');
}
?>