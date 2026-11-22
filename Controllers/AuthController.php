<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../Models/User.php';

/**
 * Controllers/AuthController.php
 * Authentication, Sessions, CSRF, Role Authorization & Security (ESPRIT Workshop)
 */
class AuthController
{
    private PDO $db;
    private const MAX_LOGIN_ATTEMPTS = 5;
    private const LOCKOUT_SECONDS    = 60;

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
    // FLASH MESSAGES
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
    // SESSION STATE & AUTHORIZATION
    // =========================================================================
    public static function isAuthenticated(): bool
    {
        return !empty($_SESSION['user_id']);
    }

    public static function hasRole(string $role): bool
    {
        return self::isAuthenticated() && (($_SESSION['role'] ?? '') === $role);
    }

    /**
     * Fetches the authenticated user fresh from MySQL (with JOIN on roles & statuses)
     * so role changes or account suspension take effect immediately.
     */
    public function getCurrentUser(): ?User
    {
        if (!self::isAuthenticated()) {
            return null;
        }
        $user = $this->findById((int)$_SESSION['user_id']);
        if (!$user || $user->getStatusName() !== 'active') {
            $wasSuspended = $user && $user->getStatusName() === 'suspended';
            $this->logout();
            session_start();
            self::flash(
                $wasSuspended
                    ? 'Your account has been suspended. Please contact an administrator.'
                    : 'Your session is no longer valid. Please log in again.',
                'error'
            );
            return null;
        }

        $_SESSION['full_name'] = $user->getFullName();
        $_SESSION['role']      = $user->getRoleName();
        $_SESSION['status']    = $user->getStatusName();
        return $user;
    }

