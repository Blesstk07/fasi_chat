<?php

class FichierService
{
    private PDO $pdo;
    private string $uploadDir;
    private int $maxSize;

    private array $allowedMimeTypes = [
        'image/jpeg', 'image/png', 'image/webp', 'image/gif',
        'video/mp4', 'video/webm', 'video/quicktime',
        'audio/mpeg', 'audio/wav', 'audio/ogg', 'audio/webm',
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'text/plain'
    ];

    public function __construct(PDO $pdo, ?string $uploadDir = null)
    {
        $this->pdo = $pdo;
        $this->uploadDir = $uploadDir ?? __DIR__ . '/../uploads/messages';
        $this->maxSize = 20 * 1024 * 1024; // 20 Mo, exigence du TP

        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0775, true);
        }
    }

    public function hasFiles(?array $files): bool
    {
        if (!$files || !isset($files['name'])) {
            return false;
        }

        $names = is_array($files['name']) ? $files['name'] : [$files['name']];
        foreach ($names as $name) {
            if (!empty($name)) {
                return true;
            }
        }

        return false;
    }

    public function enregistrerFichiersMessage(int $messageId, array $files): void
    {
        $normalizedFiles = $this->normaliserFiles($files);

        foreach ($normalizedFiles as $file) {
            if ($file['error'] === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            $this->validerFichier($file);
            $this->stockerEtEnregistrer($messageId, $file);
        }
    }

    public function getFichiersParMessages(array $messageIds): array
    {
        if (empty($messageIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($messageIds), '?'));
        $stmt = $this->pdo->prepare("SELECT * FROM fichiers WHERE message_id IN ($placeholders) ORDER BY created_at ASC");
        $stmt->execute($messageIds);

        $result = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[(int) $row['message_id']][] = $row;
        }

        return $result;
    }

    private function normaliserFiles(array $files): array
    {
        $normalized = [];

        if (!is_array($files['name'])) {
            return [$files];
        }

        foreach ($files['name'] as $index => $name) {
            $normalized[] = [
                'name' => $name,
                'type' => $files['type'][$index] ?? '',
                'tmp_name' => $files['tmp_name'][$index] ?? '',
                'error' => $files['error'][$index] ?? UPLOAD_ERR_NO_FILE,
                'size' => $files['size'][$index] ?? 0,
            ];
        }

        return $normalized;
    }

    private function validerFichier(array $file): void
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Erreur lors de l'envoi du fichier : " . $file['name']);
        }

        if ($file['size'] > $this->maxSize) {
            throw new Exception("Le fichier " . $file['name'] . " dépasse la limite de 20 Mo.");
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);

        if (!in_array($mimeType, $this->allowedMimeTypes, true)) {
            throw new Exception("Type de fichier non autorisé : " . $file['name']);
        }
    }

    private function stockerEtEnregistrer(int $messageId, array $file): void
    {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $extension = preg_replace('/[^a-z0-9]/', '', $extension) ?: $this->extensionDepuisMime($mimeType);

        // Les images compressées par GD sont enregistrées en JPG.
        if (str_starts_with($mimeType, 'image/') && $mimeType !== 'image/gif') {
            $extension = 'jpg';
        }

        // Les vidéos compressées par FFmpeg sont enregistrées en MP4.
        if (str_starts_with($mimeType, 'video/')) {
            $extension = 'mp4';
        }

        $nomStockage = uniqid('msg_' . $messageId . '_', true) . '.' . $extension;
        $destination = rtrim($this->uploadDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $nomStockage;

        if (str_starts_with($mimeType, 'image/') && $mimeType !== 'image/gif') {
            $this->compresserImage($file['tmp_name'], $destination, $mimeType);
        } elseif (str_starts_with($mimeType, 'video/')) {
            $this->compresserVideoSiPossible($file['tmp_name'], $destination, $mimeType);
        } else {
            if (!move_uploaded_file($file['tmp_name'], $destination)) {
                throw new Exception("Impossible de stocker le fichier : " . $file['name']);
            }
        }

        $cheminRelatif = 'uploads/messages/' . $nomStockage;
        $tailleFinale = filesize($destination) ?: (int) $file['size'];
        $mimeStocke = (str_starts_with($mimeType, 'image/') && $mimeType !== 'image/gif') ? 'image/jpeg' : $mimeType;
        $mimeStocke = str_starts_with($mimeType, 'video/') ? 'video/mp4' : $mimeStocke;

        $stmt = $this->pdo->prepare("INSERT INTO fichiers
            (message_id, nom_original, nom_stockage, chemin, type_mime, taille, extension)
            VALUES
            (:message_id, :nom_original, :nom_stockage, :chemin, :type_mime, :taille, :extension)");

        $stmt->execute([
            ':message_id' => $messageId,
            ':nom_original' => $file['name'],
            ':nom_stockage' => $nomStockage,
            ':chemin' => $cheminRelatif,
            ':type_mime' => $mimeStocke,
            ':taille' => $tailleFinale,
            ':extension' => $extension
        ]);
    }

    private function compresserImage(string $source, string $destination, string $mimeType): void
    {
        if (!extension_loaded('gd')) {
            if (!move_uploaded_file($source, $destination)) {
                throw new Exception("Impossible de stocker l'image.");
            }
            return;
        }

        $image = match ($mimeType) {
            'image/jpeg' => imagecreatefromjpeg($source),
            'image/png' => imagecreatefrompng($source),
            'image/webp' => imagecreatefromwebp($source),
            default => false
        };

        if (!$image) {
            if (!move_uploaded_file($source, $destination)) {
                throw new Exception("Impossible de stocker l'image.");
            }
            return;
        }

        $largeur = imagesx($image);
        $hauteur = imagesy($image);
        $maxLargeur = 1280;

        if ($largeur > $maxLargeur) {
            $nouvelleLargeur = $maxLargeur;
            $nouvelleHauteur = (int) round(($hauteur * $nouvelleLargeur) / $largeur);
            $imageReduite = imagecreatetruecolor($nouvelleLargeur, $nouvelleHauteur);
            imagecopyresampled($imageReduite, $image, 0, 0, 0, 0, $nouvelleLargeur, $nouvelleHauteur, $largeur, $hauteur);
            imagedestroy($image);
            $image = $imageReduite;
        }

        imagejpeg($image, $destination, 75);
        imagedestroy($image);
    }

    private function compresserVideoSiPossible(string $source, string $destination, string $mimeType): void
    {
        $ffmpegDisponible = trim((string) @shell_exec('command -v ffmpeg'));

        if ($ffmpegDisponible !== '') {
            $commande = sprintf(
                'ffmpeg -y -i %s -vcodec libx264 -crf 28 -preset veryfast -acodec aac %s 2>&1',
                escapeshellarg($source),
                escapeshellarg($destination)
            );
            @shell_exec($commande);

            if (file_exists($destination) && filesize($destination) > 0) {
                @unlink($source);
                return;
            }
        }

        if (!move_uploaded_file($source, $destination)) {
            throw new Exception("Impossible de stocker la vidéo.");
        }
    }

    private function extensionDepuisMime(string $mimeType): string
    {
        return match ($mimeType) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            'video/mp4' => 'mp4',
            'video/webm' => 'webm',
            'video/quicktime' => 'mov',
            'audio/mpeg' => 'mp3',
            'audio/wav' => 'wav',
            'audio/ogg' => 'ogg',
            'audio/webm' => 'webm',
            'application/pdf' => 'pdf',
            default => 'bin'
        };
    }
}
