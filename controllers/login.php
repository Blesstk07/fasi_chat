<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../helpers/redirection.php';
session_start();

if (!empty($_POST["email"]) && !empty($_POST["mot_de_passe"])) {
    $email = $_POST["email"];
    $pass = $_POST["mot_de_passe"];

    $pdo = new Database();
    $pdo = $pdo->getConnection();

    $pass_db = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = :email LIMIT 1");
    $pass_db->execute(["email" => $email]);
    $user = $pass_db->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        if ($pass === $user["mot_de_passe"]) {
            $_SESSION["user"] = [
                'id' => $user['id'],
                'nom' => $user['nom'],
                'email' => $user['email'],
                'role' => $user['role']
            ];
            $role = $user["role"];
            switch ($role) {
                case 'etudiant':
                    redirect("..","dashboard_etudiant.html");
                    break;
                case 'enseignant':
                    redirect("..","dashboard_enseignant.html");
                    break;
                case 'apparitaire':
                    redirect("..","dashboard_apparitaire.html");
                    break;
                case 'vice_doyen':
                    redirect("..","dashboard_vicedoyen.html");
                    break;
                case 'admin':
                    redirect("..","dashboard_admin.html");
                    break;
                default:
                    $_SESSION["erreur"] = '<p class="error">Mauvais rôle inscrit</p>';
                    redirect("..","login.php");
                    break;
            }
        } else {
            $_SESSION["erreur"] = '<p class="error">Mot de passe incorrect</p>';
            redirect("..","login.php");
        }
    } else {
        $_SESSION["erreur"] = '<p class="error">Email non trouvé</p>';
        redirect("..","login.php");
    }
} else {
    $_SESSION["erreur"] = '<p class="error">Veuillez remplir tous les champs</p>';
    redirect("..","login.php");
}