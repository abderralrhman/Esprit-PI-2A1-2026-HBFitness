<?php
declare(strict_types=1);

/**
 * Controllers/Blog/BlogAuthAdapter.php
 * Shared Authentication Integration Adapter for HB FITNESS Blog Module
 *
 * PURPOSE:
 * Decouples the Blog module (ArticleController & CommentController) from the
 * team's User Management authentication implementation.
 *
 * SECURITY CONTRACT:
 * - Reads ONLY from an existing PHP session created by the shared AuthController
 *   (or delegates to a loaded shared AuthController if present).
 * - NEVER invents users, NEVER provisions test accounts, NEVER bypasses auth.
 * - Protected Blog actions deny access (HTTP 401/403) when unauthenticated.
 *
 * INTEGRATION INTO Esprit-PI-2A1-2026-HBFitness:
 * Ensure the shared AuthController sets $_SESSION['user_id'], $_SESSION['role']
 * (one of 'admin', 'user', 'coach'), and $_SESSION['full_name'] — or adapt the
 * getter methods below to match your team's exact session keys.
 */
class BlogAuthAdapter
{
    private const ALLOWED_ROLES = ['admin', 'user', 'coach'];

    // =========================================================================
    // CSRF PROTECTION (Reuses shared $_SESSION['csrf_token'] when present)
    // =========================================================================
    public function getCsrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return (string)$_SESSION['csrf_token'];
    }

    public function verifyCsrfToken(?string $token): bool
    {
        if (!$token || empty($_SESSION['csrf_token'])) {
            return false;
        }
        return hash_equals((string)$_SESSION['csrf_token'], $token);
    }

    // =========================================================================
    // FLASH NOTIFICATIONS
    // =========================================================================
    public static function flash(string $message, string $type = 'success'): void
    {
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type']    = $type;
    }

    /** @return array{0: ?string, 1: string} */
    public static function pullFlash(): array
    {
        $msg  = isset($_SESSION['flash_message']) ? (string)$_SESSION['flash_message'] : null;
        $type = isset($_SESSION['flash_type']) ? (string)$_SESSION['flash_type'] : 'success';
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);
        return [$msg, $type];
    }

    // =========================================================================
    // SHARED AUTHENTICATION INTERFACE
    // =========================================================================

    /**
     * Returns true ONLY when a valid authenticated user session exists.
     */
    public function isAuthenticated(): bool
    {
        return $this->getUserId() !== null && $this->getUserRole() !== null;
    }

    /**
     * Current authenticated user ID from the shared session, or null.
     */
    public function getUserId(): ?int
    {
        $id = $_SESSION['user_id'] ?? ($_SESSION['user']['id'] ?? null);
        if ($id === null || !is_numeric($id) || (int)$id <= 0) {
            return null;
        }
        return (int)$id;
    }

    /**
     * Current authenticated user role ('admin', 'user', 'coach') from the shared session, or null.
     */
    public function getUserRole(): ?string
    {
        $raw = $_SESSION['role']
            ?? $_SESSION['user_role']
            ?? ($_SESSION['user']['role'] ?? null);

        if (!is_string($raw)) {
            return null;
        }
        $normalized = strtolower(trim($raw));
        return in_array($normalized, self::ALLOWED_ROLES, true) ? $normalized : null;
    }

    /**
     * Current authenticated user display name from the shared session, or null.
     */
    public function getUserName(): ?string
    {
        $name = $_SESSION['full_name']
            ?? $_SESSION['user_name']
            ?? ($_SESSION['user']['full_name'] ?? null);

        if (!is_string($name) || trim($name) === '') {
            return null;
        }
        return trim($name);
    }

    /**
     * Returns normalized current user array ['id', 'full_name', 'role'] or null if unauthenticated.
     */
    public function getCurrentUser(): ?array
    {
        $id   = $this->getUserId();
        $role = $this->getUserRole();
        if ($id === null || $role === null) {
            return null;
        }

        // Respect suspended status if the shared User Management session stores it
        $status = strtolower((string)($_SESSION['status'] ?? $_SESSION['user']['status'] ?? 'active'));
        if ($status !== 'active') {
            return null;
        }

        return [
            'id'        => $id,
            'full_name' => $this->getUserName() ?? ('User #' . $id),
            'role'      => $role,
            'status'    => $status,
        ];
    }

    public function isAdmin(): bool
    {
        return $this->getUserRole() === 'admin';
    }

    public function isCoach(): bool
    {
        return $this->getUserRole() === 'coach';
    }

    public function canPublishArticles(): bool
    {
        return in_array($this->getUserRole(), ['admin', 'coach'], true);
    }

    // =========================================================================
    // AUTHORIZATION GUARDS (Never bypass security when unauthenticated)
    // =========================================================================
    public function requireAuth(): array
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            $this->renderAuthRequired(
                'Authentication is required to perform this Blog action. '
                . 'Please sign in to your HB FITNESS account to continue.'
            );
        }
        return $user;
    }

    public function requireAdmin(): array
    {
        $user = $this->requireAuth();
        if ($user['role'] !== 'admin') {
            $this->renderForbidden('Administrator permissions are required to access the Blog Backoffice.', $user);
        }
        return $user;
    }

    public function requireArticlePublisher(): array
    {
        $user = $this->requireAuth();
        if (!in_array($user['role'], ['admin', 'coach'], true)) {
            $this->renderForbidden('Only Administrators and Coaches are authorized to publish or manage articles.', $user);
        }
        return $user;
    }

    public function renderAuthRequired(string $reason): never
    {
        http_response_code(401);
        $currentUser = null;
        $title       = '401 Authentication Required — HB FITNESS Blog';
        $currentPage = 'auth_required';
        ob_start();
        ?>
        <div class="container py-5">
            <div class="hb-card text-center mx-auto" style="max-width:580px;padding:3rem 2rem;">
                <div class="mb-3" style="font-size:3rem;color:#E8405E;"><i class="fa-solid fa-lock"></i></div>
                <h1 class="mb-2">401 — Authentication Required</h1>
                <p class="text-silver mb-4"><?= htmlspecialchars($reason, ENT_QUOTES, 'UTF-8') ?></p>
                <div class="d-flex justify-content-center gap-2 flex-wrap">
                    <a href="index.php?action=login" class="btn btn-hb-primary">
                        <i class="fa-solid fa-right-to-bracket me-2"></i>Sign In
                    </a>
                    <a href="index.php?action=articles" class="btn btn-hb-outline">
                        <i class="fa-solid fa-newspaper me-2"></i>Browse Public Articles
                    </a>
                    <a href="index.php?action=blog" class="btn btn-hb-outline">
                        <i class="fa-solid fa-house me-2"></i>Blog Home
                    </a>
                </div>
            </div>
        </div>
        <?php
        $content = (string)ob_get_clean();
        require __DIR__ . '/../../Views/layout.php';
        exit;
    }

    public function renderForbidden(string $reason, ?array $currentUser = null): never
    {
        http_response_code(403);
        $title       = '403 Access Denied — HB FITNESS Blog';
        $currentPage = 'denied';
        ob_start();
        ?>
        <div class="container py-5">
            <div class="hb-card text-center mx-auto" style="max-width:580px;padding:3rem 2rem;">
                <div class="mb-3" style="font-size:3rem;color:#E8405E;"><i class="fa-solid fa-shield-halved"></i></div>
                <h1 class="mb-2">403 — Access Denied</h1>
                <p class="text-silver mb-4"><?= htmlspecialchars($reason, ENT_QUOTES, 'UTF-8') ?></p>
                <a href="index.php?action=blog" class="btn btn-hb-primary">
                    <i class="fa-solid fa-arrow-left me-2"></i>Back to Blog Home
                </a>
            </div>
        </div>
        <?php
        $content = (string)ob_get_clean();
        require __DIR__ . '/../../Views/layout.php';
        exit;
    }
}
