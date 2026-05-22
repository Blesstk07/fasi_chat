<?php

function redirect(string $dossier,string $pages){
    header("location: ../".$dossier."/".$pages);
    exit;

}