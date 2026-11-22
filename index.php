<?php
declare(strict_types=1);

/**
 * index.php — Single Entry Point / Router for HB FITNESS Blog Model
 * Local URL: http://localhost/Blog%20Model/
 *
 * All SQL and CRUD business logic lives inside Controllers/.
 * Blog routes are clearly separated for smooth future integration into
 * the shared Esprit-PI-2A1-2026-HBFitness repository.
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

// 2. Session expiration after 30 minutes of inactivity
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
require_once __DIR__ . '/Controllers/ArticleController.php';
require_once __DIR__ . '/Controllers/CommentController.php';

$authController    = new AuthController();
$articleController = new ArticleController($authController);
$commentController = new CommentController($authController);

$action = (string)($_GET['action'] ?? 'home');
[$flashMessage, $flashType] = AuthController::pullFlash();

// 5. Route Dispatch
switch ($action) {
    // =========================================================================
    // MINIMAL AUTH ROUTES (Standalone — easily replaced during integration)
    // =========================================================================
    case 'login':
        if (AuthController::isAuthenticated()) {
            $me = $authController->getCurrentUser();
            if ($me) {
                header('Location: index.php?action=' . ($me['role'] === 'admin' ? 'admin_dashboard' : 'home'));
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
                $target = ($role === 'admin') ? 'admin_dashboard' : 'home';
                AuthController::flash('Welcome back, ' . ($_SESSION['full_name'] ?? 'Member') . '!');
                header('Location: index.php?action=' . $target);
                exit;
            }
        }
        $csrfToken         = $authController->getCsrfToken();
        $quickLoginEnabled = AuthController::isLocalQuickLoginEnabled();
        $currentUser       = null;
        $currentPage       = 'login';
        require __DIR__ . '/Views/auth/login.php';
        break;

    case 'quick_login':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=login');
            exit;
        }
        $errors = $authController->quickLogin($_POST);
        if (empty($errors)) {
            $role   = (string)($_SESSION['role'] ?? 'user');
            $target = ($role === 'admin') ? 'admin_dashboard' : 'home';
            AuthController::flash('Signed in as ' . ($_SESSION['full_name'] ?? 'Member') . ' (' . strtoupper($role) . ').');
            header('Location: index.php?action=' . $target);
            exit;
        }
        AuthController::flash($errors[0] ?? 'Quick login failed.', 'error');
        header('Location: index.php?action=login');
        exit;

    case 'logout':
        $authController->logout();
        session_start();
        AuthController::flash('You have been logged out securely.');
        header('Location: index.php?action=login');
        exit;

    // =========================================================================
    // BLOG FRONTOFFICE ROUTES (Visitors, Users, Coaches, Admins)
    // =========================================================================
    case 'home':
        $currentUser    = $authController->getCurrentUser();
        $stats          = $articleController->getStats();
        $latestArticles = array_slice($articleController->getAllArticles(), 0, 6);
        $currentPage    = 'home';
        require __DIR__ . '/Views/Frontoffice/home.php';
        break;

    case 'articles':
        $currentUser = $authController->getCurrentUser();
        $search      = trim((string)($_GET['search'] ?? ''));
        $onlyMine    = !empty($_GET['mine']) && $currentUser && $currentUser['role'] === 'coach';
        $articles    = $articleController->getAllArticles($search, $onlyMine ? (int)$currentUser['id'] : null);
        $currentPage = 'articles';
        require __DIR__ . '/Views/Frontoffice/listArticles.php';
        break;

    case 'article_details':
        $currentUser = $authController->getCurrentUser();
        $articleId   = (int)($_GET['id'] ?? 0);
        $article     = $articleController->getArticleById($articleId);
        if (!$article) {
            AuthController::flash('Article not found.', 'error');
            header('Location: index.php?action=articles');
            exit;
        }
        $comments    = $commentController->getCommentsByArticle($articleId);
        $csrfToken   = $authController->getCsrfToken();
        $currentPage = 'articles';
        require __DIR__ . '/Views/Frontoffice/articleDetails.php';
        break;

    case 'create_comment':
        $currentUser = $authController->requireAuth();
        $articleId   = (int)($_GET['article_id'] ?? $_POST['article_id'] ?? 0);
        $article     = $articleController->getArticleById($articleId);
        if (!$article) {
            AuthController::flash('Target article not found.', 'error');
            header('Location: index.php?action=articles');
            exit;
        }
        $errors = [];
        $old    = ['content' => ''];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $old['content'] = trim((string)($_POST['content'] ?? ''));
            $errors         = $commentController->createComment($_POST, $currentUser);
            if (empty($errors)) {
                AuthController::flash('Your comment has been published.');
                header('Location: index.php?action=article_details&id=' . $articleId);
                exit;
            }
        }
        $csrfToken   = $authController->getCsrfToken();
        $currentPage = 'articles';
        require __DIR__ . '/Views/Frontoffice/createComment.php';
        break;

    case 'update_comment':
        $currentUser = $authController->requireAuth();
        $commentId   = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
        $comment     = $commentController->getCommentById($commentId);
        if (!$comment) {
            AuthController::flash('Comment not found.', 'error');
            header('Location: index.php?action=my_comments');
            exit;
        }
        if (!CommentController::canManageComment($comment, $currentUser)) {
            $authController->renderForbidden('You can only edit your own comments.', $currentUser);
        }
        $errors = [];
        $old    = ['content' => $comment->getContent()];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $old['content'] = trim((string)($_POST['content'] ?? ''));
            $errors         = $commentController->updateComment($commentId, $_POST, $currentUser);
            if (empty($errors)) {
                AuthController::flash('Comment updated successfully.');
                $redirect = ($currentUser['role'] === 'admin' && !empty($_POST['from_admin']))
                    ? 'admin_comments'
                    : 'article_details&id=' . $comment->getArticleId();
                header('Location: index.php?action=' . $redirect);
                exit;
            }
        }
        $csrfToken   = $authController->getCsrfToken();
        $fromAdmin   = !empty($_GET['from_admin']);
        $currentPage = 'my_comments';
        require __DIR__ . '/Views/Frontoffice/updateComment.php';
        break;

    case 'delete_comment':
        $currentUser = $authController->requireAuth();
        $commentId   = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
        $comment     = $commentController->getCommentById($commentId);
        if (!$comment) {
            AuthController::flash('Comment not found.', 'error');
            header('Location: index.php?action=my_comments');
            exit;
        }
        if (!CommentController::canManageComment($comment, $currentUser)) {
            $authController->renderForbidden('You can only delete your own comments.', $currentUser);
        }
        $errors    = [];
        $fromAdmin = !empty($_GET['from_admin']) || !empty($_POST['from_admin']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $articleId = $comment->getArticleId();
            $errors    = $commentController->deleteComment($commentId, $currentUser, $_POST['csrf_token'] ?? null);
            if (empty($errors)) {
                AuthController::flash('Comment deleted successfully.');
                $redirect = ($currentUser['role'] === 'admin' && $fromAdmin)
                    ? 'admin_comments'
                    : 'article_details&id=' . $articleId;
                header('Location: index.php?action=' . $redirect);
                exit;
            }
        }
        $csrfToken   = $authController->getCsrfToken();
        $currentPage = 'my_comments';
        require __DIR__ . '/Views/Frontoffice/deleteComment.php';
        break;

    case 'my_comments':
        $currentUser = $authController->requireAuth();
        $comments    = $commentController->getCommentsByUser((int)$currentUser['id']);
        $currentPage = 'my_comments';
        require __DIR__ . '/Views/Frontoffice/myComments.php';
        break;

    // =========================================================================
    // ARTICLE AUTHORING ROUTES (Admin: Backoffice | Coach: Frontoffice, own ONLY)
    // =========================================================================
    case 'create_article':
        $currentUser = $authController->requireArticlePublisher();
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
                AuthController::flash('Article published successfully!');
                $target = $currentUser['role'] === 'admin' ? 'admin_articles' : 'articles';
                header('Location: index.php?action=' . $target);
                exit;
            }
        }
        $csrfToken   = $authController->getCsrfToken();
        $currentPage = $currentUser['role'] === 'admin' ? 'admin_create_article' : 'create_article';
        require __DIR__ . '/Views/Backoffice/createArticle.php';
        break;

    case 'update_article':
        $currentUser = $authController->requireArticlePublisher();
        $articleId   = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
        $article     = $articleController->getArticleById($articleId);
        if (!$article) {
            AuthController::flash('Article not found.', 'error');
            header('Location: index.php?action=' . ($currentUser['role'] === 'admin' ? 'admin_articles' : 'articles'));
            exit;
        }
        if (!ArticleController::canManageArticle($article, $currentUser)) {
            $authController->renderForbidden('Coaches can only edit their own published articles.', $currentUser);
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
                AuthController::flash("Article #{$articleId} updated successfully.");
                $target = $currentUser['role'] === 'admin' ? 'admin_articles' : 'article_details&id=' . $articleId;
                header('Location: index.php?action=' . $target);
                exit;
            }
        }
        $csrfToken   = $authController->getCsrfToken();
        $currentPage = $currentUser['role'] === 'admin' ? 'admin_articles' : 'articles';
        require __DIR__ . '/Views/Backoffice/updateArticle.php';
        break;

    case 'delete_article':
        $currentUser = $authController->requireArticlePublisher();
        $articleId   = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
        $article     = $articleController->getArticleById($articleId);
        if (!$article) {
            AuthController::flash('Article not found.', 'error');
            header('Location: index.php?action=' . ($currentUser['role'] === 'admin' ? 'admin_articles' : 'articles'));
            exit;
        }
        if (!ArticleController::canManageArticle($article, $currentUser)) {
            $authController->renderForbidden('Coaches can only delete their own published articles.', $currentUser);
        }
        $errors = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = $articleController->deleteArticle($articleId, $currentUser, $_POST['csrf_token'] ?? null);
            if (empty($errors)) {
                AuthController::flash("Article #{$articleId} and its comments have been deleted.");
                $target = $currentUser['role'] === 'admin' ? 'admin_articles' : 'articles';
                header('Location: index.php?action=' . $target);
                exit;
            }
        }
        $csrfToken   = $authController->getCsrfToken();
        $currentPage = $currentUser['role'] === 'admin' ? 'admin_articles' : 'articles';
        require __DIR__ . '/Views/Backoffice/deleteArticle.php';
        break;

    // =========================================================================
    // BLOG BACKOFFICE ROUTES (ADMIN ONLY)
    // =========================================================================
    case 'admin_dashboard':
        $currentUser    = $authController->requireAdmin();
        $stats          = $articleController->getStats();
        $recentArticles = array_slice($articleController->getAllArticles(), 0, 5);
        $recentComments = array_slice($commentController->getAllComments(), 0, 5);
        $currentPage    = 'admin_dashboard';
        require __DIR__ . '/Views/Backoffice/dashboard.php';
        break;

    case 'admin_articles':
        $currentUser = $authController->requireAdmin();
        $search      = trim((string)($_GET['search'] ?? ''));
        $articles    = $articleController->getAllArticles($search);
        $currentPage = 'admin_articles';
        require __DIR__ . '/Views/Backoffice/listArticles.php';
        break;

    case 'admin_comments':
        $currentUser   = $authController->requireAdmin();
        $search        = trim((string)($_GET['search'] ?? ''));
        $filterArticle = isset($_GET['article_id']) && $_GET['article_id'] !== '' ? (int)$_GET['article_id'] : null;
        $comments      = $commentController->getAllComments($search, $filterArticle);
        $allArticles   = $articleController->getAllArticles();
        $currentPage   = 'admin_comments';
        require __DIR__ . '/Views/Backoffice/manageComments.php';
        break;

    default:
        http_response_code(404);
        $currentUser = $authController->getCurrentUser();
        $title       = '404 Not Found — HB FITNESS Blog';
        $currentPage = '404';
        ob_start();
        ?>
        <div class="container py-5">
            <div class="hb-card text-center mx-auto" style="max-width:520px;padding:3rem 2rem;">
                <h1 class="mb-2 text-coral">404</h1>
                <h3 class="mb-3">Page Not Found</h3>
                <p class="text-silver mb-4">The requested Blog route does not exist.</p>
                <a href="index.php?action=home" class="btn btn-hb-primary">Back to Blog Home</a>
            </div>
        </div>
        <?php
        $content = (string)ob_get_clean();
        require __DIR__ . '/Views/layout.php';
        break;
}
