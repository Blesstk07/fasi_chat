<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$host = 'localhost';
$dbname = 'campus_relay';
$username = 'root';
$password = '1234';

$userId = $_SESSION['user_id'];
$role = $_SESSION['user_role'];
$nom = $_SESSION['user_nom'];
$identifiant = $_SESSION['user_identifiant'] ?? '---';

$rolesFR = [
    'etudiant' => 'Étudiant',
    'enseignant' => 'Enseignant',
    'assistant' => 'Assistant',
    'doyen' => 'Doyen',
    'vice_doyen' => 'Vice-Doyen',
    'apparitaire' => 'Apparitaire'
];

$roleFR = $rolesFR[$role] ?? ucfirst($role);

$roleColors = [
    'etudiant' => '#00f7ff',
    'enseignant' => '#ff00ff',
    'assistant' => '#00ff88',
    'doyen' => '#ffcc00',
    'vice_doyen' => '#ff6600',
    'apparitaire' => '#ff3366'
];

$mainColor = $roleColors[$role] ?? '#00f7ff';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->query("
        SELECT a.*, u.nom_complet as auteur_nom, u.texte_avatar
        FROM annonces a
        JOIN utilisateur u ON a.auteur_id = u.id
        ORDER BY a.priorite DESC, a.cree_le DESC
    ");
    $annonces = $stmt->fetchAll();
    
    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $stmt = $pdo->prepare("UPDATE annonces SET vues = vues + 1 WHERE id = ?");
        $stmt->execute([$_GET['id']]);
    }
    
} catch (PDOException $e) {
    $error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Valve - Campus Relay</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Segoe UI", sans-serif;
            background: #040816;
            color: white;
            overflow-x: hidden;
            min-height: 100vh;
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            width: 200%;
            height: 200%;
            background:
                radial-gradient(circle at center, rgba(0,247,255,0.15), transparent 30%),
                radial-gradient(circle at top left, rgba(255,0,255,0.10), transparent 25%),
                radial-gradient(circle at bottom right, rgba(255,180,0,0.08), transparent 25%);
            animation: bgMove 20s linear infinite;
            z-index: -2;
        }

        body::after {
            content: '';
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.03) 1px, transparent 1px);
            background-size: 40px 40px;
            z-index: -1;
        }

        @keyframes bgMove {
            from { transform: translate(-10%, -10%) rotate(0deg); }
            to { transform: translate(-10%, -10%) rotate(360deg); }
        }

        .navbar {
            width: 100%;
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(5,10,25,0.88);
            backdrop-filter: blur(15px);
            border-bottom: 1px solid rgba(255,255,255,0.08);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .logo {
            font-size: 30px;
            font-weight: bold;
            color: <?= $mainColor ?>;
            text-shadow: 0 0 12px <?= $mainColor ?>;
            animation: glow 2s infinite alternate;
        }

        @keyframes glow {
            from { text-shadow: 0 0 10px <?= $mainColor ?>; }
            to { text-shadow: 0 0 20px <?= $mainColor ?>, 0 0 40px <?= $mainColor ?>; }
        }

        .user-box {
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .badge {
            padding: 10px 18px;
            border-radius: 30px;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.08);
            transition: 0.3s;
        }

        .badge:hover {
            transform: translateY(-3px);
            border-color: <?= $mainColor ?>;
            box-shadow: 0 0 15px <?= $mainColor ?>55;
        }

        .logout {
            text-decoration: none;
            color: white;
            padding: 12px 20px;
            border-radius: 14px;
            font-weight: bold;
            background: linear-gradient(135deg, #ff0055, #ff6600);
            transition: 0.3s;
        }

        .logout:hover {
            transform: scale(1.05);
            box-shadow: 0 0 20px #ff0055;
        }

        .container {
            max-width: 1350px;
            margin: auto;
            padding: 40px 25px;
        }

        .hero {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 28px;
            padding: 45px;
            margin-bottom: 35px;
            backdrop-filter: blur(12px);
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            background: <?= $mainColor ?>22;
            border-radius: 50%;
            top: -100px;
            right: -100px;
            filter: blur(80px);
        }

        .hero h1 {
            font-size: 48px;
            margin-bottom: 12px;
            color: <?= $mainColor ?>;
            text-shadow: 0 0 15px <?= $mainColor ?>;
        }

        .hero p {
            font-size: 18px;
            color: #d0d7ff;
            max-width: 700px;
            line-height: 1.7;
        }

        .admin-actions {
            margin-bottom: 30px;
            display: flex;
            justify-content: flex-end;
        }

        .btn-add {
            background: linear-gradient(135deg, #00ff88, #00cc66);
            color: #040816;
            padding: 14px 28px;
            border: none;
            border-radius: 30px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
            font-size: 16px;
        }

        .btn-add:hover {
            transform: translateY(-3px);
            box-shadow: 0 0 20px #00ff88;
        }

        .annonce-card {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 25px;
            padding: 30px;
            margin-bottom: 25px;
            backdrop-filter: blur(10px);
            transition: 0.4s;
        }

        .annonce-card:hover {
            transform: translateY(-5px);
            border-color: <?= $mainColor ?>;
            box-shadow: 0 0 20px <?= $mainColor ?>66;
        }

        .annonce-categorie {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 15px;
        }

        .categorie-urgent { background: #dc3545; color: white; }
        .categorie-convocation { background: #ffc107; color: #333; }
        .categorie-information { background: #17a2b8; color: white; }
        .categorie-academique { background: #6c757d; color: white; }

        .annonce-titre {
            font-size: 24px;
            color: <?= $mainColor ?>;
            margin-bottom: 15px;
        }

        .annonce-contenu {
            color: #d0d7ff;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .annonce-meta {
            display: flex;
            gap: 20px;
            font-size: 13px;
            color: #9ea9ff;
            border-top: 1px solid rgba(255,255,255,0.1);
            padding-top: 15px;
            margin-top: 10px;
        }

        .annonce-actions {
            margin-top: 20px;
            display: flex;
            gap: 12px;
        }

        .btn-edit {
            background: #ffc107;
            color: #333;
            padding: 8px 20px;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            font-weight: bold;
        }

        .btn-delete {
            background: #dc3545;
            color: white;
            padding: 8px 20px;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            font-weight: bold;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.8);
            justify-content: center;
            align-items: center;
            z-index: 1000;
            backdrop-filter: blur(5px);
        }

        .modal-content {
            background: #0d132e;
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 25px;
            padding: 35px;
            width: 550px;
            max-width: 90%;
        }

        .modal-content h3 {
            color: <?= $mainColor ?>;
            margin-bottom: 20px;
            font-size: 28px;
        }

        .modal-content input,
        .modal-content select,
        .modal-content textarea {
            width: 100%;
            padding: 12px 16px;
            margin: 12px 0;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 12px;
            color: white;
            font-size: 14px;
        }

        .modal-content input:focus,
        .modal-content select:focus,
        .modal-content textarea:focus {
            outline: none;
            border-color: <?= $mainColor ?>;
        }

        .modal-buttons {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }

        .btn-primary {
            flex: 1;
            background: linear-gradient(135deg, <?= $mainColor ?>, #ff00ff);
            color: #040816;
            padding: 12px;
            border: none;
            border-radius: 30px;
            font-weight: bold;
            cursor: pointer;
        }

        .btn-secondary {
            flex: 1;
            background: #444;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 30px;
            font-weight: bold;
            cursor: pointer;
        }

        .empty {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 25px;
            padding: 60px;
            text-align: center;
            backdrop-filter: blur(10px);
        }

        .empty p {
            color: #9ea9ff;
            font-size: 18px;
        }

        @media(max-width: 900px) {
            .navbar {
                flex-direction: column;
                gap: 20px;
            }
            .hero h1 {
                font-size: 34px;
            }
            .container {
                padding: 25px 15px;
            }
            .annonce-meta {
                flex-wrap: wrap;
                gap: 10px;
            }
        }
    </style>

</head>

<body>

    <nav class="navbar">
        <div class="logo">
            📢 Valve - Campus Relay
        </div>
        <div class="user-box">
            <div class="badge">👋 <?= htmlspecialchars($nom) ?></div>
            <div class="badge">🎭 <?= htmlspecialchars($roleFR) ?></div>
            <div class="badge">🆔 <?= htmlspecialchars($identifiant) ?></div>
            <a href="index.php" class="badge" style="text-decoration:none;">🏠 Dashboard</a>
            <a href="logout.php" class="logout">Déconnexion</a>
        </div>
    </nav>

    <div class="container">

        <section class="hero">
            <h1>📢 Valve Facultaire</h1>
            <p>Informations officielles, annonces et communications de la faculté des Sciences de l'Information - FASI</p>
        </section>

        <?php if ($role === 'apparitaire'): ?>
            <div class="admin-actions">
                <button class="btn-add" onclick="openAddModal()">+ Nouvelle annonce</button>
            </div>
        <?php endif; ?>

        <?php if (!empty($annonces)): ?>
            <?php foreach ($annonces as $annonce): ?>
                <div class="annonce-card">
                    <span class="annonce-categorie categorie-<?= $annonce['categorie'] ?>">
                        <?= ucfirst($annonce['categorie']) ?>
                        <?php if ($annonce['priorite'] == 1): ?> 🔴 Prioritaire<?php endif; ?>
                    </span>
                    <h2 class="annonce-titre"><?= htmlspecialchars($annonce['titre']) ?></h2>
                    <div class="annonce-contenu"><?= nl2br(htmlspecialchars($annonce['contenu'])) ?></div>
                    
                    <?php if ($annonce['date_evenement']): ?>
                        <div style="background: rgba(0,247,255,0.1); padding: 12px 18px; border-radius: 15px; margin: 15px 0;">
                            📅 <?= date('d/m/Y', strtotime($annonce['date_evenement'])) ?>
                            <?php if ($annonce['heure_evenement']): ?>
                                à <?= substr($annonce['heure_evenement'], 0, 5) ?>
                            <?php endif; ?>
                            <?php if ($annonce['lieu']): ?>
                                • 📍 <?= htmlspecialchars($annonce['lieu']) ?>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="annonce-meta">
                        <span>✍️ Par <?= htmlspecialchars($annonce['auteur_nom']) ?></span>
                        <span>📅 <?= date('d/m/Y H:i', strtotime($annonce['cree_le'])) ?></span>
                        <span>👁️ <?= $annonce['vues'] ?> vues</span>
                    </div>
                    
                    <?php if ($role === 'apparitaire'): ?>
                        <div class="annonce-actions">
                            <button class="btn-edit" onclick="openEditModal(<?= $annonce['id'] ?>)">✏️ Modifier</button>
                            <button class="btn-delete" onclick="deleteAnnonce(<?= $annonce['id'] ?>)">🗑️ Supprimer</button>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty">
                <p>📭 Aucune annonce pour le moment.</p>
            </div>
        <?php endif; ?>
    </div>

    <div id="annonceModal" class="modal">
        <div class="modal-content">
            <h3 id="modalTitle">➕ Nouvelle annonce</h3>
            <form id="annonceForm" method="POST" action="valve_admin.php">
                <input type="hidden" name="action" id="formAction" value="create">
                <input type="hidden" name="annonce_id" id="annonceId" value="">
                
                <input type="text" name="titre" id="titre" placeholder="Titre de l'annonce" required>
                <select name="categorie" id="categorie" required>
                    <option value="information">📘 Information</option>
                    <option value="urgent">⚠️ Urgent</option>
                    <option value="convocation">📅 Convocation</option>
                    <option value="academique">🎓 Académique</option>
                </select>
                <textarea name="contenu" id="contenu" rows="5" placeholder="Contenu de l'annonce..." required></textarea>
                <input type="date" name="date_evenement" id="date_evenement" placeholder="Date (optionnel)">
                <input type="time" name="heure_evenement" id="heure_evenement" placeholder="Heure (optionnel)">
                <input type="text" name="lieu" id="lieu" placeholder="Lieu (optionnel)">
                <select name="priorite" id="priorite">
                    <option value="0">Normal</option>
                    <option value="1">🔴 Prioritaire</option>
                </select>
                
                <div class="modal-buttons">
                    <button type="button" class="btn-secondary" onclick="closeModal()">Annuler</button>
                    <button type="submit" class="btn-primary">📢 Publier</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openAddModal() {
            document.getElementById('modalTitle').innerText = '➕ Nouvelle annonce';
            document.getElementById('formAction').value = 'create';
            document.getElementById('annonceId').value = '';
            document.getElementById('titre').value = '';
            document.getElementById('categorie').value = 'information';
            document.getElementById('contenu').value = '';
            document.getElementById('date_evenement').value = '';
            document.getElementById('heure_evenement').value = '';
            document.getElementById('lieu').value = '';
            document.getElementById('priorite').value = '0';
            document.getElementById('annonceModal').style.display = 'flex';
        }
        
        function openEditModal(id) {
            fetch(`get_annonce.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('modalTitle').innerText = '✏️ Modifier l\'annonce';
                    document.getElementById('formAction').value = 'update';
                    document.getElementById('annonceId').value = data.id;
                    document.getElementById('titre').value = data.titre;
                    document.getElementById('categorie').value = data.categorie;
                    document.getElementById('contenu').value = data.contenu;
                    document.getElementById('date_evenement').value = data.date_evenement || '';
                    document.getElementById('heure_evenement').value = data.heure_evenement || '';
                    document.getElementById('lieu').value = data.lieu || '';
                    document.getElementById('priorite').value = data.priorite;
                    document.getElementById('annonceModal').style.display = 'flex';
                });
        }

        function deleteAnnonce(id) {
            if (confirm('Supprimer cette annonce définitivement ?')) {
                window.location.href = `valve_admin.php?action=delete&id=${id}`;
            }
        }

        function closeModal() {
            document.getElementById('annonceModal').style.display = 'none';
        }

        window.onclick = function(e) {
            if (e.target === document.getElementById('annonceModal')) {
                closeModal();
            }
        }
    </script>

</body>

</html>