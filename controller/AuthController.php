<?php
/**
 * AuthController — authentication & session management (login, signup, logout, guards, flash messages)
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/UserController.php';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

class AuthController {
    /** Inactivity timeout in seconds (30 minutes) */
    const SESSION_TIMEOUT = 1800;

    /** Verifies credentials (password_verify) and opens a fresh session */
    public static function login(string $email, string $password): ?array {
        $userC = new UserController();
        $user = $userC->authenticate($email, $password);
        if (!$user) {
            return null;
        }
        self::openSession($user);
        return $_SESSION['user'];
    }

    /** Creates a normal 'user' account (password hashed in UserController::addUser) and logs it in */
    public static function register(string $fullName, string $email, string $password, string $phone = ''): ?array {
        $userC = new UserController();
        if ($userC->emailExists($email)) {
            return null;
        }
        $colors = ['#dc2626', '#171717', '#991b1b', '#ef4444', '#262626'];
        $userC->addUser(new User(null, $fullName, $email, $password, 'user', $phone !== '' ? $phone : null, $colors[array_rand($colors)]));
        $created = $userC->getUserByEmail($email);
        if (!$created) {
            return null;
        }
        self::openSession($created);
        return $_SESSION['user'];
    }

    public static function logout(): void {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
        session_start();
        session_regenerate_id(true);
    }

    /** Returns the logged-in user (refreshed from DB so role changes apply immediately) or null */
    public static function currentUser(): ?array {
        if (empty($_SESSION['user']['id'])) {
            return null;
        }
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > self::SESSION_TIMEOUT) {
            self::logout();
            self::flash('Your session expired after 30 minutes of inactivity. Please log in again.', 'error');
            return null;
        }
        $fresh = (new UserController())->getUserById((int)$_SESSION['user']['id']);
        if (!$fresh) {
            self::logout();
            self::flash('Your account no longer exists.', 'error');
            return null;
        }
        $_SESSION['last_activity'] = time();
        self::storeUser($fresh);
        return $_SESSION['user'];
    }

    public static function isLoggedIn(): bool {
        return !empty($_SESSION['user']['id']);
    }

    public static function isAdmin(): bool {
        return self::isLoggedIn() && ($_SESSION['user']['role'] ?? '') === 'admin';
    }

    public static function isCoach(): bool {
        return self::isLoggedIn() && ($_SESSION['user']['role'] ?? '') === 'coach';
    }

    /** True for admins and coaches (editorial / Coach Pick privileges) */
    public static function isStaff(): bool {
        return self::isAdmin() || self::isCoach();
    }

    /** Guard: any authenticated user */
    public static function requireLogin(): array {
        $user = self::currentUser();
        if (!$user) {
            if (!isset($_SESSION['flash'])) {
                self::flash('Please log in to continue.', 'error');
            }
            self::redirect('/view/auth/login.php');
        }
        return $user;
    }

    /** Guard: administrators only */
    public static function requireAdmin(): array {
        $user = self::requireLogin();
        if ($user['role'] !== 'admin') {
            self::flash('Access denied: administrator privileges are required.', 'error');
            self::redirect('/index.php?page=user');
        }
        return $user;
    }

    /** Used on login/signup pages: logged-in users go straight to their dashboard */
    public static function redirectIfLoggedIn(): void {
        $user = self::currentUser();
        if ($user) {
            self::redirect(self::homeFor($user));
        }
    }

    public static function homeFor(array $user): string {
        $role = $user['role'] ?? 'user';
        if ($role === 'admin') return '/index.php?page=admin';
        if ($role === 'coach') return '/index.php?page=coach';
        return '/index.php?page=user';
    }

    public static function flash(string $message, string $type = 'success'): void {
        $_SESSION['flash'] = $message;
        $_SESSION['flash_type'] = $type;
    }

    /** @return array{0: ?string, 1: string} */
    public static function pullFlash(): array {
        $msg  = $_SESSION['flash'] ?? null;
        $type = $_SESSION['flash_type'] ?? 'success';
        unset($_SESSION['flash'], $_SESSION['flash_type']);
        return [$msg, $type];
    }

    public static function redirect(string $path): void {
        header('Location: ' . BASE_URL . $path);
        exit;
    }

    private static function openSession(array $user): void {
        session_regenerate_id(true); // prevent session fixation
        self::storeUser($user);
        $_SESSION['login_time'] = date('Y-m-d H:i:s');
        $_SESSION['last_activity'] = time();
        unset($_SESSION['login_attempts'], $_SESSION['login_lock_until']);
    }

    /** Never keep the password hash in the session */
    private static function storeUser(array $u): void {
        $_SESSION['user'] = [
            'id'           => (int)$u['id'],
            'full_name'    => $u['full_name'],
            'email'        => $u['email'],
            'role'         => $u['role'],
            'phone'        => $u['phone'] ?? null,
            'avatar_color' => $u['avatar_color'] ?? '#f97316',
            'created_at'   => $u['created_at'] ?? null,
        ];
    }
}
?>
