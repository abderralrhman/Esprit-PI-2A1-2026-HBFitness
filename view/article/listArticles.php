<?php
require_once __DIR__ . '/../../controller/AuthController.php';
require_once __DIR__ . '/../../controller/article/ArticleController.php';

$currentUser = AuthController::requireLogin();
$isAdmin     = AuthController::isAdmin();
$isCoach     = AuthController::isCoach();
$isStaff     = AuthController::isStaff();
$articleC    = new ArticleController();

// Handle inline upvote POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'like') {
    $aid   = (int)($_POST['id'] ?? 0);
    $liked = $_SESSION['liked_articles'] ?? [];
    if ($aid > 0 && !in_array($aid, $liked, true)) {
        $articleC->likeArticle($aid);
        $_SESSION['liked_articles'][] = $aid;
        AuthController::flash("Upvoted fitness article!");
    } else {
        AuthController::flash("You already liked this article in this session.", 'error');
    }
    $catParam = isset($_GET['category']) && $_GET['category'] !== '' ? '?category=' . urlencode($_GET['category']) : '';
    header("Location: listArticles.php" . $catParam);
    exit;
}

$filterCat = trim($_GET['category'] ?? '');
$articles  = $articleC->listArticles($filterCat !== '' ? $filterCat : null);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fitness Articles — List (HB FITNESS)</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/fitness.css">
</head>
<body>
<div class="app-wrapper">
    <?php
    $basePrefix = '../../';
    $activeNav  = 'articles';
    include __DIR__ . '/../partials/navbar.php';
    ?>

    <div class="container">
        <section class="crud-page-header">
            <div>
                <div class="breadcrumb-trail">
                    <a href="../../index.php?page=<?= $isAdmin ? 'admin' : ($isCoach ? 'coach' : 'user') ?>"><?= $isAdmin ? 'Admin Dashboard' : ($isCoach ? 'Coach Studio' : 'Athlete Hub') ?></a>
                    <span>/</span> <span>Fitness Articles</span>
                    <?php if ($filterCat !== ''): ?>
                        <span>/</span> <span><?= htmlspecialchars($filterCat) ?></span>
                    <?php endif; ?>
                </div>
                <h1><i class="fa-solid fa-newspaper"></i> Fitness Articles<?= $filterCat !== '' ? ' — ' . htmlspecialchars($filterCat) : '' ?></h1>
                <p>Explore, publish, and upvote workout, nutrition, and sport performance articles.</p>
            </div>
            <div style="display:flex;gap:0.6rem;flex-wrap:wrap;z-index:1;">
                <?php if ($filterCat !== ''): ?>
                    <a href="listArticles.php" class="btn btn-outline" >
                        <i class="fa-solid fa-filter-circle-xmark"></i> Clear Filter
                    </a>
                <?php endif; ?>
                <a href="createArticle.php" class="btn btn-green">
                    <i class="fa-solid fa-plus"></i> New Article
                </a>
            </div>
        </section>

        <div class="crud-subnav">
            <a href="listArticles.php" class="subnav-pill active"><i class="fa-solid fa-newspaper"></i> Articles List</a>
            <a href="createArticle.php" class="subnav-pill"><i class="fa-solid fa-pen-nib"></i> Create Article</a>
            <a href="listComments.php" class="subnav-pill"><i class="fa-solid fa-comments"></i> Comments List</a>
            <a href="createComment.php" class="subnav-pill"><i class="fa-solid fa-reply"></i> Add Comment</a>
        </div>

        <div class="full-card">
            <div class="card-header">
                <div class="card-title"><i class="fa-solid fa-list-check"></i> All Articles (<?= count($articles) ?>)</div>
                <div class="search-input-wrap">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" class="form-control" placeholder="Search articles or categories..." data-search-target=".article-row">
                </div>
            </div>
            <div class="table-responsive">
                <table class="smart-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title & Excerpt</th>
                            <th>Category</th>
                            <th>Author</th>
                            <th>Engagement</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($articles as $a):
                            $canEdit = $isStaff || ((int)$a['user_id'] === (int)$currentUser['id']);
                        ?>
                        <tr class="article-row">
                            <td><strong>#<?= (int)$a['id'] ?></strong></td>
                            <td>
                                <strong><?= htmlspecialchars($a['title']) ?></strong>
                                <div style="font-size:0.78rem;color:var(--slate-500);"><?= htmlspecialchars($a['content']) ?></div>
                                <div style="font-size:0.74rem;color:var(--orange-600);margin-top:0.2rem;">
                                    <i class="fa-regular fa-clock"></i> <?= (int)$a['read_time'] ?> min read
                                    <?php if (!empty($a['tags'])): ?> · <i class="fa-solid fa-tags"></i> <?= htmlspecialchars($a['tags']) ?><?php endif; ?>
                                </div>
                            </td>
                            <td><span class="badge badge-orange"><?= htmlspecialchars($a['category']) ?></span></td>
                            <td><?= htmlspecialchars($a['author_name'] ?? 'Athlete') ?></td>
                            <td>
                                <div style="display:flex;gap:0.35rem;align-items:center;">
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="like">
                                        <input type="hidden" name="id" value="<?= (int)$a['id'] ?>">
                                        <button type="submit" class="btn btn-outline btn-sm" title="Upvote">
                                            <i class="fa-solid fa-thumbs-up" style="color:var(--orange-600);"></i> <?= (int)$a['likes_count'] ?>
                                        </button>
                                    </form>
                                    <a href="createComment.php?article_id=<?= (int)$a['id'] ?>" class="badge badge-green" style="text-decoration:none;">
                                        <i class="fa-solid fa-comment"></i> <?= (int)$a['comments_count'] ?>
                                    </a>
                                </div>
                            </td>
                            <td><span class="badge badge-<?= htmlspecialchars($a['status']) ?>"><?= htmlspecialchars($a['status']) ?></span></td>
                            <td>
                                <div style="display:flex;gap:0.4rem;">
                                    <a href="createComment.php?article_id=<?= (int)$a['id'] ?>" class="btn btn-green btn-sm" title="Comment"><i class="fa-solid fa-reply"></i></a>
                                    <?php if ($canEdit): ?>
                                        <a href="updateArticle.php?id=<?= (int)$a['id'] ?>" class="btn btn-outline btn-sm"><i class="fa-solid fa-pen"></i> Edit</a>
                                        <a href="deleteArticle.php?id=<?= (int)$a['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this article and all its comments?');"><i class="fa-solid fa-trash"></i></a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($articles)): ?>
                        <tr>
                            <td colspan="7" style="text-align:center;padding:2rem;color:var(--slate-500);">
                                No articles found<?= $filterCat !== '' ? ' in ' . htmlspecialchars($filterCat) : '' ?>. Be the first to <a href="createArticle.php" style="color:var(--orange-600);font-weight:700;">write one</a>!
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script src="../assets/js/fitness.js"></script>
</body>
</html>
