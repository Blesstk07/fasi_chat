<?php
$host = 'localhost';
$dbname = 'campus_relay';
$username = 'root';
$password = '1234';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Nouveau hash pour password123
    $newHash = password_hash('password123', PASSWORD_DEFAULT);
    
    echo "<h1>Mise à jour des mots de passe</h1>";
    echo "<p>Nouveau hash : " . $newHash . "</p>";
    
    // Mettre à jour tous les utilisateurs
    $stmt = $pdo->prepare("UPDATE utilisateur SET mot_de_passe = ?");
    $stmt->execute([$newHash]);
    
    echo "<p style='color:green'>✅ " . $stmt->rowCount() . " utilisateurs mis à jour</p>";
    
    // Vérifier
    $stmt = $pdo->prepare("SELECT courriel, mot_de_passe FROM utilisateur WHERE courriel = ?");
    $stmt->execute(['blesstk@fasi.edu']);
    $user = $stmt->fetch();
    
    if (password_verify('password123', $user['mot_de_passe'])) {
        echo "<p style='color:green'>✅ Fonctionne ! Connecte-toi avec blesstk@fasi.edu / password123</p>";
    }
    
    echo "<hr><a href='login.php'>Login</a>";
    
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?>