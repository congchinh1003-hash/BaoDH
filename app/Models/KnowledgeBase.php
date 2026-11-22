<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class KnowledgeBase extends Model
{
    public function getCategories(): array
    {
        $stmt = $this->db->query("
            SELECT c.*, COUNT(a.id) as article_count
            FROM kb_categories c
            LEFT JOIN kb_articles a ON c.id = a.category_id AND a.status = 'PUBLISHED'
            WHERE c.is_active = 1
            GROUP BY c.id
            ORDER BY c.id ASC
        ");
        return $stmt->fetchAll();
    }

    public function getCategoryById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM kb_categories WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public function getArticles(array $filters = []): array
    {
        $sql = "
            SELECT a.*, c.name as category_name, c.icon as category_icon, u.full_name as author_name
            FROM kb_articles a
            JOIN kb_categories c ON a.category_id = c.id
            JOIN users u ON a.author_id = u.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($filters['category_id'])) {
            $sql .= " AND a.category_id = :category_id";
            $params['category_id'] = (int)$filters['category_id'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND a.status = :status";
            $params['status'] = $filters['status'];
        } else {
            // Default published
            $sql .= " AND a.status = 'PUBLISHED'";
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (a.title LIKE :search OR a.content LIKE :search)";
            $params['search'] = '%' . trim($filters['search']) . '%';
        }

        $sql .= " ORDER BY a.helpful_count DESC, a.view_count DESC, a.id DESC";

        if (!empty($filters['limit'])) {
            $sql .= " LIMIT " . (int)$filters['limit'];
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getArticleById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT a.*, c.name as category_name, c.icon as category_icon, u.full_name as author_name, u.email as author_email
            FROM kb_articles a
            JOIN kb_categories c ON a.category_id = c.id
            JOIN users u ON a.author_id = u.id
            WHERE a.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public function getPopularArticles(int $limit = 5): array
    {
        $stmt = $this->db->prepare("
            SELECT a.id, a.title, a.view_count, a.helpful_count, c.name as category_name, c.icon as category_icon
            FROM kb_articles a
            JOIN kb_categories c ON a.category_id = c.id
            WHERE a.status = 'PUBLISHED'
            ORDER BY a.helpful_count DESC, a.view_count DESC
            LIMIT " . (int)$limit
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function search(string $query, int $limit = 5): array
    {
        $rawQuery = trim($query);
        if (empty($rawQuery)) {
            return [];
        }

        // 1. Try verbatim match first
        $stmt = $this->db->prepare("
            SELECT a.id, a.title, SUBSTRING(a.content, 1, 150) as snippet, c.name as category_name
            FROM kb_articles a
            JOIN kb_categories c ON a.category_id = c.id
            WHERE a.status = 'PUBLISHED' AND (a.title LIKE :q OR a.content LIKE :q)
            ORDER BY a.helpful_count DESC
            LIMIT " . (int)$limit
        );
        $stmt->execute(['q' => '%' . $rawQuery . '%']);
        $results = $stmt->fetchAll();

        if (!empty($results)) {
            return $results;
        }

        // 2. Tokenize and search individual keywords (length >= 3)
        $words = preg_split('/[\s,\.\?\!]+/u', $rawQuery);
        $meaningfulWords = array_values(array_filter($words, function($w) {
            return mb_strlen(trim($w), 'UTF-8') >= 3;
        }));

        if (empty($meaningfulWords)) {
            return [];
        }

        $conditions = [];
        $params = [];
        foreach (array_slice($meaningfulWords, 0, 4) as $idx => $word) {
            $paramKey = "w" . $idx;
            $conditions[] = "(a.title LIKE :{$paramKey} OR a.content LIKE :{$paramKey})";
            $params[$paramKey] = '%' . trim($word) . '%';
        }

        $sql = "
            SELECT a.id, a.title, SUBSTRING(a.content, 1, 150) as snippet, c.name as category_name
            FROM kb_articles a
            JOIN kb_categories c ON a.category_id = c.id
            WHERE a.status = 'PUBLISHED' AND (" . implode(' OR ', $conditions) . ")
            ORDER BY a.helpful_count DESC
            LIMIT " . (int)$limit;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function createArticle(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO kb_articles (category_id, title, content, author_id, status, created_at)
            VALUES (:category_id, :title, :content, :author_id, :status, NOW())
        ");
        $stmt->execute([
            'category_id' => (int)$data['category_id'],
            'title'       => $data['title'],
            'content'     => $data['content'],
            'author_id'   => (int)$data['author_id'],
            'status'      => $data['status'] ?? 'PUBLISHED'
        ]);
        return (int)$this->db->lastInsertId();
    }

    /**
     * Alias for createArticle
     */
    public function create(array $data): int
    {
        return $this->createArticle($data);
    }

    public function updateArticle(int $id, array $data): bool
    {
        $stmt = $this->db->prepare("
            UPDATE kb_articles SET
                category_id = :category_id,
                title = :title,
                content = :content,
                status = :status,
                updated_at = NOW()
            WHERE id = :id
        ");
        return $stmt->execute([
            'category_id' => (int)$data['category_id'],
            'title'       => $data['title'],
            'content'     => $data['content'],
            'status'      => $data['status'] ?? 'PUBLISHED',
            'id'          => $id
        ]);
    }

    public function deleteArticle(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM kb_articles WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function incrementView(int $id): void
    {
        $stmt = $this->db->prepare("UPDATE kb_articles SET view_count = view_count + 1 WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }

    public function voteHelpful(int $id, bool $isHelpful): void
    {
        $col = $isHelpful ? 'helpful_count' : 'unhelpful_count';
        $stmt = $this->db->prepare("UPDATE kb_articles SET {$col} = {$col} + 1 WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }
}
