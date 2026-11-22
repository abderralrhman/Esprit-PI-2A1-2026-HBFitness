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
