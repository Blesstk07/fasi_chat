<?php
// Configuration des fichiers
define('MAX_FILE_SIZE', 20 * 1024 * 1024); // 20 Mo
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('COMPRESSED_PATH', __DIR__ . '/../uploads/compressed/');

// Types de fichiers autorisés
define('ALLOWED_IMAGES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
define('ALLOWED_VIDEOS', ['video/mp4', 'video/webm', 'video/ogg']);
define('ALLOWED_DOCS', ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']);

// Qualité de compression (0-100)
define('IMAGE_QUALITY', 75);
define('VIDEO_BITRATE', '1M');

// Créer les dossiers s'ils n'existent pas
if (!is_dir(UPLOAD_PATH)) {
    mkdir(UPLOAD_PATH, 0755, true);
}
if (!is_dir(COMPRESSED_PATH)) {
    mkdir(COMPRESSED_PATH, 0755, true);
}
?>