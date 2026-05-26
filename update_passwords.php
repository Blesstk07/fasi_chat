<?php
$host = 'localhost';
$dbname = 'campus_relay';
$username = 'root';
$password = '1234';

echo "<h1>Mise à jour des mots de passe</h1>";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Liste des utilisateurs avec leurs identifiants
    $users = [
        ['id' => 1, 'email' => 'doyen@fasi.edu', 'mdp' => 'password123'],
        ['id' => 2, 'email' => 'vdoyen@fasi.edu', 'mdp' => 'password123'],
        ['id' => 3, 'email' => 'apparitaire@fasi.edu', 'mdp' => 'password123'],
        ['id' => 4, 'email' => 'enseignant@fasi.edu', 'mdp' => 'password123'],
        ['id' => 5, 'email' => 'assistant@fasi.edu', 'mdp' => 'password123'],
        ['id' => 6, 'email' => 'samiel@fasi.edu', 'mdp' => 'password123'],
        ['id' => 7, 'email' => 'beloved@fasi.edu', 'mdp' => 'password123'],
        ['id' => 8, 'email' => 'dan@fasi.edu', 'mdp' => 'password123'],
        ['id' => 9, 'email' => 'divine@fasi.edu', 'mdp' => 'password123'],
    ];
    
    echo "<h2>Mise à jour des mots de passe :</h2>";
    echo "<ul>";
    
    foreach ($users as $user) {
        // Hacher le nouveau mot de passe
        $hashedPassword = password_hash($user['mdp'], PASSWORD_DEFAULT);
        
        // Mettre à jour dans la base
        $stmt = $pdo->prepare("UPDATE utilisateur SET mot_de_passe = ? WHERE id = ?");
        $stmt->execute([$hashedPassword, $user['id']]);
        
        // Vérifier
        $stmt2 = $pdo->prepare("SELECT mot_de_passe FROM utilisateur WHERE id = ?");
        $stmt2->execute([$user['id']]);
        $newHash = $stmt2->fetchColumn();
        
        $verify = password_verify($user['mdp'], $newHash);
        
        if ($verify) {
            echo "<li style='color:green'>✅ {$user['email']} : mot de passe mis à jour avec succès</li>";
        } else {
            echo "<li style='color:red'>❌ {$user['email']} : échec de la mise à jour</li>";
        }
    }
    
    echo "</ul>";
    
    // Vérification finale
    echo "<h2>Vérification finale :</h2>";
    
    $stmt = $pdo->prepare("SELECT id, courriel, mot_de_passe FROM utilisateur WHERE courriel = ?");
    $stmt->execute(['doyen@fasi.edu']);
    $doyen = $stmt->fetch();
    
    if ($doyen && password_verify('password123', $doyen['mot_de_passe'])) {
        echo "<p style='color:green; font-size:18px; font-weight:bold'>✅ DOYEN: Connexion fonctionne maintenant avec password123 !</p>";
    } else {
        echo "<p style='color:red; font-size:18px; font-weight:bold'>❌ DOYEN: La connexion ne fonctionne toujours pas</p>";
    }
    
    echo "<hr>";
    echo "<p><a href='login.php'>Aller à la page de connexion</a></p>";
    
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?>