<?php
declare(strict_types=1);

/**
 * index.php — Unified Front Controller & Router (HB FITNESS - User, Market & Blog Integration)
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
require_once __DIR__ . '/Controllers/WorkoutController.php';
require_once __DIR__ . '/Controllers/SessionController.php';
require_once __DIR__ . '/Models/RecipeRepository.php';
require_once __DIR__ . '/Controllers/RecipeController.php';

// Load Controllers & Repository (Marketplace)
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/Models/MarketplaceRepository.php';
require_once __DIR__ . '/Controllers/PageController.php';

// Load Controllers & Shared Auth Adapter (Blog Management)
require_once __DIR__ . '/Controllers/Blog/BlogAuthAdapter.php';
require_once __DIR__ . '/Controllers/Blog/ArticleController.php';
require_once __DIR__ . '/Controllers/Blog/CommentController.php';

$authController    = new AuthController();
$userController    = new UserController($authController);
$roleController    = new RoleController($authController);
$blogAuth          = new BlogAuthAdapter();
$articleController = new ArticleController($blogAuth);
$commentController = new CommentController($blogAuth);

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
        if ($currentUser) {
            $destination = $currentUser->getRoleName() === 'admin' ? 'admin_dashboard' : 'profile';
            header('Location: index.php?action=' . $destination);
            exit;
        }
        $stats       = $userController->getStats();
        $needsSetup  = AuthController::isLocalhost() && !$authController->hasAnyAdmin();
        $currentPage = 'home';
        require __DIR__ . '/Views/Frontoffice/home.php';
        break;

    case 'workouts':
        (new WorkoutController(Database::getConnexion(), $authController))->showPlans();
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
                $target = (($_SESSION['role'] ?? '') === 'admin') ? 'admin_dashboard' : 'profile';
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

    case 'forgot_password':
        $errors = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = $authController->requestPasswordReset($_POST);
            if (empty($errors)) {
                AuthController::flash('If an active account matches that email, a reset link has been sent to the local test inbox.');
                header('Location: index.php?action=login');
                exit;
            }
        }
        $csrfToken   = $authController->getCsrfToken();
        $currentUser = null;
        $currentPage = 'forgot_password';
        require __DIR__ . '/Views/Frontoffice/forgotPassword.php';
        break;

    case 'reset_password':
        $token = trim((string)($_POST['token'] ?? $_GET['token'] ?? ''));
        $errors = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = $authController->resetPassword($token, $_POST);
            if (empty($errors)) {
                AuthController::flash('Your password has been reset. You can now log in.');
                header('Location: index.php?action=login');
                exit;
            }
        }
        $isValidToken = $authController->isPasswordResetTokenValid($token);
        $csrfToken    = $authController->getCsrfToken();
        $currentUser  = null;
        $currentPage  = 'reset_password';
        require __DIR__ . '/Views/Frontoffice/resetPassword.php';
        break;

    case 'register':
        if (AuthController::isAuthenticated()) {
            header('Location: index.php?action=profile');
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
        $destination = $currentUser->getRoleName() === 'admin' ? 'admin_dashboard' : 'profile';
        header('Location: index.php?action=' . $destination);
        exit;

    case 'profile':
        $currentUser = $authController->requireAuth();
        if ($currentUser->getRoleName() === 'admin') {
            header('Location: index.php?action=admin_profile');
            exit;
        }
        $currentPage = 'profile';
        require __DIR__ . '/Views/Frontoffice/profile.php';
        break;

    case 'edit_profile':
        $currentUser = $authController->requireAuth();
        if ($currentUser->getRoleName() === 'admin') {
            header('Location: index.php?action=admin_edit_profile');
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
    case 'recipes':
        $currentUser      = $authController->getCurrentUser();
        $recipeRepository = new RecipeRepository(Database::getConnexion());
        $categories       = $recipeRepository->categories();
        $activeCategory   = (int)($_GET['category'] ?? 0);
        $search           = trim((string)($_GET['q'] ?? ''));
        $recipes          = $recipeRepository->recipes($activeCategory > 0 ? $activeCategory : null, $search);
        $currentPage      = 'recipes';
        require __DIR__ . '/Views/Frontoffice/nutrition/recipes.php';
        break;

    case 'recipe_details':
        $currentUser      = $authController->getCurrentUser();
        $recipeRepository = new RecipeRepository(Database::getConnexion());
        $recipeController = new RecipeController($recipeRepository);
        $recipe           = $recipeRepository->find((int)($_GET['id'] ?? 0));
        if (!$recipe) {
            AuthController::flash('Recipe not found.', 'error');
            header('Location: index.php?action=recipes');
            exit;
        }
        $canManage   = $currentUser && $recipeController->canManage($currentUser, $recipe);
        $csrfToken   = $authController->getCsrfToken();
        $currentPage = 'recipes';
        require __DIR__ . '/Views/Frontoffice/nutrition/recipeDetails.php';
        break;

    // =========================================================================
    // NUTRITION BACKOFFICE ROUTES (Admin: everything | Coach: own recipes)
    // =========================================================================
    case 'admin_recipes':
    case 'coach_recipes':
        $currentUser = $authController->requireAuth();
        if (!in_array($currentUser->getRoleName(), ['admin', 'coach'], true)) {
            http_response_code(403);
            echo '403 Access Denied';
            break;
        }
        $recipeRepository = new RecipeRepository(Database::getConnexion());
        $recipeController = new RecipeController($recipeRepository);
        $isAdmin    = $currentUser->getRoleName() === 'admin';
        $recipes    = $recipeRepository->recipes(null, null, $isAdmin ? null : $currentUser->getId());
        $categories = $recipeRepository->categories();
        $stats      = $recipeRepository->stats($isAdmin ? null : $currentUser->getId());
        $editing    = null;
        $errors     = [];
        $old        = $recipeController->defaults();
        $csrfToken  = $authController->getCsrfToken();
        $currentPage = 'admin_recipes';
        require __DIR__ . '/Views/Backoffice/nutrition/recipes.php';
        break;

    case 'admin_create_recipe':
    case 'coach_create_recipe':
        $currentUser = $authController->requireAuth();
        if (!in_array($currentUser->getRoleName(), ['admin', 'coach'], true)) {
            http_response_code(403);
            echo '403 Access Denied';
            break;
        }
        $recipeRepository = new RecipeRepository(Database::getConnexion());
        $recipeController = new RecipeController($recipeRepository);
        $isAdmin = $currentUser->getRoleName() === 'admin';
        $errors  = [];
        $old     = $recipeController->defaults();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $old = $recipeController->fromPost($_POST);
            if (!$authController->verifyCsrfToken($_POST['csrf_token'] ?? null)) {
                $errors['csrf'] = 'Your form has expired. Please try again.';
            } else {
                $errors = $recipeController->validate($old);
                if (empty($errors)) {
                    $recipeRepository->create(
                        $recipeController->normalized($old),
                        $recipeController->parseSteps($old['steps']),
                        $currentUser->getId()
                    );
                    AuthController::flash('Recipe created successfully.');
                    header('Location: index.php?action=' . ($isAdmin ? 'admin_recipes' : 'coach_recipes'));
                    exit;
                }
            }
        }
        $recipes     = $recipeRepository->recipes(null, null, $isAdmin ? null : $currentUser->getId());
        $categories  = $recipeRepository->categories();
        $stats       = $recipeRepository->stats($isAdmin ? null : $currentUser->getId());
        $editing     = null;
        $csrfToken   = $authController->getCsrfToken();
        $currentPage = 'admin_recipes';
        require __DIR__ . '/Views/Backoffice/nutrition/recipes.php';
        break;

    case 'admin_edit_recipe':
    case 'coach_edit_recipe':
        $currentUser = $authController->requireAuth();
        if (!in_array($currentUser->getRoleName(), ['admin', 'coach'], true)) {
            http_response_code(403);
            echo '403 Access Denied';
            break;
        }
        $recipeRepository = new RecipeRepository(Database::getConnexion());
        $recipeController = new RecipeController($recipeRepository);
        $isAdmin = $currentUser->getRoleName() === 'admin';
        $editing = $recipeRepository->find((int)($_GET['id'] ?? $_POST['id'] ?? 0));
        if (!$editing || !$recipeController->canManage($currentUser, $editing)) {
            AuthController::flash('Recipe not found or access denied.', 'error');
            header('Location: index.php?action=' . ($isAdmin ? 'admin_recipes' : 'coach_recipes'));
            exit;
        }
        $errors = [];
        $old    = $recipeController->fromRecipe($editing);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $old = $recipeController->fromPost($_POST);
            if (!$authController->verifyCsrfToken($_POST['csrf_token'] ?? null)) {
                $errors['csrf'] = 'Your form has expired. Please try again.';
            } else {
                $errors = $recipeController->validate($old);
                if (empty($errors)) {
                    $recipeRepository->update(
                        $editing['id'],
                        $recipeController->normalized($old),
                        $recipeController->parseSteps($old['steps'])
                    );
                    AuthController::flash('Recipe updated successfully.');
                    header('Location: index.php?action=' . ($isAdmin ? 'admin_recipes' : 'coach_recipes'));
                    exit;
                }
            }
        }
        $recipes     = $recipeRepository->recipes(null, null, $isAdmin ? null : $currentUser->getId());
        $categories  = $recipeRepository->categories();
        $stats       = $recipeRepository->stats($isAdmin ? null : $currentUser->getId());
        $csrfToken   = $authController->getCsrfToken();
        $currentPage = 'admin_recipes';
        require __DIR__ . '/Views/Backoffice/nutrition/recipes.php';
        break;

    case 'admin_delete_recipe':
    case 'coach_delete_recipe':
        $currentUser = $authController->requireAuth();
        $isAdmin     = $currentUser->getRoleName() === 'admin';
        $back        = 'index.php?action=' . ($isAdmin ? 'admin_recipes' : 'coach_recipes');
        if (!in_array($currentUser->getRoleName(), ['admin', 'coach'], true)) {
            http_response_code(403);
            echo '403 Access Denied';
            break;
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$authController->verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            AuthController::flash('Your form has expired. Please try again.', 'error');
            header('Location: ' . $back);
            exit;
        }
        $recipeRepository = new RecipeRepository(Database::getConnexion());
        $recipeController = new RecipeController($recipeRepository);
        $target = $recipeRepository->find((int)($_POST['id'] ?? 0));
        if (!$target || !$recipeController->canManage($currentUser, $target)) {
            AuthController::flash('Recipe not found or access denied.', 'error');
        } else {
            $recipeRepository->delete($target['id']);
            AuthController::flash('Recipe deleted.');
        }
        header('Location: ' . $back);
        exit;

    case 'admin_recipe_categories':
        $currentUser      = $authController->requireRole('admin');
        $recipeRepository = new RecipeRepository(Database::getConnexion());
        $recipeController = new RecipeController($recipeRepository);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$authController->verifyCsrfToken($_POST['csrf_token'] ?? null)) {
                AuthController::flash('Your form has expired. Please try again.', 'error');
            } else {
                $operation = (string)($_POST['op'] ?? '');
                $name      = trim((string)($_POST['name'] ?? ''));
                $id        = (int)($_POST['id'] ?? 0);
                try {
                    if ($operation === 'delete') {
                        $recipeRepository->deleteCategory($id)
                            ? AuthController::flash('Category deleted.')
                            : AuthController::flash('Category not found.', 'error');
                    } else {
                        $nameError = $recipeController->validateCategoryName($name);
                        if ($nameError !== null) {
                            AuthController::flash($nameError, 'error');
                        } elseif ($operation === 'update') {
                            $recipeRepository->updateCategory($id, $name)
                                ? AuthController::flash('Category updated.')
                                : AuthController::flash('Category not found.', 'error');
                        } else {
                            $recipeRepository->createCategory($name);
                            AuthController::flash('Category created.');
                        }
                    }
                } catch (DomainException $error) {
                    AuthController::flash($error->getMessage(), 'error');
                }
            }
            header('Location: index.php?action=admin_recipe_categories');
            exit;
        }
        $categories  = $recipeRepository->categories();
        $csrfToken   = $authController->getCsrfToken();
        $currentPage = 'admin_recipe_categories';
        require __DIR__ . '/Views/Backoffice/nutrition/categories.php';
        break;
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

    case 'admin_workouts':
        (new WorkoutController(Database::getConnexion(), $authController))->managePlans();
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

    // =========================================================================
    // BLOG FRONTOFFICE ROUTES (Public Reading + Shared-Auth Commenting)
    // =========================================================================
    case 'blog':
    case 'blog_home':
        $currentUser    = $blogAuth->getCurrentUser();
        $stats          = $articleController->getStats();
        $latestArticles = array_slice($articleController->getAllArticles(), 0, 6);
        $currentPage    = 'blog';
        require __DIR__ . '/Views/Frontoffice/blog/home.php';
        break;

    case 'articles':
        $currentUser = $blogAuth->getCurrentUser();
        $search      = trim((string)($_GET['search'] ?? ''));
        $onlyMine    = !empty($_GET['mine']) && $currentUser && $currentUser['role'] === 'coach';
        $articles    = $articleController->getAllArticles($search, $onlyMine ? (int)$currentUser['id'] : null);
        $currentPage = 'articles';
        require __DIR__ . '/Views/Frontoffice/blog/listArticles.php';
        break;

    case 'article_details':
        $currentUser = $blogAuth->getCurrentUser();
        $articleId   = (int)($_GET['id'] ?? 0);
        $article     = $articleController->getArticleById($articleId);
        if (!$article) {
            BlogAuthAdapter::flash('Article not found.', 'error');
            header('Location: index.php?action=articles');
            exit;
        }
        $comments    = $commentController->getCommentsByArticle($articleId);
        $csrfToken   = $blogAuth->getCsrfToken();
        $currentPage = 'articles';
        require __DIR__ . '/Views/Frontoffice/blog/articleDetails.php';
        break;

    case 'create_comment':
        $currentUser = $blogAuth->requireAuth();
        $articleId   = (int)($_GET['article_id'] ?? $_POST['article_id'] ?? 0);
        $article     = $articleController->getArticleById($articleId);
        if (!$article) {
            BlogAuthAdapter::flash('Target article not found.', 'error');
            header('Location: index.php?action=articles');
            exit;
        }
        $errors = [];
        $old    = ['content' => ''];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $old['content'] = trim((string)($_POST['content'] ?? ''));
            $errors         = $commentController->createComment($_POST, $currentUser);
            if (empty($errors)) {
                BlogAuthAdapter::flash('Your comment has been published.');
                header('Location: index.php?action=article_details&id=' . $articleId);
                exit;
            }
        }
        $csrfToken   = $blogAuth->getCsrfToken();
        $currentPage = 'articles';
        require __DIR__ . '/Views/Frontoffice/blog/createComment.php';
        break;

    case 'update_comment':
        $currentUser = $blogAuth->requireAuth();
        $commentId   = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
        $comment     = $commentController->getCommentById($commentId);
        if (!$comment) {
            BlogAuthAdapter::flash('Comment not found.', 'error');
            header('Location: index.php?action=my_comments');
            exit;
        }
        if (!CommentController::canManageComment($comment, $currentUser)) {
            $blogAuth->renderForbidden('You can only edit your own comments.', $currentUser);
        }
        $errors = [];
        $old    = ['content' => $comment->getContent()];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $old['content'] = trim((string)($_POST['content'] ?? ''));
            $errors         = $commentController->updateComment($commentId, $_POST, $currentUser);
            if (empty($errors)) {
                BlogAuthAdapter::flash('Comment updated successfully.');
                $redirect = ($currentUser['role'] === 'admin' && !empty($_POST['from_admin']))
                    ? 'admin_comments'
                    : 'article_details&id=' . $comment->getArticleId();
                header('Location: index.php?action=' . $redirect);
                exit;
            }
        }
        $csrfToken   = $blogAuth->getCsrfToken();
        $fromAdmin   = !empty($_GET['from_admin']);
        $currentPage = 'my_comments';
        require __DIR__ . '/Views/Frontoffice/blog/updateComment.php';
        break;

    case 'delete_comment':
        $currentUser = $blogAuth->requireAuth();
        $commentId   = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
        $comment     = $commentController->getCommentById($commentId);
        if (!$comment) {
            BlogAuthAdapter::flash('Comment not found.', 'error');
            header('Location: index.php?action=my_comments');
            exit;
        }
        if (!CommentController::canManageComment($comment, $currentUser)) {
            $blogAuth->renderForbidden('You can only delete your own comments.', $currentUser);
        }
        $errors    = [];
        $fromAdmin = !empty($_GET['from_admin']) || !empty($_POST['from_admin']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $articleId = $comment->getArticleId();
            $errors    = $commentController->deleteComment($commentId, $currentUser, $_POST['csrf_token'] ?? null);
            if (empty($errors)) {
                BlogAuthAdapter::flash('Comment deleted successfully.');
                $redirect = ($currentUser['role'] === 'admin' && $fromAdmin)
                    ? 'admin_comments'
                    : 'article_details&id=' . $articleId;
                header('Location: index.php?action=' . $redirect);
                exit;
            }
        }
        $csrfToken   = $blogAuth->getCsrfToken();
        $currentPage = 'my_comments';
        require __DIR__ . '/Views/Frontoffice/blog/deleteComment.php';
        break;

    case 'my_comments':
        $currentUser = $blogAuth->requireAuth();
        $comments    = $commentController->getCommentsByUser((int)$currentUser['id']);
        $currentPage = 'my_comments';
        require __DIR__ . '/Views/Frontoffice/blog/myComments.php';
        break;

    // =========================================================================
    // BLOG ARTICLE AUTHORING ROUTES (Admin: Backoffice | Coach: Frontoffice)
    // =========================================================================
    case 'create_article':
        $currentUser = $blogAuth->requireArticlePublisher();
        $errors      = [];
        $old         = ['title' => '', 'content' => '', 'recipe_id' => ''];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $old = [
                'title'     => trim((string)($_POST['title'] ?? '')),
                'content'   => trim((string)($_POST['content'] ?? '')),
                'recipe_id' => trim((string)($_POST['recipe_id'] ?? '')),
            ];
            $errors = $articleController->createArticle($_POST, $currentUser);
            if (empty($errors)) {
                BlogAuthAdapter::flash('Article published successfully!');
                $target = $currentUser['role'] === 'admin' ? 'admin_articles' : 'articles';
                header('Location: index.php?action=' . $target);
                exit;
            }
        }
        $csrfToken   = $blogAuth->getCsrfToken();
        $currentPage = $currentUser['role'] === 'admin' ? 'admin_create_article' : 'create_article';
        require __DIR__ . '/Views/Backoffice/blog/createArticle.php';
        break;

    case 'update_article':
        $currentUser = $blogAuth->requireArticlePublisher();
        $articleId   = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
        $article     = $articleController->getArticleById($articleId);
        if (!$article) {
            BlogAuthAdapter::flash('Article not found.', 'error');
            header('Location: index.php?action=' . ($currentUser['role'] === 'admin' ? 'admin_articles' : 'articles'));
            exit;
        }
        if (!ArticleController::canManageArticle($article, $currentUser)) {
            $blogAuth->renderForbidden('Coaches can only edit their own published articles.', $currentUser);
        }
        $errors = [];
        $old    = [
            'title'     => $article->getTitle(),
            'content'   => $article->getContent(),
            'recipe_id' => $article->getRecipeId() !== null ? (string)$article->getRecipeId() : '',
        ];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $old = [
                'title'     => trim((string)($_POST['title'] ?? '')),
                'content'   => trim((string)($_POST['content'] ?? '')),
                'recipe_id' => trim((string)($_POST['recipe_id'] ?? '')),
            ];
            $errors = $articleController->updateArticle($articleId, $_POST, $currentUser);
            if (empty($errors)) {
                BlogAuthAdapter::flash("Article #{$articleId} updated successfully.");
                $target = $currentUser['role'] === 'admin' ? 'admin_articles' : 'article_details&id=' . $articleId;
                header('Location: index.php?action=' . $target);
                exit;
            }
        }
        $csrfToken   = $blogAuth->getCsrfToken();
        $currentPage = $currentUser['role'] === 'admin' ? 'admin_articles' : 'articles';
        require __DIR__ . '/Views/Backoffice/blog/updateArticle.php';
        break;

    case 'delete_article':
        $currentUser = $blogAuth->requireArticlePublisher();
        $articleId   = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
        $article     = $articleController->getArticleById($articleId);
        if (!$article) {
            BlogAuthAdapter::flash('Article not found.', 'error');
            header('Location: index.php?action=' . ($currentUser['role'] === 'admin' ? 'admin_articles' : 'articles'));
            exit;
        }
        if (!ArticleController::canManageArticle($article, $currentUser)) {
            $blogAuth->renderForbidden('Coaches can only delete their own published articles.', $currentUser);
        }
        $errors = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = $articleController->deleteArticle($articleId, $currentUser, $_POST['csrf_token'] ?? null);
            if (empty($errors)) {
                BlogAuthAdapter::flash("Article #{$articleId} and its comments have been deleted.");
                $target = $currentUser['role'] === 'admin' ? 'admin_articles' : 'articles';
                header('Location: index.php?action=' . $target);
                exit;
            }
        }
        $csrfToken   = $blogAuth->getCsrfToken();
        $currentPage = $currentUser['role'] === 'admin' ? 'admin_articles' : 'articles';
        require __DIR__ . '/Views/Backoffice/blog/deleteArticle.php';
        break;

    // =========================================================================
    // BLOG BACKOFFICE ROUTES (ADMIN ONLY)
    // =========================================================================
    case 'admin_blog_dashboard':
        $currentUser    = $blogAuth->requireAdmin();
        $stats          = $articleController->getStats();
        $recentArticles = array_slice($articleController->getAllArticles(), 0, 5);
        $recentComments = array_slice($commentController->getAllComments(), 0, 5);
        $currentPage    = 'admin_blog_dashboard';
        require __DIR__ . '/Views/Backoffice/blog/dashboard.php';
        break;

    case 'admin_articles':
        $currentUser = $blogAuth->requireAdmin();
        $search      = trim((string)($_GET['search'] ?? ''));
        $articles    = $articleController->getAllArticles($search);
        $currentPage = 'admin_articles';
        require __DIR__ . '/Views/Backoffice/blog/listArticles.php';
        break;

    case 'admin_comments':
        $currentUser   = $blogAuth->requireAdmin();
        $search        = trim((string)($_GET['search'] ?? ''));
        $filterArticle = isset($_GET['article_id']) && $_GET['article_id'] !== '' ? (int)$_GET['article_id'] : null;
        $comments      = $commentController->getAllComments($search, $filterArticle);
        $allArticles   = $articleController->getAllArticles();
        $currentPage   = 'admin_comments';
        require __DIR__ . '/Views/Backoffice/blog/manageComments.php';
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
