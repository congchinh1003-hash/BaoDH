<?php

namespace App\Core;

use App\Models\User;

class Auth
{
    public static function check(): bool
    {
        return Session::has('user_id');
    }

    public static function id(): ?int
    {
        return Session::get('user_id');
    }

    public static function user(): ?array
    {
        if (!self::check()) {
            return null;
        }
        return Session::get('user_data');
    }

    public static function roles(): array
    {
        return Session::get('user_roles', []);
    }

    public static function hasRole(string $roleCode): bool
    {
        return in_array(strtoupper($roleCode), self::roles(), true);
    }

    public static function hasAnyRole(array $roles): bool
    {
        foreach ($roles as $role) {
            if (self::hasRole($role)) {
                return true;
            }
        }
        return false;
    }

    public static function isAdmin(): bool
    {
        return self::hasRole('ADMIN');
    }

    public static function isManager(): bool
    {
        return self::hasRole('IT_MANAGER') || self::isAdmin();
    }

    public static function isStaff(): bool
    {
        return self::hasRole('IT_STAFF') || self::isManager();
    }

    public static function isUser(): bool
    {
        return self::hasRole('USER');
    }

    private static ?array $cachedUserPermissions = null;

    /**
     * Check if current user has a specific permission code
     */
    public static function can(string $permissionCode): bool
    {
        if (!self::check()) {
            return false;
        }

        // Super Admin has full master access to all features
        if (self::isAdmin()) {
            return true;
        }

        $perms = self::permissions();
        return in_array($permissionCode, $perms, true);
    }

    /**
     * Get all permission codes granted to current user's roles
     */
    public static function permissions(): array
    {
        if (self::$cachedUserPermissions !== null) {
            return self::$cachedUserPermissions;
        }

        $roles = self::roles();
        if (empty($roles)) {
            self::$cachedUserPermissions = [];
            return [];
        }

        try {
            $db = Database::getConnection();
            $placeholders = implode(',', array_fill(0, count($roles), '?'));
            $sql = "
                SELECT DISTINCT p.code
                FROM role_permissions rp
                JOIN permissions p ON rp.permission_id = p.id
                JOIN roles r ON rp.role_id = r.id
                WHERE r.code IN ({$placeholders})
            ";
            $stmt = $db->prepare($sql);
            $stmt->execute(array_values($roles));
            self::$cachedUserPermissions = $stmt->fetchAll(\PDO::FETCH_COLUMN) ?: [];
        } catch (\Throwable $e) {
            self::$cachedUserPermissions = [];
        }

        return self::$cachedUserPermissions;
    }

    public static function clearPermissionCache(): void
    {
        self::$cachedUserPermissions = null;
    }

    public static function login(array $user, array $roles): void
    {
        self::clearPermissionCache();
        Session::start();
        if (session_status() === PHP_SESSION_ACTIVE && !headers_sent()) {
            session_regenerate_id(true);
        }
        Session::set('user_id', $user['id']);
        Session::set('user_data', [
            'id'            => $user['id'],
            'username'      => $user['username'] ?? '',
            'email'         => $user['email'] ?? '',
            'full_name'     => $user['full_name'] ?? '',
            'phone'         => $user['phone'] ?? '',
            'department'    => $user['department'] ?? '',
            'seat_location' => $user['seat_location'] ?? '',
            'job_title'     => $user['job_title'] ?? '',
            'avatar'        => $user['avatar'] ?? null,
        ]);
        Session::set('user_roles', array_map('strtoupper', $roles));
    }

    public static function logout(): void
    {
        self::clearPermissionCache();
        Session::destroy();
    }
}

