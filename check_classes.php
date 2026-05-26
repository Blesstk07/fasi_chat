<?php
require_once __DIR__ . '/bootstrap.php';

echo "<h1>Vérification des classes</h1>";

// Liste des classes à vérifier
$classes = [
    'Classes\Modeles\Utilisateur',
    'Classes\Modeles\Etudiant',
    'Classes\Modeles\Enseignant',
    'Classes\Modeles\Assistant',
    'Classes\Modeles\Doyen',
    'Classes\Modeles\ViceDoyen',
    'Classes\Modeles\Apparitaire',
    'Classes\Services\BaseDeDonnees',
    'Classes\Services\Authentification',
    'Classes\Traits\ConvocableTrait'
];

echo "<ul>";
foreach ($classes as $class) {
    if (class_exists($class)) {
        echo "<li style='color:green'>✅ $class existe</li>";
    } elseif (trait_exists($class)) {
        echo "<li style='color:green'>✅ $class (trait) existe</li>";
    } else {
        echo "<li style='color:red'>❌ $class n'existe pas</li>";
    }
}
echo "</ul>";

// Tester l'authentification
echo "<h2>Test d'authentification</h2>";
try {
    $auth = Classes\Services\Authentification::getInstance();
    echo "<p style='color:green'>✅ Authentification::getInstance() fonctionne</p>";
} catch (Exception $e) {
    echo "<p style='color:red'>❌ Erreur: " . $e->getMessage() . "</p>";
}

// Tester la connexion à la base
echo "<h2>Test de connexion BDD</h2>";
try {
    $db = Classes\Services\BaseDeDonnees::getInstance();
    $stmt = $db->query("SELECT COUNT(*) as total FROM utilisateur");
    $result = $stmt->fetch();
    echo "<p style='color:green'>✅ Connexion BDD réussie - " . $result['total'] . " utilisateurs</p>";
} catch (Exception $e) {
    echo "<p style='color:red'>❌ Erreur BDD: " . $e->getMessage() . "</p>";
}

// Tester la création d'un utilisateur
echo "<h2>Test de création d'utilisateur</h2>";
try {
    $user = Classes\Modeles\Utilisateur::findByEmail('doyen@fasi.edu');
    if ($user) {
        echo "<p style='color:green'>✅ Utilisateur trouvé: " . $user->getNomComplet() . " (" . $user->getRole() . ")</p>";
    } else {
        echo "<p style='color:orange'>⚠️ Utilisateur non trouvé</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red'>❌ Erreur: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='login.php'>Aller à la page de connexion</a></p>";
?>