    public function requireAuth(): User
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            if (!isset($_SESSION['flash_message'])) {
                self::flash('Please log in to access this page.', 'error');
            }
            header('Location: index.php?action=login');
            exit;
        }
        return $user;
    }

    public function requireRole(string $requiredRole): User
    {
        $user = $this->requireAuth();
        if ($user->getRoleName() !== $requiredRole) {
            http_response_code(403);
            $title = '403 Access Denied — HB FITNESS';
            $currentPage = 'denied';
            $currentUser = $user;
            ob_start();
            ?>
            <div class="container py-5">
                <div class="hb-card text-center mx-auto" style="max-width:560px;padding:3rem 2rem;">
                    <div class="mb-3" style="font-size:3rem;color:#E8405E;"><i class="fa-solid fa-shield-halved"></i></div>
                    <h1 class="mb-2">403 — Access Denied</h1>
                    <p class="text-silver mb-4">You do not have the required administrator permissions to access this backoffice resource.</p>
                    <a href="index.php?action=dashboard" class="btn btn-hb-primary">
                        <i class="fa-solid fa-arrow-left me-2"></i>Return to Frontoffice Dashboard
                    </a>
                </div>
            </div>
            <?php
            $content = (string)ob_get_clean();
            require __DIR__ . '/../Views/layout.php';
            exit;
        }
        return $user;
    }

    // =========================================================================
    // DATA ACCESS (PREPARED STATEMENTS + SQL JOIN)
    // =========================================================================
    public function findById(int $id): ?User
    {
        $sql = '
            SELECT u.*, r.name AS role_name, s.name AS status_name
            FROM users u
            INNER JOIN roles r ON u.role_id = r.id
            INNER JOIN user_statuses s ON u.status_id = s.id
            WHERE u.id = :id
            LIMIT 1
        ';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? User::fromArray($row) : null;
    }

    private function findByEmail(string $email): ?User
    {
        $sql = '
            SELECT u.*, r.name AS role_name, s.name AS status_name
            FROM users u
            INNER JOIN roles r ON u.role_id = r.id
            INNER JOIN user_statuses s ON u.status_id = s.id
            WHERE u.email = :email
            LIMIT 1
        ';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch();
        return $row ? User::fromArray($row) : null;
    }

    private function emailExists(string $email): bool
    {
        $stmt = $this->db->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        return (bool)$stmt->fetch();
    }

    private function getRoleIdByName(string $name): int
    {
        $stmt = $this->db->prepare('SELECT id FROM roles WHERE name = :name LIMIT 1');
        $stmt->execute(['name' => $name]);
        $id = $stmt->fetchColumn();
        return $id ? (int)$id : 2;
    }

    private function getStatusIdByName(string $name): int
    {
        $stmt = $this->db->prepare('SELECT id FROM user_statuses WHERE name = :name LIMIT 1');
        $stmt->execute(['name' => $name]);
        $id = $stmt->fetchColumn();
        return $id ? (int)$id : 1;
    }

    // =========================================================================
    // REGISTER (Public -> role forced to 'user', status forced to 'active')
    // =========================================================================
    public function register(array $post): array
    {
        $errors = [];

        if (!$this->verifyCsrfToken($post['csrf_token'] ?? null)) {
            return ['Invalid security token. Please refresh the page and try again.'];
        }

        $fullName        = trim((string)($post['full_name'] ?? ''));
        $email           = trim((string)($post['email'] ?? ''));
        $phone           = trim((string)($post['phone'] ?? ''));
        $password        = (string)($post['password'] ?? '');
        $passwordConfirm = (string)($post['password_confirm'] ?? '');

        if ($fullName === '' || mb_strlen($fullName) < 3 || mb_strlen($fullName) > 120 || !preg_match('/^[\p{L}\s\-\'\.]+$/u', $fullName)) {
            $errors[] = 'Full name must contain only letters and spaces (3 to 120 characters).';
        }
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        }
        if ($phone !== '' && !preg_match('/^\+?[0-9\s\-]{8,20}$/', $phone)) {
            $errors[] = 'Phone number must contain 8 to 20 digits.';
        }
        if (mb_strlen($password) < 8 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must be at least 8 characters and include both letters and digits.';
        }
        if ($password !== $passwordConfirm) {
            $errors[] = 'Password confirmation does not match.';
        }

        if (!empty($errors)) {
            return $errors;
        }

        if ($this->emailExists($email)) {
            return ['An account with this email address already exists.'];
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $userRoleId     = $this->getRoleIdByName('user');
        $activeStatusId = $this->getStatusIdByName('active');

        $stmt = $this->db->prepare('
            INSERT INTO users (full_name, email, password_hash, phone, role_id, status_id)
            VALUES (:full_name, :email, :password_hash, :phone, :role_id, :status_id)
        ');
        $ok = $stmt->execute([
            'full_name'     => $fullName,
            'email'         => $email,
            'password_hash' => $hashedPassword,
            'phone'         => $phone !== '' ? $phone : null,
            'role_id'       => $userRoleId,
            'status_id'     => $activeStatusId,
        ]);

        if (!$ok) {
            return ['An error occurred while creating your account. Please try again.'];
        }

        return [];
    }

    // =========================================================================
    // LOGIN (ONLY Email + Password + CSRF, brute-force protection, status check)
    // =========================================================================
    public function login(array $post): array
    {
        if (!$this->verifyCsrfToken($post['csrf_token'] ?? null)) {
            return ['Invalid security token. Please try again.'];
        }

        $lockUntil = (int)($_SESSION['login_lock_until'] ?? 0);
        if (time() < $lockUntil) {
            $remaining = $lockUntil - time();
            return ["Too many failed login attempts. Please wait {$remaining} second(s) and try again."];
        }

        $email    = trim((string)($post['email'] ?? ''));
        $password = (string)($post['password'] ?? '');

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
            return ['Please enter a valid email address and password.'];
        }

        $user = $this->findByEmail($email);

        if (!$user || !password_verify($password, $user->getPasswordHash())) {
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

        if ($user->getStatusName() === 'suspended') {
            return ['This account is currently suspended. Please contact an administrator.'];
        }

        // Prevent session fixation
        session_regenerate_id(true);
        $_SESSION['user_id']       = $user->getId();
        $_SESSION['full_name']     = $user->getFullName();
        $_SESSION['role']          = $user->getRoleName();
        $_SESSION['status']        = $user->getStatusName();
        $_SESSION['last_activity'] = time();
        $_SESSION['login_time']    = date('Y-m-d H:i:s');
        unset($_SESSION['login_attempts'], $_SESSION['login_lock_until']);

        return [];
    }

    // =========================================================================
    // LOGOUT
    // =========================================================================
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

    // =========================================================================
    // SAFE LOCAL DEMO ACCOUNT BOOTSTRAP (Localhost only, when no admin exists)
    // =========================================================================
    public function hasAnyAdmin(): bool
    {
        $stmt = $this->db->query("
            SELECT COUNT(*)
            FROM users u
            INNER JOIN roles r ON u.role_id = r.id
            WHERE r.name = 'admin'
        ");
        return (int)$stmt->fetchColumn() > 0;
    }

    public static function isLocalhost(): bool
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        return in_array($ip, ['127.0.0.1', '::1'], true);
    }

    /**
     * Creates initial local Admin, Coach, and Athlete accounts using a user-supplied
     * password hashed via password_hash() — never stores plaintext passwords in repo.
     */
    public function setupLocalDemoAccounts(array $post): array
    {
        if (!self::isLocalhost()) {
            return ['Local setup is only allowed from localhost.'];
        }
        if ($this->hasAnyAdmin()) {
            return ['An administrator account already exists.'];
        }
        if (!$this->verifyCsrfToken($post['csrf_token'] ?? null)) {
            return ['Invalid security token. Please try again.'];
        }

        $adminName  = trim((string)($post['admin_name'] ?? 'HB Fitness Admin'));
        $adminEmail = trim((string)($post['admin_email'] ?? 'admin@hbfitess.local'));
        $password   = (string)($post['setup_password'] ?? '');
        $confirm    = (string)($post['setup_password_confirm'] ?? '');
        $seedOthers = !empty($post['seed_sample_users']);

        $errors = [];
        if (mb_strlen($adminName) < 3 || !preg_match('/^[\p{L}\s\-\'\.]+$/u', $adminName)) {
            $errors[] = 'Administrator name must contain letters and spaces (min 3 characters).';
        }
        if (!filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please provide a valid administrator email.';
        }
        if (mb_strlen($password) < 8 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must be at least 8 characters and include letters and digits.';
        }
        if ($password !== $confirm) {
            $errors[] = 'Password confirmation does not match.';
        }
        if ($errors) {
            return $errors;
        }

        $hash      = password_hash($password, PASSWORD_DEFAULT);
        $activeId  = $this->getStatusIdByName('active');
        $adminRole = $this->getRoleIdByName('admin');
        $coachRole = $this->getRoleIdByName('coach');
        $userRole  = $this->getRoleIdByName('user');

        $accounts = [
            [$adminName, $adminEmail, '+216 22 100 200', $adminRole],
        ];
        if ($seedOthers) {
            $accounts[] = ['Coach Amira Mansouri', 'coach@hbfitess.local', '+216 55 300 400', $coachRole];
            $accounts[] = ['Karim Trabelsi', 'user@hbfitess.local', '+216 98 765 432', $userRole];
        }

        $stmt = $this->db->prepare('
            INSERT INTO users (full_name, email, password_hash, phone, role_id, status_id)
            VALUES (:full_name, :email, :password_hash, :phone, :role_id, :status_id)
        ');

        foreach ($accounts as [$name, $email, $phone, $roleId]) {
            if (!$this->emailExists($email)) {
                $stmt->execute([
                    'full_name'     => $name,
                    'email'         => $email,
                    'password_hash' => $hash,
                    'phone'         => $phone,
                    'role_id'       => $roleId,
                    'status_id'     => $activeId,
                ]);
            }
        }

        return [];
    }
}
