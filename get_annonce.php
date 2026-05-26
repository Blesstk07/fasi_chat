<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'apparitaire') {
    http_response_code(403);
    exit;
}

$host = 'localhost';
$dbname = 'campus_relay';
$username = 'root';
$password = '1234';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->prepare("SELECT * FROM annonces WHERE id = ? AND auteur_id = ?");
    $stmt->execute([$_GET['id'], $_SESSION['user_id']]);
    $annonce = $stmt->fetch(PDO::FETCH_ASSOC);
    
    header('Content-Type: application/json');
    echo json_encode($annonce);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>