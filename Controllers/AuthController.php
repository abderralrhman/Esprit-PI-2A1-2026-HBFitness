<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

/**
 * Controllers/AuthController.php
 * Minimal Standalone Authentication & Authorization for HB FITNESS Blog Model
 *
 * INTEGRATION NOTE:
 * Blog controllers depend only on the clean public interface methods below
 * (isAuthenticated, getUserId, getUserRole, getUserName, isAdmin, isCoach,
 * canPublishArticles, requireAuth, requireAdmin, requireArticlePublisher,
 * getCsrfToken, verifyCsrfToken). During future GitHub integration with the
 * team's shared User Management module, this file can be replaced by the
 * shared AuthController without changing Blog business logic.
 */
class AuthController
{
    private PDO $db;
    private const MAX_LOGIN_ATTEMPTS = 5;
    private const LOCKOUT_SECONDS    = 60;

    /**
     * Dedicated local-only test accounts for the 3-button Esprit validation shortcut.
     * No plaintext passwords are stored. If missing in local MySQL, ensureLocalTestAccount()
     * provisions them with a cryptographically random bcrypt hash.
     */
    private const LOCAL_TEST_ACCOUNTS = [
        'admin' => [
            'email'     => 'admin.local@hbfitness.tn',
            'full_name' => 'Admin HB Fitness',
            'role'      => 'admin',
        ],
        'user' => [
            'email'     => 'user.local@hbfitness.tn',
            'full_name' => 'Karim Trabelsi',
            'role'      => 'user',
        ],
        'coach' => [
            'email'     => 'coach.local@hbfitness.tn',
            'full_name' => 'Coach Amine',
            'role'      => 'coach',
        ],
    ];

    public function __construct()
    {
        $this->db = Database::getConnexion();
    }

    // =========================================================================
    // CSRF PROTECTION
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
    // SESSION & ROLE HELPERS (3 ROLES: admin, user, coach)
    // =========================================================================
    public static function isAuthenticated(): bool
    {
        return !empty($_SESSION['user_id']);
    }

    private ?bool $hasStatusColumn = null;

    private function usersTableHasStatusColumn(): bool
    {
        if ($this->hasStatusColumn === null) {
            try {
                $stmt = $this->db->query("SHOW COLUMNS FROM users LIKE 'status'");
                $this->hasStatusColumn = (bool)$stmt->fetch();
            } catch (PDOException $e) {
                $this->hasStatusColumn = false;
            }
        }
        return $this->hasStatusColumn;
    }

    public function getCurrentUser(): ?array
    {
        if (!self::isAuthenticated()) {
            return null;
        }
        $select = $this->usersTableHasStatusColumn()
            ? 'SELECT id, full_name, email, role, status, created_at FROM users WHERE id = :id LIMIT 1'
            : 'SELECT id, full_name, email, role, created_at FROM users WHERE id = :id LIMIT 1';

        $stmt = $this->db->prepare($select);
        $stmt->execute(['id' => (int)$_SESSION['user_id']]);
        $row = $stmt->fetch();
        if (!$row) {
            $this->logout();
            session_start();
            self::flash('Your session is no longer valid. Please log in again.', 'error');
            return null;
        }

        $status = (string)($row['status'] ?? 'active');
        if ($status !== 'active') {
            $this->logout();
            session_start();
            self::flash('Your account is suspended and cannot perform protected actions.', 'error');
            return null;
        }

        $_SESSION['full_name'] = (string)$row['full_name'];
        $_SESSION['role']      = (string)$row['role'];
        return [
            'id'         => (int)$row['id'],
            'full_name'  => (string)$row['full_name'],
            'email'      => (string)$row['email'],
            'role'       => (string)$row['role'],
            'status'     => $status,
            'created_at' => (string)$row['created_at'],
        ];
    }

    public function getUserId(): ?int
    {
        $u = $this->getCurrentUser();
        return $u ? $u['id'] : null;
    }

    public function getUserRole(): ?string
    {
        $u = $this->getCurrentUser();
        return $u ? $u['role'] : null;
    }

    public function getUserName(): ?string
    {
        $u = $this->getCurrentUser();
        return $u ? $u['full_name'] : null;
    }

    public function isAdmin(): bool
    {
        return $this->getUserRole() === 'admin';
    }

    public function isCoach(): bool
    {
        return $this->getUserRole() === 'coach';
    }

