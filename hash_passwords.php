<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/classes/services/BaseDeDonnees.php';

echo "<h1>Mise à jour des mots de passe (hachage)</h1>";

try {
    $db = Classes\Services\BaseDeDonnees::getInstance();
    
    // Récupérer tous les utilisateurs
    $stmt = $db->query("SELECT id, mot_de_passe FROM utilisateur");
    $users = $stmt->fetchAll();
    
    $count = 0;
    foreach ($users as $user) {
        $currentPassword = $user['mot_de_passe'];
        
        // Vérifier si le mot de passe est déjà haché (commence par $2y$)
        if (strpos($currentPassword, '$2y$') !== 0) {
            // Hacher le mot de passe
            $hashedPassword = password_hash($currentPassword, PASSWORD_DEFAULT);
            
            // Mettre à jour dans la base
            $update = $db->prepare("UPDATE utilisateur SET mot_de_passe = ? WHERE id = ?");
            $update->execute([$hashedPassword, $user['id']]);
            
            echo "✅ Utilisateur ID {$user['id']} : mot de passe haché<br>";
            $count++;
        } else {
            echo "⏭️ Utilisateur ID {$user['id']} : déjà haché<br>";
        }
    }
    
    echo "<h2>Terminé ! $count mots de passe ont été hachés.</h2>";
    echo "<p>Vous pouvez maintenant utiliser la version avec password_verify()</p>";
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage();
}