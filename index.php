<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$nom = $_SESSION['user_nom'];
$role = $_SESSION['user_role'];
$identifiant = $_SESSION['user_identifiant'];

$rolesFR = [
    'etudiant' => 'Étudiant',
    'enseignant' => 'Enseignant',
    'assistant' => 'Assistant',
    'doyen' => 'Doyen',
    'vice_doyen' => 'Vice-Doyen',
    'apparitaire' => 'Apparitaire'
];
$roleFR = $rolesFR[$role] ?? $role;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Campus Relay - Tableau de bord</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f2f5;
        }
        .navbar {
            background: linear-gradient(135deg, #1a2980 0%, #26d0ce 100%);
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .navbar h1 { font-size: 1.5rem; }
        .user-info { display: flex; align-items: center; gap: 1rem; }
        .role-badge {
            background: rgba(255,255,255,0.2);
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
        }
        .logout-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            text-decoration: none;
        }
        .container { max-width: 1200px; margin: 0 auto; padding: 2rem; }
        .card {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
        }
        .card h2 { color: #1a2980; margin-bottom: 0.5rem; }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .stat {
            background: white;
            padding: 1.5rem;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .stat h3 { color: #666; margin-bottom: 0.5rem; }
        .stat .number { font-size: 2.5rem; font-weight: bold; color: #1a2980; }
    </style>
</head>
<body>
    <nav class="navbar">
        <h1>🏛️ Campus Relay - FASI</h1>
        <div class="user-info">
            <span>👋 <?= htmlspecialchars($nom) ?></span>
            <span class="role-badge"><?= htmlspecialchars($roleFR) ?></span>
            <span class="role-badge"><?= htmlspecialchars($identifiant) ?></span>
            <a href="logout.php" class="logout-btn">Déconnexion</a>
        </div>
    </nav>
    
    <div class="container">
        <div class="success">
            ✅ Connexion réussie ! Bienvenue sur Campus Relay.
        </div>
        
        <div class="card">
            <h2>Tableau de bord - <?= htmlspecialchars($roleFR) ?></h2>
            <p>Plateforme de communication interne de la Faculté des Sciences de l'Information</p>
        </div>
        
        <div class="stats">
            <div class="stat">
                <h3>💬 Messages</h3>
                <div class="number">0</div>
            </div>
            <div class="stat">
                <h3>📢 Annonces</h3>
                <div class="number">5</div>
            </div>
            <div class="stat">
                <h3>👥 Membres</h3>
                <div class="number">9</div>
            </div>
        </div>
        
        <div class="card">
            <h3>📌 Projet FasiChat Classroom</h3>
            <p><strong>✅ Partie 1 terminée :</strong> Authentification et structure de base</p>
            <p><strong>⏳ Partie 2 :</strong> Hiérarchie des classes (à venir)</p>
            <p><strong>⏳ Partie 3 :</strong> Système de messagerie (à venir)</p>
            <p><strong>⏳ Partie 4 :</strong> Gestion des fichiers (à venir)</p>
            <p><strong>⏳ Partie 5 :</strong> Valve et tableaux de bord (à venir)</p>
        </div>
    </div>
</body>
</html>