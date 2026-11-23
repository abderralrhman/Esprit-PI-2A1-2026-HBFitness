<?php
declare(strict_types=1);

/**
 * index.php — Main Front Controller & Router (ESPRIT MVC + Security Workshop)
 * Local URL: http://localhost/HBfitess/
 */

// 1. Secure session configuration (MUST precede session_start)
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || ((int)($_SERVER['SERVER_PORT'] ?? 80) === 443);

ini_set('session.use_strict_mode', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
if ($isHttps) {
    ini_set('session.cookie_secure', '1');
}
session_start();

// 2. Session expiration after 30 minutes of inactivity (1800 seconds)
$sessionLifetime = 1800;
if (isset($_SESSION['last_activity']) && (time() - (int)$_SESSION['last_activity'] > $sessionLifetime)) {
    $_SESSION = [];
    session_destroy();
    session_start();
    $_SESSION['flash_message'] = 'Your session expired after 30 minutes of inactivity. Please log in again.';
    $_SESSION['flash_type']    = 'error';
}
$_SESSION['last_activity'] = time();

// 3. HTTP security headers
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header(
    "Content-Security-Policy: default-src 'self'; "
    . "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.googleapis.com; "
    . "font-src 'self' https://cdnjs.cloudflare.com https://fonts.gstatic.com; "
    . "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; "
    . "img-src 'self' data:;"
);

// 4. Load Controllers
require_once __DIR__ . '/Controllers/AuthController.php';
require_once __DIR__ . '/Controllers/UserController.php';
require_once __DIR__ . '/Controllers/RoleController.php';

$authController = new AuthController();
$userController = new UserController($authController);
$roleController = new RoleController($authController);

$action = (string)($_GET['action'] ?? 'home');
[$flashMessage, $flashType] = AuthController::pullFlash();

// 5. Route requests to Controllers & Views
switch ($action) {
    // =========================================================================
    // FRONTOFFICE PUBLIC ROUTES
    // =========================================================================
    case 'home':
        $currentUser = $authController->getCurrentUser();
        if ($currentUser) {
            header('Location: index.php?action=' . ($currentUser->getRoleName() === 'admin' ? 'admin_dashboard' : 'profile'));
            exit;
        }
        $stats       = $userController->getStats();
        $needsSetup  = AuthController::isLocalhost() && !$authController->hasAnyAdmin();
        $currentPage = 'home';
        require __DIR__ . '/Views/Frontoffice/home.php';
        break;

    case 'setup':
        if (!AuthController::isLocalhost() || $authController->hasAnyAdmin()) {
            AuthController::flash('Local demo setup is unavailable.', 'error');
            header('Location: index.php?action=login');
            exit;
        }
        $errors = [];
        $old    = [
            'admin_name'  => 'HB Fitness Admin',
            'admin_email' => 'admin@hbfitess.local',
        ];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $old['admin_name']  = trim((string)($_POST['admin_name'] ?? ''));
            $old['admin_email'] = trim((string)($_POST['admin_email'] ?? ''));
            $errors = $authController->setupLocalDemoAccounts($_POST);
            if (empty($errors)) {
                AuthController::flash('Local demo accounts created securely with hashed passwords! You can now log in.');
                header('Location: index.php?action=login');
                exit;
            }
        }
        $csrfToken   = $authController->getCsrfToken();
        $currentUser = null;
        $currentPage = 'setup';
        require __DIR__ . '/Views/Frontoffice/login.php';
        break;

    case 'login':
        if (AuthController::isAuthenticated()) {
            $me = $authController->getCurrentUser();
            if ($me) {
                header('Location: index.php?action=' . ($me->getRoleName() === 'admin' ? 'admin_dashboard' : 'profile'));
                exit;
            }
        }
        $errors = [];
        $old    = ['email' => ''];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $old['email'] = trim((string)($_POST['email'] ?? ''));
            $errors       = $authController->login($_POST);
            if (empty($errors)) {
                $role   = (string)($_SESSION['role'] ?? 'user');
                $target = $role === 'admin' ? 'admin_dashboard' : 'profile';
                $roleLabel = match ($role) {
                    'admin' => 'Administrator',
                    'coach' => 'Coach',
                    default => 'Member',
                };
                AuthController::flash('Welcome back, ' . ($_SESSION['full_name'] ?? $roleLabel) . '!');
                header('Location: index.php?action=' . $target);
                exit;
            }
        }
        $csrfToken   = $authController->getCsrfToken();
        $needsSetup  = AuthController::isLocalhost() && !$authController->hasAnyAdmin();
        $currentUser = null;
        $currentPage = 'login';
        require __DIR__ . '/Views/Frontoffice/login.php';
        break;

    case 'forgot_password':
        if (!AuthController::isLocalhost()) {
            http_response_code(404);
            exit('Not found.');
        }
        if (AuthController::isAuthenticated()) {
            $me = $authController->getCurrentUser();
            if ($me) {
                header('Location: index.php?action=' . ($me->getRoleName() === 'admin' ? 'admin_dashboard' : 'profile'));
                exit;
            }
        }
        $errors    = [];
        $submitted = false;
        $old       = ['email' => ''];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $old['email'] = trim((string)($_POST['email'] ?? ''));
            $errors = $authController->requestPasswordReset($_POST);
            $submitted = empty($errors);
        }
        $csrfToken   = $authController->getCsrfToken();
        $currentUser = null;
        $currentPage = 'forgot_password';
        require __DIR__ . '/Views/Frontoffice/forgotPassword.php';
        break;

    case 'reset_password':
        if (!AuthController::isLocalhost()) {
            http_response_code(404);
            exit('Not found.');
        }
        $token = (string)($_POST['token'] ?? $_GET['token'] ?? '');
        $errors = [];
        $isValidToken = $authController->isPasswordResetTokenValid($token);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = $authController->resetPassword($token, $_POST);
            if (empty($errors)) {
                AuthController::flash('Your password has been reset. You can now log in.');
                header('Location: index.php?action=login');
                exit;
            }
        }
        $csrfToken   = $authController->getCsrfToken();
        $currentUser = null;
        $currentPage = 'reset_password';
        require __DIR__ . '/Views/Frontoffice/resetPassword.php';
        break;

    case 'register':
        if (AuthController::isAuthenticated()) {
            $me = $authController->getCurrentUser();
            header('Location: index.php?action=' . ($me && $me->getRoleName() === 'admin' ? 'admin_dashboard' : 'profile'));
            exit;
        }
        $errors = [];
        $old    = ['full_name' => '', 'email' => '', 'phone' => ''];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $old = [
                'full_name' => trim((string)($_POST['full_name'] ?? '')),
                'email'     => trim((string)($_POST['email'] ?? '')),
                'phone'     => trim((string)($_POST['phone'] ?? '')),
            ];
            $errors = $authController->register($_POST);
            if (empty($errors)) {
                AuthController::flash('Account created successfully! Please log in with your email and password.');
                header('Location: index.php?action=login&registered=1');
                exit;
            }
        }
        $csrfToken   = $authController->getCsrfToken();
        $currentUser = null;
        $currentPage = 'register';
        require __DIR__ . '/Views/Frontoffice/register.php';
        break;

    case 'logout':
        $authController->logout();
        session_start();
        AuthController::flash('You have been logged out securely.');
        header('Location: index.php?action=login');
        exit;

    // =========================================================================
    // FRONTOFFICE PROTECTED ROUTES (User / Coach / Admin)
    // =========================================================================
    case 'dashboard':
        $currentUser = $authController->requireAuth();
        if ($currentUser->getRoleName() === 'admin') {
            header('Location: index.php?action=admin_dashboard');
            exit;
        }
        header('Location: index.php?action=profile');
        exit;
        break;

    case 'profile':
        $currentUser = $authController->requireAuth();
        if ($currentUser->getRoleName() === 'admin') {
            header('Location: index.php?action=admin_dashboard');
            exit;
        }
        $currentPage = 'profile';
        require __DIR__ . '/Views/Frontoffice/profile.php';
        break;

    case 'edit_profile':
        $currentUser = $authController->requireAuth();
        if ($currentUser->getRoleName() === 'admin') {
            header('Location: index.php?action=admin_dashboard');
            exit;
        }
        $errors      = [];
        $old         = [
            'full_name' => $currentUser->getFullName(),
            'email'     => $currentUser->getEmail(),
            'phone'     => $currentUser->getPhone() ?? '',
        ];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $old = [
                'full_name' => trim((string)($_POST['full_name'] ?? '')),
                'email'     => trim((string)($_POST['email'] ?? '')),
                'phone'     => trim((string)($_POST['phone'] ?? '')),
            ];
            $errors = $userController->updateOwnProfile($currentUser->getId(), $_POST);
            if (empty($errors)) {
                AuthController::flash('Your profile has been updated successfully.');
                header('Location: index.php?action=profile');
                exit;
            }
        }
        $csrfToken   = $authController->getCsrfToken();
        $currentPage = 'edit_profile';
        require __DIR__ . '/Views/Frontoffice/editProfile.php';
        break;

    // =========================================================================
    // BACKOFFICE PROTECTED ROUTES (Admin ONLY)
    // =========================================================================
    case 'admin_profile':
        $currentUser = $authController->requireRole('admin');
        $currentPage = 'admin_profile';
        require __DIR__ . '/Views/Backoffice/profile.php';
        break;

    case 'admin_edit_profile':
        $currentUser = $authController->requireRole('admin');
        $errors      = [];
        $old         = [
            'full_name' => $currentUser->getFullName(),
            'email'     => $currentUser->getEmail(),
            'phone'     => $currentUser->getPhone() ?? '',
        ];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $old = [
                'full_name' => trim((string)($_POST['full_name'] ?? '')),
                'email'     => trim((string)($_POST['email'] ?? '')),
                'phone'     => trim((string)($_POST['phone'] ?? '')),
            ];
            $errors = $userController->updateOwnProfile($currentUser->getId(), $_POST);
            if (empty($errors)) {
                AuthController::flash('Your administrator profile has been updated successfully.');
                header('Location: index.php?action=admin_profile');
                exit;
            }
        }
        $csrfToken   = $authController->getCsrfToken();
        $currentPage = 'admin_profile';
        require __DIR__ . '/Views/Backoffice/editProfile.php';
        break;

    case 'admin_dashboard':
        $currentUser = $authController->requireRole('admin');
        $stats       = $userController->getStats();
        $recentUsers = array_slice($userController->getAllUsers(), 0, 6);
        $roles       = $roleController->getAllRoles();
        $statuses    = $roleController->getAllStatuses();
        $currentPage = 'admin_dashboard';
        require __DIR__ . '/Views/Backoffice/dashboard.php';
        break;

    case 'admin_users':
        $currentUser = $authController->requireRole('admin');
        $search      = trim((string)($_GET['search'] ?? ''));
        $filterRole  = isset($_GET['role_id']) && $_GET['role_id'] !== '' ? (int)$_GET['role_id'] : null;
        $filterStat  = isset($_GET['status_id']) && $_GET['status_id'] !== '' ? (int)$_GET['status_id'] : null;

        $users       = $userController->getAllUsers($search, $filterRole, $filterStat);
        $roles       = $roleController->getAllRoles();
        $statuses    = $roleController->getAllStatuses();
        $stats       = $userController->getStats();
        $csrfToken   = $authController->getCsrfToken();
        $currentPage = 'admin_users';
        require __DIR__ . '/Views/Backoffice/listUsers.php';
        break;

    case 'admin_create_user':
        $currentUser = $authController->requireRole('admin');
        $roles       = $roleController->getAllRoles();
        $statuses    = $roleController->getAllStatuses();
        $errors      = [];
        $old         = [
            'full_name' => '',
            'email'     => '',
            'phone'     => '',
            'role_id'   => 2,
            'status_id' => 1,
        ];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $old = [
                'full_name' => trim((string)($_POST['full_name'] ?? '')),
                'email'     => trim((string)($_POST['email'] ?? '')),
                'phone'     => trim((string)($_POST['phone'] ?? '')),
                'role_id'   => (int)($_POST['role_id'] ?? 2),
                'status_id' => (int)($_POST['status_id'] ?? 1),
            ];
            $errors = $userController->createUser($_POST);
            if (empty($errors)) {
                AuthController::flash('User account created successfully with hashed password.');
                header('Location: index.php?action=admin_users');
                exit;
            }
        }
        $csrfToken   = $authController->getCsrfToken();
        $currentPage = 'admin_create_user';
        require __DIR__ . '/Views/Backoffice/createUser.php';
        break;

    case 'admin_update_user':
        $currentUser = $authController->requireRole('admin');
        $targetId    = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
        $targetUser  = $userController->getUserById($targetId);
        if (!$targetUser) {
            AuthController::flash('User account not found.', 'error');
            header('Location: index.php?action=admin_users');
            exit;
        }
        $roles  = $roleController->getAllRoles();
        $statuses = $roleController->getAllStatuses();
        $errors = [];
        $old    = [
            'full_name' => $targetUser->getFullName(),
            'email'     => $targetUser->getEmail(),
            'phone'     => $targetUser->getPhone() ?? '',
            'role_id'   => $targetUser->getRoleId(),
            'status_id' => $targetUser->getStatusId(),
        ];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $old = [
                'full_name' => trim((string)($_POST['full_name'] ?? '')),
                'email'     => trim((string)($_POST['email'] ?? '')),
                'phone'     => trim((string)($_POST['phone'] ?? '')),
                'role_id'   => (int)($_POST['role_id'] ?? $targetUser->getRoleId()),
                'status_id' => (int)($_POST['status_id'] ?? $targetUser->getStatusId()),
            ];
            $errors = $userController->updateUser($targetId, $_POST, $currentUser->getId());
            if (empty($errors)) {
                AuthController::flash("User #{$targetId} updated successfully.");
                header('Location: index.php?action=admin_users');
                exit;
            }
        }
        $csrfToken   = $authController->getCsrfToken();
        $currentPage = 'admin_users';
        require __DIR__ . '/Views/Backoffice/updateUser.php';
        break;

    case 'admin_delete_user':
        $currentUser = $authController->requireRole('admin');
        $targetId    = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
        $targetUser  = $userController->getUserById($targetId);
        if (!$targetUser) {
            AuthController::flash('User account not found.', 'error');
            header('Location: index.php?action=admin_users');
            exit;
        }
        $errors = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = $userController->deleteUser($targetId, $currentUser->getId(), $_POST['csrf_token'] ?? null);
            if (empty($errors)) {
                AuthController::flash("User #{$targetId} ({$targetUser->getFullName()}) has been deleted.");
                header('Location: index.php?action=admin_users');
                exit;
            }
        }
        $csrfToken   = $authController->getCsrfToken();
        $currentPage = 'admin_users';
        require __DIR__ . '/Views/Backoffice/deleteUser.php';
        break;

    case 'admin_roles':
        $currentUser = $authController->requireRole('admin');
        $errors      = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $uid      = (int)($_POST['user_id'] ?? 0);
            $roleId   = (int)($_POST['role_id'] ?? 0);
            $statusId = (int)($_POST['status_id'] ?? 0);
            $errors   = $roleController->updateUserRoleAndStatus(
                $uid,
                $roleId,
                $statusId,
                $currentUser->getId(),
                $_POST['csrf_token'] ?? null
            );
            if (empty($errors)) {
                AuthController::flash("Role & status updated for user #{$uid}.");
                header('Location: index.php?action=admin_roles');
                exit;
            }
        }
        $roles       = $roleController->getAllRoles();
        $statuses    = $roleController->getAllStatuses();
        $users       = $userController->getAllUsers();
        $csrfToken   = $authController->getCsrfToken();
        $currentPage = 'admin_roles';
        require __DIR__ . '/Views/Backoffice/manageRoles.php';
        break;

    default:
        http_response_code(404);
        $currentUser = $authController->getCurrentUser();
        $title       = '404 Not Found — HB FITNESS';
        $currentPage = '404';
        ob_start();
        ?>
        <div class="container py-5">
            <div class="hb-card text-center mx-auto" style="max-width:520px;padding:3rem 2rem;">
                <h1 class="mb-2" style="color:#E8405E;">404</h1>
                <h3 class="mb-3">Page Not Found</h3>
                <p class="text-silver mb-4">The requested route does not exist in HB FITNESS.</p>
                <a href="index.php?action=home" class="btn btn-hb-primary">Back to Home</a>
            </div>
        </div>
        <?php
        $content = (string)ob_get_clean();
        require __DIR__ . '/Views/layout.php';
        break;
}
