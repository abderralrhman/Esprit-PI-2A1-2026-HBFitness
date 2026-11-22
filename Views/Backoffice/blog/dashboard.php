<?php
/**
 * Views/Backoffice/dashboard.php — Admin Blog Management Dashboard
 */
$title        = 'Blog Dashboard — HB FITNESS Backoffice';
$adminHeading = 'Blog Dashboard';
ob_start();
?>
<!-- KPI Row -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="hb-stat-card">
            <div>
                <div class="hb-stat-value" data-count="<?= (int)($stats['total_articles'] ?? 0) ?>">0</div>
                <div class="hb-stat-label">Total Articles</div>
            </div>
            <div class="hb-stat-icon"><i class="fa-solid fa-newspaper"></i></div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="hb-stat-card">
            <div>
                <div class="hb-stat-value" data-count="<?= (int)($stats['total_comments'] ?? 0) ?>">0</div>
                <div class="hb-stat-label">Total Comments</div>
            </div>
            <div class="hb-stat-icon"><i class="fa-solid fa-comments"></i></div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="hb-stat-card">
            <div>
                <div class="hb-stat-value" data-count="<?= (int)($stats['coach_articles'] ?? 0) ?>">0</div>
                <div class="hb-stat-label">Coach Articles</div>
            </div>
            <div class="hb-stat-icon" style="color:#fbbf24;background:rgba(245,158,11,0.14);"><i class="fa-solid fa-medal"></i></div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="hb-stat-card">
            <div>
                <div class="hb-stat-value" data-count="<?= (int)($stats['admin_articles'] ?? 0) ?>">0</div>
                <div class="hb-stat-label">Admin Articles</div>
            </div>
            <div class="hb-stat-icon"><i class="fa-solid fa-shield-halved"></i></div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="d-flex flex-wrap gap-2 mb-4">
    <a href="index.php?action=create_article" class="btn btn-hb-primary btn-sm">
        <i class="fa-solid fa-circle-plus me-1"></i>Publish New Article
    </a>
    <a href="index.php?action=admin_articles" class="btn btn-hb-outline btn-sm">
        <i class="fa-solid fa-list me-1"></i>Manage All Articles
    </a>
    <a href="index.php?action=admin_comments" class="btn btn-hb-outline btn-sm">
        <i class="fa-solid fa-comment-dots me-1"></i>Moderate Comments
    </a>
</div>

<div class="row g-4">
    <!-- Recent Articles -->
    <div class="col-lg-7">
        <div class="hb-card h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h5 mb-0">Recent Articles</h2>
                <a href="index.php?action=admin_articles" class="small">View All</a>
            </div>
            <?php if (empty($recentArticles)): ?>
                <p class="text-silver small mb-0">No articles published yet.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="hb-table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Author</th>
                                <th>Comments</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentArticles as $art): ?>
                                <?php $rBadge = $art->getAuthorRole() === 'admin' ? 'hb-badge-admin' : ($art->getAuthorRole() === 'coach' ? 'hb-badge-coach' : 'hb-badge-user'); ?>
                                <tr>
                                    <td>
                                        <a href="index.php?action=article_details&id=<?= $art->getId() ?>" class="text-white fw-semibold">
                                            <?= htmlspecialchars(mb_strimwidth($art->getTitle(), 0, 42, '...'), ENT_QUOTES, 'UTF-8') ?>
                                        </a>
                                    </td>
                                    <td>
                                        <span class="small d-block"><?= htmlspecialchars($art->getAuthorName(), ENT_QUOTES, 'UTF-8') ?></span>
                                        <span class="hb-badge <?= $rBadge ?>"><?= htmlspecialchars($art->getAuthorRole(), ENT_QUOTES, 'UTF-8') ?></span>
                                    </td>
                                    <td><?= $art->getCommentsCount() ?></td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="index.php?action=update_article&id=<?= $art->getId() ?>" class="btn btn-hb-outline btn-sm" title="Edit"><i class="fa-solid fa-pen"></i></a>
                                            <a href="index.php?action=delete_article&id=<?= $art->getId() ?>" class="btn btn-hb-danger btn-sm" title="Delete"><i class="fa-solid fa-trash"></i></a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recent Comments -->
    <div class="col-lg-5">
        <div class="hb-card h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h5 mb-0">Recent Comments</h2>
                <a href="index.php?action=admin_comments" class="small">Moderate All</a>
            </div>
            <?php if (empty($recentComments)): ?>
                <p class="text-silver small mb-0">No comments yet.</p>
            <?php else: ?>
                <div class="d-flex flex-column gap-3">
                    <?php foreach ($recentComments as $c): ?>
                        <div class="p-3 rounded-3" style="background:rgba(255,255,255,0.03);border:1px solid var(--hb-border);">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <strong class="small text-white"><?= htmlspecialchars($c->getAuthorName(), ENT_QUOTES, 'UTF-8') ?></strong>
                                <span class="small text-muted-hb"><?= htmlspecialchars(date('M d, H:i', strtotime($c->getCreatedAt() ?: 'now')), ENT_QUOTES, 'UTF-8') ?></span>
                            </div>
                            <p class="small text-silver mb-2"><?= htmlspecialchars(mb_strimwidth($c->getContent(), 0, 95, '...'), ENT_QUOTES, 'UTF-8') ?></p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="small text-muted-hb">On: <?= htmlspecialchars(mb_strimwidth($c->getArticleTitle(), 0, 32, '...'), ENT_QUOTES, 'UTF-8') ?></span>
                                <a href="index.php?action=delete_comment&id=<?= $c->getId() ?>&from_admin=1" class="small text-coral">Delete</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php
$content = (string)ob_get_clean();
require __DIR__ . '/../../layout_admin.php';
