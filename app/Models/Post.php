<?php
namespace App\Models;

use App\Config\Database;
use PDO;

class Post {
    public static function getAll(int $page = 1, int $perPage = 6, ?string $search = null, ?string $categorySlug = null): array {
        $db = Database::getConnection();
        if (!$db) return ['posts' => [], 'total' => 0, 'pages' => 0];

        $offset = ($page - 1) * $perPage;
        $where = ["p.status = 'published'"];
        $params = [];

        if (!empty($search)) {
            $where[] = "(p.title LIKE :search OR p.content LIKE :search2 OR p.ai_tags LIKE :search3)";
            $params['search'] = "%{$search}%";
            $params['search2'] = "%{$search}%";
            $params['search3'] = "%{$search}%";
        }

        if (!empty($categorySlug)) {
            $where[] = "c.slug = :category";
            $params['category'] = $categorySlug;
        }

        $whereClause = implode(' AND ', $where);

        // Count total
        $countSql = "SELECT COUNT(*) as total FROM posts p LEFT JOIN categories c ON p.category_id = c.id WHERE {$whereClause}";
        $countStmt = $db->prepare($countSql);
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        // Get paginated posts
        $sql = "SELECT p.*, u.username as author_name, u.avatar as author_avatar, c.name as category_name, c.slug as category_slug
                FROM posts p
                INNER JOIN users u ON p.user_id = u.id
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE {$whereClause}
                ORDER BY p.created_at DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue(":{$k}", $v);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'posts' => $stmt->fetchAll(),
            'total' => $total,
            'pages' => ceil($total / $perPage),
            'current_page' => $page
        ];
    }

    public static function getBySlug(string $slug): ?array {
        $db = Database::getConnection();
        if (!$db) return null;

        $stmt = $db->prepare("SELECT p.*, u.username as author_name, u.avatar as author_avatar, u.bio as author_bio, c.name as category_name, c.slug as category_slug
                              FROM posts p
                              INNER JOIN users u ON p.user_id = u.id
                              LEFT JOIN categories c ON p.category_id = c.id
                              WHERE p.slug = :slug LIMIT 1");
        $stmt->execute(['slug' => $slug]);
        $post = $stmt->fetch();
        return $post ?: null;
    }

    public static function getById(int $id): ?array {
        $db = Database::getConnection();
        if (!$db) return null;

        $stmt = $db->prepare("SELECT * FROM posts WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $post = $stmt->fetch();
        return $post ?: null;
    }

    public static function create(array $data): int {
        $db = Database::getConnection();
        if (!$db) throw new \Exception("Database unavailable.");

        $readingTime = max(1, (int)ceil(str_word_count(strip_tags($data['content'])) / 200));
        $slug = self::generateUniqueSlug($data['title']);

        $stmt = $db->prepare("INSERT INTO posts 
            (user_id, category_id, title, slug, content, excerpt, cover_image, ai_summary, ai_tags, reading_time, status)
            VALUES (:user_id, :category_id, :title, :slug, :content, :excerpt, :cover_image, :ai_summary, :ai_tags, :reading_time, :status)");

        $stmt->execute([
            'user_id'      => $data['user_id'],
            'category_id'  => !empty($data['category_id']) ? $data['category_id'] : null,
            'title'        => $data['title'],
            'slug'         => $slug,
            'content'      => $data['content'],
            'excerpt'      => $data['excerpt'] ?? substr(strip_tags($data['content']), 0, 160) . '...',
            'cover_image'  => !empty($data['cover_image']) ? $data['cover_image'] : 'https://images.unsplash.com/photo-1499750310107-5fef28a66643?auto=format&fit=crop&w=1200&q=80',
            'ai_summary'   => $data['ai_summary'] ?? null,
            'ai_tags'      => $data['ai_tags'] ?? null,
            'reading_time' => $readingTime,
            'status'       => $data['status'] ?? 'published'
        ]);

        return (int)$db->lastInsertId();
    }

    public static function update(int $id, array $data): bool {
        $db = Database::getConnection();
        if (!$db) return false;

        $readingTime = max(1, (int)ceil(str_word_count(strip_tags($data['content'])) / 200));

        $stmt = $db->prepare("UPDATE posts SET 
            category_id = :category_id,
            title = :title,
            content = :content,
            excerpt = :excerpt,
            cover_image = :cover_image,
            ai_summary = :ai_summary,
            ai_tags = :ai_tags,
            reading_time = :reading_time,
            status = :status
            WHERE id = :id");

        return $stmt->execute([
            'id'           => $id,
            'category_id'  => !empty($data['category_id']) ? $data['category_id'] : null,
            'title'        => $data['title'],
            'content'      => $data['content'],
            'excerpt'      => $data['excerpt'] ?? substr(strip_tags($data['content']), 0, 160) . '...',
            'cover_image'  => !empty($data['cover_image']) ? $data['cover_image'] : 'https://images.unsplash.com/photo-1499750310107-5fef28a66643?auto=format&fit=crop&w=1200&q=80',
            'ai_summary'   => $data['ai_summary'] ?? null,
            'ai_tags'      => $data['ai_tags'] ?? null,
            'reading_time' => $readingTime,
            'status'       => $data['status'] ?? 'published'
        ]);
    }

    public static function delete(int $id, int $userId, bool $isAdmin = false): bool {
        $db = Database::getConnection();
        if (!$db) return false;

        if ($isAdmin) {
            $stmt = $db->prepare("DELETE FROM posts WHERE id = :id");
            return $stmt->execute(['id' => $id]);
        }

        $stmt = $db->prepare("DELETE FROM posts WHERE id = :id AND user_id = :user_id");
        return $stmt->execute(['id' => $id, 'user_id' => $userId]);
    }

    public static function incrementViews(int $id): void {
        $db = Database::getConnection();
        if (!$db) return;
        $stmt = $db->prepare("UPDATE posts SET views = views + 1 WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }

    public static function updateAISummary(int $id, string $summary): bool {
        $db = Database::getConnection();
        if (!$db) return false;
        $stmt = $db->prepare("UPDATE posts SET ai_summary = :summary WHERE id = :id");
        return $stmt->execute(['summary' => $summary, 'id' => $id]);
    }

    public static function getAllCategories(): array {
        $db = Database::getConnection();
        if (!$db) return [];
        $stmt = $db->query("SELECT * FROM categories ORDER BY name ASC");
        return $stmt->fetchAll();
    }

    private static function generateUniqueSlug(string $title): string {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
        $slug = trim($slug, '-');
        if (empty($slug)) $slug = 'post-' . time();

        $db = Database::getConnection();
        if (!$db) return $slug . '-' . time();

        $stmt = $db->prepare("SELECT COUNT(*) FROM posts WHERE slug = :slug");
        $stmt->execute(['slug' => $slug]);
        if ($stmt->fetchColumn() > 0) {
            $slug .= '-' . substr(bin2hex(random_bytes(4)), 0, 6);
        }

        return $slug;
    }
}
