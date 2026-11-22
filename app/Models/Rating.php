<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class Rating extends Model
{
    public function create(int $ticketId, int $userId, int $score, ?string $comment = null): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO ticket_ratings (ticket_id, user_id, score, comment, created_at)
            VALUES (:ticket_id, :user_id, :score, :comment, NOW())
            ON DUPLICATE KEY UPDATE score = VALUES(score), comment = VALUES(comment), created_at = NOW()
        ");
        $stmt->execute([
            'ticket_id' => $ticketId,
            'user_id'   => $userId,
            'score'     => max(1, min(5, $score)),
            'comment'   => $comment
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function findByTicketId(int $ticketId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT tr.*, u.full_name as user_name, u.email as user_email
            FROM ticket_ratings tr
            JOIN users u ON tr.user_id = u.id
            WHERE tr.ticket_id = :ticket_id
            LIMIT 1
        ");
        $stmt->execute(['ticket_id' => $ticketId]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public function getAverageOverall(): array
    {
        $stmt = $this->db->query("
            SELECT 
                COUNT(*) as total_ratings,
                ROUND(AVG(score), 1) as avg_score,
                SUM(CASE WHEN score = 5 THEN 1 ELSE 0 END) as star_5,
                SUM(CASE WHEN score = 4 THEN 1 ELSE 0 END) as star_4,
                SUM(CASE WHEN score = 3 THEN 1 ELSE 0 END) as star_3,
                SUM(CASE WHEN score <= 2 THEN 1 ELSE 0 END) as star_low
            FROM ticket_ratings
        ");
        return $stmt->fetch() ?: ['total_ratings' => 0, 'avg_score' => 0];
    }
}

