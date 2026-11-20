<?php
/**
 * FitPulse Fitness — Front Controller (MVC router)
 * Routes: ?page=landing (public) | ?page=sports (public/member) | ?page=user (logged-in) | ?page=admin (admins only) | ?action=logout
 * Login & signup live in view/auth/login.php and view/auth/signup.php
 */
require_once __DIR__ . '/controller/AuthController.php';
require_once __DIR__ . '/controller/article/ArticleController.php';
require_once __DIR__ . '/controller/article/CommentController.php';

$userC    = new UserController();
$articleC = new ArticleController();
$commentC = new CommentController();

// ---------------------------------------------------------------
// 1. Session actions
// ---------------------------------------------------------------
$getAction = $_GET['action'] ?? '';
if ($getAction === 'logout') {
    AuthController::logout();
    AuthController::flash('You have been logged out successfully. Keep pushing your limits!');
    AuthController::redirect('/index.php?page=landing');
}
if ($getAction === 'login') {
    AuthController::redirect('/view/auth/login.php');
}
if ($getAction === 'signup') {
    AuthController::redirect('/view/auth/signup.php');
}

$currentUser = AuthController::currentUser();
$isAdmin     = AuthController::isAdmin();

$page = $_GET['page'] ?? ($currentUser ? ($isAdmin ? 'admin' : 'user') : 'landing');
if (!in_array($page, ['landing', 'sports', 'admin', 'user'], true)) {
    $page = 'landing';
}

// ---------------------------------------------------------------
// 2. Public Sports Showcase redirect
// ---------------------------------------------------------------
if ($page === 'sports') {
    AuthController::redirect('/view/sports/sports.php');
}

// ---------------------------------------------------------------
// 3. Public landing page
// ---------------------------------------------------------------
if ($page === 'landing') {
    $allArticles = $articleC->listArticles();
    $allComments = $commentC->listComments();
    $stats = [
        'articles'    => count($allArticles),
        'featured'    => count(array_filter($allArticles, fn($a) => $a['status'] === 'Featured')),
        'comments'    => count($allComments),
        'athletes'    => count($userC->listUsers()),
        'total_likes' => array_sum(array_map(fn($a) => (int)$a['likes_count'], $allArticles)),
    ];
    $featuredArticles = array_slice(array_values(array_filter($allArticles, fn($a) => $a['status'] !== 'Archived')), 0, 3);
    [$flashMessage, $flashType] = AuthController::pullFlash();
    include __DIR__ . '/view/landing.php';
    exit;
}

// ---------------------------------------------------------------
// 4. Protected area (session required, admin page restricted)
// ---------------------------------------------------------------
if (!$currentUser) {
    if (!isset($_SESSION['flash'])) {
        AuthController::flash('Please log in to access your FitPulse space.', 'error');
    }
    AuthController::redirect('/view/auth/login.php');
}
if ($page === 'admin' && !$isAdmin) {
    AuthController::flash('Access denied: the admin back-office is reserved for administrators.', 'error');
    AuthController::redirect('/index.php?page=user');
}

$allowedTabs = $page === 'admin' ? ['articles', 'comments', 'users'] : ['articles', 'comments'];
$activeTab = $_GET['tab'] ?? 'articles';
if (!in_array($activeTab, $allowedTabs, true)) {
    $activeTab = 'articles';
}

/** Non-admin users may only modify records they own */
function assertOwnership(?array $record, array $currentUser, bool $isAdmin, string $label): void {
    if (!$record) {
        throw new Exception("$label not found.");
    }
    if (!$isAdmin && (int)$record['user_id'] !== (int)$currentUser['id']) {
        throw new Exception("Access denied: you can only modify your own $label.");
    }
}

// In-page validation helpers (contrôle de saisie)
$hasLetters = fn(string $v, int $n = 2): bool => (bool)preg_match('/[\p{L}]{' . $n . ',}/u', $v);
$isAlpha    = fn(string $v): bool => (bool)preg_match('/^[\p{L}\s\-\'\.&]+$/u', $v);

