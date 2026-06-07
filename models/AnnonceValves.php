<?php
// models/AnnonceValve.php

class AnnonceValve {
    // Instance de connexion PDO
    private $db;

    // Propriétés correspondant exactement aux colonnes de ta table
    private $id;
    private $auteur_id;
    private $categorie;
    private $titre;
    private $contenu;
    private $date_publication;
    private $date_expiration;
    private $fichier_joint;
    private $created_at;

    // Le constructeur reçoit la connexion PDO globale depuis le contrôleur
    public function __construct($database) {
        $this->db = $database;
    }

    /**
     * Récupère TOUTES les annonces actives (non expirées)
     * Jointure avec utilisateurs pour afficher le nom et le rôle de l'auteur sur le Front
     */
    public function findAll() {
        $query = "SELECT va.*, u.nom AS auteur_nom, u.role AS auteur_role 
                  FROM valve_annonces va
                  LEFT JOIN utilisateurs u ON va.auteur_id = u.id
                  WHERE va.date_expiration IS NULL OR va.date_expiration >= CURDATE()
                  ORDER BY va.date_publication DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Filtre les annonces par catégorie (ex: 'urgent', 'convocation', 'info', 'academique')
     */
    public function findByCategorie($categorie) {
        $query = "SELECT va.*, u.nom AS auteur_nom, u.role AS auteur_role 
                  FROM valve_annonces va
                  LEFT JOIN utilisateurs u ON va.auteur_id = u.id
                  WHERE va.categorie = :categorie 
                  AND (va.date_expiration IS NULL OR va.date_expiration >= CURDATE())
                  ORDER BY va.date_publication DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':categorie', $categorie, PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Moteur de recherche : cherche un mot-clé dans le titre ou le contenu
     */
    public function search($keyword) {
        $query = "SELECT va.*, u.nom AS auteur_nom, u.role AS auteur_role 
                  FROM valve_annonces va
                  LEFT JOIN utilisateurs u ON va.auteur_id = u.id
                  WHERE (va.titre LIKE :keyword OR va.contenu LIKE :keyword)
                  AND (va.date_expiration IS NULL OR va.date_expiration >= CURDATE())
                  ORDER BY va.date_publication DESC";
        
        $stmt = $this->db->prepare($query);
        $searchParam = "%" . $keyword . "%";
        $stmt->bindParam(':keyword', $searchParam, PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Insère une nouvelle annonce dans la base de données
     */
    public function save() {
        $query = "INSERT INTO valve_annonces (auteur_id, categorie, titre, contenu, date_publication, date_expiration, fichier_joint) 
                  VALUES (:auteur_id, :categorie, :titre, :contenu, NOW(), :date_expiration, :fichier_joint)";
        
        $stmt = $this->db->prepare($query);
        
        // Liaison des paramètres avec sécurisation des types
        $stmt->bindParam(':auteur_id', $this->auteur_id, PDO::PARAM_INT);
        $stmt->bindParam(':categorie', $this->categorie, PDO::PARAM_STR);
        $stmt->bindParam(':titre', $this->titre, PDO::PARAM_STR);
        $stmt->bindParam(':contenu', $this->contenu, PDO::PARAM_STR);
        $stmt->bindParam(':date_expiration', $this->date_expiration, $this->date_expiration ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindParam(':fichier_joint', $this->fichier_joint, $this->fichier_joint ? PDO::PARAM_STR : PDO::PARAM_NULL);
        
        return $stmt->execute();
    }

    // =========================================================
    // GETTERS ET SETTERS (Indispensables pour le contrôleur)
    // =========================================================

    public function getId() { return $this->id; }

    public function getAuteurId() { return $this->auteur_id; }
    public function setAuteurId($auteur_id) { $this->auteur_id = (int)$auteur_id; }

    public function getCategorie() { return $this->categorie; }
    public function setCategorie($categorie) { $this->categorie = $categorie; }

    public function getTitre() { return $this->titre; }
    public function setTitre($titre) { $this->titre = $titre; }

    public function getContenu() { return $this->contenu; }
    public function setContenu($contenu) { $this->contenu = $contenu; }

    public function getDatePublication() { return $this->date_publication; }

    public function getDateExpiration() { return $this->date_expiration; }
    public function setDateExpiration($date_expiration) { $this->date_expiration = $date_expiration; }

    public function getFichierJoint() { return $this->fichier_joint; }
    public function setFichierJoint($fichier_joint) { $this->fichier_joint = $fichier_joint; }
}