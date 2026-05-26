<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

/*
|--------------------------------------------------------------------------
| CONFIGURATION BASE DE DONNÉES
|--------------------------------------------------------------------------
*/

$host = 'localhost';
$dbname = 'campus_relay';
$username = 'root';
$password = '1234';

$erreur = '';

/*
|--------------------------------------------------------------------------
| SI UTILISATEUR DÉJÀ CONNECTÉ
|--------------------------------------------------------------------------
*/

if (isset($_SESSION['user_id'])) {

    header('Location: index.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| TRAITEMENT FORMULAIRE
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $motDePasse = $_POST['mot_de_passe'] ?? '';

    if (empty($email) || empty($motDePasse)) {

        $erreur = "Veuillez remplir tous les champs.";

    } else {

        try {

            /*
            |--------------------------------------------------------------------------
            | CONNEXION PDO
            |--------------------------------------------------------------------------
            */

            $pdo = new PDO(
                "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
                $username,
                $password
            );

            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            /*
            |--------------------------------------------------------------------------
            | RECHERCHE UTILISATEUR
            |--------------------------------------------------------------------------
            */

            $stmt = $pdo->prepare("
                SELECT *
                FROM utilisateur
                WHERE courriel = ?
                OR identification = ?
                LIMIT 1
            ");

            $stmt->execute([$email, $email]);

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            /*
            |--------------------------------------------------------------------------
            | VÉRIFICATION MOT DE PASSE
            |--------------------------------------------------------------------------
            */

            if (
                $user &&
                password_verify($motDePasse, $user['mot_de_passe'])
            ) {

                /*
                |--------------------------------------------------------------------------
                | SESSIONS
                |--------------------------------------------------------------------------
                */

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_nom'] = $user['nom_complet'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_identifiant'] = $user['identification'];

                header('Location: index.php');
                exit;

            } else {

                $erreur = "Email / identifiant ou mot de passe incorrect.";
            }

        } catch (PDOException $e) {

            $erreur = "Erreur de connexion à la base de données.";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Connexion - Campus Relay</title>

    <style>

        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
        }

        body{
            font-family:"Segoe UI",sans-serif;
            min-height:100vh;
            display:flex;
            justify-content:center;
            align-items:center;
            overflow:hidden;
            background:#050816;
            color:white;
        }

        /*
        |--------------------------------------------------------------------------
        | BACKGROUND
        |--------------------------------------------------------------------------
        */

        body::before{
            content:'';
            position:fixed;
            width:200%;
            height:200%;
            background:
                radial-gradient(circle at top left, rgba(0,247,255,0.12), transparent 30%),
                radial-gradient(circle at bottom right, rgba(255,0,255,0.10), transparent 30%),
                radial-gradient(circle at center, rgba(255,255,0,0.06), transparent 30%);
            animation:bgRotate 18s linear infinite;
            z-index:-1;
        }

        @keyframes bgRotate{

            from{
                transform:rotate(0deg);
            }

            to{
                transform:rotate(360deg);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | LOGIN CARD
        |--------------------------------------------------------------------------
        */

        .login-container{
            width:100%;
            max-width:430px;
            padding:20px;
        }

        .login-card{
            background:rgba(10,15,35,0.88);
            backdrop-filter:blur(16px);
            border:1px solid rgba(255,255,255,0.08);
            border-radius:28px;
            padding:40px;
            box-shadow:0 0 40px rgba(0,0,0,0.35);
            animation:fadeUp 0.8s ease;
        }

        .logo{
            text-align:center;
            margin-bottom:25px;
        }

        .logo h1{
            font-size:38px;
            color:#00f7ff;
            text-shadow:0 0 15px #00f7ff;
            margin-bottom:8px;
        }

        .logo p{
            color:#b7c2ff;
            font-size:15px;
        }

        /*
        |--------------------------------------------------------------------------
        | ALERTES
        |--------------------------------------------------------------------------
        */

        .alert{
            background:rgba(255,0,80,0.15);
            border:1px solid rgba(255,0,80,0.4);
            color:#ff8ba7;
            padding:14px;
            border-radius:14px;
            margin-bottom:20px;
            text-align:center;
        }

        .info{
            background:rgba(0,247,255,0.10);
            border:1px solid rgba(0,247,255,0.25);
            color:#c8f9ff;
            padding:14px;
            border-radius:14px;
            margin-bottom:25px;
            text-align:center;
            font-size:14px;
        }

        /*
        |--------------------------------------------------------------------------
        | FORMULAIRE
        |--------------------------------------------------------------------------
        */

        .form-group{
            margin-bottom:20px;
        }

        .form-group label{
            display:block;
            margin-bottom:8px;
            color:#dce3ff;
            font-weight:500;
        }

        .form-group input{
            width:100%;
            padding:15px 18px;
            border:none;
            border-radius:16px;
            background:rgba(255,255,255,0.08);
            color:white;
            font-size:15px;
            outline:none;
            transition:0.3s;
        }

        .form-group input:focus{
            border:1px solid #00f7ff;
            box-shadow:0 0 15px rgba(0,247,255,0.3);
        }

        .form-group input::placeholder{
            color:#a9b0d3;
        }

        /*
        |--------------------------------------------------------------------------
        | BUTTON
        |--------------------------------------------------------------------------
        */

        .btn{
            width:100%;
            padding:15px;
            border:none;
            border-radius:18px;
            background:linear-gradient(
                135deg,
                #00f7ff,
                #ff00ff
            );
            color:black;
            font-size:16px;
            font-weight:bold;
            cursor:pointer;
            transition:0.3s;
        }

        .btn:hover{
            transform:translateY(-3px);
            box-shadow:0 0 25px rgba(0,247,255,0.4);
        }

        /*
        |--------------------------------------------------------------------------
        | COMPTES TEST
        |--------------------------------------------------------------------------
        */

        .test-accounts{
            margin-top:25px;
            padding-top:20px;
            border-top:1px solid rgba(255,255,255,0.08);
            text-align:center;
            font-size:13px;
            color:#b7c2ff;
            line-height:1.7;
        }

        /*
        |--------------------------------------------------------------------------
        | ANIMATION
        |--------------------------------------------------------------------------
        */

        @keyframes fadeUp{

            from{
                opacity:0;
                transform:translateY(25px);
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

        @media(max-width:500px){

            .login-card{
                padding:30px 25px;
            }

            .logo h1{
                font-size:30px;
            }
        }

    </style>

</head>

<body>

    <div class="login-container">

        <div class="login-card">

            <!-- LOGO -->

            <div class="logo">

                <h1>⚡ Campus Relay</h1>

                <p>
                    Plateforme intelligente de communication académique
                </p>

            </div>

            <!-- INFO -->

            <div class="info">

                🔐 Mot de passe de test :
                <strong>password123</strong>

            </div>

            <!-- ERREUR -->

            <?php if (!empty($erreur)): ?>

                <div class="alert">

                    ⚠️ <?= htmlspecialchars($erreur) ?>

                </div>

            <?php endif; ?>

            <!-- FORMULAIRE -->

            <form method="POST">

                <div class="form-group">

                    <label>
                        Email ou identifiant
                    </label>

                    <input
                        type="text"
                        name="email"
                        placeholder="Ex : doyen@fasi.edu"
                        required
                    >

                </div>

                <div class="form-group">

                    <label>
                        Mot de passe
                    </label>

                    <input
                        type="password"
                        name="mot_de_passe"
                        placeholder="password123"
                        required
                    >

                </div>

                <button type="submit" class="btn">

                    Se connecter →

                </button>

            </form>

            <!-- COMPTES TEST -->

            <div class="test-accounts">

                <p>
                    <strong>Comptes de test :</strong>
                </p>

                <p>
                    doyen@fasi.edu
                    |
                    enseignant@fasi.edu
                    |
                    samiel@fasi.edu
                </p>

                <p>
                    <strong>Mot de passe :</strong>
                    password123
                </p>

            </div>

        </div>

    </div>

</body>

</html>