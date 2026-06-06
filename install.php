<?php

echo "<pre>";
echo "=== Installation de FasiChat Classroom ===\n\n";

$host = 'localhost';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    $sql = file_get_contents(__DIR__ . '/sql/schema.sql');
    if (!$sql) die("ERREUR: Impossible de lire sql/schema.sql\n");

    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        fn($s) => !empty($s) && !str_starts_with($s, '--') && !str_starts_with($s, 'INSERT')
    );
    foreach ($statements as $stmt) { $pdo->exec($stmt); echo "✓ Table créée\n"; }

    echo "\n--- Insertion des données réelles ---\n";
    $hash = password_hash('password', PASSWORD_DEFAULT);
    echo "✓ Hash mot de passe: password\n";

    // Utilisateurs réels
    $users = [
        ['KUTANGILA','David','doyen@faculte.edu','doyen','ADM001'],
        ['MANPUYA','Joseph','vdoyen@faculte.edu','vice_doyen','ADM002'],
        ['ROLLY','DJ','apparitaire@faculte.edu','apparitaire','ADM003'],
        ['MANPUYA','Prof','mbaye@faculte.edu','enseignant','ENS001'],
        ['KABEYA','Jean-Pierre','kabeya@faculte.edu','enseignant','ENS002'],
        ['MBUYAMBA','Georges','assistant@faculte.edu','assistant','AST001'],
        ['BAHATI','Alain','bahati@faculte.edu','assistant','AST002'],
        ['MUKENDI','Pierre','pierre@faculte.edu','enseignant','ENS003'],
        ['TSHIBANGU','Marie','marie.tshibangu@faculte.edu','enseignant','ENS004'],
        ['KALENGA','Paul','kalenga@faculte.edu','assistant','AST003'],
        ['Banzolele','Samiel','samiel@faculte.edu','etudiant','SI2024001'],
        ['YULILA','Miriam','miriam@faculte.edu','etudiant','SI2024002'],
        ['NZOLA','Love','love@faculte.edu','etudiant','SI2024003'],
        ['NGONGO','Andy','andy@faculte.edu','etudiant','SI2024004'],
        ['ANDY','Ngunz','ngunz@faculte.edu','etudiant','SI2024005'],
        ['MBO','Dan','dan@faculte.edu','etudiant','SI2024006'],
        ['JURENT','Amani','amani@faculte.edu','etudiant','SI2024007'],
        ['KATENDE','Divine','divine@faculte.edu','etudiant','SI2024008'],
        ['MUSENGI','Audrey','audrey@faculte.edu','etudiant','SI2024009'],
        ['BAKENDA','Dan','bakenda@faculte.edu','etudiant','SI2024010'],
        ['KABASELE','Esther','esther@faculte.edu','etudiant','SI2024011'],
        ['LUKUSA','Grace','grace@faculte.edu','etudiant','SI2024012'],
        ['NTUMBA','Herman','herman@faculte.edu','etudiant','SI2024013'],
        ['ILUNGA','Ruth','ruth@faculte.edu','etudiant','SI2024014'],
        ['NKASHAMA','Jonathan','jonathan@faculte.edu','etudiant','SI2024015'],
        ['KABAMBA','Rachel','rachel@faculte.edu','etudiant','SI2024016'],
    ];

    $stmtU = $pdo->prepare("INSERT IGNORE INTO utilisateurs (nom, prenom, email, mot_de_passe, role, matricule, statut) VALUES (?, ?, ?, ?, ?, ?, 'hors_ligne')");
    $userIdMap = [];
    foreach ($users as $u) {
        $stmtU->execute([$u[0], $u[1], $u[2], $hash, $u[3], $u[4]]);
        $userIdMap[$u[2]] = $pdo->lastInsertId();
        echo "✓ Utilisateur: {$u[1]} {$u[0]} ({$u[3]})\n";
    }

    // Promotions
    $pdo->exec("INSERT IGNORE INTO promotions (nom, annee) VALUES
        ('Licence 1 Informatique','2025-2026'),
        ('Licence 2 Informatique','2025-2026'),
        ('Licence 3 Informatique','2025-2026'),
        ('Master 1 Cybersécurité','2025-2026'),
        ('Master 2 Data Science','2025-2026')");
    echo "✓ Promotions créées\n";

    // ID des enseignants
    $eManpuya = $userIdMap['mbaye@faculte.edu'];
    $eKabeya = $userIdMap['kabeya@faculte.edu'];
    $eMukendi = $userIdMap['pierre@faculte.edu'];
    $eTshibangu = $userIdMap['marie.tshibangu@faculte.edu'];
    $eMbuyamba = $userIdMap['assistant@faculte.edu'];
    $eBahati = $userIdMap['bahati@faculte.edu'];
    $eKalenga = $userIdMap['kalenga@faculte.edu'];

    // Cours L1 (promotion 1)
    $pdo->exec("INSERT IGNORE INTO cours (nom, code, description, promotion_id) VALUES
        ('Algorithmique et Programmation','ALGO-L1','Cours fondamental d\'algorithmique et structures de données en C',1),
        ('Mathématiques pour l\'Informatique','MATH-L1','Algèbre linéaire, analyse et logique mathématique',1),
        ('Introduction aux Réseaux','RES-L1','Fondamentaux des réseaux TCP/IP et modélisation OSI',1),
        ('Architecture des Ordinateurs','ARCHO-L1','Composants, mémoire, processeur et assembleur',1),
        ('Systèmes d\'Exploitation','SE-L1','Processus, mémoire, fichiers et Shell Linux',1)");
    echo "✓ Cours L1 créés\n";

    // Cours L2 (promotion 2)
    $pdo->exec("INSERT IGNORE INTO cours (nom, code, description, promotion_id) VALUES
        ('Programmation Web PHP','PHP-L2','Cours de programmation web en PHP orienté objet',2),
        ('POO Java','POO-L2','Programmation orientée objet avec Java et Design Patterns',2),
        ('Base de Données','BD-L2','SGBDR, modélisation MERISE et requêtes SQL avancées',2),
        ('Génie Logiciel','GL-L2','Méthodes agiles, UML, gestion de projets',2),
        ('Réseaux Avancés','RES-L2','Routage, VLAN, sécurité réseau et simulation Cisco',2)");
    echo "✓ Cours L2 créés\n";

    // Cours L3 (promotion 3)
    $pdo->exec("INSERT IGNORE INTO cours (nom, code, description, promotion_id) VALUES
        ('Intelligence Artificielle','IA-L3','Apprentissage automatique, réseaux de neurones et Python',3),
        ('Développement Mobile','MOB-L3','Applications Android natives et Flutter',3),
        ('Sécurité des Systèmes','SECU-L3','Cryptographie, sécurité réseau et ethical hacking',3),
        ('Cloud Computing','CLOUD-L3','AWS, Docker, Kubernetes et déploiement continu',3),
        ('Programmation Avancée','AVANCEE-L3','Design patterns, architectures microservices et tests',3)");
    echo "✓ Cours L3 créés\n";

    // Assigner enseignants aux cours L1
    $pdo->exec("INSERT IGNORE INTO cours_enseignants (cours_id, enseignant_id) VALUES
        (1,$eMukendi),(2,$eTshibangu),(3,$eManpuya),(4,$eMukendi),(5,$eMukendi),
        (6,$eManpuya),(7,$eManpuya),(8,$eKabeya),(9,$eKabeya),(10,$eKabeya),
        (11,$eManpuya),(12,$eMukendi),(13,$eKabeya),(14,$eManpuya),(15,$eManpuya)");
    echo "✓ Enseignants assignés aux cours\n";

    // Assigner assistants
    $pdo->exec("INSERT IGNORE INTO cours_enseignants (cours_id, enseignant_id) VALUES
        (1,$eKalenga),(2,$eKalenga),(6,$eBahati),(7,$eMbuyamba),(11,$eMbuyamba),(13,$eBahati)");
    echo "✓ Assistants assignés aux cours\n";

    // Inscriptions L1 (étudiants 11-15 dans cours 1-5)
    $l1Students = [11,12,13,14,15];
    foreach ($l1Students as $sid) {
        foreach ([1,2,3,4,5] as $cid) {
            $pdo->exec("INSERT IGNORE INTO inscriptions (etudiant_id, cours_id) VALUES ($sid, $cid)");
        }
    }
    echo "✓ Étudiants L1 inscrits\n";

    // Inscriptions L2 (étudiants 16-20 dans cours 6-10)
    $l2Students = [16,17,18,19,20];
    foreach ($l2Students as $sid) {
        foreach ([6,7,8,9,10] as $cid) {
            $pdo->exec("INSERT IGNORE INTO inscriptions (etudiant_id, cours_id) VALUES ($sid, $cid)");
        }
    }
    echo "✓ Étudiants L2 inscrits\n";

    // Inscriptions L3 (étudiants 21-26 dans cours 11-15)
    $l3Students = [21,22,23,24,25,26];
    foreach ($l3Students as $sid) {
        foreach ([11,12,13,14,15] as $cid) {
            $pdo->exec("INSERT IGNORE INTO inscriptions (etudiant_id, cours_id) VALUES ($sid, $cid)");
        }
    }
    echo "✓ Étudiants L3 inscrits\n";

    // Messages réels
    $pdo->exec("INSERT IGNORE INTO messages (expediteur_id, type, contenu, cours_id) VALUES
        ($eManpuya,'public','Bonjour à tous ! Bienvenue dans le cours de PHP. N\'oubliez pas de rendre vos projets avant le 30 juin.',6),
        ($eManpuya,'public','Excellente question Divine ! La méthode seConnecter() doit être abstraite dans la classe mère.',6),
        (18,'public','Merci Professeur, est-ce que le rapport est obligatoire ?',6),
        ($eManpuya,'public','Oui, le rapport compte pour 40% de la note finale.',6),
        (11,'public','Bonjour à tous !',1),
        ($eMukendi,'public','Cours annulé ce vendredi pour cause de réunion pédagogique.',1),
        ($eTshibangu,'public','Exercice 3 à rendre pour la semaine prochaine sur les matrices.',2),
        (17,'public','Quelqu\'un a compris le TD sur les transactions SQL ?',8),
        ($eKabeya,'public','Je ferai un rappel sur les transactions demain en cours.',8),
        (22,'public','Le lien du TP Flutter ne fonctionne plus.',12),
        ($eMukendi,'public','Voici le nouveau lien : https://classroom.google.com/c/MOB-L3',12),
        (24,'public','Merci Professeur !',11),
        ($eManpuya,'public','@Grace pour l\'IA, lisez le chapitre 5 sur les réseaux de neurones.',11),
        ($eKabeya,'public','Les résultats du partiel BD sont disponibles dans mon bureau.',8)");
    echo "✓ Messages réels créés\n";

    // 20+ annonces Valve réelles
    $annonces = [
        ['Réunion du Conseil Pédagogique — Présence Obligatoire','Le Doyen de la Faculté des Sciences convie tous les enseignants et assistants à la réunion du Conseil Pédagogique. Ordre du jour : bilan académique du semestre, préparation des examens, et évaluation des enseignements.','convocation',1],
        ['Modification du Calendrier des Examens S5','Suite à des contraintes organisationnelles, les épreuves de Programmation Web et de Mathématiques pour informaticien sont reportées au lundi 27 juillet. Consultez le nouveau calendrier joint.','urgent',3],
        ['Dépôt des Projets de Fin d\'Année','Le dépôt des projets s\'effectuera exclusivement via la plateforme en ligne. Aucun envoi par email ne sera accepté. Date limite : 30 juin 2026 à 23h59.','information',3],
        ['Inscriptions Pédagogiques 2026-2027','Les inscriptions pédagogiques pour la nouvelle année académique débutent le 1er septembre. Veuillez vous présenter à la scolarité munis de vos relevés de notes.','information',3],
        ['Résultats du Semestre 2 disponibles','Les résultats sont désormais disponibles sur le portail étudiant. Réclamations dans les 5 jours ouvrables auprès de la scolarité.','academique',3],
        ['Fermeture de la Bibliothèque pour Travaux','La bibliothèque universitaire sera fermée du 22 au 24 juillet pour des travaux de rénovation. Réouverture le 25 juillet.','information',3],
        ['Commission de Recherche — Réunion Exceptionnelle','Le Vice-Doyen à la Recherche convoque les enseignants-chercheurs pour une réunion extraordinaire. Veuillez préparer un bilan de vos activités.','convocation',2],
        ['Hackathon FasiChat 2026','Inscrivez-vous au Hackathon annuel de la faculté ! Équipes de 3 à 5 étudiants. Prix : 500$ pour l\'équipe gagnante. Date : 15-17 Août.','information',3],
        ['Bourses de Mobilité Internationale','Appel à candidatures pour les bourses de mobilité Erasmus+. 10 places disponibles pour le semestre d\'hiver 2026. Dossier à déposer avant le 15 août.','academique',3],
        ['Maintenance du Réseau','Une maintenance du réseau est prévue ce samedi de 8h à 14h. Les services en ligne seront indisponibles pendant cette période.','urgent',3],
        ['Journée Portes Ouvertes 2026','La faculté organise sa journée portes ouvertes le 12 septembre. Venez découvrir nos formations et laboratoires. Inscription obligatoire.','information',3],
        ['Soutenance de Thèse — Dr. Mbuyamba','Le département a l\'honneur de convier toute la communauté à la soutenance de thèse de Dr. Georges Mbuyamba sur le thème : "Sécurité des systèmes IoT".','academique',3],
        ['Appel à Communications — Colloque','La faculté organise un colloque international sur "IA et Éducation". Soumettez vos communications avant le 30 septembre.','academique',3],
        ['Stage d\'Été — Partenariat Entreprises','La faculté propose 15 stages d\'été dans nos entreprises partenaires. Dépôt de CV à la scolarité avant le 20 juillet.','information',3],
        ['Conseil d\'Université','Le Doyen convie les représentants des étudiants au Conseil d\'Université qui se tiendra le 28 juillet en salle A-12.','convocation',1],
        ['Bourses d\'Excellence 2025-2026','Les étudiants admis avec mention Très Bien au premier semestre peuvent postuler à une bourse d\'excellence. Dossier : relevé + lettre de motivation.','academique',3],
        ['Atelier DevOps — Niveau Débutant','Un atelier pratique sur Docker et CI/CD est organisé ce samedi en salle de TP. Places limitées à 30. Inscription sur le portail.','information',3],
        ['Rappel : Évaluations des Enseignements','Les évaluations des enseignements par les étudiants sont ouvertes jusqu\'au 15 juillet. Votre avis compte !','academique',3],
        ['Cérémonie de Remise des Diplômes','La cérémonie de graduation pour la promotion 2025-2026 aura lieu le 30 octobre au grand amphithéâtre.','information',3],
        ['Virus — Alerte Sécurité','Un ransomware circule actuellement via des pièces jointes. Ne cliquez sur aucun lien suspect et signalez tout incident au service IT.','urgent',3],
    ];
    $stmtA = $pdo->prepare("INSERT IGNORE INTO valve_annonces (titre, contenu, categorie, apparitaire_id) VALUES (?, ?, ?, ?)");
    foreach ($annonces as $a) {
        $stmtA->execute([$a[0], $a[1], $a[2], $a[3]]);
        echo "✓ Annonce: " . substr($a[0], 0, 50) . "...\n";
    }

    // Convocations réelles
    $pdo->exec("INSERT IGNORE INTO convocations (expediteur_id, objet, date_reunion, heure_reunion, lieu, message) VALUES
        (1,'Conseil Pédagogique — Bilan S5','2025-01-24','14:00:00','Salle A-12','Présence obligatoire pour tous les enseignants. Ordre du jour : bilan académique et préparation des examens finaux.'),
        (2,'Commission de Recherche','2025-01-27','10:00:00','Salle de Conférence B','Veuillez préparer un bilan de vos activités de recherche.'),
        (1,'Réunion d\'Urgence — Pédagogie','2025-02-01','09:00:00','Salle du Conseil','Réunion d\'urgence concernant les modifications des programmes.'),
        (2,'Comité de Sélection — Nouveaux Enseignants','2025-02-05','15:00:00','Bureau du Doyen','Sélection des candidats pour les postes d\'assistants.')");
    echo "✓ Convocations créées\n";

    echo "\n=== Installation terminée avec succès ! ===\n";
    echo "Mot de passe pour tous: password\n\n";
    echo "Connexions:\n";
    echo "  Doyen:       doyen@faculte.edu\n";
    echo "  Vice-Doyen:  vdoyen@faculte.edu\n";
    echo "  Apparitaire: apparitaire@faculte.edu\n";
    echo "  Enseignant:  mbaye@faculte.edu / kabeya@faculte.edu / pierre@faculte.edu / marie.tshibangu@faculte.edu\n";
    echo "  Assistant:   assistant@faculte.edu / bahati@faculte.edu / kalenga@faculte.edu\n";
    echo "  Étudiant L1: samiel@faculte.edu, miriam@faculte.edu, love@faculte.edu, andy@faculte.edu, ngunz@faculte.edu\n";
    echo "  Étudiant L2: dan@faculte.edu, amani@faculte.edu, divine@faculte.edu, audrey@faculte.edu, bakenda@faculte.edu\n";
    echo "  Étudiant L3: esther@faculte.edu, grace@faculte.edu, herman@faculte.edu, ruth@faculte.edu, jonathan@faculte.edu\n";

} catch (PDOException $e) {
    echo "ERREUR: " . $e->getMessage() . "\n";
}

echo "</pre>";
