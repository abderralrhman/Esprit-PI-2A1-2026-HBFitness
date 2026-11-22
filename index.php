<?php
declare(strict_types=1);

/**
 * index.php — Unified Front Controller & Router (HB FITNESS - User & Market Integration)
 * ESPRIT School of Engineering — Web Technologies 2A (2026-2027)
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
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

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

// 4. Load Controllers (User Management)
require_once __DIR__ . '/Controllers/AuthController.php';
require_once __DIR__ . '/Controllers/UserController.php';
require_once __DIR__ . '/Controllers/RoleController.php';
require_once __DIR__ . '/Controllers/SessionController.php';

// Load Controllers & Repository (Marketplace)
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/Models/MarketplaceRepository.php';
require_once __DIR__ . '/Controllers/PageController.php';

$authController = new AuthController();
$userController = new UserController($authController);
$roleController = new RoleController($authController);

// Determine action: supports ?action=... (User module standard) and ?page=... (Market module standard)
$action = (string)($_GET['action'] ?? ($_GET['page'] ?? 'home'));
[$flashMessage, $flashType] = AuthController::pullFlash();

// 5. Route requests to Controllers & Views
switch ($action) {
    // =========================================================================
    // MARKETPLACE ROUTES (Atelier Market)
    // =========================================================================
    case 'store':
    case 'market':
        $_GET['page'] = 'store';
        $currentUser  = $authController->getCurrentUser();
        $currentPage  = 'store';
        try {
            (new PageController(new MarketplaceRepository(database())))->handle();
        } catch (Throwable $error) {
            error_log(sprintf('[Atelier Market pages] %s: %s', get_class($error), $error->getMessage()));
            http_response_code(500);
            echo 'Unable to connect to the database. Check the MySQL configuration.';
        }
        break;

    case 'market_admin':
    case 'admin_market':
        $_GET['page'] = 'admin';
        $currentUser  = $authController->getCurrentUser();
        $currentPage  = 'market_admin';
        try {
            (new PageController(new MarketplaceRepository(database())))->handle();
        } catch (Throwable $error) {
            error_log(sprintf('[Atelier Market pages] %s: %s', get_class($error), $error->getMessage()));
            http_response_code(500);
            echo 'Unable to connect to the database. Check the MySQL configuration.';
        }
        break;

    // =========================================================================
    // FRONTOFFICE PUBLIC ROUTES (User Management)
    // =========================================================================
    case 'home':
        $currentUser = $authController->getCurrentUser();
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
                header('Location: index.php?action=' . ($me->getRoleName() === 'admin' ? 'admin_dashboard' : 'dashboard'));
                exit;
            }
        }
        $errors = [];
        $old    = ['email' => ''];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $old['email'] = trim((string)($_POST['email'] ?? ''));
            $errors       = $authController->login($_POST);
            if (empty($errors)) {
                $target = (($_SESSION['role'] ?? '') === 'admin') ? 'admin_dashboard' : 'dashboard';
                AuthController::flash('Welcome back, ' . ($_SESSION['full_name'] ?? 'Athlete') . '!');
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

    case 'register':
        if (AuthController::isAuthenticated()) {
            header('Location: index.php?action=dashboard');
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
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        AuthController::flash('You have been logged out securely.');
        header('Location: index.php?action=login');
        exit;

    // =========================================================================
    // COACHING SESSION ROUTES
    // =========================================================================
    case 'sessions':
        $currentUser = $authController->getCurrentUser();
        $sessionRepository = new SessionRepository(Database::getConnexion());
        $upcoming = $sessionRepository->upcoming();
        $csrfToken = $authController->getCsrfToken();
        $currentPage = 'sessions';
        require __DIR__ . '/Views/Frontoffice/sessions.php';
        break;

    case 'my_bookings':
        $currentUser = $authController->requireAuth();
        $sessionRepository = new SessionRepository(Database::getConnexion());
        $bookings = $sessionRepository->bookingsForUser($currentUser->getId());
        $csrfToken = $authController->getCsrfToken();
        $currentPage = 'sessions';
        require __DIR__ . '/Views/Frontoffice/bookings.php';
        break;

    case 'book_session':
        $currentUser = $authController->requireAuth();
        if (!$authController->verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            AuthController::flash('Your form has expired. Please try again.', 'error');
        } else {
            try {
                $sessionRepository = new SessionRepository(Database::getConnexion());
                $sessionRepository->book((int)($_POST['session_id'] ?? 0), $currentUser->getId());
                AuthController::flash('Your place has been booked successfully.');
            } catch (DomainException $error) {
                AuthController::flash($error->getMessage(), 'error');
            } catch (Throwable $error) {
                error_log('[Session booking] ' . $error->getMessage());
                AuthController::flash('The booking could not be completed. Please try again.', 'error');
            }
        }
        header('Location: index.php?action=sessions');
        exit;

    case 'cancel_booking':
        $currentUser = $authController->requireAuth();
        if (!$authController->verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            AuthController::flash('Your form has expired. Please try again.', 'error');
        } else {
            $sessionRepository = new SessionRepository(Database::getConnexion());
            $cancelled = $sessionRepository->cancel((int)($_POST['session_id'] ?? 0), $currentUser->getId());
            AuthController::flash($cancelled ? 'Your booking has been cancelled.' : 'No active booking was found.', $cancelled ? 'success' : 'error');
        }
        header('Location: index.php?action=my_bookings');
        exit;

    // =========================================================================
    // FRONTOFFICE PROTECTED ROUTES (User / Coach / Admin)
    // =========================================================================
    case 'dashboard':
        $currentUser = $authController->requireAuth();
        $stats       = $userController->getStats();
        $currentPage = 'dashboard';
        require __DIR__ . '/Views/Frontoffice/dashboard.php';
        break;

    case 'profile':
        $currentUser = $authController->requireAuth();
        $currentPage = 'profile';
        require __DIR__ . '/Views/Frontoffice/profile.php';
        break;

    case 'edit_profile':
        $currentUser = $authController->requireAuth();
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
    case 'admin_sessions':
    case 'coach_sessions':
        $currentUser = $authController->requireAuth();
        if (!in_array($currentUser->getRoleName(), ['admin', 'coach'], true)) {
            http_response_code(403);
            echo '403 Access Denied';
            break;
        }
        $sessionRepository = new SessionRepository(Database::getConnexion());
        $sessionController = new SessionController($sessionRepository);
        $isAdmin = $currentUser->getRoleName() === 'admin';
        $sessions = $sessionRepository->allSessions($isAdmin ? null : $currentUser->getId());
        $coaches = $sessionRepository->coaches();
        $editing = null;
        $errors = [];
        $old = ['title' => '', 'mode' => 'in-person', 'start_at' => '', 'duration_min' => 60, 'max_participants' => 20, 'location' => '', 'coach_id' => $currentUser->getId()];
        $csrfToken = $authController->getCsrfToken();
        $currentPage = 'admin_sessions';
        require __DIR__ . '/Views/Backoffice/sessions.php';
        break;

    case 'admin_create_session':
    case 'coach_create_session':
        $currentUser = $authController->requireAuth();
        if (!in_array($currentUser->getRoleName(), ['admin', 'coach'], true)) {
            http_response_code(403);
            echo '403 Access Denied';
            break;
        }
        $sessionRepository = new SessionRepository(Database::getConnexion());
        $sessionController = new SessionController($sessionRepository);
        $errors = [];
        $old = [
            'title' => trim((string)($_POST['title'] ?? '')),
            'mode' => (string)($_POST['mode'] ?? 'in-person'),
            'start_at' => trim((string)($_POST['start_at'] ?? '')),
            'duration_min' => (int)($_POST['duration_min'] ?? 60),
            'max_participants' => (int)($_POST['max_participants'] ?? 20),
            'location' => trim((string)($_POST['location'] ?? '')),
            'coach_id' => (int)($_POST['coach_id'] ?? $currentUser->getId()),
        ];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$authController->verifyCsrfToken($_POST['csrf_token'] ?? null)) {
                $errors['csrf'] = 'Your form has expired. Please try again.';
            } else {
                $errors = $sessionController->validate($old);
                if ($currentUser->getRoleName() !== 'admin' && $old['coach_id'] !== $currentUser->getId()) {
                    $errors['coach_id'] = 'Coaches can only create sessions assigned to themselves.';
                }
                if (empty($errors)) {
                    $sessionRepository->create($sessionController->normalized($old));
                    AuthController::flash('Training session created successfully.');
                    header('Location: index.php?action=' . ($currentUser->getRoleName() === 'admin' ? 'admin_sessions' : 'coach_sessions'));
                    exit;
                }
            }
        }
        $isAdmin = $currentUser->getRoleName() === 'admin';
        $sessions = $sessionRepository->allSessions($isAdmin ? null : $currentUser->getId());
        $coaches = $sessionRepository->coaches();
        $editing = null;
        $csrfToken = $authController->getCsrfToken();
        $currentPage = 'admin_sessions';
        require __DIR__ . '/Views/Backoffice/sessions.php';
        break;

    case 'admin_edit_session':
    case 'coach_edit_session':
        $currentUser = $authController->requireAuth();
        if (!in_array($currentUser->getRoleName(), ['admin', 'coach'], true)) {
            http_response_code(403);
            echo '403 Access Denied';
            break;
        }
        $sessionRepository = new SessionRepository(Database::getConnexion());
        $sessionController = new SessionController($sessionRepository);
        $editing = $sessionRepository->find((int)($_GET['id'] ?? $_POST['id'] ?? 0));
        if (!$editing || !$sessionController->updatableFor($currentUser, $editing)) {
            AuthController::flash('Session not found or access denied.', 'error');
            header('Location: index.php?action=' . ($currentUser->getRoleName() === 'admin' ? 'admin_sessions' : 'coach_sessions'));
            exit;
        }
        $old = [
            'title' => $editing->getTitle(),
            'mode' => $editing->getMode(),
            'start_at' => str_replace(' ', 'T', substr($editing->getStartAt(), 0, 16)),
            'duration_min' => $editing->getDurationMin(),
            'max_participants' => $editing->getMaxParticipants(),
            'location' => $editing->getLocation(),
            'coach_id' => $editing->getCoachId(),
        ];
        $errors = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $old = [
                'title' => trim((string)($_POST['title'] ?? '')),
                'mode' => (string)($_POST['mode'] ?? 'in-person'),
                'start_at' => trim((string)($_POST['start_at'] ?? '')),
                'duration_min' => (int)($_POST['duration_min'] ?? 0),
                'max_participants' => (int)($_POST['max_participants'] ?? 0),
                'location' => trim((string)($_POST['location'] ?? '')),
                'coach_id' => (int)($_POST['coach_id'] ?? $editing->getCoachId()),
            ];
            if (!$authController->verifyCsrfToken($_POST['csrf_token'] ?? null)) {
                $errors['csrf'] = 'Your form has expired. Please try again.';
            } else {
                $errors = $sessionController->validate($old);
                if ($currentUser->getRoleName() !== 'admin' && $old['coach_id'] !== $currentUser->getId()) {
                    $errors['coach_id'] = 'Coaches can only manage their own sessions.';
                }
                if (empty($errors)) {
                    $sessionRepository->update($editing->getId(), $sessionController->normalized($old));
                    AuthController::flash('Training session updated successfully.');
                    header('Location: index.php?action=' . ($currentUser->getRoleName() === 'admin' ? 'admin_sessions' : 'coach_sessions'));
                    exit;
                }
            }
        }
        $isAdmin = $currentUser->getRoleName() === 'admin';
        $sessions = $sessionRepository->allSessions($isAdmin ? null : $currentUser->getId());
        $coaches = $sessionRepository->coaches();
        $csrfToken = $authController->getCsrfToken();
        $currentPage = 'admin_sessions';
        require __DIR__ . '/Views/Backoffice/sessions.php';
        break;

    case 'admin_delete_session':
        $currentUser = $authController->requireRole('admin');
        if (!$authController->verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            AuthController::flash('Your form has expired. Please try again.', 'error');
        } else {
            $sessionRepository = new SessionRepository(Database::getConnexion());
            $sessionRepository->delete((int)($_POST['id'] ?? 0));
            AuthController::flash('Training session deleted.');
        }
        header('Location: index.php?action=admin_sessions');
        exit;

    case 'admin_session_bookings':
    case 'coach_session_bookings':
        $currentUser = $authController->requireAuth();
        if (!in_array($currentUser->getRoleName(), ['admin', 'coach'], true)) {
            http_response_code(403);
            echo '403 Access Denied';
            break;
        }
        $sessionRepository = new SessionRepository(Database::getConnexion());
        $session = $sessionRepository->find((int)($_GET['id'] ?? 0));
        if (!$session || ($currentUser->getRoleName() !== 'admin' && $session->getCoachId() !== $currentUser->getId())) {
            AuthController::flash('Session not found or access denied.', 'error');
            header('Location: index.php?action=' . ($currentUser->getRoleName() === 'admin' ? 'admin_sessions' : 'coach_sessions'));
            exit;
        }
        $bookings = $sessionRepository->bookingsForSession($session->getId());
        $currentPage = 'admin_sessions';
        require __DIR__ . '/Views/Backoffice/sessionBookings.php';
        break;

    case 'admin':
        // If ?page=admin was requested without specific user action, delegate to Marketplace admin
        if (($_GET['page'] ?? '') === 'admin') {
            $_GET['page'] = 'admin';
            $currentUser  = $authController->getCurrentUser();
            $currentPage  = 'market_admin';
            try {
                (new PageController(new MarketplaceRepository(database())))->handle();
            } catch (Throwable $error) {
                error_log(sprintf('[Atelier Market pages] %s: %s', get_class($error), $error->getMessage()));
                http_response_code(500);
                echo 'Unable to connect to the database. Check the MySQL configuration.';
            }
            break;
        }
        // Fallback to admin_dashboard
        header('Location: index.php?action=admin_dashboard');
        exit;

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
        $roles    = $roleController->getAllRoles();
        $statuses = $roleController->getAllStatuses();
        $errors   = [];
        $old      = [
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
        // Support direct ?page=store or ?page=admin
        if (isset($_GET['page']) && in_array($_GET['page'], ['admin', 'store'], true)) {
            try {
                (new PageController(new MarketplaceRepository(database())))->handle();
                break;
            } catch (Throwable $error) {
                error_log(sprintf('[Atelier Market fallback] %s: %s', get_class($error), $error->getMessage()));
            }
        }

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
