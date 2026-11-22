<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../Models/Blog/Article.php';
require_once __DIR__ . '/BlogAuthAdapter.php';

/**
 * Controllers/Blog/ArticleController.php
 * Complete Article CRUD + SQL JOINs (Article JOIN User, Article JOIN Comment)
 * Enforces 3-Role Permissions via BlogAuthAdapter (shared auth compatible):
 *   - Admin: full CRUD on all articles
 *   - Coach: create articles, edit/delete ONLY own articles
 *   - User / Visitor: read-only
 */
class ArticleController
{
    private PDO $db;
    private BlogAuthAdapter $auth;
    private ?bool $usesNormalizedRolesTable = null;
    private static bool $blogSchemaChecked = false;

    public function __construct(?BlogAuthAdapter $auth = null)
    {
        $this->db   = Database::getConnexion();
        $this->auth = $auth ?? new BlogAuthAdapter();
        $this->ensureBlogSchema();
    }

    /**
     * Non-destructively ensures the Blog tables (articles, comments) exist
     * when integrating into an existing hbfitess_db database.
     */
    private function ensureBlogSchema(): void
    {
        if (self::$blogSchemaChecked) {
            return;
        }
        self::$blogSchemaChecked = true;

        try {
            $exists = (bool)$this->db->query("SHOW TABLES LIKE 'articles'")->fetch();
            if (!$exists) {
                $blogSql = dirname(__DIR__, 2) . '/blog.sql';
                if (file_exists($blogSql)) {
                    $this->db->exec((string)file_get_contents($blogSql));
                }
            }
        } catch (PDOException $e) {
            error_log('[HBfitess Blog Schema Bootstrap Error] ' . $e->getMessage());
        }
    }

    /**
     * Supports both the shared normalized User Management schema (users.role_id -> roles.id)
     * and a flat users.role column without modifying either schema.
     */
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
    // READ (Article JOIN User + LEFT JOIN Comment for comment counts)
    // =========================================================================

    /**
     * @return Article[]
     */
    public function getAllArticles(string $search = '', ?int $authorId = null): array
    {
        $roleSelect = $this->usesNormalizedRoles() ? "COALESCE(r.name, 'user')" : 'u.role';
        $roleJoin   = $this->usesNormalizedRoles() ? 'LEFT JOIN roles r ON u.role_id = r.id' : '';
        $roleGroup  = $this->usesNormalizedRoles() ? 'r.name' : 'u.role';

        $sql = "
            SELECT a.id, a.title, a.content, a.published_at, a.author_id, a.recipe_id,
                   u.full_name AS author_name,
                   {$roleSelect} AS author_role,
                   COUNT(c.id) AS comments_count
            FROM articles a
            INNER JOIN users u ON a.author_id = u.id
            {$roleJoin}
            LEFT JOIN comments c ON c.article_id = a.id
            WHERE 1 = 1
        ";
        $params = [];

        $search = trim($search);
        if ($search !== '') {
            $sql .= ' AND (a.title LIKE :q_title OR a.content LIKE :q_content OR u.full_name LIKE :q_author)';
            $params['q_title']   = '%' . $search . '%';
            $params['q_content'] = '%' . $search . '%';
            $params['q_author']  = '%' . $search . '%';
        }
        if ($authorId !== null && $authorId > 0) {
            $sql .= ' AND a.author_id = :author_id';
            $params['author_id'] = $authorId;
        }

        $sql .= " GROUP BY a.id, a.title, a.content, a.published_at, a.author_id, a.recipe_id, u.full_name, {$roleGroup}
                  ORDER BY a.published_at DESC, a.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        return array_map(static fn(array $row): Article => Article::fromArray($row), $rows);
    }

