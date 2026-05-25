<?php
session_start();

// Configuration directe
$host = 'localhost';
$dbname = 'campus_relay';
$username = 'root';
$password = '1234';

$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $motDePasse = $_POST['mot_de_passe'] ?? '';
    
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE courriel = ? OR identification = ?");
        $stmt->execute([$email, $email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($motDePasse, $user['mot_de_passe'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nom'] = $user['nom_complet'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_identifiant'] = $user['identification'];
            header('Location: index.php');
            exit;
        } else {
            $erreur = "Email/identifiant ou mot de passe incorrect";
        }
    } catch (PDOException $e) {
        $erreur = "Erreur technique";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Campus Relay - Connexion</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1a2980 0%, #26d0ce 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .login-container { width: 100%; max-width: 400px; padding: 20px; }
        .login-card {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        .login-card h1 { color: #1a2980; text-align: center; margin-bottom: 10px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; color: #333; font-weight: 500; }
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
        }
        .btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #1a2980 0%, #26d0ce 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }
        .alert {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            background: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }
        .info {
            background: #e7f3ff;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 14px;
        }
        .test-accounts {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #eee;
            font-size: 12px;
            color: #666;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <h1>🏛️ Campus Relay</h1>
            <div class="info">
                🔐 <strong>Mot de passe : password123</strong>
            </div>
            
            <?php if ($erreur): ?>
                <div class="alert">⚠️ <?= htmlspecialchars($erreur) ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label>Email ou Identifiant</label>
                    <input type="text" name="email" required placeholder="ex: doyen@fasi.edu">
                </div>
                <div class="form-group">
                    <label>Mot de passe</label>
                    <input type="password" name="mot_de_passe" required placeholder="password123">
                </div>
                <button type="submit" class="btn">Se connecter</button>
            </form>
            
            <div class="test-accounts">
                <p><strong>Comptes test :</strong> doyen@fasi.edu | enseignant@fasi.edu | samiel@fasi.edu</p>
                <p><strong>Mot de passe :</strong> password123</p>
            </div>
        </div>
    </div>
</body>
</html>