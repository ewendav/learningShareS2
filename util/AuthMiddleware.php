<?php

namespace Util;

class AuthMiddleware
{
    public static function isAuthenticated(): bool
    {
        // La session est déjà démarrée dans index.php
        return isset($_SESSION['user_id']);
    }

    public static function requireAuth(): void
    {
        if (!self::isAuthenticated()) {
            header('Location: /login');
            exit;
        }
    }

    public static function getUser(): ?array
    {
        if (self::isAuthenticated()) {
            return [
                'id' => $_SESSION['user_id'],
                'email' => $_SESSION['user_email'],
                'name' => $_SESSION['user_name'],
                'avatarPath' => $_SESSION['avatar_path'] ?? ''
            ];
        }
        return null;
    }
}
