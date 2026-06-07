<?php
session_start();

require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/models/AnnonceValves.php';

$db = (new Database())->getConnection();
$annonceModel = new AnnonceValve($db);

function cleanInput($data) {
    return htmlspecialchars(trim((string)$data), ENT_QUOTES, 'UTF-8');
}

$searchQuery = isset($_GET['q']) ? cleanInput($_GET['q']) : '';
$type = isset($_GET['type']) ? cleanInput($_GET['type']) : null;
$action = isset($_GET['action']) ? cleanInput($_GET['action']) : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'store') {
    $titre = isset($_POST['titre']) ? cleanInput($_POST['titre']) : '';
    $categorie = isset($_POST['categorie']) ? cleanInput($_POST['categorie']) : 'info';
    $contenu = isset($_POST['contenu']) ? cleanInput($_POST['contenu']) : '';
    $date_expiration = !empty($_POST['date_expiration']) ? cleanInput($_POST['date_expiration']) : null;
    $nom_fichier = null;

    if (empty($titre) || empty($contenu)) {
        $_SESSION['error'] = "Le titre et le contenu sont obligatoires.";
        header('Location: valve.php');
        exit;
    }

    if (isset($_FILES['fichier_joint']) && $_FILES['fichier_joint']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['fichier_joint']['tmp_name'];
        $fileName = $_FILES['fichier_joint']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
        $allowedExtensions = ['pdf', 'doc', 'docx'];

        if (in_array($fileExtension, $allowedExtensions)) {
            $uploadFileDir = __DIR__ . '/uploads/';
            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0755, true);
            }
            $dest_path = $uploadFileDir . $newFileName;
            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $nom_fichier = $newFileName;
            }
        }
    }

    $auteur_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1;

    $annonceModel->setAuteurId($auteur_id);
    $annonceModel->setCategorie($categorie);
    $annonceModel->setTitre($titre);
    $annonceModel->setContenu($contenu);
    $annonceModel->setDateExpiration($date_expiration);
    $annonceModel->setFichierJoint($nom_fichier);

    if ($annonceModel->save()) {
        $_SESSION['success'] = "Annonce publiée avec succès sur le Valve !";
    } else {
        $_SESSION['error'] = "Une erreur technique est survenue lors de la publication.";
    }

    header('Location: valve.php');
    exit;
}

// --- REPARATION DE LA LOGIQUE DES COMPTEURS ---
// 1. On récupère TOUJOURS la totalité des annonces pour calculer les vrais nombres fidèles
$toutesLesAnnonces = $annonceModel->findAll() ?: [];

$countToutes = count($toutesLesAnnonces);
$countUrgent = 0;
$countConvocation = 0;
$countInfo = 0;
$countAcademique = 0;

foreach ($toutesLesAnnonces as $a) {
    switch ($a['categorie']) {
        case 'urgent': $countUrgent++; break;
        case 'convocation': $countConvocation++; break;
        case 'info': $countInfo++; break;
        case 'academique': $countAcademique++; break;
    }
}

// 2. On détermine le sous-ensemble d'affichage pour la grille de droite uniquement
if ($type && in_array($type, ['urgent', 'convocation', 'info', 'academique'])) {
    $annonces = $annonceModel->findByCategorie($type);
} elseif (!empty($searchQuery)) {
    $annonces = $annonceModel->search($searchQuery);
} else {
    $annonces = $toutesLesAnnonces; // Si pas de filtre, on affiche tout
}
$annonces = $annonces ?: [];

// 3. Condition pour afficher ou masquer la zone Résumé (Hero)
// Le Hero s'affiche UNIQUEMENT si on n'est pas en mode filtre et qu'aucune recherche n'est active
$afficherHero = (!isset($_GET['type']) && empty($searchQuery));

$flashSuccess = $_SESSION['success'] ?? null;
$flashError = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FasiChat — Valve Faculté</title>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/valve.css">
</head>
<body>

