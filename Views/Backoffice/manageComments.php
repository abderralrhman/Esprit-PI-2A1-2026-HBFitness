<?php
/**
 * Views/Backoffice/manageComments.php — Admin Comment Moderation (3-Table JOIN)
 */
$title        = 'Manage Comments — HB FITNESS Backoffice';
$adminHeading = 'Comments Moderation';
ob_start();
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h2 class="h4 mb-1">All Community Comments</h2>
        <p class="small text-silver mb-0">
            Showing <?= count($comments) ?> comment<?= count($comments) === 1 ? '' : 's' ?> (Comment JOIN Article JOIN User).
        </p>
    </div>
</div>

<!-- Search & Filter by Article -->
<form method="GET" action="index.php" class="hb-card p-3 mb-4">
    <input type="hidden" name="action" value="admin_comments">
    <div class="row g-2 align-items-center">
        <div class="col-md-5">
            <input
                type="text"
                name="search"
                class="form-control hb-input"
                placeholder="Search comment text, author, or article..."
                value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"
            >
        </div>
        <div class="col-md-4">
            <select name="article_id" class="form-select hb-select">
                <option value="">All Articles</option>
                <?php foreach ($allArticles as $art): ?>
                    <option value="<?= $art->getId() ?>" <?= $filterArticle === $art->getId() ? 'selected' : '' ?>>
                        #<?= $art->getId() ?> — <?= htmlspecialchars(mb_strimwidth($art->getTitle(), 0, 48, '...'), ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-hb-primary flex-grow-1">
                <i class="fa-solid fa-filter me-1"></i>Apply
            </button>
            <?php if ($search !== '' || $filterArticle !== null): ?>
                <a href="index.php?action=admin_comments" class="btn btn-hb-outline" title="Clear filters">
                    <i class="fa-solid fa-xmark"></i>
                </a>
            <?php endif; ?>
        </div>
    </div>
</form>

<div class="hb-card p-0 overflow-hidden">
    <?php if (empty($comments)): ?>
        <div class="hb-empty-state">
            <i class="fa-regular fa-comments"></i>
            <h5>No Comments Match Your Filter</h5>
            <p class="mb-0">Try resetting the search or article filter.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="hb-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Comment</th>
                        <th>Author</th>
                        <th>Related Article</th>
                        <th>Created</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($comments as $c): ?>
                        <?php $rBadge = $c->getAuthorRole() === 'admin' ? 'hb-badge-admin' : ($c->getAuthorRole() === 'coach' ? 'hb-badge-coach' : 'hb-badge-user'); ?>
                        <tr>
                            <td class="text-muted-hb">#<?= $c->getId() ?></td>
                            <td style="max-width:340px;">
                                <div class="small text-silver"><?= htmlspecialchars(mb_strimwidth($c->getContent(), 0, 140, '...'), ENT_QUOTES, 'UTF-8') ?></div>
                            </td>
                            <td>
                                <div class="small fw-semibold"><?= htmlspecialchars($c->getAuthorName(), ENT_QUOTES, 'UTF-8') ?></div>
                                <span class="hb-badge <?= $rBadge ?>"><?= htmlspecialchars($c->getAuthorRole(), ENT_QUOTES, 'UTF-8') ?></span>
                            </td>
                            <td>
                                <a href="index.php?action=article_details&id=<?= $c->getArticleId() ?>" class="small">
                                    <?= htmlspecialchars(mb_strimwidth($c->getArticleTitle(), 0, 40, '...'), ENT_QUOTES, 'UTF-8') ?>
                                </a>
                            </td>
                            <td class="small text-muted-hb">
                                <?= htmlspecialchars(date('Y-m-d H:i', strtotime($c->getCreatedAt() ?: 'now')), ENT_QUOTES, 'UTF-8') ?>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <a href="index.php?action=update_comment&id=<?= $c->getId() ?>&from_admin=1" class="btn btn-hb-outline btn-sm" title="Edit Comment">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <a href="index.php?action=delete_comment&id=<?= $c->getId() ?>&from_admin=1" class="btn btn-hb-danger btn-sm" title="Delete Comment">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<?php
$content = (string)ob_get_clean();
require __DIR__ . '/../layout_admin.php';
