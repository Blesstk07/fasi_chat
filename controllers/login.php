<?php
require_once "config/Database.php";
require_once "helpers/redirection.php";
session_start();

$_SESSION["erreur"]="";

if (!empty($_POST["matricule"]) && !empty($_POST["mot_de_passe"])) {
    $id=$_POST["matricule"];
    $pass=$_POST["mot_de_passe"];

    $pdo=new Database();
    $pdo = $pdo->getConnection();


    $pass_db=$pdo->prepare("SELECT * FROM utilisateurs WHERE email= :email LIMIT 1 ");
    $pass_db->execute(["email"=>$id]);
    $user=$pass_db->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        if ($pass===$user["mot_de_passe"]) {
            $_SESSION["user"]= [
                'id' => $user['id'],
                'nom' => $user['nom'],
                'email' => $user['email'],
                'role' => $user['roles']
            ];
            $role=$user["role"];
            switch ($role) {
                case 'etudiant':
                    redirect("views","dashboard_etudiant.php");
                    break;
                case 'enseignant':
                    redirect("views","dashboard_enseignant.php");
                    break;
                case 'apparitaire':
                    redirect("views","dashboard_apparitaire.php");
                    break;
                case 'vice_doyen':
                    redirect("views","dashboard_vice_doyen.php");
                    break;
                case 'admin':
                    redirect("views","dashboard_admin.php");
                    break;
                default:
                    $_SESSION["erreur"]='<p class="error">Mauvais rôle inscrit</p>';
                    break;
            }
        }else{
            $_SESSION["erreur"] = '<p class="error">Mot de passe incorrect</p>';
            redirect("views","login.php");
        }
    }else{
        $_SESSION["erreur"]='<p class="error">Email non trouvé</p>';
        redirect("views","login.php");
    }
}else{
    $_SESSION["erreur"] = '<p class="error">Veuillez remplir tous les champs</p>';
    redirect("views","login.php");
}