<div class="sidebar">
  <div class="sidebar-header">
    <div class="brand-mark">💬</div>
    <div class="brand-info"><h3>FasiChat</h3><span>Valve — Tableau d'affichage</span></div>
  </div>
  <div class="nav-tabs">
    <button class="nav-tab" onclick="location.href='messagerie.php'">💬 Chat</button>
    <button class="nav-tab active">📣 Valve</button>
    <button class="nav-tab" onclick="location.href='dashboard_admin.html'">🏛 Admin</button>
  </div>
  
  <div class="sidebar-cats">
    <div class="section-label">Catégories</div>
    
    <div class="cat-item <?= !isset($_GET['type']) ? 'active' : '' ?>" onclick="location.href='valve.php'">
      <div class="cat-icon" style="background:rgba(79,163,224,0.15);">📋</div>
      <div class="cat-info">
        <div class="cat-name">Toutes les annonces</div>
        <div class="cat-count"><?= $countToutes ?> <?= $countToutes > 1 ? 'publications' : 'publication' ?></div>
      </div>
    </div>
    
    <div class="cat-item <?= (isset($_GET['type']) && $_GET['type'] === 'urgent') ? 'active' : '' ?>" onclick="location.href='valve.php?action=filter&type=urgent'">
      <div class="cat-icon" style="background:rgba(239,68,68,0.12);">🚨</div>
      <div class="cat-info">
        <div class="cat-name">Urgences</div>
        <div class="cat-count"><?= $countUrgent ?> <?= $countUrgent > 1 ? 'publications' : 'publication' ?></div>
      </div>
      <?php if($countUrgent > 0): ?><div class="cat-badge"><?= $countUrgent ?></div><?php endif; ?>
    </div>
    
    <div class="cat-item <?= (isset($_GET['type']) && $_GET['type'] === 'convocation') ? 'active' : '' ?>" onclick="location.href='valve.php?action=filter&type=convocation'">
      <div class="cat-icon" style="background:rgba(245,158,11,0.12);">📅</div>
      <div class="cat-info">
        <div class="cat-name">Convocations</div>
        <div class="cat-count"><?= $countConvocation ?> <?= $countConvocation > 1 ? 'publications' : 'publication' ?></div>
      </div>
    </div>
    
    <div class="cat-item <?= (isset($_GET['type']) && $_GET['type'] === 'info') ? 'active' : '' ?>" onclick="location.href='valve.php?action=filter&type=info'">
      <div class="cat-icon" style="background:rgba(34,197,94,0.12);">📢</div>
      <div class="cat-info">
        <div class="cat-name">Informations</div>
        <div class="cat-count"><?= $countInfo ?> <?= $countInfo > 1 ? 'publications' : 'publication' ?></div>
      </div>
    </div>
    
    <div class="cat-item <?= (isset($_GET['type']) && $_GET['type'] === 'academique') ? 'active' : '' ?>" onclick="location.href='valve.php?action=filter&type=academique'">
      <div class="cat-icon" style="background:rgba(99,102,241,0.12);">🎓</div>
      <div class="cat-info">
        <div class="cat-name">Académique</div>
        <div class="cat-count"><?= $countAcademique ?> <?= $countAcademique > 1 ? 'publications' : 'publication' ?></div>
      </div>
    </div>
  </div>

  <div class="sidebar-profile">
    <div class="profile-avatar" style="background:linear-gradient(135deg,#6366f1,#4f46e5);">
      <div class="online-dot"></div>🗂
    </div>
    <div class="profile-info">
      <h4>DJ. ROLLY</h4>
      <span style="color:#a5b4fc;font-size:10px;">Apparitaire · Faculté</span>
    </div>
    <a href="logout.php" class="icon-btn">🚪</a>
  </div>
</div>

