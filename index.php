<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

/*
|--------------------------------------------------------------------------
| VÉRIFICATION SESSION
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| CONNEXION BASE DE DONNÉES
|--------------------------------------------------------------------------
*/

$host = 'localhost';
$dbname = 'campus_relay';
$username = 'root';
$password = '1234';

try {

    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {

    die("Erreur connexion DB : " . $e->getMessage());
}

/*
|--------------------------------------------------------------------------
| DONNÉES UTILISATEUR
|--------------------------------------------------------------------------
*/

$nom = $_SESSION['user_nom'] ?? 'Utilisateur';
$role = $_SESSION['user_role'] ?? 'inconnu';
$identifiant = $_SESSION['user_identifiant'] ?? '---';
$userId = $_SESSION['user_id'];

$rolesFR = [
    'etudiant'     => 'Étudiant',
    'enseignant'   => 'Enseignant',
    'assistant'    => 'Assistant',
    'doyen'        => 'Doyen',
    'vice_doyen'   => 'Vice-Doyen',
    'apparitaire'  => 'Apparitaire'
];

$roleFR = $rolesFR[$role] ?? ucfirst($role);

/*
|--------------------------------------------------------------------------
| COULEURS PAR RÔLE
|--------------------------------------------------------------------------
*/

$roleColors = [
    'etudiant'     => '#00f7ff',
    'enseignant'   => '#ff00ff',
    'assistant'    => '#00ff88',
    'doyen'        => '#ffcc00',
    'vice_doyen'   => '#ff6600',
    'apparitaire'  => '#ff3366'
];

$mainColor = $roleColors[$role] ?? '#00f7ff';

/*
|--------------------------------------------------------------------------
| STATISTIQUES
|--------------------------------------------------------------------------
*/

try {

    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM messages
        WHERE utilisateur_id != ?
    ");

    $stmt->execute([$userId]);

    $totalMessages = $stmt->fetchColumn();

} catch (Exception $e) {

    $totalMessages = 0;
}

try {

    $stmt = $pdo->query("
        SELECT COUNT(*) 
        FROM annonces
    ");

    $totalAnnonces = $stmt->fetchColumn();

} catch (Exception $e) {

    $totalAnnonces = 0;
}

try {

    $stmt = $pdo->query("
        SELECT COUNT(*) 
        FROM utilisateur
    ");

    $totalMembres = $stmt->fetchColumn();

} catch (Exception $e) {

    $totalMembres = 0;
}

try {

    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM conversations c
        JOIN participants_conversation pc
        ON c.id = pc.conversation_id
        WHERE pc.utilisateur_id = ?
    ");

    $stmt->execute([$userId]);

    $totalConversations = $stmt->fetchColumn();

} catch (Exception $e) {

    $totalConversations = 0;
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>FasiChat Classroom</title>

    <style>

        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
        }

        body{
            font-family:"Segoe UI", sans-serif;
            background:#040816;
            color:white;
            overflow-x:hidden;
            min-height:100vh;
        }

        /*
        |--------------------------------------------------------------------------
        | BACKGROUND CYBERPUNK
        |--------------------------------------------------------------------------
        */

        body::before{
            content:'';
            position:fixed;
            inset:0;
            width:200%;
            height:200%;
            background:
                radial-gradient(circle at center, rgba(0,247,255,0.15), transparent 30%),
                radial-gradient(circle at top left, rgba(255,0,255,0.10), transparent 25%),
                radial-gradient(circle at bottom right, rgba(255,180,0,0.08), transparent 25%);
            animation:bgMove 20s linear infinite;
            z-index:-2;
        }

        body::after{
            content:'';
            position:fixed;
            inset:0;
            background-image:
                linear-gradient(rgba(255,255,255,0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.03) 1px, transparent 1px);
            background-size:40px 40px;
            z-index:-1;
        }

        @keyframes bgMove{

            from{
                transform:translate(-10%, -10%) rotate(0deg);
            }

            to{
                transform:translate(-10%, -10%) rotate(360deg);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | NAVBAR
        |--------------------------------------------------------------------------
        */

        .navbar{
            width:100%;
            padding:20px 40px;
            display:flex;
            justify-content:space-between;
            align-items:center;
            background:rgba(5,10,25,0.88);
            backdrop-filter:blur(15px);
            border-bottom:1px solid rgba(255,255,255,0.08);
            position:sticky;
            top:0;
            z-index:100;
        }

        .logo{
            font-size:30px;
            font-weight:bold;
            color:<?= $mainColor ?>;
            text-shadow:0 0 12px <?= $mainColor ?>;
            animation:glow 2s infinite alternate;
        }

        @keyframes glow{

            from{
                text-shadow:0 0 10px <?= $mainColor ?>;
            }

            to{
                text-shadow:
                    0 0 20px <?= $mainColor ?>,
                    0 0 40px <?= $mainColor ?>;
            }
        }

        .user-box{
            display:flex;
            align-items:center;
            gap:15px;
            flex-wrap:wrap;
        }

        .badge{
            padding:10px 18px;
            border-radius:30px;
            background:rgba(255,255,255,0.05);
            border:1px solid rgba(255,255,255,0.08);
            box-shadow:0 0 15px rgba(0,0,0,0.4);
            transition:0.3s;
        }

        .badge:hover{
            transform:translateY(-3px);
            border-color:<?= $mainColor ?>;
            box-shadow:0 0 15px <?= $mainColor ?>55;
        }

        .logout{
            text-decoration:none;
            color:white;
            padding:12px 20px;
            border-radius:14px;
            font-weight:bold;
            background:linear-gradient(135deg,#ff0055,#ff6600);
            transition:0.3s;
        }

        .logout:hover{
            transform:scale(1.05);
            box-shadow:0 0 20px #ff0055;
        }

        /*
        |--------------------------------------------------------------------------
        | CONTAINER
        |--------------------------------------------------------------------------
        */

        .container{
            max-width:1350px;
            margin:auto;
            padding:40px 25px;
        }

        /*
        |--------------------------------------------------------------------------
        | HERO
        |--------------------------------------------------------------------------
        */

        .hero{
            background:rgba(255,255,255,0.05);
            border:1px solid rgba(255,255,255,0.08);
            border-radius:28px;
            padding:45px;
            margin-bottom:35px;
            backdrop-filter:blur(12px);
            animation:fadeUp 1s ease;
            position:relative;
            overflow:hidden;
        }

        .hero::before{
            content:'';
            position:absolute;
            width:300px;
            height:300px;
            background:<?= $mainColor ?>22;
            border-radius:50%;
            top:-100px;
            right:-100px;
            filter:blur(80px);
        }

        .hero h1{
            font-size:48px;
            margin-bottom:12px;
            color:<?= $mainColor ?>;
            text-shadow:0 0 15px <?= $mainColor ?>;
        }

        .hero p{
            font-size:18px;
            color:#d0d7ff;
            max-width:700px;
            line-height:1.7;
        }

        /*
        |--------------------------------------------------------------------------
        | STATS
        |--------------------------------------------------------------------------
        */

        .stats{
            display:grid;
            grid-template-columns:repeat(auto-fit,minmax(240px,1fr));
            gap:25px;
            margin-bottom:40px;
        }

        .card{
            background:rgba(255,255,255,0.05);
            border:1px solid rgba(255,255,255,0.08);
            border-radius:25px;
            padding:30px;
            text-align:center;
            transition:0.4s;
            position:relative;
            overflow:hidden;
            backdrop-filter:blur(10px);
            text-decoration:none;
            color:white;
            animation:fadeUp 1s ease;
        }

        .card::before{
            content:'';
            position:absolute;
            width:150%;
            height:150%;
            background:linear-gradient(
                45deg,
                transparent,
                rgba(255,255,255,0.08),
                transparent
            );
            transform:rotate(25deg);
            top:-150%;
            left:-150%;
            transition:0.8s;
        }

        .card:hover::before{
            top:120%;
            left:120%;
        }

        .card:hover{
            transform:translateY(-10px) scale(1.02);
            border-color:<?= $mainColor ?>;
            box-shadow:
                0 0 20px <?= $mainColor ?>66,
                0 0 40px <?= $mainColor ?>33;
        }

        .icon{
            font-size:52px;
            margin-bottom:15px;
        }

        .card h3{
            color:#d6d9ff;
            margin-bottom:10px;
            font-size:20px;
        }

        .number{
            font-size:52px;
            font-weight:bold;
            color:<?= $mainColor ?>;
            text-shadow:0 0 15px <?= $mainColor ?>;
        }

        .small-text{
            margin-top:10px;
            color:#9ea9ff;
            font-size:14px;
        }

        /*
        |--------------------------------------------------------------------------
        | ROADMAP
        |--------------------------------------------------------------------------
        */

        .roadmap{
            background:rgba(255,255,255,0.05);
            border:1px solid rgba(255,255,255,0.08);
            border-radius:28px;
            padding:35px;
            backdrop-filter:blur(12px);
            animation:fadeUp 1.2s ease;
        }

        .roadmap h2{
            margin-bottom:25px;
            color:<?= $mainColor ?>;
            text-shadow:0 0 10px <?= $mainColor ?>;
        }

        .step{
            padding:16px 18px;
            border-radius:16px;
            margin-bottom:14px;
            background:rgba(255,255,255,0.04);
            transition:0.3s;
            border-left:4px solid transparent;
        }

        .step:hover{
            transform:translateX(8px);
            background:rgba(255,255,255,0.08);
            border-left-color:<?= $mainColor ?>;
        }

        /*
        |--------------------------------------------------------------------------
        | ANIMATIONS
        |--------------------------------------------------------------------------
        */

        @keyframes fadeUp{

            from{
                opacity:0;
                transform:translateY(30px);
            }

            to{
                opacity:1;
                transform:translateY(0);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | RESPONSIVE
        |--------------------------------------------------------------------------
        */

        @media(max-width:900px){

            .navbar{
                flex-direction:column;
                gap:20px;
            }

            .hero h1{
                font-size:34px;
            }

            .container{
                padding:25px 15px;
            }
        }

    </style>

</head>

<body>

    <!-- NAVBAR -->

    <nav class="navbar">

        <div class="logo">
            ⚡ FasiChat Classroom
        </div>

        <div class="user-box">

            <div class="badge">
                👋 <?= htmlspecialchars($nom) ?>
            </div>

            <div class="badge">
                🎭 <?= htmlspecialchars($roleFR) ?>
            </div>

            <div class="badge">
                🆔 <?= htmlspecialchars($identifiant) ?>
            </div>

            <a href="logout.php" class="logout">
                Déconnexion
            </a>

        </div>

    </nav>

    <!-- CONTENT -->

    <div class="container">

        <!-- HERO -->

        <section class="hero">

            <h1>
                Bienvenue <?= htmlspecialchars($nom) ?>
            </h1>

            <p>
                Plateforme académique intelligente de communication interne
                de la FASI. Accédez à vos messages, annonces, convocations
                et espaces pédagogiques dans une interface cyberpunk immersive.
            </p>

        </section>

        <!-- STATS -->

        <section class="stats">

            <a href="messagerie.php" class="card">

                <div class="icon">💬</div>

                <h3>Messages</h3>

                <div class="number">
                    <?= $totalMessages ?>
                </div>

                <div class="small-text">
                    Ouvrir la messagerie
                </div>

            </a>

            <a href="valve.php" class="card">

                <div class="icon">📢</div>

                <h3>Annonces</h3>

                <div class="number">
                    <?= $totalAnnonces ?>
                </div>

                <div class="small-text">
                    Voir les publications
                </div>

            </a>

            <div class="card">

                <div class="icon">👥</div>

                <h3>Membres</h3>

                <div class="number">
                    <?= $totalMembres ?>
                </div>

                <div class="small-text">
                    Utilisateurs enregistrés
                </div>

            </div>

            <div class="card">

                <div class="icon">🛰️</div>

                <h3>Conversations</h3>

                <div class="number">
                    <?= $totalConversations ?>
                </div>

                <div class="small-text">
                    Espaces de discussion
                </div>

            </div>

        </section>

        <!-- ROADMAP -->

        <section class="roadmap">

            <h2>
                🚀 Avancement du projet
            </h2>

            <div class="step">
                ✅ Authentification sécurisée avec sessions PHP
            </div>

            <div class="step">
                ✅ Connexion PDO avec la base campus_relay
            </div>

            <div class="step">
                ✅ Architecture POO et organisation MVC
            </div>

            <div class="step">
                ✅ Système de messagerie dynamique
            </div>

            <div class="step">
                ✅ Upload et compression des fichiers
            </div>

            <div class="step">
                ✅ Valve et annonces officielles
            </div>

            <div class="step">
                ⏳ Convocations administratives
            </div>

            <div class="step">
                ⏳ Mur pédagogique interactif
            </div>

            <div class="step">
                ⏳ Notifications temps réel
            </div>

        </section>

    </div>

</body>

</html>