<?php
// controllers/ValveController.php

class ValveController {
    private $db;
    private $annonceModel;

    // Le constructeur reçoit la connexion PDO globale de l'application
    public function __construct($database) {
        $this->db = $database;
        // Instanciation du modèle AnnonceValve
        $this->annonceModel = new AnnonceValve($this->db);
    }

    /**
     * Action par défaut : Affiche toutes les annonces ou gère la recherche textuelle
     */
    public function index() {
        // 1. Gestion de la recherche textuelle si le paramètre 'q' est présent dans l'URL
        $searchQuery = isset($_GET['q']) ? $this->cleanInput($_GET['q']) : null;
        
        if (!empty($searchQuery)) {
            $annonces = $this->annonceModel->search($searchQuery);
        } else {
            // Sinon, on récupère toutes les annonces actives par défaut
            $annonces = $this->annonceModel->findAll();
        }

        // 2. Inclusion de la vue du Valve (ton design HTML d'origine)
        // Les variables $annonces et $searchQuery seront directement accessibles dedans
        require_once __DIR__ . '/../views/valve/index.php';
    }

    /**
     * Action de filtrage : Filtre les annonces par catégorie de manière sécurisée
     */
    public function filter() {
        $type = isset($_GET['type']) ? $this->cleanInput($_GET['type']) : 'toutes';
        
        // Liste des catégories valides correspondant à ton design
        $categoriesValides = ['urgent', 'convocation', 'info', 'academique'];

        if (in_array($type, $categoriesValides)) {
            $annonces = $this->annonceModel->findByCategorie($type);
        } else {
            $annonces = $this->annonceModel->findAll();
        }

        // Réinjection dans la vue avec le filtre actif
        require_once __DIR__ . '/../views/valve/index.php';
    }

    /**
     * Action d'enregistrement : Récupère le formulaire du modal et l'ajoute en BDD
     */
    public function store() {
        // Vérification que la requête vient bien d'une soumission de formulaire
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            // 1. Récupération et nettoyage des champs obligatoires
            $titre = isset($_POST['titre']) ? $this->cleanInput($_POST['titre']) : '';
            $categorie = isset($_POST['categorie']) ? $this->cleanInput($_POST['categorie']) : 'info';
            $contenu = isset($_POST['contenu']) ? $this->cleanInput($_POST['contenu']) : '';
            
            // Champs optionnels
            $date_expiration = !empty($_POST['date_expiration']) ? $this->cleanInput($_POST['date_expiration']) : null;
            $nom_fichier = null;

            // 2. Validation stricte du formulaire
            if (empty($titre) || empty($contenu)) {
                // Gestion temporaire d'erreur (Session) avant redirection
                $_SESSION['error'] = "Le titre et le contenu sont obligatoires.";
                header('Location: index.php?page=valve');
                exit();
            }

            // 3. Gestion de l'upload du fichier joint (si fourni et sans erreur)
            if (isset($_FILES['fichier_joint']) && $_FILES['fichier_joint']['error'] === UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['fichier_joint']['tmp_name'];
                $fileName = $_FILES['fichier_joint']['name'];
                
                // Sécurisation du nom de fichier pour éviter les conflits et caractères spéciaux
                $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                $newFileName = md5(time() . $fileName) . '.' . $fileExtension;

                // Extensions autorisées d'après ton formulaire (.pdf, .doc, .docx)
                $allowedExtensions = ['pdf', 'doc', 'docx'];

                if (in_array($fileExtension, $allowedExtensions)) {
                    // Répertoire de destination aligné avec l'arborescence du projet
                    $uploadFileDir = __DIR__ . '/../uploads/';
                    // Crée le dossier s'il n'existe pas encore
                    if (!is_dir($uploadFileDir)) {
                        mkdir($uploadFileDir, 0755, true);
                    }

                    $dest_path = $uploadFileDir . $newFileName;

                    if (move_uploaded_file($fileTmpPath, $dest_path)) {
                        $nom_fichier = $newFileName; // Sauvegarde le nom généré pour la BDD
                    }
                }
            }

            // 4. Hydratation du modèle avec les données nettoyées
            // NOTE : On utilise temporairement l'ID 1 (ex: l'Apparitaire) en attendant l'activation globale de ton modèle Session
            $auteur_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1;

            $this->annonceModel->setAuteurId($auteur_id);
            $this->annonceModel->setCategorie($categorie);
            $this->annonceModel->setTitre($titre);
            $this->annonceModel->setContenu($contenu);
            $this->annonceModel->setDateExpiration($date_expiration);
            $this->annonceModel->setFichierJoint($nom_fichier);

            // 5. Exécution et sauvegarde en BDD
            if ($this->annonceModel->save()) {
                $_SESSION['success'] = "Annonce publiée avec succès sur le Valve !";
            } else {
                $_SESSION['error'] = "Une erreur technique est survenue lors de la publication.";
            }

            // Redirection vers la page principale du Valve pour voir la nouvelle carte s'afficher
            header('Location: index.php?page=valve');
            exit();
        }
    }

    /**
     * Utilitaire de nettoyage de base (Sécurité anti XSS primitive)
     */
    private function cleanInput($data) {
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
}