<div class="main-area">
  <div class="valve-topbar">
    <div class="valve-topbar-icon">📣</div>
    <div class="valve-topbar-info">
      <h3>Valve — Faculté des Sciences Informatiques</h3>
      <p>Tableau d'affichage officiel · Géré par l'Apparitaire</p>
    </div>
    <div class="valve-topbar-actions">
      <button class="vt-btn ghost">📊 Statistiques</button>
      <button class="vt-btn primary" onclick="openModal()">+ Nouvelle annonce</button>
    </div>
  </div>

  <?php if (!empty($flashSuccess) || !empty($flashError)): ?>
    <div class="flash-container">
      <?php if (!empty($flashSuccess)): ?><div class="flash flash-success"><?= $flashSuccess ?></div><?php endif; ?>
      <?php if (!empty($flashError)): ?><div class="flash flash-error"><?= $flashError ?></div><?php endif; ?>
    </div>
  <?php endif; ?>

  <div class="filter-bar">
    <button class="filter-chip <?= !isset($_GET['type']) ? 'active' : '' ?>" onclick="location.href='valve.php'">Toutes</button>
    <button class="filter-chip <?= (isset($_GET['type']) && $_GET['type'] === 'urgent') ? 'active' : '' ?>" onclick="location.href='valve.php?action=filter&type=urgent'">🚨 Urgences</button>
    <button class="filter-chip <?= (isset($_GET['type']) && $_GET['type'] === 'convocation') ? 'active' : '' ?>" onclick="location.href='valve.php?action=filter&type=convocation'">📅 Convocations</button>
    <button class="filter-chip <?= (isset($_GET['type']) && $_GET['type'] === 'info') ? 'active' : '' ?>" onclick="location.href='valve.php?action=filter&type=info'">📢 Infos</button>
    <button class="filter-chip <?= (isset($_GET['type']) && $_GET['type'] === 'academique') ? 'active' : '' ?>" onclick="location.href='valve.php?action=filter&type=academique'">🎓 Académique</button>
    <div class="filter-spacer"></div>
    
    <form action="valve.php" method="GET" class="search-valve">
      <span class="s-ico" onclick="this.parentNode.submit();" style="cursor:pointer;">🔍</span>
      <input type="text" name="q" placeholder="Rechercher une annonce..." value="<?= isset($searchQuery) ? htmlspecialchars($searchQuery) : '' ?>">
    </form>
  </div>

  <div class="valve-content">
    
    <?php if ($afficherHero): ?>
        <div class="valve-hero">
          <div>
            <div class="hero-badge">TABLEAU D'AFFICHAGE OFFICIEL</div>
            <h2>Bienvenue sur le Valve 📣</h2>
            <p>Toutes les annonces officielles de la Faculté des Sciences Informatiques.</p>
            <div class="hero-stats">
              <div class="hero-stat"><div class="n"><?= $countToutes ?></div><div class="l">ANNONCES</div></div>
              <div class="hero-stat"><div class="n"><?= $countUrgent ?></div><div class="l">URGENCE</div></div>
              <div class="hero-stat"><div class="n"><?= $countConvocation ?></div><div class="l">CONVOCATIONS</div></div>
            </div>
          </div>
        </div>
    <?php endif; ?>

    <div class="annonces-grid">
      <?php if (empty($annonces)): ?>
          <div style="grid-column: 1/-1; text-align: center; color: var(--gray-400); padding: 40px;">
              Aucune annonce disponible pour le moment.
          </div>
      <?php else: ?>
          <?php foreach ($annonces as $annonce): ?>
              <?php 
                $cardClass = '';
                $catLabel = 'INFORMATION';
                $labelColor = '#16a34a';
                $iconBg = 'rgba(34,197,94,0.12)';
                $icon = '📢';

                if ($annonce['categorie'] === 'urgent') {
                    $cardClass = 'urgent';
                    $catLabel = 'URGENT';
                    $labelColor = '#ef4444';
                    $iconBg = 'rgba(239,68,68,0.12)';
                    $icon = '🚨';
                } elseif ($annonce['categorie'] === 'convocation') {
                    $cardClass = 'convocation';
                    $catLabel = 'CONVOCATION';
                    $labelColor = '#d97706';
                    $iconBg = 'rgba(245,158,11,0.12)';
                    $icon = '📅';
                } elseif ($annonce['categorie'] === 'academique') {
                    $catLabel = 'ACADÉMIQUE';
                    $labelColor = '#6366f1';
                    $iconBg = 'rgba(99,102,241,0.12)';
                    $icon = '🎓';
                }
              ?>
              
              <div class="annonce-card <?= $cardClass ?>" <?= $annonce['categorie'] === 'urgent' ? 'style="grid-column:1/-1;"' : '' ?>>
                <div class="ac-header">
                  <div class="ac-cat-icon" style="background:<?= $iconBg ?>;"><?= $icon ?></div>
                  <div class="ac-meta">
                    <div class="ac-cat-label" style="color:<?= $labelColor ?>;"><?= $catLabel ?></div>
                    <div class="ac-title"><?= htmlspecialchars($annonce['titre']) ?></div>
                  </div>
                  <?php if($annonce['categorie'] === 'urgent'): ?>
                    <div class="ac-priority" style="background:rgba(239,68,68,0.1);color:#ef4444;">⚠</div>
                  <?php endif; ?>
                </div>
                <div class="ac-body">
                  <div class="ac-text"><?= nl2br(htmlspecialchars($annonce['contenu'])) ?></div>
                  
                  <?php if (!empty($annonce['fichier_joint'])): ?>
                      <div class="ac-file" onclick="window.open('uploads/<?= htmlspecialchars($annonce['fichier_joint']) ?>')" style="cursor:pointer;">
                        <span style="font-size:20px;">📄</span>
                        <span><?= htmlspecialchars($annonce['fichier_joint']) ?></span>
                        <small>Pièce jointe</small>
                      </div>
                  <?php endif; ?>

                  <div class="ac-footer">
                    <div class="ac-author">
                      <div class="ac-author-ava" style="background:linear-gradient(135deg,#6366f1,#4f46e5);">
                        <?= strtoupper(substr($annonce['auteur_nom'] ?? 'A', 0, 2)) ?>
                      </div>
                      <div>
                        <div class="ac-author-name">
                            <?= htmlspecialchars($annonce['auteur_nom'] ?? 'DJ. ROLLY') ?> · 
                            <small style="color:var(--gray-400);"><?= htmlspecialchars($annonce['auteur_role'] ?? 'Apparitaire') ?></small>
                        </div>
                      </div>
                    </div>
                    <div class="ac-date">Publié le : <?= date('d/m/Y à H:i', strtotime($annonce['date_publication'])) ?></div>
                  </div>
                </div>
              </div>
          <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="modal-overlay" id="modal" onclick="closeModalOutside(event)">
  <div class="modal">
    <form action="valve.php?action=store" method="POST" enctype="multipart/form-data">
        <div class="modal-header">
          <div class="modal-icon">📣</div>
          <div>
            <h3>Nouvelle annonce</h3>
            <p>Publication sur le Valve — visible par tous les utilisateurs</p>
          </div>
          <button type="button" class="modal-close" onclick="closeModal()">✕</button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label class="form-label">Titre de l'annonce *</label>
            <input type="text" name="titre" class="form-input" placeholder="Ex: Réunion du conseil pédagogique..." required>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Catégorie *</label>
              <select name="categorie" class="form-select" required>
                <option value="info">📢 Information</option>
                <option value="urgent">🚨 Urgent</option>
                <option value="convocation">📅 Convocation</option>
                <option value="academique">🎓 Académique</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Date d'expiration</label>
              <input type="date" name="date_expiration" class="form-input">
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Contenu de l'annonce *</label>
            <textarea name="contenu" class="form-textarea" placeholder="Rédigez le contenu de votre annonce ici..." required></textarea>
          </div>
          <div class="form-group">
            <label class="form-label">Pièce jointe (optionnel)</label>
            <input type="file" name="fichier_joint" class="form-input" accept=".pdf,.doc,.docx">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn-cancel" onclick="closeModal()">Annuler</button>
          <button type="submit" class="btn-publish">📣 Publier sur le Valve</button>
        </div>
    </form>
  </div>
</div>

<script src="assets/js/valve.js"></script>
</body>
</html>