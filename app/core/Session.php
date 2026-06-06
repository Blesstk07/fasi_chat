<?php

class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key): mixed
    {
        return $_SESSION[$key] ?? null;
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function destroy(): void
    {
        session_destroy();
        $_SESSION = [];
    }

    public static function isLoggedIn(): bool
    {
        return self::has('user_id');
    }

    public static function getUserRole(): ?string
    {
        return self::get('user_role');
    }

    public static function getUserId(): ?int
    {
        return self::get('user_id');
    }

    public static function getUserName(): ?string
    {
        return self::get('user_nom') . ' ' . self::get('user_prenom');
    }

    public static function requireAuth(): void
    {
        self::start();
        if (!self::isLoggedIn()) {
            header('Location: /FasiChatClassRoom/login.html');
            exit;
        }
    }

    public static function requireRole(string|array $roles): void
    {
        self::start();
        if (!self::isLoggedIn()) {
            header('Location: /FasiChatClassRoom/login.html');
            exit;
        }
        $userRole = self::getUserRole();
        if (is_array($roles) && !in_array($userRole, $roles)) {
            http_response_code(403);
            echo json_encode(['error' => 'Accès non autorisé.']);
            exit;
        }
        if (is_string($roles) && $userRole !== $roles) {
            http_response_code(403);
            echo json_encode(['error' => 'Accès non autorisé.']);
            exit;
        }
    }

    public static function setUser(array $user): void
    {
        self::set('user_id', $user['id']);
        self::set('user_nom', $user['nom']);
        self::set('user_prenom', $user['prenom']);
        self::set('user_email', $user['email']);
        self::set('user_role', $user['role']);
        self::set('user_matricule', $user['matricule'] ?? '');
        self::set('user_promotion_id', $user['promotion_id'] ?? null);
    }
}
