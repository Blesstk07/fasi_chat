<?php
session_start();
require_once "redirection.php";

function aurise(string $role){
    //verifie si l'utilisateur est connecté
    if(!isset($_SESSION['user'])){
        redirect("controllers","login.php");
    }else{
        //verifie si le role de l'utilisateur correspond au role requis
        if($_SESSION['user']['role'] != $role){
            redirect("controllers","login.php");
        }else{
            //l'utilisateur est autorisé à accéder à la page
            return true;
        }
    }
}