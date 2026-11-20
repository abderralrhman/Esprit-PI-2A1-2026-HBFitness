<?php
require_once __DIR__ . '/../../controller/AuthController.php';
require_once __DIR__ . '/../../controller/article/CommentController.php';

$currentUser = AuthController::requireLogin();
$isAdmin     = AuthController::isAdmin();
$commentC    = new CommentController();
$comments    = $commentC->listComments();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Article Comments — List (HB FITNESS)</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/fitness.css">
</head>
<body>
<div class="app-wrapper">
    <?php
    $basePrefix = '../../';
    $activeNav  = 'comments';
    include __DIR__ . '/../partials/navbar.php';
    ?>

    <div class="container">
        <section class="crud-page-header">
            <div>
                <div class="breadcrumb-trail">
                    <a href="../../index.php?page=<?= $isAdmin ? 'admin' : 'user' ?>"><?= $isAdmin ? 'Admin Dashboard' : 'Athlete Hub' ?></a>
                    <span>/</span> <span>Articles</span> <span>/</span> <span>Comments</span>
                </div>
                <h1><i class="fa-solid fa-comments"></i> Article Comments</h1>
                <p>Browse and manage athlete replies across all fitness articles.</p>
            </div>
            <a href="createComment.php" class="btn btn-green" style="z-index:1;">
                <i class="fa-solid fa-reply"></i> Add New Comment
            </a>
        </section>

        <div class="crud-subnav">
            <a href="listArticles.php" class="subnav-pill"><i class="fa-solid fa-newspaper"></i> Articles List</a>
            <a href="createArticle.php" class="subnav-pill"><i class="fa-solid fa-pen-nib"></i> Create Article</a>
            <a href="listComments.php" class="subnav-pill active"><i class="fa-solid fa-comments"></i> Comments List</a>
            <a href="createComment.php" class="subnav-pill"><i class="fa-solid fa-reply"></i> Add Comment</a>
        </div>

        <div class="full-card">
            <div class="card-header">
                <div class="card-title"><i class="fa-solid fa-comment-dots"></i> All Comments (<?= count($comments) ?>)</div>
                <div class="search-input-wrap">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" class="form-control" placeholder="Search comments..." data-search-target=".comment-row">
                </div>
            </div>
            <div class="table-responsive">
                <table class="smart-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Article</th>
                            <th>Comment Content</th>
                            <th>Author</th>
                            <th>Badge</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($comments as $c):
                            $canEdit = $isAdmin || ((int)$c['user_id'] === (int)$currentUser['id']);
                        ?>
                        <tr class="comment-row">
                            <td><strong>#<?= (int)$c['id'] ?></strong></td>
                            <td><span class="badge badge-orange">#<?= (int)$c['article_id'] ?> <?= htmlspecialchars($c['article_title']) ?></span></td>
                            <td><?= htmlspecialchars($c['content']) ?></td>
                            <td><?= htmlspecialchars($c['author_name'] ?? 'Athlete') ?></td>
                            <td>
                                <?php if ((int)$c['is_coach_pick'] === 1): ?>
                                    <span class="badge badge-green"><i class="fa-solid fa-medal"></i> Coach Pick</span>
                                <?php else: ?>
                                    <span class="badge badge-amber">Discussion</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($canEdit): ?>
                                    <div style="display:flex;gap:0.4rem;">
                                        <a href="updateComment.php?id=<?= (int)$c['id'] ?>" class="btn btn-outline btn-sm"><i class="fa-solid fa-pen"></i> Edit</a>
                                        <a href="deleteComment.php?id=<?= (int)$c['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this comment?');"><i class="fa-solid fa-trash"></i></a>
                                    </div>
                                <?php else: ?>
                                    <span style="font-size:0.78rem;color:var(--slate-500);">Read-only</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script src="../assets/js/fitness.js"></script>
</body>
</html>
