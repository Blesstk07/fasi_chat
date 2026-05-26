<?php
namespace Classes\Modeles;

class Image extends Fichier {
    
    public function compresser(): bool {
        // Ne compresser que si c'est une image
        if (!in_array($this->typeMime, ALLOWED_IMAGES)) {
            return false;
        }
        
        // Créer l'image source selon le type
        switch ($this->typeMime) {
            case 'image/jpeg':
                $source = imagecreatefromjpeg($this->chemin);
                break;
            case 'image/png':
                $source = imagecreatefrompng($this->chemin);
                // Conserver la transparence
                imagepalettetotruecolor($source);
                imagealphablending($source, true);
                imagesavealpha($source, true);
                break;
            case 'image/gif':
                $source = imagecreatefromgif($this->chemin);
                break;
            case 'image/webp':
                $source = imagecreatefromwebp($this->chemin);
                break;
            default:
                return false;
        }
        
        if (!$source) {
            return false;
        }
        
        // Obtenir les dimensions
        $width = imagesx($source);
        $height = imagesy($source);
        
        // Calculer nouvelles dimensions (max 1200px)
        $maxWidth = 1200;
        $maxHeight = 1200;
        $ratio = min($maxWidth / $width, $maxHeight / $height);
        
        if ($ratio < 1) {
            $newWidth = (int)($width * $ratio);
            $newHeight = (int)($height * $ratio);
        } else {
            $newWidth = $width;
            $newHeight = $height;
        }
        
        // Créer l'image redimensionnée
        $compressed = imagecreatetruecolor($newWidth, $newHeight);
        
        // PNG: conserver la transparence
        if ($this->typeMime === 'image/png') {
            imagealphablending($compressed, false);
            imagesavealpha($compressed, true);
            $transparent = imagecolorallocatealpha($compressed, 0, 0, 0, 127);
            imagefilledrectangle($compressed, 0, 0, $newWidth, $newHeight, $transparent);
        }
        
        imagecopyresampled($compressed, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        
        // Sauvegarder l'image compressée
        $tempPath = UPLOAD_PATH . 'temp_' . $this->nomStockage;
        
        switch ($this->typeMime) {
            case 'image/jpeg':
                imagejpeg($compressed, $tempPath, IMAGE_QUALITY);
                break;
            case 'image/png':
                imagepng($compressed, $tempPath, 9);
                break;
            case 'image/gif':
                imagegif($compressed, $tempPath);
                break;
            case 'image/webp':
                imagewebp($compressed, $tempPath, IMAGE_QUALITY);
                break;
        }
        
        // Nettoyer
        imagedestroy($source);
        imagedestroy($compressed);
        
        // Remplacer l'original par la version compressée
        if (file_exists($tempPath)) {
            unlink($this->chemin);
            rename($tempPath, $this->chemin);
            return true;
        }
        
        return false;
    }
    
    public function estValide(): bool {
        return in_array($this->typeMime, ALLOWED_IMAGES);
    }
}