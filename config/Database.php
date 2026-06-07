<?php

class Database{

    private string $host = 'localhost:3306';
    private string $dbName = 'fasichat';
    private string $username = 'root';
    private string $password = '1234';

    // Php Data Object
    private ?PDO  $connexion = null;


    public function __construct() {
        $this -> connexion;
    }
    
    private function connect(){
        try {
            // dsn = data source name
            $dsn = "mysql:host={$this->host};dbname={$this->dbName};charset=utf8";

            $this->connexion = new PDO(
                $dsn,
                $this->username,
                $this->password
            );

            // setAttribute permet de gérer les erreurs SQL
            $this -> connexion -> setAttribute (
                PDO :: ATTR_ERRMODE,
                PDO :: ERRMODE_EXCEPTION 
            );

            $this -> connexion -> setAttribute (
                PDO :: ATTR_DEFAULT_FETCH_MODE,
                PDO :: FETCH_ASSOC
            );

                }
         catch (PDOException $e) {
            die("Erreur de connexion: ". $e -> getMessage());
        }
    }

    public function getConnection(): PDO {
        $this -> connect();
        return $this -> connexion;
    }

}