<?php

require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../models/Doyen.php';
require_once __DIR__ . '/../models/ViceDoyen.php';
require_once __DIR__ . '/../models/Enseignant.php';
require_once __DIR__ . '/../models/Etudiant.php';
require_once __DIR__ . '/../models/Apparitaire.php';
require_once __DIR__ . '/../models/Cours.php';

class DashboardController
{
    public static function admin(): void
    {
        Session::start();
        header('Content-Type: application/json');
        Session::requireRole('doyen');

        $user  = new Doyen(['id' => Session::getUserId()]);
        $stats = $user->getStats();
        $users = Doyen::getAll();

        echo json_encode([
            'success' => true,
            'stats'   => $stats,
            'users'   => $users,
        ]);
    }

    public static function enseignant(): void
    {
        Session::start();
        header('Content-Type: application/json');
        Session::requireRole(['enseignant', 'assistant']);

        $user      = new Enseignant(['id' => Session::getUserId()]);
        $cours     = $user->getCours();
        $etudiants = $user->getEtudiants();
        $collegues = $user->getCollegues();

        echo json_encode([
            'success'   => true,
            'cours'     => $cours,
            'etudiants' => $etudiants,
            'collegues' => $collegues,
        ]);
    }

    public static function etudiant(): void
    {
        Session::start();
        header('Content-Type: application/json');
        Session::requireRole('etudiant');

        $user        = new Etudiant(['id' => Session::getUserId()]);
        $cours       = $user->getCours();
        $camarades   = $user->getCamaradesPromotion();
        $enseignants = $user->getEnseignants();

        echo json_encode([
            'success'     => true,
            'cours'       => $cours,
            'camarades'   => $camarades,
            'enseignants' => $enseignants,
        ]);
    }

    public static function apparitaire(): void
    {
        Session::start();
        header('Content-Type: application/json');
        Session::requireRole('apparitaire');

        $user   = new Apparitaire(['id' => Session::getUserId()]);
        $stats  = $user->getStatsValve();
        $annonces = $user->getAnnonces();

        echo json_encode([
            'success'  => true,
            'stats'    => $stats,
            'annonces' => $annonces,
        ]);
    }

    public static function vicedoyen(): void
    {
        Session::start();
        header('Content-Type: application/json');
        Session::requireRole('vice_doyen');

        $user = new ViceDoyen(['id' => Session::getUserId()]);
        $convocations = $user->getConvocations();
        $messages     = $user->getMessagesDoyen();

        echo json_encode([
            'success'      => true,
            'convocations' => $convocations,
            'messages'     => $messages,
        ]);
    }
}
