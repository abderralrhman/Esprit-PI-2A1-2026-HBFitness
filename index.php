<?php
declare(strict_types=1);

/**
 * index.php — Blog Module Entry Point / Router (Integration-Ready)
 *
 * GITHUB INTEGRATION NOTE:
 * Do NOT overwrite the shared Esprit-PI-2A1-2026-HBFitness index.php with this file.
 * Instead, merge the Blog `switch ($action)` cases below into the shared front controller.
 * Authentication is delegated to Controllers/Blog/BlogAuthAdapter.php (shared session).
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
    $_SESSION['flash_message'] = 'Your session expired after 30 minutes of inactivity.';
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

// 4. Load Blog Controllers & Shared Auth Adapter
require_once __DIR__ . '/Controllers/Blog/BlogAuthAdapter.php';
require_once __DIR__ . '/Controllers/Blog/ArticleController.php';
require_once __DIR__ . '/Controllers/Blog/CommentController.php';

$blogAuth          = new BlogAuthAdapter();
$articleController = new ArticleController($blogAuth);
$commentController = new CommentController($blogAuth);

$action = (string)($_GET['action'] ?? 'home');
[$flashMessage, $flashType] = BlogAuthAdapter::pullFlash();

// 5. Blog Route Dispatch
switch ($action) {
    // =========================================================================
    // BLOG FRONTOFFICE ROUTES (Public Reading + Shared-Auth Commenting)
    // =========================================================================
    case 'home':
        $currentUser    = $blogAuth->getCurrentUser();
        $stats          = $articleController->getStats();
        $latestArticles = array_slice($articleController->getAllArticles(), 0, 6);
        $currentPage    = 'home';
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
    // ARTICLE AUTHORING ROUTES (Admin: Backoffice | Coach: Frontoffice, own ONLY)
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
    case 'admin_dashboard':
        $currentUser    = $blogAuth->requireAdmin();
        $stats          = $articleController->getStats();
        $recentArticles = array_slice($articleController->getAllArticles(), 0, 5);
        $recentComments = array_slice($commentController->getAllComments(), 0, 5);
        $currentPage    = 'admin_dashboard';
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
        http_response_code(404);
        $currentUser = $blogAuth->getCurrentUser();
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
