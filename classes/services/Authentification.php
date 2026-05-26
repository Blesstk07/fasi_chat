<?php
namespace Classes\Services;

use Classes\Modeles\Utilisateur;

class Authentification {
    private static ?Authentification $instance = null;
    private ?Utilisateur $utilisateurCourant = null;
    private BaseDeDonnees $db;
    
    private function __construct() {
        $this->db = BaseDeDonnees::getInstance();
        $this->chargerSession();
    }
    
    public static function getInstance(): Authentification {
        if (self::$instance === null) {
            self::$instance = new Authentification();
        }
        return self::$instance;
    }
    
    public function login(string $email, string $motDePasse): bool {
        $email = trim(strtolower($email));
        
        // Chercher par email ou identifiant
        $utilisateur = Utilisateur::findByEmail($email);
        
        if (!$utilisateur) {
            $utilisateur = Utilisateur::findByIdentification($email);
        }
        
        if ($utilisateur && $utilisateur->verifierMotDePasse($motDePasse)) {
            $this->utilisateurCourant = $utilisateur;
            
            $_SESSION['utilisateur_id'] = $this->utilisateurCourant->getId();
            $_SESSION['role'] = $this->utilisateurCourant->getRole();
            $_SESSION['nom'] = $this->utilisateurCourant->getNomComplet();
            $_SESSION['auth_time'] = time();
            
            session_regenerate_id(true);
            
            $this->utilisateurCourant->mettreAJourStatut('enligne');
            
            return true;
        }
        
        $_SESSION['auth_error'] = "Email/identifiant ou mot de passe incorrect";
        return false;
    }
    
    public function logout(): void {
        if ($this->utilisateurCourant) {
            $this->utilisateurCourant->mettreAJourStatut('hors-ligne');
        }
        
        $_SESSION = [];
        
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params["path"]);
        }
        
        session_destroy();
        $this->utilisateurCourant = null;
    }
    
    public function getUtilisateurCourant(): ?Utilisateur {
        return $this->utilisateurCourant;
    }
    
    public function estConnecte(): bool {
        return $this->utilisateurCourant !== null;
    }
    
    public function verifierRole(string $role): bool {
        return $this->estConnecte() && $this->utilisateurCourant->getRole() === $role;
    }
    
    private function chargerSession(): void {
        if (isset($_SESSION['utilisateur_id'])) {
            $utilisateur = Utilisateur::findById($_SESSION['utilisateur_id']);
            if ($utilisateur) {
                $this->utilisateurCourant = $utilisateur;
            }
        }
    }
    
    public function requireLogin(): void {
        if (!$this->estConnecte()) {
            header('Location: login.php');
            exit;
        }
    }
}