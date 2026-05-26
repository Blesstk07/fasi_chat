<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$host = 'localhost';
$dbname = 'campus_relay';
$username = 'root';
$password = '1234';

$userId = $_SESSION['user_id'];
$role = $_SESSION['user_role'];
$nom = $_SESSION['user_nom'];
$identifiant = $_SESSION['user_identifiant'] ?? '---';

$rolesFR = [
    'etudiant' => 'Étudiant',
    'enseignant' => 'Enseignant',
    'assistant' => 'Assistant',
    'doyen' => 'Doyen',
    'vice_doyen' => 'Vice-Doyen',
    'apparitaire' => 'Apparitaire'
];

$roleFR = $rolesFR[$role] ?? ucfirst($role);

$roleColors = [
    'etudiant' => '#00f7ff',
    'enseignant' => '#ff00ff',
    'assistant' => '#00ff88',
    'doyen' => '#ffcc00',
    'vice_doyen' => '#ff6600',
    'apparitaire' => '#ff3366'
];

$mainColor = $roleColors[$role] ?? '#00f7ff';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Récupérer les cours auxquels l'utilisateur est lié
    if ($role === 'enseignant' || $role === 'assistant') {
        $stmt = $pdo->prepare("
            SELECT id, nom, code 
            FROM cours 
            WHERE professeur_id = ? OR id IN (
                SELECT cours_id FROM cours_utilisateur WHERE utilisateur_id = ?
            )
        ");
        $stmt->execute([$userId, $userId]);
        $cours = $stmt->fetchAll();
    } else {
        $stmt = $pdo->prepare("
            SELECT DISTINCT c.id, c.nom, c.code
            FROM cours c
            JOIN cours_utilisateur cu ON c.id = cu.cours_id
            WHERE cu.utilisateur_id = ?
        ");
        $stmt->execute([$userId]);
        $cours = $stmt->fetchAll();
    }
    
    // Traitement publication
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['publier'])) {
        $coursId = (int)$_POST['cours_id'];
        $contenu = trim($_POST['contenu']);
        
        if ($coursId > 0 && !empty($contenu)) {
            $stmt = $pdo->prepare("
                INSERT INTO publications_mur (cours_id, auteur_id, contenu, cree_le)
                VALUES (?, ?, ?, NOW())
            ");
            $stmt->execute([$coursId, $userId, $contenu]);
            header("Location: mur_pedagogique.php?success=1");
            exit;
        }
    }
    
    // Récupérer les publications
    $coursId = isset($_GET['cours']) ? (int)$_GET['cours'] : 0;
    
    if ($coursId > 0) {
        $stmt = $pdo->prepare("
            SELECT p.*, u.nom_complet as auteur_nom, u.role as auteur_role, c.nom as cours_nom
            FROM publications_mur p
            JOIN utilisateur u ON p.auteur_id = u.id
            JOIN cours c ON p.cours_id = c.id
            WHERE p.cours_id = ?
            ORDER BY p.cree_le DESC
        ");
        $stmt->execute([$coursId]);
        $publications = $stmt->fetchAll();
        
        $stmt = $pdo->prepare("SELECT nom FROM cours WHERE id = ?");
        $stmt->execute([$coursId]);
        $courant = $stmt->fetch();
    } else {
        $publications = [];
        $courant = null;
    }
    
} catch (PDOException $e) {
    $error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mur pédagogique - Campus Relay</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Segoe UI", sans-serif;
            background: #040816;
            color: white;
            overflow-x: hidden;
            min-height: 100vh;
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            width: 200%;
            height: 200%;
            background:
                radial-gradient(circle at center, rgba(0,247,255,0.15), transparent 30%),
                radial-gradient(circle at top left, rgba(255,0,255,0.10), transparent 25%),
                radial-gradient(circle at bottom right, rgba(255,180,0,0.08), transparent 25%);
            animation: bgMove 20s linear infinite;
            z-index: -2;
        }

        body::after {
            content: '';
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.03) 1px, transparent 1px);
            background-size: 40px 40px;
            z-index: -1;
        }

        @keyframes bgMove {
            from { transform: translate(-10%, -10%) rotate(0deg); }
            to { transform: translate(-10%, -10%) rotate(360deg); }
        }

        .navbar {
            width: 100%;
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(5,10,25,0.88);
            backdrop-filter: blur(15px);
            border-bottom: 1px solid rgba(255,255,255,0.08);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .logo {
            font-size: 30px;
            font-weight: bold;
            color: <?= $mainColor ?>;
            text-shadow: 0 0 12px <?= $mainColor ?>;
            animation: glow 2s infinite alternate;
        }

        @keyframes glow {
            from { text-shadow: 0 0 10px <?= $mainColor ?>; }
            to { text-shadow: 0 0 20px <?= $mainColor ?>, 0 0 40px <?= $mainColor ?>; }
        }

        .user-box {
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .badge {
            padding: 10px 18px;
            border-radius: 30px;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.08);
            transition: 0.3s;
        }

        .badge:hover {
            transform: translateY(-3px);
            border-color: <?= $mainColor ?>;
            box-shadow: 0 0 15px <?= $mainColor ?>55;
        }

        .logout {
            text-decoration: none;
            color: white;
            padding: 12px 20px;
            border-radius: 14px;
            font-weight: bold;
            background: linear-gradient(135deg, #ff0055, #ff6600);
            transition: 0.3s;
        }

        .logout:hover {
            transform: scale(1.05);
            box-shadow: 0 0 20px #ff0055;
        }

        .container {
            max-width: 1350px;
            margin: auto;
            padding: 40px 25px;
        }

        .hero {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 28px;
            padding: 45px;
            margin-bottom: 35px;
            backdrop-filter: blur(12px);
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            background: <?= $mainColor ?>22;
            border-radius: 50%;
            top: -100px;
            right: -100px;
            filter: blur(80px);
        }

        .hero h1 {
            font-size: 48px;
            margin-bottom: 12px;
            color: <?= $mainColor ?>;
            text-shadow: 0 0 15px <?= $mainColor ?>;
        }

        .hero p {
            font-size: 18px;
            color: #d0d7ff;
            max-width: 700px;
            line-height: 1.7;
        }

        .cours-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .cours-card {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 20px;
            padding: 20px;
            text-decoration: none;
            color: white;
            transition: 0.3s;
            display: block;
        }

        .cours-card:hover {
            transform: translateY(-5px);
            border-color: <?= $mainColor ?>;
            box-shadow: 0 0 20px <?= $mainColor ?>66;
        }

        .cours-card.active {
            border-color: <?= $mainColor ?>;
            background: rgba(255,255,255,0.1);
        }

        .cours-code {
            font-size: 12px;
            color: <?= $mainColor ?>;
            margin-bottom: 8px;
        }

        .cours-nom {
            font-size: 18px;
            font-weight: bold;
        }

        .publish-area {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 25px;
            padding: 25px;
            margin-bottom: 35px;
            backdrop-filter: blur(10px);
        }

        .publish-area h3 {
            color: <?= $mainColor ?>;
            margin-bottom: 15px;
        }

        .publish-form textarea {
            width: 100%;
            padding: 15px;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 15px;
            color: white;
            font-size: 14px;
            resize: vertical;
            margin-bottom: 15px;
        }

        .publish-form textarea:focus {
            outline: none;
            border-color: <?= $mainColor ?>;
        }

        .btn-publish {
            background: linear-gradient(135deg, <?= $mainColor ?>, #ff00ff);
            color: #040816;
            padding: 12px 30px;
            border: none;
            border-radius: 30px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn-publish:hover {
            transform: scale(1.05);
            box-shadow: 0 0 20px <?= $mainColor ?>;
        }

        .publication-card {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 25px;
            padding: 25px;
            margin-bottom: 20px;
            backdrop-filter: blur(10px);
            transition: 0.3s;
        }

        .publication-card:hover {
            transform: translateX(5px);
            border-color: <?= $mainColor ?>;
        }

        .publication-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .auteur-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .auteur-avatar {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, <?= $mainColor ?>, #ff00ff);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 18px;
        }

        .auteur-nom {
            font-weight: bold;
        }

        .auteur-role {
            font-size: 12px;
            color: <?= $mainColor ?>;
        }

        .publication-date {
            font-size: 12px;
            color: #9ea9ff;
        }

        .publication-contenu {
            line-height: 1.6;
            color: #d0d7ff;
            margin-top: 15px;
            white-space: pre-wrap;
        }

        .empty {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 25px;
            padding: 60px;
            text-align: center;
        }

        .empty p {
            color: #9ea9ff;
            font-size: 18px;
        }

        .success {
            background: rgba(0,255,136,0.1);
            border: 1px solid #00ff88;
            border-radius: 15px;
            padding: 15px 20px;
            margin-bottom: 20px;
            color: #00ff88;
        }

        @media(max-width: 900px) {
            .navbar {
                flex-direction: column;
                gap: 20px;
            }
            .hero h1 {
                font-size: 34px;
            }
            .container {
                padding: 25px 15px;
            }
        }
    </style>

</head>

<body>

    <nav class="navbar">
        <div class="logo">
            📝 Mur pédagogique
        </div>
        <div class="user-box">
            <div class="badge">👋 <?= htmlspecialchars($nom) ?></div>
            <div class="badge">🎭 <?= htmlspecialchars($roleFR) ?></div>
            <div class="badge">🆔 <?= htmlspecialchars($identifiant) ?></div>
            <a href="index.php" class="badge" style="text-decoration:none;">🏠 Dashboard</a>
            <a href="logout.php" class="logout">Déconnexion</a>
        </div>
    </nav>

    <div class="container">

        <section class="hero">
            <h1>📝 Mur pédagogique</h1>
            <p>Espace d'échange entre enseignants et étudiants. Questions, annonces, rappels de cours...</p>
        </section>

        <?php if (isset($_GET['success'])): ?>
            <div class="success">✅ Publication ajoutée avec succès !</div>
        <?php endif; ?>

        <!-- Sélection du cours -->
        <?php if (!empty($cours)): ?>
            <div class="cours-grid">
                <?php foreach ($cours as $c): ?>
                    <a href="?cours=<?= $c['id'] ?>" class="cours-card <?= ($coursId == $c['id']) ? 'active' : '' ?>">
                        <div class="cours-code"><?= htmlspecialchars($c['code']) ?></div>
                        <div class="cours-nom">📚 <?= htmlspecialchars($c['nom']) ?></div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty">
                <p>📭 Vous n'êtes inscrit à aucun cours.</p>
            </div>
        <?php endif; ?>

        <!-- Zone de publication (enseignants/assistants seulement) -->
        <?php if (($role === 'enseignant' || $role === 'assistant') && $coursId > 0): ?>
            <div class="publish-area">
                <h3>✏️ Publier sur le mur</h3>
                <form method="POST" class="publish-form">
                    <input type="hidden" name="cours_id" value="<?= $coursId ?>">
                    <textarea name="contenu" rows="4" placeholder="Question, annonce, rappel..." required></textarea>
                    <button type="submit" name="publier" class="btn-publish">📢 Publier</button>
                </form>
            </div>
        <?php endif; ?>

        <!-- Liste des publications -->
        <?php if ($coursId > 0): ?>
            <h2 style="margin-bottom: 20px; color: <?= $mainColor ?>;">
                💬 Discussions - <?= htmlspecialchars($courant['nom'] ?? '') ?>
            </h2>
            
            <?php if (!empty($publications)): ?>
                <?php foreach ($publications as $pub): ?>
                    <div class="publication-card">
                        <div class="publication-header">
                            <div class="auteur-info">
                                <div class="auteur-avatar">
                                    <?= substr(htmlspecialchars($pub['auteur_nom']), 0, 2) ?>
                                </div>
                                <div>
                                    <div class="auteur-nom"><?= htmlspecialchars($pub['auteur_nom']) ?></div>
                                    <div class="auteur-role"><?= $rolesFR[$pub['auteur_role']] ?? $pub['auteur_role'] ?></div>
                                </div>
                            </div>
                            <div class="publication-date">
                                📅 <?= date('d/m/Y H:i', strtotime($pub['cree_le'])) ?>
                            </div>
                        </div>
                        <div class="publication-contenu">
                            <?= nl2br(htmlspecialchars($pub['contenu'])) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty">
                    <p>💬 Aucune publication sur ce mur pour le moment.</p>
                    <?php if ($role === 'enseignant' || $role === 'assistant'): ?>
                        <p style="margin-top: 10px;">Soyez le premier à publier !</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php elseif (!empty($cours)): ?>
            <div class="empty">
                <p>👆 Sélectionnez un cours pour voir son mur pédagogique.</p>
            </div>
        <?php endif; ?>

    </div>

</body>

</html>