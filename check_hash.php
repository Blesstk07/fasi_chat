<?php
$host = 'localhost';
$dbname = 'campus_relay';
$username = 'root';
$password = '1234';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Vérifier le hash pour blesstk@fasi.edu
    $stmt = $pdo->prepare("SELECT id, nom_complet, courriel, mot_de_passe FROM utilisateur WHERE courriel = ?");
    $stmt->execute(['blesstk@fasi.edu']);
    $user = $stmt->fetch();
    
    if ($user) {
        echo "<h2>Utilisateur trouvé : " . $user['nom_complet'] . "</h2>";
        echo "<p><strong>Hash stocké :</strong> " . $user['mot_de_passe'] . "</p>";
        
        // Tester password_verify
        $testPassword = 'password123';
        if (password_verify($testPassword, $user['mot_de_passe'])) {
            echo "<p style='color:green'>✅ password_verify fonctionne avec 'password123'</p>";
        } else {
            echo "<p style='color:red'>❌ password_verify échoue avec 'password123'</p>";
        }
        
        // Générer un nouveau hash
        $newHash = password_hash('password123', PASSWORD_DEFAULT);
        echo "<p><strong>Nouveau hash pour 'password123' :</strong> " . $newHash . "</p>";
    } else {
        echo "<p style='color:red'>Utilisateur blesstk@fasi.edu non trouvé</p>";
    }
    
    // Lister tous les utilisateurs
    echo "<h2>Tous les utilisateurs :</h2>";
    $stmt = $pdo->query("SELECT id, nom_complet, courriel, role FROM utilisateur ORDER BY role");
    echo "<ul>";
    while ($user = $stmt->fetch()) {
        echo "<li>" . $user['id'] . " - " . $user['nom_complet'] . " - " . $user['courriel'] . " - " . $user['role'] . "</li>";
    }
    echo "</ul>";
    
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?>