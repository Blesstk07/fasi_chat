<?php

//Fonction pour assurer le nettoyage des entrées des infos
function cleanInput($data){
    $data = trim($data);          //supprime les espaces en trop
    $data = strip_tags($data);   //supprime les balises html
    return $data;
}

//Fonction pour assurer la sortie des infos

function X($data){
    return htmlspecialchars($data);
}
