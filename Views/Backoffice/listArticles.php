<?php
/**
 * Views/Backoffice/listArticles.php — Admin All Articles Management Table
 */
$title        = 'Manage Articles — HB FITNESS Backoffice';
$adminHeading = 'All Blog Articles';
ob_start();
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h2 class="h4 mb-1">Articles Directory</h2>
        <p class="small text-silver mb-0">Full CRUD management over all Admin and Coach published articles.</p>
    </div>
    <a href="index.php?action=create_article" class="btn btn-hb-primary btn-sm">
        <i class="fa-solid fa-circle-plus me-1"></i>Publish Article
    </a>
</div>

<form method="GET" action="index.php" class="hb-card p-3 mb-4">
    <input type="hidden" name="action" value="admin_articles">
    <div class="row g-2 align-items-center">
        <div class="col-md-9">
            <input
                type="text"
                name="search"
                class="form-control hb-input"
                placeholder="Search by title, content, or author..."
                value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"
            >
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-hb-primary flex-grow-1">
                <i class="fa-solid fa-magnifying-glass me-1"></i>Filter
            </button>
            <?php if ($search !== ''): ?>
                <a href="index.php?action=admin_articles" class="btn btn-hb-outline"><i class="fa-solid fa-xmark"></i></a>
            <?php endif; ?>
        </div>
    </div>
</form>

<div class="hb-card p-0 overflow-hidden">
    <?php if (empty($articles)): ?>
        <div class="hb-empty-state">
            <i class="fa-solid fa-newspaper"></i>
            <h5>No Articles Found</h5>
            <p class="mb-3">Publish a new article or clear your search filter.</p>
            <a href="index.php?action=create_article" class="btn btn-hb-primary btn-sm">Publish Article</a>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="hb-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Author &amp; Role</th>
                        <th>Recipe</th>
                        <th>Published</th>
                        <th>Comments</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($articles as $art): ?>
                        <?php $rBadge = $art->getAuthorRole() === 'admin' ? 'hb-badge-admin' : ($art->getAuthorRole() === 'coach' ? 'hb-badge-coach' : 'hb-badge-user'); ?>
                        <tr>
                            <td class="text-muted-hb">#<?= $art->getId() ?></td>
                            <td>
                                <a href="index.php?action=article_details&id=<?= $art->getId() ?>" class="text-white fw-semibold d-block">
                                    <?= htmlspecialchars($art->getTitle(), ENT_QUOTES, 'UTF-8') ?>
                                </a>
                                <span class="small text-muted-hb"><?= htmlspecialchars($art->getExcerpt(70), ENT_QUOTES, 'UTF-8') ?></span>
                            </td>
                            <td>
                                <div class="small fw-semibold"><?= htmlspecialchars($art->getAuthorName(), ENT_QUOTES, 'UTF-8') ?></div>
                                <span class="hb-badge <?= $rBadge ?>"><?= htmlspecialchars($art->getAuthorRole(), ENT_QUOTES, 'UTF-8') ?></span>
                            </td>
                            <td>
                                <?php if ($art->getRecipeId() !== null): ?>
                                    <span class="hb-badge hb-badge-recipe">#<?= $art->getRecipeId() ?></span>
                                <?php else: ?>
                                    <span class="text-muted-hb small">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="small text-silver">
                                <?= htmlspecialchars(date('Y-m-d H:i', strtotime($art->getPublishedAt() ?: 'now')), ENT_QUOTES, 'UTF-8') ?>
                            </td>
                            <td>
                                <a href="index.php?action=admin_comments&article_id=<?= $art->getId() ?>" class="small">
                                    <i class="fa-regular fa-comments me-1"></i><?= $art->getCommentsCount() ?>
                                </a>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <a href="index.php?action=article_details&id=<?= $art->getId() ?>" class="btn btn-hb-outline btn-sm" title="View"><i class="fa-solid fa-eye"></i></a>
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
<?php
$content = (string)ob_get_clean();
require __DIR__ . '/../layout_admin.php';
