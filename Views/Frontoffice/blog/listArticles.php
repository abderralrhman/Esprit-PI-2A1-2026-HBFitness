<?php
/**
 * Views/Frontoffice/listArticles.php — Browse & Search Blog Articles
 */
$title = 'Fitness Articles — HB FITNESS Blog';
$role  = $currentUser['role'] ?? null;
ob_start();
?>
<section class="container py-5">
    <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4 reveal">
        <div>
            <span class="text-coral small fw-bold text-uppercase" style="letter-spacing:0.1em;">HB FITNESS Knowledge Hub</span>
            <h1 class="h2 mb-1">All Fitness Articles</h1>
            <p class="text-silver mb-0">
                Showing <?= count($articles) ?> article<?= count($articles) === 1 ? '' : 's' ?>
                <?= !empty($onlyMine) ? 'authored by you' : '' ?>
                <?= $search !== '' ? 'matching "' . htmlspecialchars($search, ENT_QUOTES, 'UTF-8') . '"' : '' ?>.
            </p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <?php if ($role === 'coach'): ?>
                <a href="index.php?action=articles" class="btn btn-sm <?= empty($onlyMine) ? 'btn-hb-primary' : 'btn-hb-outline' ?>">
                    All Articles
                </a>
                <a href="index.php?action=articles&mine=1" class="btn btn-sm <?= !empty($onlyMine) ? 'btn-hb-primary' : 'btn-hb-outline' ?>">
                    <i class="fa-solid fa-medal me-1"></i>My Articles
                </a>
                <a href="index.php?action=create_article" class="btn btn-hb-primary btn-sm">
                    <i class="fa-solid fa-pen-nib me-1"></i>Publish Article
                </a>
            <?php elseif ($role === 'admin'): ?>
                <a href="index.php?action=create_article" class="btn btn-hb-primary btn-sm">
                    <i class="fa-solid fa-circle-plus me-1"></i>Publish Article
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Search Bar -->
    <form method="GET" action="index.php" class="hb-card mb-4 p-3 reveal">
        <input type="hidden" name="action" value="articles">
        <?php if (!empty($onlyMine)): ?>
            <input type="hidden" name="mine" value="1">
        <?php endif; ?>
        <div class="row g-2 align-items-center">
            <div class="col-md-9">
                <input
                    type="text"
                    name="search"
                    class="form-control hb-input"
                    placeholder="Search articles by title, topic, or author..."
                    value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"
                >
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-hb-primary flex-grow-1">
                    <i class="fa-solid fa-magnifying-glass me-1"></i>Search
                </button>
                <?php if ($search !== ''): ?>
                    <a href="index.php?action=articles<?= !empty($onlyMine) ? '&mine=1' : '' ?>" class="btn btn-hb-outline" title="Clear search">
                        <i class="fa-solid fa-xmark"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </form>

    <?php if (empty($articles)): ?>
        <div class="hb-card hb-empty-state reveal">
            <i class="fa-solid fa-file-circle-question"></i>
            <h4>No Matching Articles Found</h4>
            <p class="mb-3">Try a different keyword or clear the search filter.</p>
            <a href="index.php?action=articles" class="btn btn-hb-outline btn-sm">Reset Filters</a>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($articles as $art): ?>
                <?php
                $aRole     = $art->getAuthorRole();
                $bClass    = $aRole === 'admin' ? 'hb-badge-admin' : ($aRole === 'coach' ? 'hb-badge-coach' : 'hb-badge-user');
                $canManage = ArticleController::canManageArticle($art, $currentUser);
                ?>
                <div class="col-md-6 col-lg-4 reveal">
                    <article class="hb-card hb-card-interactive hb-article-card">
                        <div class="hb-article-meta">
                            <span class="hb-badge <?= $bClass ?>"><?= htmlspecialchars($aRole, ENT_QUOTES, 'UTF-8') ?></span>
                            <span><i class="fa-regular fa-user me-1"></i><?= htmlspecialchars($art->getAuthorName(), ENT_QUOTES, 'UTF-8') ?></span>
                            <?php if ($art->getRecipeId() !== null): ?>
                                <span class="hb-badge hb-badge-recipe">
                                    <i class="fa-solid fa-utensils"></i>Recipe #<?= $art->getRecipeId() ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <h2 class="hb-article-title">
                            <a href="index.php?action=article_details&id=<?= $art->getId() ?>">
                                <?= htmlspecialchars($art->getTitle(), ENT_QUOTES, 'UTF-8') ?>
                            </a>
                        </h2>

                        <p class="hb-article-excerpt">
                            <?= htmlspecialchars($art->getExcerpt(155), ENT_QUOTES, 'UTF-8') ?>
                        </p>

                        <div class="small text-muted-hb mb-3">
                            <i class="fa-regular fa-clock me-1"></i>
                            Published <?= htmlspecialchars(date('M d, Y · H:i', strtotime($art->getPublishedAt() ?: 'now')), ENT_QUOTES, 'UTF-8') ?>
                        </div>

                        <div class="hb-article-footer">
                            <span class="small text-silver">
                                <i class="fa-regular fa-comments me-1 text-coral"></i>
                                <?= $art->getCommentsCount() ?>
                            </span>

                            <div class="d-flex gap-2 align-items-center">
                                <?php if ($canManage): ?>
                                    <a href="index.php?action=update_article&id=<?= $art->getId() ?>" class="btn btn-hb-outline btn-sm" title="Edit Article">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <a href="index.php?action=delete_article&id=<?= $art->getId() ?>" class="btn btn-hb-danger btn-sm" title="Delete Article">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                <?php endif; ?>
                                <a href="index.php?action=article_details&id=<?= $art->getId() ?>" class="btn btn-hb-primary btn-sm">
                                    Read <i class="fa-solid fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
<?php
$content = (string)ob_get_clean();
require __DIR__ . '/../../layout.php';
