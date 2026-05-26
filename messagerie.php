<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

/*
|--------------------------------------------------------------------------
| VÉRIFICATION SESSION
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['user_id'])) {

    header('Location: login.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| CONNEXION BASE DE DONNÉES
|--------------------------------------------------------------------------
*/

$host     = "localhost";
$dbname   = "campus_relay";
$username = "root";
$password = "1234";

$error = '';

try {

    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {

    die("Erreur BDD : " . $e->getMessage());
}

/*
|--------------------------------------------------------------------------
| DONNÉES UTILISATEUR
|--------------------------------------------------------------------------
*/

$userId      = $_SESSION['user_id'];
$nom         = $_SESSION['user_nom'] ?? 'Utilisateur';
$role        = $_SESSION['user_role'] ?? 'inconnu';
$identifiant = $_SESSION['user_identifiant'] ?? '---';

/*
|--------------------------------------------------------------------------
| RÔLES FR
|--------------------------------------------------------------------------
*/

$rolesFR = [

    'etudiant'    => 'Étudiant',
    'enseignant'  => 'Enseignant',
    'assistant'   => 'Assistant',
    'doyen'       => 'Doyen',
    'vice_doyen'  => 'Vice-Doyen',
    'apparitaire' => 'Apparitaire'
];

$roleFR = $rolesFR[$role] ?? ucfirst($role);

/*
|--------------------------------------------------------------------------
| COULEURS PAR RÔLE
|--------------------------------------------------------------------------
*/

$roleColors = [

    'etudiant'    => '#00f7ff',
    'enseignant'  => '#ff00ff',
    'assistant'   => '#00ff99',
    'doyen'       => '#ffcc00',
    'vice_doyen'  => '#ff6600',
    'apparitaire' => '#ff3366'
];

$mainColor = $roleColors[$role] ?? '#00f7ff';

/*
|--------------------------------------------------------------------------
| ENVOI MESSAGE + FICHIER AVEC COMPRESSION
|--------------------------------------------------------------------------
*/

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['message'])
) {

    $conversationId = (int) ($_POST['conversation_id'] ?? 0);

    $contenu = trim($_POST['message']);

    if (
        $conversationId > 0
        && (
            !empty($contenu)
            || !empty($_FILES['fichier']['name'])
        )
    ) {

        /*
        |--------------------------------------------------------------------------
        | INSERT MESSAGE
        |--------------------------------------------------------------------------
        */

        $stmt = $pdo->prepare("
            INSERT INTO messages
            (
                conversation_id,
                utilisateur_id,
                type_message,
                contenu,
                cree_le
            )
            VALUES
            (
                ?, ?, 'texte', ?, NOW()
            )
        ");

        $stmt->execute([
            $conversationId,
            $userId,
            $contenu
        ]);

        $messageId = $pdo->lastInsertId();

        /*
        |--------------------------------------------------------------------------
        | UPLOAD FICHIER AVEC COMPRESSION
        |--------------------------------------------------------------------------
        */

        if (
            isset($_FILES['fichier'])
            && $_FILES['fichier']['error'] === UPLOAD_ERR_OK
        ) {

            $uploadDir = "uploads/";

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileTmp  = $_FILES['fichier']['tmp_name'];
            $fileType = $_FILES['fichier']['type'];
            $fileSize = $_FILES['fichier']['size'];
            $originalName = $_FILES['fichier']['name'];
            
            // Vérifier la taille max (20 Mo)
            if ($fileSize > 20 * 1024 * 1024) {
                header("Location: messagerie.php?conv=$conversationId&error=file_too_large");
                exit;
            }
            
            // Générer un nom unique
            $extension = pathinfo($originalName, PATHINFO_EXTENSION);
            $fileName = time() . '_' . uniqid() . '.' . $extension;
            $filePath = $uploadDir . $fileName;
            
            $isCompressed = false;
            $finalSize = $fileSize;
            
            // Compression pour les images
            if (strpos($fileType, 'image/') === 0) {
                
                // Créer l'image selon le type
                switch ($fileType) {
                    case 'image/jpeg':
                    case 'image/jpg':
                        $source = imagecreatefromjpeg($fileTmp);
                        break;
                    case 'image/png':
                        $source = imagecreatefrompng($fileTmp);
                        imagepalettetotruecolor($source);
                        imagealphablending($source, true);
                        imagesavealpha($source, true);
                        break;
                    case 'image/webp':
                        $source = imagecreatefromwebp($fileTmp);
                        break;
                    case 'image/gif':
                        $source = imagecreatefromgif($fileTmp);
                        break;
                    default:
                        $source = null;
                }
                
                if ($source) {
                    $width = imagesx($source);
                    $height = imagesy($source);
                    
                    $maxSize = 1200;
                    $ratio = min($maxSize / $width, $maxSize / $height);
                    
                    if ($ratio < 1) {
                        $newWidth = (int)($width * $ratio);
                        $newHeight = (int)($height * $ratio);
                    } else {
                        $newWidth = $width;
                        $newHeight = $height;
                    }
                    
                    $compressed = imagecreatetruecolor($newWidth, $newHeight);
                    
                    if ($fileType === 'image/png') {
                        imagealphablending($compressed, false);
                        imagesavealpha($compressed, true);
                        $transparent = imagecolorallocatealpha($compressed, 0, 0, 0, 127);
                        imagefilledrectangle($compressed, 0, 0, $newWidth, $newHeight, $transparent);
                    }
                    
                    imagecopyresampled($compressed, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                    
                    switch ($fileType) {
                        case 'image/jpeg':
                        case 'image/jpg':
                            imagejpeg($compressed, $filePath, 75);
                            break;
                        case 'image/png':
                            imagepng($compressed, $filePath, 8);
                            break;
                        case 'image/webp':
                            imagewebp($compressed, $filePath, 75);
                            break;
                        case 'image/gif':
                            imagegif($compressed, $filePath);
                            break;
                        default:
                            copy($fileTmp, $filePath);
                    }
                    
                    imagedestroy($source);
                    imagedestroy($compressed);
                    
                    $isCompressed = true;
                    $finalSize = filesize($filePath);
                } else {
                    move_uploaded_file($fileTmp, $filePath);
                }
                
            } else {
                move_uploaded_file($fileTmp, $filePath);
            }
            
            // Insérer dans la BDD
            $stmt = $pdo->prepare("
                INSERT INTO fichiers
                (
                    message_id,
                    nom_original,
                    chemin,
                    type_mime,
                    taille,
                    est_compresse,
                    taille_originale,
                    cree_le
                )
                VALUES
                (
                    ?, ?, ?, ?, ?, ?, ?, NOW()
                )
            ");
            
            $stmt->execute([
                $messageId,
                $originalName,
                $filePath,
                $fileType,
                $finalSize,
                $isCompressed ? 1 : 0,
                $fileSize
            ]);
        }

        header("Location: messagerie.php?conv=$conversationId");
        exit;
    }
}

/*
|--------------------------------------------------------------------------
| CRÉATION CONVERSATION
|--------------------------------------------------------------------------
*/

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['new_conversation'])
) {

    $titre          = trim($_POST['titre'] ?? '');
    $destinataireId = (int) ($_POST['destinataire_id'] ?? 0);

    if (!empty($titre) && $destinataireId > 0) {

        $stmt = $pdo->prepare("
            INSERT INTO conversations
            (
                type,
                titre,
                cree_le
            )
            VALUES
            (
                'private',
                ?,
                NOW()
            )
        ");

        $stmt->execute([$titre]);

        $newConvId = $pdo->lastInsertId();

        $stmt = $pdo->prepare("
            INSERT INTO participants_conversation
            (
                conversation_id,
                utilisateur_id
            )
            VALUES
            (?, ?), (?, ?)
        ");

        $stmt->execute([
            $newConvId,
            $userId,
            $newConvId,
            $destinataireId
        ]);

        header("Location: messagerie.php?conv=$newConvId");
        exit;
    }
}

/*
|--------------------------------------------------------------------------
| CONVERSATIONS
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        c.*,

        (
            SELECT COUNT(*)
            FROM messages
            WHERE conversation_id = c.id
        ) AS total_messages

    FROM conversations c

    INNER JOIN participants_conversation pc
    ON c.id = pc.conversation_id

    WHERE pc.utilisateur_id = ?

    ORDER BY c.cree_le DESC
");

$stmt->execute([$userId]);

$conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| CONVERSATION ACTIVE
|--------------------------------------------------------------------------
*/

$convId = isset($_GET['conv'])
    ? (int) $_GET['conv']
    : 0;

$currentConv = null;
$messages    = [];

if ($convId > 0) {

    $stmt = $pdo->prepare("
        SELECT *
        FROM conversations
        WHERE id = ?
    ");

    $stmt->execute([$convId]);

    $currentConv = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("
        SELECT
            m.*,
            u.nom_complet,
            u.role,
            u.texte_avatar

        FROM messages m

        INNER JOIN utilisateur u
        ON m.utilisateur_id = u.id

        WHERE m.conversation_id = ?

        ORDER BY m.cree_le ASC
    ");

    $stmt->execute([$convId]);

    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/*
|--------------------------------------------------------------------------
| UTILISATEURS
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        id,
        nom_complet,
        role,
        identification

    FROM utilisateur

    WHERE id != ?

    ORDER BY nom_complet ASC
");

$stmt->execute([$userId]);

$utilisateurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Messagerie - Campus Relay</title>

    <style>

        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
        }

        body{
            font-family:"Segoe UI",sans-serif;
            background:#050816;
            color:white;
            overflow:hidden;
        }

        body::before{
            content:'';
            position:fixed;
            inset:0;
            background:
                radial-gradient(circle at top left, rgba(0,247,255,0.10), transparent 30%),
                radial-gradient(circle at bottom right, rgba(255,0,255,0.10), transparent 30%);
            animation:bgRotate 20s linear infinite;
            z-index:-1;
        }

        @keyframes bgRotate{

            from{
                transform:rotate(0deg) scale(1.2);
            }

            to{
                transform:rotate(360deg) scale(1.2);
            }
        }

        .navbar{
            height:80px;
            background:rgba(10,15,35,0.88);
            backdrop-filter:blur(12px);
            display:flex;
            justify-content:space-between;
            align-items:center;
            padding:0 35px;
            border-bottom:1px solid rgba(255,255,255,0.08);
        }

        .logo{
            font-size:28px;
            font-weight:bold;
            color:<?= $mainColor ?>;
            text-shadow:0 0 15px <?= $mainColor ?>;
        }

        .user{
            display:flex;
            align-items:center;
            gap:15px;
            flex-wrap:wrap;
        }

        .badge{
            padding:10px 18px;
            border-radius:30px;
            background:rgba(255,255,255,0.06);
            border:1px solid rgba(255,255,255,0.08);
        }

        .logout{
            text-decoration:none;
            padding:10px 18px;
            border-radius:14px;
            background:linear-gradient(135deg,#ff0066,#ff6600);
            color:white;
            font-weight:bold;
            transition:0.3s;
        }

        .logout:hover{
            transform:translateY(-3px);
            box-shadow:0 0 20px #ff0066;
        }

        .container{
            display:flex;
            height:calc(100vh - 80px);
        }

        .sidebar{
            width:340px;
            background:rgba(10,15,35,0.92);
            border-right:1px solid rgba(255,255,255,0.08);
            overflow-y:auto;
        }

        .sidebar-header{
            padding:25px;
            border-bottom:1px solid rgba(255,255,255,0.08);
        }

        .sidebar-header h2{
            color:<?= $mainColor ?>;
            margin-bottom:15px;
        }

        .new-btn{
            width:100%;
            padding:14px;
            border:none;
            border-radius:16px;
            background:linear-gradient(
                135deg,
                <?= $mainColor ?>,
                #ff00ff
            );
            color:black;
            font-weight:bold;
            cursor:pointer;
            transition:0.3s;
        }

        .new-btn:hover{
            transform:scale(1.03);
            box-shadow:0 0 20px <?= $mainColor ?>;
        }

        .conversation{
            display:block;
            padding:18px 22px;
            text-decoration:none;
            color:white;
            border-bottom:1px solid rgba(255,255,255,0.05);
            transition:0.3s;
        }

        .conversation:hover{
            background:rgba(255,255,255,0.05);
        }

        .conversation.active{
            background:rgba(255,255,255,0.08);
            border-left:4px solid <?= $mainColor ?>;
        }

        .conversation h3{
            margin-bottom:6px;
            font-size:16px;
        }

        .conversation small{
            color:#cfd3ff;
        }

        .chat{
            flex:1;
            display:flex;
            flex-direction:column;
        }

        .chat-header{
            padding:22px 30px;
            border-bottom:1px solid rgba(255,255,255,0.08);
            background:rgba(10,15,35,0.75);
            backdrop-filter:blur(10px);
        }

        .chat-header h2{
            color:<?= $mainColor ?>;
        }

        .messages{
            flex:1;
            overflow-y:auto;
            padding:30px;
            display:flex;
            flex-direction:column;
            gap:20px;
        }

        .message{
            max-width:70%;
            padding:18px;
            border-radius:22px;
            animation:fadeUp 0.3s ease;
            word-wrap:break-word;
        }

        .sent{
            align-self:flex-end;
            background:linear-gradient(
                135deg,
                <?= $mainColor ?>,
                #ff00ff
            );
            color:black;
            border-bottom-right-radius:6px;
        }

        .received{
            align-self:flex-start;
            background:rgba(255,255,255,0.08);
            backdrop-filter:blur(10px);
            border-bottom-left-radius:6px;
        }

        .message small{
            display:block;
            margin-top:10px;
            font-size:11px;
            opacity:0.75;
        }

        .file-attachment{
            margin-top:12px;
            padding:10px 14px;
            border-radius:12px;
            background:rgba(255,255,255,0.10);
        }

        .file-attachment a{
            text-decoration:none;
            color:white;
            font-size:13px;
        }

        .file-input-wrapper{
            display:flex;
            align-items:center;
            gap:10px;
            background:rgba(255,255,255,0.08);
            padding:0 14px;
            border-radius:30px;
        }

        .file-label{
            cursor:pointer;
            font-size:22px;
        }

        .file-name{
            font-size:12px;
            max-width:120px;
            overflow:hidden;
            text-overflow:ellipsis;
            white-space:nowrap;
            color:#ccc;
        }

        .input-area{
            padding:20px;
            display:flex;
            gap:15px;
            background:rgba(10,15,35,0.85);
            border-top:1px solid rgba(255,255,255,0.08);
        }

        .input-area input[type="text"]{
            flex:1;
            padding:16px 20px;
            border:none;
            border-radius:30px;
            background:rgba(255,255,255,0.08);
            color:white;
            outline:none;
            font-size:15px;
        }

        .input-area button{
            padding:16px 25px;
            border:none;
            border-radius:30px;
            cursor:pointer;
            font-weight:bold;
            background:linear-gradient(
                135deg,
                <?= $mainColor ?>,
                #ff00ff
            );
            color:black;
            transition:0.3s;
        }

        .input-area button:hover{
            transform:scale(1.05);
            box-shadow:0 0 20px <?= $mainColor ?>;
        }

        .empty{
            flex:1;
            display:flex;
            justify-content:center;
            align-items:center;
            flex-direction:column;
            opacity:0.7;
            text-align:center;
        }

        .modal{
            display:none;
            position:fixed;
            inset:0;
            background:rgba(0,0,0,0.7);
            justify-content:center;
            align-items:center;
            z-index:999;
        }

        .modal-content{
            width:420px;
            max-width:90%;
            padding:35px;
            border-radius:25px;
            background:#0d132e;
            border:1px solid rgba(255,255,255,0.08);
        }

        .modal-content h2{
            color:<?= $mainColor ?>;
            margin-bottom:20px;
        }

        .modal-content input,
        .modal-content select{
            width:100%;
            margin-bottom:15px;
            padding:14px;
            border:none;
            border-radius:15px;
            background:rgba(255,255,255,0.08);
            color:white;
            outline:none;
        }

        .modal-buttons{
            display:flex;
            gap:10px;
        }

        .modal-buttons button{
            flex:1;
            padding:14px;
            border:none;
            border-radius:14px;
            cursor:pointer;
            font-weight:bold;
        }

        .cancel{
            background:#444;
            color:white;
        }

        .create{
            background:linear-gradient(
                135deg,
                <?= $mainColor ?>,
                #ff00ff
            );
            color:black;
        }

        @media(max-width:900px){

            .container{
                flex-direction:column;
            }

            .sidebar{
                width:100%;
                height:300px;
            }

            .message{
                max-width:90%;
            }

            .input-area{
                flex-wrap:wrap;
            }
        }

    </style>

</head>

<body>

    <nav class="navbar">

        <div class="logo">
            ⚡ Campus Relay Chat
        </div>

        <div class="user">

            <div class="badge">
                👋 <?= htmlspecialchars($nom) ?>
            </div>

            <div class="badge">
                🎭 <?= htmlspecialchars($roleFR) ?>
            </div>

            <div class="badge">
                🆔 <?= htmlspecialchars($identifiant) ?>
            </div>

            <a
                href="index.php"
                class="badge"
                style="text-decoration:none;color:white;"
            >
                🏠 Dashboard
            </a>

            <a href="logout.php" class="logout">
                Déconnexion
            </a>

        </div>

    </nav>

    <div class="container">

        <aside class="sidebar">

            <div class="sidebar-header">

                <h2>💬 Conversations</h2>

                <button class="new-btn" onclick="openModal()">
                    + Nouvelle conversation
                </button>

            </div>

            <?php if (!empty($conversations)): ?>

                <?php foreach ($conversations as $conv): ?>

                    <a
                        href="?conv=<?= $conv['id'] ?>"
                        class="conversation <?= ($convId == $conv['id']) ? 'active' : '' ?>"
                    >

                        <h3>
                            <?= htmlspecialchars($conv['titre']) ?>
                        </h3>

                        <small>
                            <?= (int) $conv['total_messages'] ?> messages
                        </small>

                    </a>

                <?php endforeach; ?>

            <?php else: ?>

                <div style="padding:25px; opacity:0.7;">
                    Aucune conversation disponible.
                </div>

            <?php endif; ?>

        </aside>

        <section class="chat">

            <?php if ($currentConv): ?>

                <div class="chat-header">

                    <h2>
                        <?= htmlspecialchars($currentConv['titre']) ?>
                    </h2>

                </div>

                <div class="messages" id="messages">

                    <?php if (!empty($messages)): ?>

                        <?php foreach ($messages as $msg): ?>

                            <div class="message <?= ($msg['utilisateur_id'] == $userId) ? 'sent' : 'received' ?>">

                                <?= nl2br(htmlspecialchars($msg['contenu'])) ?>

                                <?php

                                $stmtFiles = $pdo->prepare("
                                    SELECT *
                                    FROM fichiers
                                    WHERE message_id = ?
                                ");

                                $stmtFiles->execute([$msg['id']]);

                                $files = $stmtFiles->fetchAll(PDO::FETCH_ASSOC);

                                ?>

                                <?php foreach ($files as $file): ?>

                                    <div class="file-attachment">

                                        <a
                                            href="<?= htmlspecialchars($file['chemin']) ?>"
                                            target="_blank"
                                        >
                                            <?php
                                            if (strpos($file['type_mime'], 'image/') === 0) {
                                                echo '🖼️';
                                            } elseif (strpos($file['type_mime'], 'video/') === 0) {
                                                echo '🎬';
                                            } elseif ($file['type_mime'] === 'application/pdf') {
                                                echo '📄';
                                            } else {
                                                echo '📎';
                                            }
                                            ?>
                                            <?= htmlspecialchars($file['nom_original']) ?>
                                            —
                                            <?= round($file['taille'] / 1024) ?> Ko
                                            
                                            <?php if ($file['est_compresse'] == 1): ?>
                                                <span style="background:#00ff99; color:#000; padding:2px 8px; border-radius:12px; font-size:10px;">
                                                    📦 compressé
                                                </span>
                                                <span style="font-size:10px; opacity:0.7;">
                                                    (<?= round($file['taille_originale'] / 1024) ?> Ko → <?= round($file['taille'] / 1024) ?> Ko)
                                                </span>
                                            <?php endif; ?>
                                        </a>

                                    </div>

                                <?php endforeach; ?>

                                <small>
                                    <?= htmlspecialchars($msg['nom_complet']) ?>
                                    •
                                    <?= date('d/m/Y H:i', strtotime($msg['cree_le'])) ?>
                                </small>

                            </div>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <div class="empty">

                            <h2>Aucun message</h2>

                            <p>
                                Commencez la discussion 🚀
                            </p>

                        </div>

                    <?php endif; ?>

                </div>

                <form
                    method="POST"
                    class="input-area"
                    enctype="multipart/form-data"
                >

                    <input
                        type="hidden"
                        name="conversation_id"
                        value="<?= $convId ?>"
                    >

                    <input
                        type="text"
                        name="message"
                        placeholder="Écrire un message..."
                        autocomplete="off"
                    >

                    <div class="file-input-wrapper">

                        <label
                            for="file_upload"
                            class="file-label"
                        >
                            📎
                        </label>

                        <input
                            type="file"
                            name="fichier"
                            id="file_upload"
                            accept="image/*,video/*,.pdf,.doc,.docx"
                            style="display:none;"
                        >

                        <span
                            class="file-name"
                            id="fileName"
                        ></span>

                    </div>

                    <button type="submit">
                        Envoyer →
                    </button>

                </form>

            <?php else: ?>

                <div class="empty">

                    <div style="font-size:70px;">
                        💬
                    </div>

                    <h2>
                        Sélectionnez une conversation
                    </h2>

                    <p style="margin-top:10px;">
                        ou créez-en une nouvelle.
                    </p>

                </div>

            <?php endif; ?>

        </section>

    </div>

    <div class="modal" id="modal">

        <div class="modal-content">

            <h2>
                ➕ Nouvelle conversation
            </h2>

            <form method="POST">

                <input
                    type="text"
                    name="titre"
                    placeholder="Titre de la conversation"
                    required
                >

                <select
                    name="destinataire_id"
                    required
                >

                    <option value="">
                        Choisir un destinataire
                    </option>

                    <?php foreach ($utilisateurs as $u): ?>

                        <option value="<?= $u['id'] ?>">

                            <?= htmlspecialchars($u['nom_complet']) ?>

                            —
                            <?= htmlspecialchars($rolesFR[$u['role']] ?? $u['role']) ?>

                        </option>

                    <?php endforeach; ?>

                </select>

                <div class="modal-buttons">

                    <button
                        type="button"
                        class="cancel"
                        onclick="closeModal()"
                    >
                        Annuler
                    </button>

                    <button
                        type="submit"
                        name="new_conversation"
                        class="create"
                    >
                        Créer
                    </button>

                </div>

            </form>

        </div>

    </div>

    <script>

        function openModal(){
            document.getElementById('modal').style.display = 'flex';
        }

        function closeModal(){
            document.getElementById('modal').style.display = 'none';
        }

        const messages = document.getElementById('messages');

        if(messages){
            messages.scrollTop = messages.scrollHeight;
        }

        const fileUpload = document.getElementById('file_upload');

        if(fileUpload){
            fileUpload.addEventListener('change', function(e){
                const fileName = e.target.files[0]?.name || '';
                document.getElementById('fileName').textContent = fileName;
            });
        }

        window.onclick = function(e){
            const modal = document.getElementById('modal');
            if(e.target === modal){
                closeModal();
            }
        }

    </script>

</body>

</html>