    /** True for roles authorized to publish articles (admin and coach) */
    public function canPublishArticles(): bool
    {
        return in_array($this->getUserRole(), ['admin', 'coach'], true);
    }

    // =========================================================================
    // AUTHORIZATION GUARDS
    // =========================================================================
    public function requireAuth(): array
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            self::flash('Please log in to continue.', 'error');
            header('Location: index.php?action=login');
            exit;
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

    public function renderForbidden(string $reason, ?array $currentUser = null): never
    {
        http_response_code(403);
        $title       = '403 Access Denied — HB FITNESS Blog';
        $currentPage = 'denied';
        ob_start();
        ?>
        <div class="container py-5">
            <div class="hb-card text-center mx-auto" style="max-width:560px;padding:3rem 2rem;">
                <div class="mb-3" style="font-size:3rem;color:#E8405E;"><i class="fa-solid fa-shield-halved"></i></div>
                <h1 class="mb-2">403 — Access Denied</h1>
                <p class="text-silver mb-4"><?= htmlspecialchars($reason, ENT_QUOTES, 'UTF-8') ?></p>
                <a href="index.php?action=home" class="btn btn-hb-primary">
                    <i class="fa-solid fa-arrow-left me-2"></i>Back to Blog Home
                </a>
            </div>
        </div>
        <?php
        $content = (string)ob_get_clean();
        require __DIR__ . '/../Views/layout.php';
        exit;
    }

    // =========================================================================
    // LOGIN (ONLY Email + Password + CSRF) & LOGOUT
    // =========================================================================
    public function login(array $post): array
    {
        if (!$this->verifyCsrfToken($post['csrf_token'] ?? null)) {
            return ['Invalid security token. Please refresh the page and try again.'];
        }

        $lockUntil = (int)($_SESSION['login_lock_until'] ?? 0);
        if (time() < $lockUntil) {
            $remaining = $lockUntil - time();
            return ["Too many failed login attempts. Please wait {$remaining} second(s)."];
        }

        $email    = trim((string)($post['email'] ?? ''));
        $password = (string)($post['password'] ?? '');

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
            return ['Please enter a valid email address and password.'];
        }

        $select = $this->usersTableHasStatusColumn()
            ? 'SELECT id, full_name, email, password_hash, role, status FROM users WHERE email = :email LIMIT 1'
            : 'SELECT id, full_name, email, password_hash, role FROM users WHERE email = :email LIMIT 1';

        $stmt = $this->db->prepare($select);
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch();

        if (!$row || !password_verify($password, (string)$row['password_hash'])) {
            $attempts = (int)($_SESSION['login_attempts'] ?? 0) + 1;
            $_SESSION['login_attempts'] = $attempts;
            if ($attempts >= self::MAX_LOGIN_ATTEMPTS) {
                $_SESSION['login_lock_until'] = time() + self::LOCKOUT_SECONDS;
                $_SESSION['login_attempts']   = 0;
                return ['Too many failed login attempts. Login locked for ' . self::LOCKOUT_SECONDS . ' seconds.'];
            }
            $left = self::MAX_LOGIN_ATTEMPTS - $attempts;
            return ["Incorrect email or password. ({$left} attempt(s) remaining)"];
        }

        if (($row['status'] ?? 'active') !== 'active') {
            return ['This account is suspended. Please contact an administrator.'];
        }

        // Prevent session fixation
        session_regenerate_id(true);
        $_SESSION['user_id']       = (int)$row['id'];
        $_SESSION['full_name']     = (string)$row['full_name'];
        $_SESSION['role']          = (string)$row['role'];
        $_SESSION['last_activity'] = time();
        unset($_SESSION['login_attempts'], $_SESSION['login_lock_until']);