    public function getArticleById(int $id): ?Article
    {
        if ($id <= 0) {
            return null;
        }
        $roleSelect = $this->usesNormalizedRoles() ? "COALESCE(r.name, 'user')" : 'u.role';
        $roleJoin   = $this->usesNormalizedRoles() ? 'LEFT JOIN roles r ON u.role_id = r.id' : '';
        $roleGroup  = $this->usesNormalizedRoles() ? 'r.name' : 'u.role';

        $sql = "
            SELECT a.id, a.title, a.content, a.published_at, a.author_id, a.recipe_id,
                   u.full_name AS author_name,
                   {$roleSelect} AS author_role,
                   COUNT(c.id) AS comments_count
            FROM articles a
            INNER JOIN users u ON a.author_id = u.id
            {$roleJoin}
            LEFT JOIN comments c ON c.article_id = a.id
            WHERE a.id = :id
            GROUP BY a.id, a.title, a.content, a.published_at, a.author_id, a.recipe_id, u.full_name, {$roleGroup}
            LIMIT 1
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? Article::fromArray($row) : null;
    }

    public function getStats(): array
    {
        $totalArticles = (int)$this->db->query('SELECT COUNT(*) FROM articles')->fetchColumn();
        $totalComments = (int)$this->db->query('SELECT COUNT(*) FROM comments')->fetchColumn();

        if ($this->usesNormalizedRoles()) {
            $coachArticles = (int)$this->db->query("
                SELECT COUNT(*)
                FROM articles a
                INNER JOIN users u ON a.author_id = u.id
                INNER JOIN roles r ON u.role_id = r.id
                WHERE r.name = 'coach'
            ")->fetchColumn();
            $adminArticles = (int)$this->db->query("
                SELECT COUNT(*)
                FROM articles a
                INNER JOIN users u ON a.author_id = u.id
                INNER JOIN roles r ON u.role_id = r.id
                WHERE r.name = 'admin'
            ")->fetchColumn();
        } else {
            $coachArticles = (int)$this->db->query("
                SELECT COUNT(*)
                FROM articles a
                INNER JOIN users u ON a.author_id = u.id
                WHERE u.role = 'coach'
            ")->fetchColumn();
            $adminArticles = (int)$this->db->query("
                SELECT COUNT(*)
                FROM articles a
                INNER JOIN users u ON a.author_id = u.id
                WHERE u.role = 'admin'
            ")->fetchColumn();
        }

        return [
            'total_articles' => $totalArticles,
            'total_comments' => $totalComments,
            'coach_articles' => $coachArticles,
            'admin_articles' => $adminArticles,
        ];
    }

    // =========================================================================
    // OWNERSHIP & ROLE CHECKS
    // =========================================================================
    public static function canManageArticle(Article $article, ?array $user): bool
    {
        if (!$user) {
            return false;
        }
        if ($user['role'] === 'admin') {
            return true;
        }
        return $user['role'] === 'coach' && $article->getAuthorId() === (int)$user['id'];
    }

    // =========================================================================
    // VALIDATION
    // =========================================================================
    private function validateArticleInput(array $post): array
    {
        $title    = trim((string)($post['title'] ?? ''));
        $content  = trim((string)($post['content'] ?? ''));
        $recipeIn = trim((string)($post['recipe_id'] ?? ''));

        $errors = [];
        if ($title === '' || mb_strlen($title) < 5 || mb_strlen($title) > 200) {
            $errors[] = 'Article title is required and must be between 5 and 200 characters.';
        } elseif (!preg_match('/[\p{L}]{2,}/u', $title)) {
            $errors[] = 'Article title must contain meaningful text.';
        }

        if ($content === '' || mb_strlen($content) < 20 || mb_strlen($content) > 15000) {
            $errors[] = 'Article content is required and must be between 20 and 15,000 characters.';
        } elseif (!preg_match('/[\p{L}]{5,}/u', $content)) {
            $errors[] = 'Article content must contain meaningful text.';
        }

        if ($recipeIn !== '' && (!ctype_digit($recipeIn) || (int)$recipeIn <= 0)) {
            $errors[] = 'Optional Recipe ID must be a positive integer.';
        }

        return $errors;
    }

    // =========================================================================
    // CREATE (Admin & Coach)
    // =========================================================================
    public function createArticle(array $post, array $currentUser): array
    {
        if (!in_array($currentUser['role'], ['admin', 'coach'], true)) {
            return ['Access denied: only administrators and coaches can publish articles.'];
        }
        if (!$this->auth->verifyCsrfToken($post['csrf_token'] ?? null)) {
            return ['Invalid security token. Please refresh and try again.'];
        }

        $errors = $this->validateArticleInput($post);
        if (!empty($errors)) {
            return $errors;
        }

        $title    = trim((string)$post['title']);
        $content  = trim((string)$post['content']);
        $recipeIn = trim((string)($post['recipe_id'] ?? ''));
        $recipeId = $recipeIn !== '' ? (int)$recipeIn : null;

        try {
            $stmt = $this->db->prepare('
                INSERT INTO articles (title, content, author_id, recipe_id)
                VALUES (:title, :content, :author_id, :recipe_id)
            ');
            $ok = $stmt->execute([
                'title'     => $title,
                'content'   => $content,
                'author_id' => (int)$currentUser['id'],
                'recipe_id' => $recipeId,
            ]);
            return $ok ? [] : ['Failed to publish article.'];
        } catch (PDOException $e) {
            error_log('[ArticleController::createArticle] ' . $e->getMessage());
            return ['A database error occurred while publishing the article.'];
        }
    }

    // =========================================================================
    // UPDATE (Admin: any article | Coach: own articles ONLY)
    // =========================================================================
    public function updateArticle(int $id, array $post, array $currentUser): array
    {
        if (!$this->auth->verifyCsrfToken($post['csrf_token'] ?? null)) {
            return ['Invalid security token. Please try again.'];
        }

        $article = $this->getArticleById($id);
        if (!$article) {
            return ['Article not found.'];
        }
        if (!self::canManageArticle($article, $currentUser)) {
            return ['Access denied: you are not authorized to edit this article.'];
        }

        $errors = $this->validateArticleInput($post);
        if (!empty($errors)) {
            return $errors;
        }

        $title    = trim((string)$post['title']);
        $content  = trim((string)$post['content']);
        $recipeIn = trim((string)($post['recipe_id'] ?? ''));
        $recipeId = $recipeIn !== '' ? (int)$recipeIn : null;

        try {
            $stmt = $this->db->prepare('
                UPDATE articles
                SET title = :title, content = :content, recipe_id = :recipe_id
                WHERE id = :id
            ');
            $ok = $stmt->execute([
                'title'     => $title,
                'content'   => $content,
                'recipe_id' => $recipeId,
                'id'        => $id,
            ]);
            return $ok ? [] : ['Failed to update article.'];
        } catch (PDOException $e) {
            error_log('[ArticleController::updateArticle] ' . $e->getMessage());
            return ['A database error occurred while updating the article.'];
        }
    }

    // =========================================================================
    // DELETE (Admin: any article | Coach: own articles ONLY)
    // Never deletes User accounts; FK ON DELETE CASCADE safely removes comments.
    // =========================================================================
    public function deleteArticle(int $id, array $currentUser, ?string $csrfToken): array
    {
        if (!$this->auth->verifyCsrfToken($csrfToken)) {
            return ['Invalid security token. Please try again.'];
        }

        $article = $this->getArticleById($id);
        if (!$article) {
            return ['Article not found.'];
        }
        if (!self::canManageArticle($article, $currentUser)) {
            return ['Access denied: you are not authorized to delete this article.'];
        }

        try {
            $stmt = $this->db->prepare('DELETE FROM articles WHERE id = :id');
            $ok   = $stmt->execute(['id' => $id]);
            return ($ok && $stmt->rowCount() > 0) ? [] : ['Failed to delete article.'];
        } catch (PDOException $e) {
            error_log('[ArticleController::deleteArticle] ' . $e->getMessage());
            return ['A database error occurred while deleting the article.'];
        }
    }
}
