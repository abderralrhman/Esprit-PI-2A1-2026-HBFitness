<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../Models/Blog/Comment.php';
require_once __DIR__ . '/BlogAuthAdapter.php';

/**
 * Controllers/Blog/CommentController.php
 * Complete Comment CRUD + SQL JOINs (Comment JOIN User, Article JOIN Comment JOIN User)
 * Enforces 3-Role Permissions via BlogAuthAdapter (shared auth compatible):
 *   - Admin: moderate (edit/delete) all comments
 *   - User & Coach: create comments, edit/delete ONLY their own comments
 */
class CommentController
{
    private PDO $db;
    private BlogAuthAdapter $auth;
    private ?bool $usesNormalizedRolesTable = null;

    public function __construct(?BlogAuthAdapter $auth = null)
    {
        $this->db   = Database::getConnexion();
        $this->auth = $auth ?? new BlogAuthAdapter();
    }

    private function usesNormalizedRoles(): bool
    {
        if ($this->usesNormalizedRolesTable === null) {
            try {
                $stmt = $this->db->query("SHOW COLUMNS FROM users LIKE 'role_id'");
                $this->usesNormalizedRolesTable = (bool)$stmt->fetch();
            } catch (PDOException $e) {
                $this->usesNormalizedRolesTable = false;
            }
        }
        return $this->usesNormalizedRolesTable;
    }

    // =========================================================================
    // READ (Comment JOIN User + Article JOIN Comment JOIN User)
    // =========================================================================

