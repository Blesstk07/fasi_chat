<?php
session_start();

require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/classes/MessageService.php';
require_once __DIR__ . '/classes/FichierService.php';

if (!isset($_SESSION['user']['id'])) {
    header('Location: login.php');
    exit;
}

$pdo = (new Database())->getConnection();
$messageService = new MessageService($pdo);
$fichierService = new FichierService($pdo);

$user = $_SESSION['user'];
$userId = (int) $user['id'];
$role = $user['role'];

$messages = $messageService->getMessagesVisibles($userId);
$messageIds = array_map(fn($m) => (int) $m['id'], $messages);
$fichiersParMessage = $fichierService->getFichiersParMessages($messageIds);
$destinataires = $messageService->getUtilisateursDisponibles($userId);
$cours = $messageService->getCoursAccessibles($userId);

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function badgeClass(string $type): string
{
    return match ($type) {
        'prive' => 'badge-prive',
        'public' => 'badge-public',
        'mur_pedagogique' => 'badge-mur',
        'convocation' => 'badge-convocation',
        default => 'badge-public'
    };
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FasiChat — Messagerie</title>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/messagerie.css">
</head>
<body>

<div class="sidebar">
    <div class="brand">
        <div class="brand-icon">💬</div>
        <div>
            <h2>FasiChat</h2>
            <p>Messagerie académique</p>
        </div>
    </div>

    <div class="profile-card">
        <div class="avatar"><?= e(substr($user['nom'], 0, 1)) ?></div>
        <div>
            <strong><?= e($user['nom']) ?></strong>
            <span><?= e($role) ?></span>
        </div>
    </div>

    <nav class="menu">
        <a class="active" href="messagerie.php">💬 Messagerie</a>
        <a href="valve.html">📣 Valve</a>
        <?php if ($role === 'etudiant'): ?>
            <a href="dashboard_etudiant.html">🎓 Dashboard étudiant</a>
        <?php elseif (in_array($role, ['enseignant', 'assistant'], true)): ?>
            <a href="dashboard_enseignant.html">👨‍🏫 Dashboard enseignant</a>
        <?php elseif ($role === 'apparitaire'): ?>
            <a href="dashboard_apparitaire.html">🗂 Dashboard apparitaire</a>
        <?php elseif ($role === 'vice_doyen'): ?>
            <a href="dashboard_vicedoyen.html">🏛 Dashboard Vice-Doyen</a>
        <?php elseif ($role === 'doyen'): ?>
            <a href="dashboard_admin.html">🏛 Dashboard Doyen</a>
        <?php endif; ?>
        <a href="controllers/logout.php">🚪 Déconnexion</a>
    </nav>
</div>

<main class="main">
    <header class="topbar">
        <div>
            <h1>Messagerie</h1>
            <p>Messages privés, publics et mur pédagogique selon les règles de visibilité.</p>
        </div>
        <div class="top-actions">
            <span><?= count($messages) ?> message(s)</span>
        </div>
    </header>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert success">Message envoyé avec succès.</div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert error"><?= e($_GET['error']) ?></div>
    <?php endif; ?>

    <section class="composer-card">
        <h2>Nouveau message</h2>
        <form method="POST" action="controllers/envoyer_message.php" class="composer" enctype="multipart/form-data">
            <div class="grid-3">
                <div class="form-group">
                    <label>Type</label>
                    <select name="type" id="typeMessage" required onchange="toggleFields()">
                        <option value="prive">Message privé</option>
                        <option value="public">Message public lié à un cours</option>
                        <?php if (in_array($role, ['enseignant', 'assistant'], true)): ?>
                            <option value="mur_pedagogique">Mur pédagogique</option>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="form-group" id="destinataireGroup">
                    <label>Destinataire privé</label>
                    <select name="destinataire_id">
                        <option value="">Choisir...</option>
                        <?php foreach ($destinataires as $dest): ?>
                            <option value="<?= (int) $dest['id'] ?>">
                                <?= e($dest['nom']) ?> — <?= e($dest['role']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group" id="coursGroup">
                    <label>Cours</label>
                    <select name="cours_id">
                        <option value="">Choisir...</option>
                        <?php foreach ($cours as $c): ?>
                            <option value="<?= (int) $c['id'] ?>"><?= e($c['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Contenu</label>
                <textarea name="contenu" rows="4" placeholder="Écrire votre message ou joindre un fichier..."></textarea>
            </div>

            <div class="form-group">
                <label>Fichiers joints</label>
                <input type="file" name="fichiers[]" multiple accept="image/*,video/*,audio/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt">
                <small class="help-text">Maximum 20 Mo par fichier. Images compressées automatiquement si l'extension GD est active. Les vidéos sont compressées si FFmpeg est disponible sur le serveur.</small>
            </div>

            <button type="submit">Envoyer</button>
        </form>
    </section>

    <section class="messages-list">
        <?php if (empty($messages)): ?>
            <div class="empty">Aucun message visible pour le moment.</div>
        <?php endif; ?>

        <?php foreach ($messages as $message): ?>
            <?php $isMine = (int)$message['expediteur_id'] === $userId; ?>
            <article class="message-card <?= $isMine ? 'mine' : '' ?>">
                <div class="message-head">
                    <div>
                        <strong><?= e($message['nom_expediteur']) ?></strong>
                        <?php if (!empty($message['nom_destinataire'])): ?>
                            <span> → <?= e($message['nom_destinataire']) ?></span>
                        <?php endif; ?>
                    </div>
                    <span class="badge <?= badgeClass($message['type_message']) ?>">
                        <?= e($message['type_message']) ?>
                    </span>
                </div>

                <?php if (!empty($message['nom_cours'])): ?>
                    <div class="course-line">📘 <?= e($message['nom_cours']) ?></div>
                <?php endif; ?>

                <p><?= nl2br(e($message['contenu'] ?? '')) ?></p>

                <?php $fichiers = $fichiersParMessage[(int) $message['id']] ?? []; ?>
                <?php if (!empty($fichiers)): ?>
                    <div class="attachments">
                        <?php foreach ($fichiers as $fichier): ?>
                            <a class="attachment" href="<?= e($fichier['chemin']) ?>" target="_blank" rel="noopener">
                                <span class="attachment-icon">📎</span>
                                <span>
                                    <strong><?= e($fichier['nom_original']) ?></strong>
                                    <small><?= e($fichier['type_mime']) ?> — <?= number_format(((int) $fichier['taille']) / 1024, 1, ',', ' ') ?> Ko</small>
                                </span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="message-date">
                    <?= e($message['created_at']) ?>
                </div>
            </article>
        <?php endforeach; ?>
    </section>
</main>

<script>
function toggleFields() {
    const type = document.getElementById('typeMessage').value;
    const destinataireGroup = document.getElementById('destinataireGroup');
    const coursGroup = document.getElementById('coursGroup');

    destinataireGroup.style.display = type === 'prive' ? 'block' : 'none';
    coursGroup.style.display = (type === 'public' || type === 'mur_pedagogique') ? 'block' : 'none';
}

toggleFields();
</script>
</body>
</html>
