<?php

namespace App\Models;

use App\Core\Model;

class Notification extends Model
{
    public function create(array $data): int
    {
        $link = $data['link'] ?? (!empty($data['ticket_id']) ? '/tickets/' . $data['ticket_id'] : null);
        $stmt = $this->db->prepare("
            INSERT INTO notifications (user_id, ticket_id, title, message, type, link)
            VALUES (:user_id, :ticket_id, :title, :message, :type, :link)
        ");
        $stmt->execute([
            'user_id'   => $data['user_id'],
            'ticket_id' => $data['ticket_id'] ?? null,
            'title'     => $data['title'],
            'message'   => $data['message'],
            'type'      => $data['type'] ?? 'info',
            'link'      => $link
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM notifications WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function getUnreadForUser(int $userId): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM notifications 
            WHERE user_id = :user_id AND is_read = 0 
            ORDER BY created_at DESC LIMIT 20
        ");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function markAsRead(int $id, int $userId): void
    {
        $stmt = $this->db->prepare("UPDATE notifications SET is_read = 1 WHERE id = :id AND user_id = :user_id");
        $stmt->execute(['id' => $id, 'user_id' => $userId]);
    }
    
    public function markAllAsRead(int $userId): void
    {
        $stmt = $this->db->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $userId]);
    }

    public function notifyUser(int $userId, array $data): int
    {
        $data['user_id'] = $userId;
        return $this->create($data);
    }

    public function notifyUsers(array $userIds, array $data): void
    {
        $userIds = array_unique(array_filter($userIds));
        foreach ($userIds as $uid) {
            $data['user_id'] = (int)$uid;
            $this->create($data);
        }
    }

    public function notifyGroup(int $groupId, array $data, ?int $excludeUserId = null): void
    {
        $stmt = $this->db->prepare("
            SELECT DISTINCT user_id FROM it_group_members WHERE group_id = :group_id
        ");
        $stmt->execute(['group_id' => $groupId]);
        $userIds = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        // Also include leader of the group if assigned
        $stmtLeader = $this->db->prepare("SELECT leader_id FROM it_groups WHERE id = :id LIMIT 1");
        $stmtLeader->execute(['id' => $groupId]);
        $leaderId = $stmtLeader->fetchColumn();
        if ($leaderId) {
            $userIds[] = (int)$leaderId;
        }

        if ($excludeUserId) {
            $userIds = array_filter($userIds, fn($id) => (int)$id !== (int)$excludeUserId);
        }

        $this->notifyUsers($userIds, $data);
    }

    public function notifyRoles(array $roleCodes, array $data, ?int $excludeUserId = null): void
    {
        $placeholders = implode(',', array_fill(0, count($roleCodes), '?'));
        $stmt = $this->db->prepare("
            SELECT DISTINCT ur.user_id 
            FROM user_roles ur 
            JOIN roles r ON ur.role_id = r.id 
            WHERE r.code IN ($placeholders)
        ");
        $stmt->execute(array_values($roleCodes));
        $userIds = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        if ($excludeUserId) {
            $userIds = array_filter($userIds, fn($id) => (int)$id !== (int)$excludeUserId);
        }

        $this->notifyUsers($userIds, $data);
    }
}

