<?php
$host = 'localhost';
$dbname = 'campus_relay';
$username = 'root';
$password = '1234';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h1>Vérification de la base campus_relay</h1>";
    
    // Vérifier la structure de la table utilisateur
    $stmt = $pdo->query("DESCRIBE utilisateur");
    echo "<h2>Structure de la table utilisateur :</h2>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Champ</th><th>Type</th><th>Null</th><th>Clé</th></tr>";
    while ($row = $stmt->fetch()) {
        echo "<tr>";
        echo "<td>{$row['Field']}</td>";
        echo "<td>{$row['Type']}</td>";
        echo "<td>{$row['Null']}</td>";
        echo "<td>{$row['Key']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Afficher tous les utilisateurs
    $stmt = $pdo->query("SELECT id, nom_complet, courriel, identification, role, mot_de_passe FROM utilisateur");
    echo "<h2>Liste des utilisateurs :</h2>";
    echo "<table border='1' cellpadding='8'>";
    echo "<tr style='background:#ccc'><th>ID</th><th>Nom</th><th>Email</th><th>Identifiant</th><th>Rôle</th><th>Mot de passe (stocké)</th><th>Longueur</th></tr>";
    
    while ($user = $stmt->fetch()) {
        echo "<tr>";
        echo "<td>{$user['id']}</td>";
        echo "<td>" . htmlspecialchars($user['nom_complet']) . "</td>";
        echo "<td>" . htmlspecialchars($user['courriel']) . "</td>";
        echo "<td>" . htmlspecialchars($user['identification']) . "</td>";
        echo "<td>" . htmlspecialchars($user['role']) . "</td>";
        echo "<td>" . htmlspecialchars($user['mot_de_passe']) . "</td>";
        echo "<td>" . strlen($user['mot_de_passe']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Tester avec les identifiants
    echo "<h2>Test des connexions :</h2>";
    
    $tests = [
        ['courriel' => 'doyen@fasi.edu', 'identification' => 'doyen'],
        ['courriel' => 'enseignant@fasi.edu', 'identification' => 'enseignant'],
        ['courriel' => 'samiel@fasi.edu', 'identification' => 'SI2024001'],
    ];
    
    foreach ($tests as $test) {
        $stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE courriel = ? OR identification = ?");
        $stmt->execute([$test['courriel'], $test['identification']]);
        $user = $stmt->fetch();
        
        if ($user) {
            echo "<p><strong>Test pour {$test['courriel']} :</strong><br>";
            echo "Utilisateur trouvé : {$user['nom_complet']}<br>";
            echo "Mot de passe stocké : '{$user['mot_de_passe']}'<br>";
            echo "Comparaison avec '123456' : ";
            if ($user['mot_de_passe'] === '123456') {
                echo "<span style='color:green'>✅ IDENTIQUE</span>";
            } else {
                echo "<span style='color:red'>❌ DIFFÉRENT</span>";
            }
            echo "</p>";
        } else {
            echo "<p style='color:red'>❌ Utilisateur {$test['courriel']} non trouvé</p>";
        }
    }
    
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?>