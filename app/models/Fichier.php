<?php

require_once __DIR__ . '/../core/Database.php';

class Fichier
{
    public const ALLOWED_TYPES = [
        'pdf'  => 'application/pdf',
        'doc'  => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'gif'  => 'image/gif',
        'mp4'  => 'video/mp4',
        'mp3'  => 'audio/mpeg',
        'wav'  => 'audio/wav',
    ];

    public static function upload(array $file, int $uploaderId): ?int
    {
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!isset(self::ALLOWED_TYPES[$extension])) {
            throw new InvalidArgumentException("Type de fichier non autorisé: $extension");
        }

        if ($file['size'] > MAX_FILE_SIZE) {
            throw new InvalidArgumentException("Le fichier dépasse la taille maximale de 20 Mo.");
        }

        $nomStocke = uniqid() . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
        $chemin = UPLOAD_DIR . $nomStocke;

        if (!is_dir(UPLOAD_DIR)) {
            mkdir(UPLOAD_DIR, 0755, true);
        }

        if (!move_uploaded_file($file['tmp_name'], $chemin)) {
            throw new RuntimeException("Échec de l'upload du fichier.");
        }

        return Database::insert(
            "INSERT INTO fichiers (nom_original, nom_stocke, type, taille, chemin, uploader_id)
             VALUES (?, ?, ?, ?, ?, ?)",
            [
                $file['name'],
                $nomStocke,
                $file['type'],
                $file['size'],
                $chemin,
                $uploaderId,
            ]
        );
    }

    public static function getById(int $id): ?array
    {
        return Database::fetch("SELECT * FROM fichiers WHERE id = ?", [$id]);
    }

    public static function getByUploader(int $userId): array
    {
        return Database::fetchAll(
            "SELECT * FROM fichiers WHERE uploader_id = ? ORDER BY created_at DESC",
            [$userId]
        );
    }

    public static function supprimer(int $id): bool
    {
        $fichier = self::getById($id);
        if (!$fichier) return false;
        if (file_exists($fichier['chemin'])) {
            unlink($fichier['chemin']);
        }
        Database::execute("DELETE FROM fichiers WHERE id = ?", [$id]);
        return true;
    }

    public static function compresserImage(string $chemin, int $qualite = 70): void
    {
        $info = getimagesize($chemin);
        if (!$info) return;

        $mime = $info['mime'];
        $image = match ($mime) {
            'image/jpeg' => imagecreatefromjpeg($chemin),
            'image/png'  => imagecreatefrompng($chemin),
            'image/gif'  => imagecreatefromgif($chemin),
            default      => null,
        };

        if ($image) {
            imagejpeg($image, $chemin, $qualite);
            imagedestroy($image);
        }
    }
}
