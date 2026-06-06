<?php

require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../models/Utilisateur.php';
require_once __DIR__ . '/../models/Etudiant.php';
require_once __DIR__ . '/../models/Enseignant.php';
require_once __DIR__ . '/../models/Assistant.php';
require_once __DIR__ . '/../models/Doyen.php';
require_once __DIR__ . '/../models/ViceDoyen.php';
require_once __DIR__ . '/../models/Apparitaire.php';

class AuthController
{
    public static function login(): void
    {
        header('Content-Type: application/json');

        $email    = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Email et mot de passe requis.']);
            return;
        }

        $user = Utilisateur::getByEmail($email);
        if (!$user || !password_verify($password, $user->getMotDePasse())) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Identifiants incorrects.']);
            return;
        }

        Session::start();
        Session::setUser([
            'id'        => $user->getId(),
            'nom'       => $user->getNom(),
            'prenom'    => $user->getPrenom(),
            'email'     => $user->getEmail(),
            'role'      => $user->getRole(),
            'matricule' => $user->getMatricule(),
            'promotion_id' => $user->getPromotionId(),
        ]);

        Database::execute(
            "UPDATE utilisateurs SET statut = 'en_ligne', derniere_connexion = NOW() WHERE id = ?",
            [$user->getId()]
        );

        $redirect = match ($user->getRole()) {
            'etudiant'    => '/FasiChatClassRoom/dashboard_etudiant.html',
            'enseignant'  => '/FasiChatClassRoom/dashboard_enseignant.html',
            'assistant'   => '/FasiChatClassRoom/dashboard_enseignant.html',
            'doyen'       => '/FasiChatClassRoom/dashboard_admin.html',
            'vice_doyen'  => '/FasiChatClassRoom/dashboard_vicedoyen.html',
            'apparitaire' => '/FasiChatClassRoom/dashboard_apparitaire.html',
            default       => '/FasiChatClassRoom/login.html',
        };

        echo json_encode([
            'success'  => true,
            'redirect' => $redirect,
            'user'     => [
                'id'    => $user->getId(),
                'nom'   => $user->getNom(),
                'prenom'=> $user->getPrenom(),
                'role'  => $user->getRole(),
                'email' => $user->getEmail(),
            ],
        ]);
    }

    public static function logout(): void
    {
        Session::start();
        $userId = Session::getUserId();
        if ($userId) {
            Database::execute(
                "UPDATE utilisateurs SET statut = 'hors_ligne' WHERE id = ?",
                [$userId]
            );
        }
        Session::destroy();
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
    }

    public static function me(): void
    {
        Session::start();
        header('Content-Type: application/json');
        if (!Session::isLoggedIn()) {
            http_response_code(401);
            echo json_encode(['error' => 'Non connecté.']);
            return;
        }
        echo json_encode([
            'id'        => Session::getUserId(),
            'nom'       => Session::get('user_nom'),
            'prenom'    => Session::get('user_prenom'),
            'email'     => Session::get('user_email'),
            'role'      => Session::getUserRole(),
            'matricule' => Session::get('user_matricule'),
            'promotion_id' => Session::get('user_promotion_id'),
        ]);
    }
}
