<?php
namespace Classes\Services;

use PDO;
use PDOException;
use Config\DatabaseConfig;

class BaseDeDonnees {
    private static ?BaseDeDonnees $instance = null;
    private ?PDO $connection = null;
    
    private function __construct() {
        $this->connect();
    }
    
    public static function getInstance(): BaseDeDonnees {
        if (self::$instance === null) {
            self::$instance = new BaseDeDonnees();
        }
        return self::$instance;
    }
    
    private function connect(): void {
        $config = DatabaseConfig::getConfig();
        
        try {
            $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
            $this->connection = new PDO($dsn, $config['username'], $config['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
        } catch (PDOException $e) {
            die("Erreur de connexion : " . $e->getMessage());
        }
    }
    
    public function getConnection(): PDO {
        return $this->connection;
    }
    
    public function prepare(string $sql): \PDOStatement {
        return $this->connection->prepare($sql);
    }
    
    public function query(string $sql, array $params = []): \PDOStatement {
        $stmt = $this->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
    
    public function lastInsertId(): int {
        return (int)$this->connection->lastInsertId();
    }
    
    private function __clone() {}
    public function __wakeup() {}
}