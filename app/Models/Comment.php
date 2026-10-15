<?php
namespace App\Models;

use App\Config\Database;
use PDO;

class Comment {
    public static function getByPostId(int $postId): array {
        $db = Database::getConnection();
        if (!$db) return [];

        $stmt = $db->prepare("SELECT c.*, u.username, u.avatar, u.role
                              FROM comments c
                              INNER JOIN users u ON c.user_id = u.id
                              WHERE c.post_id = :post_id
                              ORDER BY c.created_at DESC");
        $stmt->execute(['post_id' => $postId]);
        return $stmt->fetchAll();
    }

    public static function create(int $postId, int $userId, string $content): int {
        $db = Database::getConnection();
        if (!$db) throw new \Exception("Database unavailable.");

        $stmt = $db->prepare("INSERT INTO comments (post_id, user_id, content) VALUES (:post_id, :user_id, :content)");
        $stmt->execute([
            'post_id' => $postId,
            'user_id' => $userId,
            'content' => trim($content)
        ]);

        return (int)$db->lastInsertId();
    }

    public static function delete(int $commentId, int $userId, bool $isAdmin = false): bool {
        $db = Database::getConnection();
        if (!$db) return false;

        if ($isAdmin) {
            $stmt = $db->prepare("DELETE FROM comments WHERE id = :id");
            return $stmt->execute(['id' => $commentId]);
        }

        $stmt = $db->prepare("DELETE FROM comments WHERE id = :id AND user_id = :user_id");
        return $stmt->execute(['id' => $commentId, 'user_id' => $userId]);
    }
}