        return [];
    }

    // =========================================================================
    // LOCAL DEVELOPMENT ONLY: ONE-CLICK ROLE LOGIN (127.0.0.1 / ::1 + CSRF)
    // Disable by setting HB_LOCAL_QUICK_LOGIN_ENABLED = false in config/database.php
    // =========================================================================
    public static function isLocalQuickLoginEnabled(?string $remoteAddr = null, ?string $httpHost = null): bool
    {
        if (!defined('HB_LOCAL_QUICK_LOGIN_ENABLED') || HB_LOCAL_QUICK_LOGIN_ENABLED !== true) {
            return false;
        }
        $addr = $remoteAddr ?? (string)($_SERVER['REMOTE_ADDR'] ?? '');
        if (!in_array($addr, ['127.0.0.1', '::1'], true)) {
            return false;
        }
        $rawHost = strtolower($httpHost ?? (string)($_SERVER['HTTP_HOST'] ?? ''));
        $host    = preg_replace('/:\d+$/', '', $rawHost) ?? '';
        return in_array($host, ['localhost', '127.0.0.1', '[::1]'], true);
    }

    /**
     * Safely retrieves (or provisions on localhost if absent) one of the 3 whitelisted
     * test accounts from MySQL using PDO prepared statements and password_hash().
     */
    private function ensureLocalTestAccount(string $slot): ?array
    {
        if (!array_key_exists($slot, self::LOCAL_TEST_ACCOUNTS)) {
            return null;
        }
        $spec       = self::LOCAL_TEST_ACCOUNTS[$slot];
        $hasStatus  = $this->usersTableHasStatusColumn();
        $selectSql  = $hasStatus
            ? 'SELECT id, full_name, email, role, status FROM users WHERE email = :email LIMIT 1'
            : 'SELECT id, full_name, email, role FROM users WHERE email = :email LIMIT 1';

        $stmt = $this->db->prepare($selectSql);
        $stmt->execute(['email' => $spec['email']]);
        $row = $stmt->fetch();

        if (!$row) {
            $randomHash = password_hash(bin2hex(random_bytes(24)), PASSWORD_DEFAULT);
            if ($hasStatus) {
                $ins = $this->db->prepare(
                    'INSERT INTO users (full_name, email, password_hash, role, status)
                     VALUES (:full_name, :email, :password_hash, :role, :status)'
                );
                $ins->execute([
                    'full_name'     => $spec['full_name'],
                    'email'         => $spec['email'],
                    'password_hash' => $randomHash,
                    'role'          => $spec['role'],
                    'status'        => 'active',
                ]);
            } else {
                $ins = $this->db->prepare(
                    'INSERT INTO users (full_name, email, password_hash, role)
                     VALUES (:full_name, :email, :password_hash, :role)'
                );
                $ins->execute([
                    'full_name'     => $spec['full_name'],
                    'email'         => $spec['email'],
                    'password_hash' => $randomHash,
                    'role'          => $spec['role'],
                ]);
            }
            $stmt->execute(['email' => $spec['email']]);
            $row = $stmt->fetch();
        }

        return $row ?: null;
    }

    /**
     * Authenticates one of the 3 strictly whitelisted local test accounts.
     * Never trusts POST role directly — always verifies actual DB role & active status.
     */
    public function quickLogin(array $post): array
    {
        if (!self::isLocalQuickLoginEnabled()) {
            return ['Local quick login is disabled outside the localhost development environment.'];
        }
        if (!$this->verifyCsrfToken($post['csrf_token'] ?? null)) {
            return ['Invalid security token. Please refresh the login page and try again.'];
        }

        $slot = strtolower(trim((string)($post['role_slot'] ?? '')));
        if (!array_key_exists($slot, self::LOCAL_TEST_ACCOUNTS)) {
            return ['Invalid quick-login account selection.'];
        }

        try {
            $row = $this->ensureLocalTestAccount($slot);
        } catch (PDOException $e) {
            error_log('[AuthController::quickLogin] ' . $e->getMessage());
            return ['Database error while loading the local test account. Verify MySQL is running and blog.sql is imported.'];
        }

        if (!$row) {
            return ['Local test account could not be loaded from the database.'];
        }

        $expectedRole = self::LOCAL_TEST_ACCOUNTS[$slot]['role'];
        if ((string)$row['role'] !== $expectedRole) {
            return ['Account role mismatch in database. Quick login aborted for security.'];
        }
        if ((string)($row['status'] ?? 'active') !== 'active') {
            return ['This local test account is suspended in the database.'];
        }

        session_regenerate_id(true);
        $_SESSION['user_id']       = (int)$row['id'];
        $_SESSION['full_name']     = (string)$row['full_name'];
        $_SESSION['role']          = (string)$row['role'];
        $_SESSION['last_activity'] = time();
        unset($_SESSION['login_attempts'], $_SESSION['login_lock_until']);

        return [];
    }

    public function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                (bool)$params['secure'],
                (bool)$params['httponly']
            );
        }
        session_destroy();
    }
}