    /**
     * Comments belonging to one article (Article JOIN Comment JOIN User)
     * @return Comment[]
     */
    public function getCommentsByArticle(int $articleId): array
    {
        if ($articleId <= 0) {
            return [];
        }
        $roleSelect = $this->usesNormalizedRoles() ? "COALESCE(r.name, 'user')" : 'u.role';
        $roleJoin   = $this->usesNormalizedRoles() ? 'LEFT JOIN roles r ON u.role_id = r.id' : '';

        $sql = "
            SELECT c.id, c.content, c.created_at, c.article_id, c.user_id,
                   u.full_name AS author_name,
                   {$roleSelect} AS author_role,
                   a.title AS article_title
            FROM comments c
            INNER JOIN users u ON c.user_id = u.id
            {$roleJoin}
            INNER JOIN articles a ON c.article_id = a.id
            WHERE c.article_id = :article_id
            ORDER BY c.created_at ASC, c.id ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['article_id' => $articleId]);
        return array_map(static fn(array $row): Comment => Comment::fromArray($row), $stmt->fetchAll());
    }

    /**
     * All comments across all articles for Admin Backoffice moderation (Article JOIN Comment JOIN User)
     * @return Comment[]
     */
    public function getAllComments(string $search = '', ?int $articleId = null): array
    {
        $roleSelect = $this->usesNormalizedRoles() ? "COALESCE(r.name, 'user')" : 'u.role';
        $roleJoin   = $this->usesNormalizedRoles() ? 'LEFT JOIN roles r ON u.role_id = r.id' : '';

        $sql = "
            SELECT c.id, c.content, c.created_at, c.article_id, c.user_id,
                   u.full_name AS author_name,
                   {$roleSelect} AS author_role,
                   a.title AS article_title
            FROM comments c
            INNER JOIN articles a ON c.article_id = a.id
            INNER JOIN users u ON c.user_id = u.id
            {$roleJoin}
            WHERE 1 = 1
        ";
        $params = [];

        $search = trim($search);
        if ($search !== '') {
            $sql .= ' AND (c.content LIKE :q_content OR u.full_name LIKE :q_author OR a.title LIKE :q_article)';
            $params['q_content'] = '%' . $search . '%';
            $params['q_author']  = '%' . $search . '%';
            $params['q_article'] = '%' . $search . '%';
        }
        if ($articleId !== null && $articleId > 0) {
            $sql .= ' AND c.article_id = :article_id';
            $params['article_id'] = $articleId;
        }

        $sql .= ' ORDER BY c.created_at DESC, c.id DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return array_map(static fn(array $row): Comment => Comment::fromArray($row), $stmt->fetchAll());
    }

    /**
     * Comments authored by the logged-in user (Frontoffice -> My Comments)
     * @return Comment[]
     */
    public function getCommentsByUser(int $userId): array
    {
        if ($userId <= 0) {
            return [];
        }
        $roleSelect = $this->usesNormalizedRoles() ? "COALESCE(r.name, 'user')" : 'u.role';
        $roleJoin   = $this->usesNormalizedRoles() ? 'LEFT JOIN roles r ON u.role_id = r.id' : '';

        $sql = "
            SELECT c.id, c.content, c.created_at, c.article_id, c.user_id,
                   u.full_name AS author_name,
                   {$roleSelect} AS author_role,
                   a.title AS article_title
            FROM comments c
            INNER JOIN articles a ON c.article_id = a.id
            INNER JOIN users u ON c.user_id = u.id
            {$roleJoin}
            WHERE c.user_id = :user_id
            ORDER BY c.created_at DESC, c.id DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return array_map(static fn(array $row): Comment => Comment::fromArray($row), $stmt->fetchAll());
    }

    public function getCommentById(int $id): ?Comment
    {
        if ($id <= 0) {
            return null;
        }
        $roleSelect = $this->usesNormalizedRoles() ? "COALESCE(r.name, 'user')" : 'u.role';
        $roleJoin   = $this->usesNormalizedRoles() ? 'LEFT JOIN roles r ON u.role_id = r.id' : '';

        $sql = "
            SELECT c.id, c.content, c.created_at, c.article_id, c.user_id,
                   u.full_name AS author_name,
                   {$roleSelect} AS author_role,
                   a.title AS article_title
            FROM comments c
            INNER JOIN articles a ON c.article_id = a.id
            INNER JOIN users u ON c.user_id = u.id
            {$roleJoin}
            WHERE c.id = :id
            LIMIT 1
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? Comment::fromArray($row) : null;
    }

    // =========================================================================
    // OWNERSHIP CHECK (Admin: any | User & Coach: own comments ONLY)
    // =========================================================================
    public static function canManageComment(Comment $comment, ?array $user): bool
    {
        if (!$user) {
            return false;
        }
        if ($user['role'] === 'admin') {
            return true;
        }
        return $comment->getUserId() === (int)$user['id'];
    }

    private function articleExists(int $articleId): bool
    {
        if ($articleId <= 0) {
            return false;
        }
        $stmt = $this->db->prepare('SELECT id FROM articles WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $articleId]);
        return (bool)$stmt->fetch();
    }

    private function validateContent(string $content): array
    {
        $content = trim($content);
        $errors  = [];
        if ($content === '' || mb_strlen($content) < 3 || mb_strlen($content) > 2000) {
            $errors[] = 'Comment content is required and must be between 3 and 2,000 characters.';
        } elseif (!preg_match('/[\p{L}0-9]{2,}/u', $content)) {
            $errors[] = 'Comment must contain meaningful text.';
        }
        return $errors;
    }

    // =========================================================================
    // CREATE (Any authenticated user: admin, user, coach)
    // =========================================================================
    public function createComment(array $post, array $currentUser): array
    {
        if (!$this->auth->verifyCsrfToken($post['csrf_token'] ?? null)) {
            return ['Invalid security token. Please try again.'];
        }

        $articleId = (int)($post['article_id'] ?? 0);
        $content   = trim((string)($post['content'] ?? ''));

        $errors = [];
        if (!$this->articleExists($articleId)) {
            $errors[] = 'The target article does not exist.';
        }
        $errors = array_merge($errors, $this->validateContent($content));

        if (!empty($errors)) {
            return $errors;
        }

        try {
            $stmt = $this->db->prepare('
                INSERT INTO comments (content, article_id, user_id)
                VALUES (:content, :article_id, :user_id)
            ');
            $ok = $stmt->execute([
                'content'    => $content,
                'article_id' => $articleId,
                'user_id'    => (int)$currentUser['id'],
            ]);
            return $ok ? [] : ['Failed to save your comment.'];
        } catch (PDOException $e) {
            error_log('[CommentController::createComment] ' . $e->getMessage());
            return ['A database error occurred while posting your comment.'];
        }
    }

    // =========================================================================
    // UPDATE (Owner or Admin)
    // =========================================================================
    public function updateComment(int $id, array $post, array $currentUser): array
    {
        if (!$this->auth->verifyCsrfToken($post['csrf_token'] ?? null)) {
            return ['Invalid security token. Please try again.'];
        }

        $comment = $this->getCommentById($id);
        if (!$comment) {
            return ['Comment not found.'];
        }
        if (!self::canManageComment($comment, $currentUser)) {
            return ['Access denied: you can only edit your own comments.'];
        }

        $content = trim((string)($post['content'] ?? ''));
        $errors  = $this->validateContent($content);
        if (!empty($errors)) {
            return $errors;
        }

        try {
            $stmt = $this->db->prepare('UPDATE comments SET content = :content WHERE id = :id');
            $ok   = $stmt->execute(['content' => $content, 'id' => $id]);
            return $ok ? [] : ['Failed to update comment.'];
        } catch (PDOException $e) {
            error_log('[CommentController::updateComment] ' . $e->getMessage());
            return ['A database error occurred while updating the comment.'];
        }
    }

    // =========================================================================
    // DELETE (Owner or Admin)
    // =========================================================================
    public function deleteComment(int $id, array $currentUser, ?string $csrfToken): array
    {
        if (!$this->auth->verifyCsrfToken($csrfToken)) {
            return ['Invalid security token. Please try again.'];
        }

        $comment = $this->getCommentById($id);
        if (!$comment) {
            return ['Comment not found.'];
        }
        if (!self::canManageComment($comment, $currentUser)) {
            return ['Access denied: you can only delete your own comments.'];
        }

        try {
            $stmt = $this->db->prepare('DELETE FROM comments WHERE id = :id');
            $ok   = $stmt->execute(['id' => $id]);
            return ($ok && $stmt->rowCount() > 0) ? [] : ['Failed to delete comment.'];
        } catch (PDOException $e) {
            error_log('[CommentController::deleteComment] ' . $e->getMessage());
            return ['A database error occurred while deleting the comment.'];
        }
    }
}
