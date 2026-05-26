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

// Vérifier que seul Doyen ou Vice-Doyen peut accéder
if ($role !== 'doyen' && $role !== 'vice_doyen') {
    header('Location: index.php');
    exit;
}

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

$mainColor = $roleColors[$role] ?? '#ffcc00';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $message = '';
    $erreur = '';
    
    // Traitement du formulaire de convocation
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['convoquer'])) {
        $titre = trim($_POST['titre']);
        $date_reunion = $_POST['date_reunion'];
        $heure_reunion = $_POST['heure_reunion'];
        $lieu = trim($_POST['lieu']);
        $note = trim($_POST['note']);
        
        if (empty($titre) || empty($date_reunion) || empty($heure_reunion) || empty($lieu)) {
            $erreur = "Veuillez remplir tous les champs obligatoires.";
        } else {
            // Récupérer tous les enseignants et assistants
            $stmt = $pdo->prepare("SELECT id FROM utilisateur WHERE role IN ('enseignant', 'assistant')");
            $stmt->execute();
            $destinataires = $stmt->fetchAll();
            
            $success = 0;
            foreach ($destinataires as $dest) {
                $stmt = $pdo->prepare("
                    INSERT INTO reunions (expediteur_id, titre, audience, date_reunion, heure_reunion, lieu, note, cree_le)
                    VALUES (?, ?, 'enseignants_assistants', ?, ?, ?, ?, NOW())
                ");
                if ($stmt->execute([$userId, $titre, $date_reunion, $heure_reunion, $lieu, $note])) {
                    $success++;
                }
            }
            
            if ($success > 0) {
                $message = "✅ Convocation envoyée à $success enseignant(s) et assistant(s).";
            } else {
                $erreur = "❌ Erreur lors de l'envoi de la convocation.";
            }
        }
    }
    
    // Récupérer les convocations envoyées
    $stmt = $pdo->prepare("
        SELECT * FROM reunions 
        WHERE expediteur_id = ? 
        ORDER BY date_reunion DESC, heure_reunion DESC
    ");
    $stmt->execute([$userId]);
    $convocationsEnvoyees = $stmt->fetchAll();
    
    // Récupérer les convocations reçues (si l'utilisateur est enseignant ou assistant)
    if ($role === 'enseignant' || $role === 'assistant') {
        $stmt = $pdo->prepare("
            SELECT * FROM reunions 
            WHERE audience = 'enseignants_assistants'
            ORDER BY date_reunion DESC, heure_reunion DESC
        ");
        $stmt->execute();
        $convocationsRecues = $stmt->fetchAll();
    } else {
        $convocationsRecues = [];
    }
    
} catch (PDOException $e) {
    $erreur = "Erreur technique : " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Convocations - Campus Relay</title>

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

        .form-container {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 25px;
            padding: 30px;
            margin-bottom: 40px;
            backdrop-filter: blur(10px);
        }

        .form-container h2 {
            color: <?= $mainColor ?>;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #d0d7ff;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px 16px;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 12px;
            color: white;
            font-size: 14px;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: <?= $mainColor ?>;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .btn-submit {
            background: linear-gradient(135deg, <?= $mainColor ?>, #ff00ff);
            color: #040816;
            padding: 14px 30px;
            border: none;
            border-radius: 30px;
            font-weight: bold;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn-submit:hover {
            transform: scale(1.05);
            box-shadow: 0 0 20px <?= $mainColor ?>;
        }

        .message {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
        }

        .message-success {
            background: rgba(0,255,136,0.1);
            border: 1px solid #00ff88;
            color: #00ff88;
        }

        .message-error {
            background: rgba(255,0,85,0.1);
            border: 1px solid #ff0055;
            color: #ff0055;
        }

        .section-title {
            color: <?= $mainColor ?>;
            margin-bottom: 20px;
            font-size: 24px;
        }

        .convocation-card {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 15px;
            transition: 0.3s;
        }

        .convocation-card:hover {
            transform: translateX(5px);
            border-color: <?= $mainColor ?>;
        }

        .convocation-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .convocation-titre {
            font-size: 18px;
            font-weight: bold;
            color: <?= $mainColor ?>;
        }

        .convocation-date {
            font-size: 14px;
            color: #ffcc00;
            background: rgba(255,204,0,0.1);
            padding: 5px 12px;
            border-radius: 20px;
        }

        .convocation-info {
            display: flex;
            gap: 20px;
            margin: 15px 0;
            flex-wrap: wrap;
        }

        .convocation-info span {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #d0d7ff;
            font-size: 14px;
        }

        .convocation-note {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid rgba(255,255,255,0.1);
            color: #9ea9ff;
            font-size: 14px;
        }

        .badge-expediteur {
            background: <?= $mainColor ?>22;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
        }

        .empty {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 20px;
            padding: 40px;
            text-align: center;
            color: #9ea9ff;
        }

        @media(max-width: 900px) {
            .navbar {
                flex-direction: column;
                gap: 20px;
            }
            .hero h1 {
                font-size: 34px;
            }
            .form-row {
                grid-template-columns: 1fr;
                gap: 0;
            }
        }
    </style>

</head>

<body>

    <nav class="navbar">
        <div class="logo">
            📅 Convocation - Campus Relay
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
            <h1>📅 Système de convocation</h1>
            <p>Convoquez une réunion officielle à l'attention de tous les enseignants et assistants de la faculté.</p>
        </section>

        <?php if ($message): ?>
            <div class="message message-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <?php if ($erreur): ?>
            <div class="message message-error"><?= htmlspecialchars($erreur) ?></div>
        <?php endif; ?>

        <!-- Formulaire de convocation (Doyen/Vice-Doyen uniquement) -->
        <div class="form-container">
            <h2>📢 Nouvelle convocation</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Objet de la réunion *</label>
                    <input type="text" name="titre" placeholder="Ex: Conseil pédagogique" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Date *</label>
                        <input type="date" name="date_reunion" required>
                    </div>
                    <div class="form-group">
                        <label>Heure *</label>
                        <input type="time" name="heure_reunion" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Lieu *</label>
                    <input type="text" name="lieu" placeholder="Ex: Salle A-12 / Lien Zoom" required>
                </div>
                
                <div class="form-group">
                    <label>Message explicatif (optionnel)</label>
                    <textarea name="note" rows="4" placeholder="Informations complémentaires, ordre du jour..."></textarea>
                </div>
                
                <button type="submit" name="convoquer" class="btn-submit">📅 Envoyer la convocation</button>
            </form>
        </div>

        <!-- Convocations envoyées -->
        <h2 class="section-title">📤 Convocations envoyées</h2>
        <?php if (!empty($convocationsEnvoyees)): ?>
            <?php foreach ($convocationsEnvoyees as $conv): ?>
                <div class="convocation-card">
                    <div class="convocation-header">
                        <span class="convocation-titre"><?= htmlspecialchars($conv['titre']) ?></span>
                        <span class="convocation-date">📅 <?= date('d/m/Y', strtotime($conv['date_reunion'])) ?> à <?= substr($conv['heure_reunion'], 0, 5) ?></span>
                    </div>
                    <div class="convocation-info">
                        <span>📍 <?= htmlspecialchars($conv['lieu']) ?></span>
                        <span>👥 Destinataires: Enseignants et assistants</span>
                        <span>📅 Envoyée le <?= date('d/m/Y', strtotime($conv['cree_le'])) ?></span>
                    </div>
                    <?php if ($conv['note']): ?>
                        <div class="convocation-note">
                            📝 <?= nl2br(htmlspecialchars($conv['note'])) ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty">
                <p>📭 Aucune convocation envoyée pour le moment.</p>
            </div>
        <?php endif; ?>

    </div>

</body>

</html>