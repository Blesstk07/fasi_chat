<?php
require_once __DIR__.'/Database.php';
var_dump((new Database())->getConnection());