<?php
$host = 'localhost';
$dbname = 'campus_relay';
$username = 'root';
$password = '1234';

echo "<h1>Diagnostic complet des mots de passe</h1>";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Récupérer le doyen
    $stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE courriel = ?");
    $stmt->execute(['doyen@fasi.edu']);
    $user = $stmt->fetch();
    
    if ($user) {
        echo "<h2>Test pour doyen@fasi.edu</h2>";
        echo "<p><strong>Hash stocké :</strong> " . $user['mot_de_passe'] . "</p>";
        
        // Tester différents mots de passe
        $passwords = ['123456', 'password123', 'Password123', 'doyen123', 'admin123'];
        
        echo "<h3>Test de différents mots de passe :</h3>";
        echo "<table border='1' cellpadding='8'>";
        echo "<tr><th>Mot de passe testé</th><th>password_verify()</th><th>Comparaison directe</th></tr>";
        
        foreach ($passwords as $testMdp) {
            $verifyResult = password_verify($testMdp, $user['mot_de_passe']);
            $directResult = ($user['mot_de_passe'] === $testMdp);
            
            $verifyText = $verifyResult ? "✅ OK" : "❌ ÉCHEC";
            $directText = $directResult ? "✅ OK" : "❌ ÉCHEC";
            $color = ($verifyResult || $directResult) ? "#d4edda" : "#f8d7da";
            
            echo "<tr style='background:$color'>";
            echo "<td><strong>$testMdp</strong></td>";
            echo "<td>$verifyText</td>";
            echo "<td>$directText</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Vérifier si le hash est valide
        $hashInfo = password_get_info($user['mot_de_passe']);
        echo "<h3>Informations sur le hash :</h3>";
        echo "<pre>";
        print_r($hashInfo);
        echo "</pre>";
        
        // Tenter de recréer le hash avec password_hash
        echo "<h3>Test de recréation du hash :</h3>";
        $newHash = password_hash('password123', PASSWORD_DEFAULT);
        echo "<p>Nouveau hash pour 'password123' : $newHash</p>";
        echo "<p>Hash stocké : " . $user['mot_de_passe'] . "</p>";
        
        if ($newHash === $user['mot_de_passe']) {
            echo "<p style='color:green'>✅ Les hashs sont identiques</p>";
        } else {
            echo "<p style='color:orange'>⚠️ Les hashs sont différents (c'est normal, bcrypt utilise un sel aléatoire)</p>";
        }
    }
    
    // Tester la connexion avec PDO directement
    echo "<h2>Test de connexion directe avec PDO</h2>";
    
    $testEmail = 'doyen@fasi.edu';
    $testPassword = 'password123';
    
    $stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE courriel = ? OR identification = ?");
    $stmt->execute([$testEmail, $testEmail]);
    $user = $stmt->fetch();
    
    if ($user) {
        echo "<p>Utilisateur trouvé : {$user['nom_complet']}</p>";
        
        if (password_verify($testPassword, $user['mot_de_passe'])) {
            echo "<p style='color:green; font-size:18px; font-weight:bold'>✅ CONNEXION RÉUSSIE AVEC password_verify()</p>";
        } else {
            echo "<p style='color:red; font-size:18px; font-weight:bold'>❌ ÉCHEC DE password_verify()</p>";
        }
    }
    
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?>