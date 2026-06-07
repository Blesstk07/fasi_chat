<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: messagerie.php");
    exit;
}

header("Location: login.php");
exit;