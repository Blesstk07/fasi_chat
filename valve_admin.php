<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'apparitaire') {
    header('Location: valve.php');
    exit;
}

$host = 'localhost';
$dbname = 'campus_relay';
$username = 'root';
$password = '1234';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'];
        
        if ($action === 'create') {
            $stmt = $pdo->prepare("
                INSERT INTO annonces (auteur_id, titre, categorie, contenu, date_evenement, heure_evenement, lieu, priorite)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $_SESSION['user_id'],
                $_POST['titre'],
                $_POST['categorie'],
                $_POST['contenu'],
                $_POST['date_evenement'] ?: null,
                $_POST['heure_evenement'] ?: null,
                $_POST['lieu'] ?: null,
                $_POST['priorite']
            ]);
        }
        
        elseif ($action === 'update') {
            $stmt = $pdo->prepare("
                UPDATE annonces 
                SET titre = ?, categorie = ?, contenu = ?, date_evenement = ?, heure_evenement = ?, lieu = ?, priorite = ?
                WHERE id = ? AND auteur_id = ?
            ");
            $stmt->execute([
                $_POST['titre'],
                $_POST['categorie'],
                $_POST['contenu'],
                $_POST['date_evenement'] ?: null,
                $_POST['heure_evenement'] ?: null,
                $_POST['lieu'] ?: null,
                $_POST['priorite'],
                $_POST['annonce_id'],
                $_SESSION['user_id']
            ]);
        }
    }
    
    elseif (isset($_GET['action']) && $_GET['action'] === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM annonces WHERE id = ? AND auteur_id = ?");
        $stmt->execute([$_GET['id'], $_SESSION['user_id']]);
    }
    
    header('Location: valve.php');
    exit;
    
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>