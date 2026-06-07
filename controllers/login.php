<?php
require_once __DIR__ . '/../config/Database.php';
session_start();

if (!empty($_POST['email']) && !empty($_POST['mot_de_passe'])) {
    $email = trim($_POST['email']);
    $pass = $_POST['mot_de_passe'];

    $pdo = (new Database())->getConnection();

    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = :email AND statut = 'actif' LIMIT 1");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $passwordOk = password_verify($pass, $user['mot_de_passe']) || $pass === $user['mot_de_passe'];

        if ($passwordOk) {
            $_SESSION['user'] = [
                'id' => $user['id'],
                'nom' => $user['nom'],
                'email' => $user['email'],
                'role' => $user['role'],
                'promotion_id' => $user['promotion_id']
            ];

            // La messagerie est maintenant la page dynamique principale.
            header('Location: ../messagerie.php');
            exit;
        } else {
            $_SESSION['erreur'] = '<p class="error">Mot de passe incorrect</p>';
            header('Location: ../login.php');
            exit;
        }
    } else {
        $_SESSION['erreur'] = '<p class="error">Email non trouvé</p>';
        header('Location: ../login.php');
            exit;
    }
} else {
    $_SESSION['erreur'] = '<p class="error">Veuillez remplir tous les champs</p>';
    header('Location: ../login.php');
            exit;
}
