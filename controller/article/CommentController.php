<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../model/article/Comment.php';

class CommentController {
    /**
     * Explicit JOIN: Comment.article_id = Article.id
     */
    public function listComments(?int $article_id = null): array {
        $db = Config::getConnexion();
        $sql = "
            SELECT c.*,
                   a.title AS article_title,
                   a.category AS article_category,
                   u.full_name AS author_name,
                   u.avatar_color AS author_color,
                   u.role AS author_role
            FROM comments c
            INNER JOIN articles a ON c.article_id = a.id
            LEFT JOIN users u ON c.user_id = u.id
        ";
        if ($article_id !== null) {
            $sql .= " WHERE c.article_id = :article_id ORDER BY c.is_coach_pick DESC, c.created_at ASC";
            $stmt = $db->prepare($sql);
            $stmt->execute(['article_id' => $article_id]);
            return $stmt->fetchAll();
        }
        $sql .= " ORDER BY c.created_at DESC";
        return $db->query($sql)->fetchAll();
    }

    public function getCommentById(int $id): ?array {
        $db = Config::getConnexion();
        $stmt = $db->prepare("
            SELECT c.*, a.title AS article_title, u.full_name AS author_name
            FROM comments c
            INNER JOIN articles a ON c.article_id = a.id
            LEFT JOIN users u ON c.user_id = u.id
            WHERE c.id = :id
        ");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function addComment(Comment $comment): bool {
        $db = Config::getConnexion();
        $stmt = $db->prepare("
            INSERT INTO comments (article_id, user_id, content, is_coach_pick)
            VALUES (:article_id, :user_id, :content, :is_coach_pick)
        ");
        return $stmt->execute([
            'article_id'    => $comment->getArticleId(),
            'user_id'       => $comment->getUserId(),
            'content'       => $comment->getContent(),
            'is_coach_pick' => $comment->getIsCoachPick()
        ]);
    }

    public function updateComment(Comment $comment, int $id): bool {
        $db = Config::getConnexion();
        $stmt = $db->prepare("
            UPDATE comments SET
                article_id = :article_id,
                content = :content,
                is_coach_pick = :is_coach_pick
            WHERE id = :id
        ");
        return $stmt->execute([
            'article_id'    => $comment->getArticleId(),
            'content'       => $comment->getContent(),
            'is_coach_pick' => $comment->getIsCoachPick(),
            'id'            => $id
        ]);
    }

    public function deleteComment(int $id): bool {
        $db = Config::getConnexion();
        $stmt = $db->prepare("DELETE FROM comments WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
?>
