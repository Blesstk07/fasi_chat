<?php
namespace Classes\Modeles;

class Document extends Fichier {
    
    public function compresser(): bool {
        // Les documents ne sont pas compressés (seulement validation)
        return false;
    }
    
    public function estValide(): bool {
        return in_array($this->typeMime, ALLOWED_DOCS);
    }
}