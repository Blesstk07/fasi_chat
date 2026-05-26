<?php
namespace Classes\Modeles;

class Video extends Fichier {
    
    public function compresser(): bool {
        // Vérifier que FFmpeg est installé
        $ffmpegPath = trim(shell_exec('which ffmpeg'));
        if (empty($ffmpegPath)) {
            // Si FFmpeg n'est pas installé, on ne compresse pas
            return false;
        }
        
        $outputPath = UPLOAD_PATH . 'compressed_' . $this->nomStockage;
        
        // Commande FFmpeg pour compresser
        $command = sprintf(
            '%s -i %s -vcodec libx264 -crf 28 -preset fast -acodec aac -b:a 128k %s 2>&1',
            escapeshellcmd($ffmpegPath),
            escapeshellarg($this->chemin),
            escapeshellarg($outputPath)
        );
        
        exec($command, $output, $returnCode);
        
        if ($returnCode === 0 && file_exists($outputPath)) {
            // Remplacer l'original par la version compressée
            unlink($this->chemin);
            rename($outputPath, $this->chemin);
            return true;
        }
        
        return false;
    }
    
    public function estValide(): bool {
        return in_array($this->typeMime, ALLOWED_VIDEOS);
    }
}