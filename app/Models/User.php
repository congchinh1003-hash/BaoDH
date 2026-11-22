<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class User extends Model
{
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function findByUsernameOrEmail(string $account): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE (username = :account_user OR email = :account_email) AND is_active = 1 LIMIT 1");
        $stmt->execute([
            'account_user'  => $account,
            'account_email' => $account
        ]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function findByUsername(string $username): ?array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM users 
            WHERE (username = :u OR email LIKE :e) 
              AND is_active = 1 
            ORDER BY (username = :u2) DESC 
            LIMIT 1
        ");
        $stmt->execute([
            'u'  => $username,
            'u2' => $username,
            'e'  => $username . '@%'
        ]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function searchUsersForMention(string $query, int $limit = 5): array
    {
        $stmt = $this->db->prepare("
            SELECT username, full_name 
            FROM users 
            WHERE is_active = 1 AND (username LIKE :q OR full_name LIKE :q) 
            ORDER BY full_name ASC 
            LIMIT " . (int)$limit
        );
        $stmt->execute(['q' => '%' . $query . '%']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRoles(int $userId): array
    {
        $stmt = $this->db->prepare("
            SELECT r.code 
            FROM roles r 
            JOIN user_roles ur ON r.id = ur.role_id 
            WHERE ur.user_id = :user_id
        ");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getAllUsers(): array
    {
        $stmt = $this->db->query("
            SELECT u.*, 
                   GROUP_CONCAT(r.name SEPARATOR ', ') as role_names,
                   GROUP_CONCAT(r.code SEPARATOR ', ') as role_codes,
                   COALESCE(GROUP_CONCAT(r.name SEPARATOR ', '), 'User') as role
            FROM users u
            LEFT JOIN user_roles ur ON u.id = ur.user_id
            LEFT JOIN roles r ON ur.role_id = r.id
            GROUP BY u.id
            ORDER BY u.id ASC
        ");
        return $stmt->fetchAll();
    }

    public function getStaffMembersByGroup(?int $groupId = null): array
    {
        if ($groupId) {
            $stmt = $this->db->prepare("
                SELECT u.id, u.full_name, u.email, igm.is_primary
                FROM users u
                JOIN it_group_members igm ON u.id = igm.user_id
                WHERE igm.group_id = :group_id AND u.is_active = 1
                ORDER BY u.full_name ASC
            ");
            $stmt->execute(['group_id' => $groupId]);
            return $stmt->fetchAll();
        }

        // Get all IT staff and managers
        $stmt = $this->db->query("
            SELECT DISTINCT u.id, u.full_name, u.email, u.department
            FROM users u
            JOIN user_roles ur ON u.id = ur.user_id
            JOIN roles r ON ur.role_id = r.id
            WHERE r.code IN ('IT_STAFF', 'IT_MANAGER', 'ADMIN') AND u.is_active = 1
            ORDER BY u.full_name ASC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Get all active staff and manager users (alias for getStaffMembersByGroup)
     */
    public function getStaffUsers(): array
    {
        $stmt = $this->db->query("
            SELECT DISTINCT u.id, u.full_name, u.email, u.department, r.name as role
            FROM users u
            JOIN user_roles ur ON u.id = ur.user_id
            JOIN roles r ON ur.role_id = r.id
            WHERE r.code IN ('IT_STAFF', 'IT_MANAGER', 'ADMIN') AND u.is_active = 1
            ORDER BY u.full_name ASC
        ");
        return $stmt->fetchAll();
    }
}

