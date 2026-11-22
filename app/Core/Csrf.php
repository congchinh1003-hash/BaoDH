<?php

declare(strict_types=1);

namespace App\Core;

class Csrf
{
    public static function getToken(): string
    {
        return Session::csrfToken();
    }

    public static function field(): string
    {
        $token = self::getToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    public static function validate(?string $token): bool
    {
        return Session::validateCsrf($token);
    }
}