// ---------------------------------------------------------------
// 5. CRUD operations (POST)
// ---------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entity = $_POST['entity'] ?? '';
    $action = $_POST['crud_action'] ?? '';
    $uid    = (int)$currentUser['id'];
    $id     = (int)($_POST['id'] ?? 0);

    try {
        if ($entity === 'user' && !$isAdmin) {
            throw new Exception('Access denied: only administrators can manage user accounts.');
        }

        switch ($entity) {
            // ---------------- User (admin) ----------------
            case 'user':
                $activeTab = 'users';
                if ($action === 'create' || $action === 'update') {
                    $fullName = trim($_POST['full_name'] ?? '');
                    $email    = trim($_POST['email'] ?? '');
                    $password = $_POST['password'] ?? '';
                    $role     = $_POST['role'] ?? 'user';
                    $phone    = trim($_POST['phone'] ?? '');
                    $errors   = [];
                    if ($fullName === '' || mb_strlen($fullName) < 3 || !$isAlpha($fullName)) {
                        $errors[] = 'Full Name must contain only letters and spaces (min 3 chars, not empty).';
                    }
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $errors[] = 'Invalid email address.';
                    } elseif ($userC->emailExists($email, $action === 'update' ? $id : null)) {
                        $errors[] = 'This email is already registered.';
                    }
                    if (($action === 'create' || $password !== '') && mb_strlen($password) < 6) {
                        $errors[] = 'Password must be at least 6 characters.';
                    }
                    if (!in_array($role, ['admin', 'coach', 'user'], true)) {
                        $errors[] = 'Role must be admin, coach, or user.';
                    }
                    if ($phone !== '' && !preg_match('/^\+?[0-9\s\-]{8,20}$/', $phone)) {
                        $errors[] = 'Phone must contain 8 to 20 digits.';
                    }
                    if ($action === 'update' && $id === $uid && $role !== 'admin') {
                        $errors[] = 'You cannot remove your own administrator role.';
                    }
                    if ($errors) {
                        throw new Exception(implode(' | ', $errors));
                    }
                    $u = new User($action === 'create' ? null : $id, $fullName, $email, $password, $role, $phone !== '' ? $phone : null);
                    if ($action === 'create') {
                        $userC->addUser($u);
                        AuthController::flash('Athlete account created (password hashed).');
                    } else {
                        if (!$userC->getUserById($id)) {
                            throw new Exception('User not found.');
                        }
                        $userC->updateUser($u, $id);
                        AuthController::flash("User account #$id updated.");
                    }
                } elseif ($action === 'delete') {
                    if ($id === $uid) {
                        throw new Exception('You cannot delete your own account while logged in.');
                    }
                    $userC->deleteUser($id);
                    AuthController::flash('User account deleted.');
                }
                break;

            // ---------------- Article (owner or admin) ----------------
            case 'article':
                $activeTab = 'articles';
                $existing = null;
                if ($action === 'update' || $action === 'delete') {
                    $existing = $articleC->getArticleById($id);
                    assertOwnership($existing, $currentUser, $isAdmin, 'fitness article');
                }
                if ($action === 'like') {
                    if (!$articleC->getArticleById($id)) {
                        throw new Exception('Article not found.');
                    }
                    $liked = $_SESSION['liked_articles'] ?? [];
                    if (in_array($id, $liked, true)) {
                        throw new Exception('You already liked this article during this session.');
                    }
                    $articleC->likeArticle($id);
                    $_SESSION['liked_articles'][] = $id;
                    AuthController::flash('Article upvoted!');
                } elseif ($action === 'create' || $action === 'update') {
                    $title    = trim($_POST['title'] ?? '');
                    $content  = trim($_POST['content'] ?? '');
                    $category = trim($_POST['category'] ?? '');
                    $tags     = trim($_POST['tags'] ?? '');
                    $readTime = (int)($_POST['read_time'] ?? 5);
                    $status   = AuthController::isStaff() ? ($_POST['status'] ?? 'Published') : ($existing['status'] ?? 'Published');
                    $errors = [];
                    if (mb_strlen($title) < 5 || !$hasLetters($title)) {
                        $errors[] = 'Article title must contain letters (min 5 chars).';
                    }
                    if (mb_strlen($category) < 3 || !$isAlpha($category)) {
                        $errors[] = 'Category must contain letters only (min 3 chars).';
                    }
                    if (mb_strlen($content) < 10 || !$hasLetters($content, 3)) {
                        $errors[] = 'Content must be at least 10 characters with letters.';
                    }
                    if ($readTime < 1 || $readTime > 60) {
                        $errors[] = 'Read time must be between 1 and 60 minutes.';
                    }
                    if ($tags !== '' && !preg_match('/^[\p{L}0-9\s,\-_]+$/u', $tags)) {
                        $errors[] = 'Tags may only contain letters, digits and commas.';
                    }
                    if (!in_array($status, ['Published', 'Featured', 'Archived'], true)) {
                        $errors[] = 'Invalid status.';
                    }
                    if ($errors) {
                        throw new Exception(implode(' | ', $errors));
                    }
                    if ($action === 'create') {
                        $articleC->addArticle(new Article(null, $title, $content, $category, $tags, $readTime, 0, 0, $status, $uid));
                        AuthController::flash('Fitness article published.');
                    } else {
                        $articleC->updateArticle(new Article($id, $title, $content, $category, $tags, $readTime, (int)$existing['likes_count'], (int)$existing['views_count'], $status, (int)$existing['user_id']), $id);
                        AuthController::flash("Article #$id updated.");
                    }
                } elseif ($action === 'delete') {
                    $articleC->deleteArticle($id);
                    AuthController::flash('Fitness article deleted.');
                }
                break;

            // ---------------- Comment (owner or admin) ----------------
            case 'comment':
                $activeTab = 'comments';
                $existing = null;
                if ($action === 'update' || $action === 'delete') {
                    $existing = $commentC->getCommentById($id);
                    assertOwnership($existing, $currentUser, $isAdmin, 'comment');
                }
                if ($action === 'create' || $action === 'update') {
                    $articleId   = (!$isAdmin && $existing) ? (int)$existing['article_id'] : (int)($_POST['article_id'] ?? 0);
                    $content     = trim($_POST['content'] ?? '');
                    $isCoachPick = AuthController::isStaff() ? (isset($_POST['is_coach_pick']) ? 1 : 0) : ($existing ? (int)$existing['is_coach_pick'] : 0);
                    $errors = [];
                    if (!$articleC->getArticleById($articleId)) {
                        $errors[] = 'Parent article not found.';
                    }
                    if (mb_strlen($content) < 3 || !$hasLetters($content)) {
                        $errors[] = 'Comment cannot be empty and must contain letters (min 3 chars).';
                    }
                    if ($errors) {
                        throw new Exception(implode(' | ', $errors));
                    }
                    if ($action === 'create') {
                        $commentC->addComment(new Comment(null, $articleId, $uid, $content, $isCoachPick));
                        AuthController::flash('Comment added to the article.');
                    } else {
                        $commentC->updateComment(new Comment($id, $articleId, (int)$existing['user_id'], $content, $isCoachPick), $id);
                        AuthController::flash("Comment #$id updated.");
                    }
                } elseif ($action === 'delete') {
                    $commentC->deleteComment($id);
                    AuthController::flash('Comment deleted.');
                }
                break;

            default:
                throw new Exception('Unknown operation.');
        }
    } catch (PDOException $e) {
        AuthController::flash('Database error: ' . $e->getMessage(), 'error');
    } catch (Exception $e) {
        AuthController::flash('Contrôle de saisie: ' . $e->getMessage(), 'error');
    }

    AuthController::redirect("/index.php?page=$page&tab=$activeTab");
}

// ---------------------------------------------------------------
// 6. Render views
// ---------------------------------------------------------------
$users    = $isAdmin ? $userC->listUsers() : [];
$articles = $articleC->listArticles();
$comments = $commentC->listComments();

[$flashMessage, $flashType] = AuthController::pullFlash();

if ($page === 'admin') {
    include __DIR__ . '/view/admin_dashboard.php';
} else {
    include __DIR__ . '/view/user_portal.php';
}
?>
