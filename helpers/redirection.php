<?php

function redirect(string $dossier, string $pages = ''){
    if ($pages === '') {
        header("Location: {$dossier}");
    } else {
        header("Location: ../{$dossier}/{$pages}");
    }
    exit;

}