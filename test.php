<?php
require_once __DIR__ . '/bootstrap.php';

echo "<h1>Diagnostic Campus Relay</h1>";

// Test 1: Vérifier l'autoloader
echo "<h2>1. Test de l'autoloader</h2>";
try {
    $test = new Classes\Services\BaseDeDonnees();
    echo "✅ BaseDeDonnees chargée avec succès<br>";
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "<br>";
}

try {
    $test2 = new Classes\Services\Authentification();
    echo "✅ Authentification chargée avec succès<br>";
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "<br>";
}

// Test 2: Vérifier la connexion BDD
echo "<h2>2. Test de connexion BDD</h2>";
try {
    $db = Classes\Services\BaseDeDonnees::getInstance();
    $stmt = $db->query("SELECT COUNT(*) as total FROM utilisateur");
    $result = $stmt->fetch();
    echo "✅ Connexion BDD réussie ! " . $result['total'] . " utilisateurs trouvés<br>";
} catch (Exception $e) {
    echo "❌ Erreur BDD: " . $e->getMessage() . "<br>";
}

// Test 3: Vérifier la classe Utilisateur
echo "<h2>3. Test de recherche utilisateur</h2>";
try {
    $user = Classes\Modeles\Utilisateur::findByEmail('doyen@fasi.edu');
    if ($user) {
        echo "✅ Utilisateur trouvé: " . $user->getNomComplet() . " (" . $user->getRole() . ")<br>";
    } else {
        echo "❌ Utilisateur non trouvé<br>";
    }
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "<br>";
}