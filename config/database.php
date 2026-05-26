<?php
namespace Config;

class DatabaseConfig {
    private static array $config = [
        'host' => 'localhost',
        'dbname' => 'campus_relay',     // Votre base existante
        'username' => 'root',
        'password' => '1234',                // Votre mot de passe MySQL
        'charset' => 'utf8mb4',
        'port' => 3306
    ];
    
    public static function getConfig(): array {
        return self::$config;
    }
}