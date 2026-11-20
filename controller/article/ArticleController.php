<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../model/article/Article.php';

class ArticleController {
    public function listArticles(?string $category = null): array {
        $db = Config::getConnexion();
        $sql = "
            SELECT a.*,
                   u.full_name AS author_name,
                   u.avatar_color AS author_color,
                   u.role AS author_role,
                   COUNT(c.id) AS comments_count
            FROM articles a
            LEFT JOIN users u ON a.user_id = u.id
            LEFT JOIN comments c ON c.article_id = a.id
        ";
        if ($category !== null && $category !== '') {
            $sql .= " WHERE a.category = :category GROUP BY a.id ORDER BY (a.status = 'Featured') DESC, a.created_at DESC";
            $stmt = $db->prepare($sql);
            $stmt->execute(['category' => $category]);
            return $stmt->fetchAll();
        }
        $sql .= " GROUP BY a.id ORDER BY (a.status = 'Featured') DESC, a.created_at DESC";
        return $db->query($sql)->fetchAll();
    }

    public function getArticleById(int $id): ?array {
        $db = Config::getConnexion();
        $stmt = $db->prepare("
            SELECT a.*, u.full_name AS author_name, u.avatar_color AS author_color
            FROM articles a
            LEFT JOIN users u ON a.user_id = u.id
            WHERE a.id = :id
        ");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function addArticle(Article $article): bool {
        $db = Config::getConnexion();
        $stmt = $db->prepare("
            INSERT INTO articles (title, content, category, tags, read_time, status, user_id)
            VALUES (:title, :content, :category, :tags, :read_time, :status, :user_id)
        ");
        return $stmt->execute([
            'title'     => $article->getTitle(),
            'content'   => $article->getContent(),
            'category'  => $article->getCategory(),
            'tags'      => $article->getTags(),
            'read_time' => $article->getReadTime(),
            'status'    => $article->getStatus(),
            'user_id'   => $article->getUserId()
        ]);
    }

    public function updateArticle(Article $article, int $id): bool {
        $db = Config::getConnexion();
        $stmt = $db->prepare("
            UPDATE articles SET
                title = :title,
                content = :content,
                category = :category,
                tags = :tags,
                read_time = :read_time,
                status = :status
            WHERE id = :id
        ");
        return $stmt->execute([
            'title'     => $article->getTitle(),
            'content'   => $article->getContent(),
            'category'  => $article->getCategory(),
            'tags'      => $article->getTags(),
            'read_time' => $article->getReadTime(),
            'status'    => $article->getStatus(),
            'id'        => $id
        ]);
    }

    public function likeArticle(int $id): bool {
        $db = Config::getConnexion();
        $stmt = $db->prepare("UPDATE articles SET likes_count = likes_count + 1 WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function incrementViews(int $id): bool {
        $db = Config::getConnexion();
        $stmt = $db->prepare("UPDATE articles SET views_count = views_count + 1 WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function deleteArticle(int $id): bool {
        $db = Config::getConnexion();
        $stmt = $db->prepare("DELETE FROM articles WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
?>
