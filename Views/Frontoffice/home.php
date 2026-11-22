<?php
/**
 * Views/Frontoffice/home.php — Dynamic HB FITNESS Blog Frontoffice Home
 */
$title = 'HB FITNESS — Blog & Community Articles';
$role  = $currentUser['role'] ?? null;
ob_start();
?>
<!-- Dynamic Hero Section -->
<section class="hb-hero">
    <div class="container">
        <div class="row align-items-center gy-5">
            <div class="col-lg-6 reveal">
                <span class="hb-eyebrow">
                    <i class="fa-solid fa-fire text-coral"></i>
                    HB FITNESS · TRAIN SMART. REUSE MORE.
                </span>

                <h1 class="hb-hero-title">
                    TRAIN SMART AND GROW<br>
                    <span id="hb-typed-word" class="typed-word">STRONGER</span>
                </h1>

                <p class="text-silver fs-5 mb-4" style="max-width: 540px;">
                    Explore science-backed training articles, nutrition guides, and athlete discussions crafted by HB FITNESS Coaches and Administrators.
                </p>

                <div class="d-flex flex-wrap gap-3">
                    <a href="index.php?action=articles" class="btn btn-hb-primary">
                        <i class="fa-solid fa-newspaper me-2"></i>Explore Articles
                    </a>

                    <?php if ($role === 'coach' || $role === 'admin'): ?>
                        <a href="index.php?action=create_article" class="btn btn-hb-outline">
                            <i class="fa-solid fa-pen-nib me-2"></i>Publish Article
                        </a>
                    <?php elseif (!$currentUser): ?>
                        <a href="index.php?action=login" class="btn btn-hb-outline">
                            <i class="fa-solid fa-right-to-bracket me-2"></i>Log In to Comment
                        </a>
                    <?php else: ?>
                        <a href="index.php?action=my_comments" class="btn btn-hb-outline">
                            <i class="fa-solid fa-comments me-2"></i>My Comments
                        </a>
                    <?php endif; ?>

                    <?php if ($role === 'admin'): ?>
                        <a href="index.php?action=admin_dashboard" class="btn btn-hb-outline">
                            <i class="fa-solid fa-gauge-high me-2"></i>Backoffice
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-lg-6 reveal">
                <div class="hb-hero-stage">
                    <div class="pulse-ring"></div>
                    <div class="pulse-ring ring-2"></div>

                    <div class="floating-chip chip-1">
                        <i class="fa-solid fa-newspaper text-coral"></i>
                        <span><strong data-count="<?= (int)($stats['total_articles'] ?? 0) ?>">0</strong> fitness articles</span>
                    </div>

                    <div class="floating-chip chip-2">
                        <i class="fa-solid fa-comments text-coral"></i>
                        <span><strong data-count="<?= (int)($stats['total_comments'] ?? 0) ?>">0</strong> community replies</span>
                    </div>

                    <div class="floating-chip chip-3">
                        <i class="fa-solid fa-medal text-warning"></i>
                        <span><strong data-count="<?= (int)($stats['coach_articles'] ?? 0) ?>">0</strong> coach guides</span>
                    </div>

                    <img src="Views/assets/images/hb-fitness-logo.svg" alt="HB FITNESS" width="185" class="position-relative" style="z-index:2;">

                    <div class="eq-bars position-relative" style="z-index:2;" aria-hidden="true">
                        <span class="eq-bar"></span>
                        <span class="eq-bar"></span>
                        <span class="eq-bar"></span>
                        <span class="eq-bar"></span>
                        <span class="eq-bar"></span>
                        <span class="eq-bar"></span>
                        <span class="eq-bar"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Live Blog Metrics Strip -->
<section class="container py-4">
    <div class="row g-3 reveal">
        <div class="col-md-4">
            <div class="hb-card d-flex align-items-center justify-content-between">
                <div>
                    <div class="hb-stat-value" data-count="<?= (int)($stats['total_articles'] ?? 0) ?>">0</div>
                    <div class="hb-stat-label">Published Articles</div>
                </div>
                <div class="hb-stat-icon"><i class="fa-solid fa-book-open"></i></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="hb-card d-flex align-items-center justify-content-between">
                <div>
                    <div class="hb-stat-value" data-count="<?= (int)($stats['total_comments'] ?? 0) ?>">0</div>
                    <div class="hb-stat-label">Athlete Comments</div>
                </div>
                <div class="hb-stat-icon"><i class="fa-solid fa-comments"></i></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="hb-card d-flex align-items-center justify-content-between">
                <div>
                    <div class="hb-stat-value" data-count="<?= (int)($stats['coach_articles'] ?? 0) ?>">0</div>
                    <div class="hb-stat-label">Coach-Authored Guides</div>
                </div>
                <div class="hb-stat-icon"><i class="fa-solid fa-medal"></i></div>
            </div>
        </div>
    </div>
</section>

<!-- Latest Articles Section -->
<section class="container py-5">
    <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4 reveal">
        <div>
            <span class="text-coral small fw-bold text-uppercase" style="letter-spacing:0.1em;">Latest From The Hub</span>
            <h2 class="mb-0 mt-1">Recent Fitness Articles</h2>
        </div>
        <a href="index.php?action=articles" class="btn btn-hb-outline btn-sm">
            View All Articles <i class="fa-solid fa-arrow-right ms-1"></i>
        </a>
    </div>

    <?php if (empty($latestArticles)): ?>
        <div class="hb-card hb-empty-state reveal">
            <i class="fa-solid fa-newspaper"></i>
            <h4>No Articles Published Yet</h4>
            <p class="mb-3">Check back soon or sign in as a Coach or Admin to publish the first fitness guide.</p>
            <?php if ($role === 'coach' || $role === 'admin'): ?>
                <a href="index.php?action=create_article" class="btn btn-hb-primary btn-sm">
                    <i class="fa-solid fa-pen-nib me-1"></i>Publish First Article
                </a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($latestArticles as $art): ?>
                <?php
                $aRole  = $art->getAuthorRole();
                $bClass = $aRole === 'admin' ? 'hb-badge-admin' : ($aRole === 'coach' ? 'hb-badge-coach' : 'hb-badge-user');
                ?>
                <div class="col-md-6 col-lg-4 reveal">
                    <article class="hb-card hb-card-interactive hb-article-card">
                        <div class="hb-article-meta">
                            <span class="hb-badge <?= $bClass ?>"><?= htmlspecialchars($aRole, ENT_QUOTES, 'UTF-8') ?></span>
                            <span><i class="fa-regular fa-user me-1"></i><?= htmlspecialchars($art->getAuthorName(), ENT_QUOTES, 'UTF-8') ?></span>
                            <span><i class="fa-regular fa-calendar me-1"></i><?= htmlspecialchars(date('M d, Y', strtotime($art->getPublishedAt() ?: 'now')), ENT_QUOTES, 'UTF-8') ?></span>
                        </div>

                        <h3 class="hb-article-title">
                            <a href="index.php?action=article_details&id=<?= $art->getId() ?>">
                                <?= htmlspecialchars($art->getTitle(), ENT_QUOTES, 'UTF-8') ?>
                            </a>
                        </h3>

                        <p class="hb-article-excerpt">
                            <?= htmlspecialchars($art->getExcerpt(145), ENT_QUOTES, 'UTF-8') ?>
                        </p>

                        <div class="hb-article-footer">
                            <span class="small text-silver">
                                <i class="fa-regular fa-comment-dots me-1 text-coral"></i>
                                <?= $art->getCommentsCount() ?> comment<?= $art->getCommentsCount() === 1 ? '' : 's' ?>
                            </span>
                            <a href="index.php?action=article_details&id=<?= $art->getId() ?>" class="btn btn-hb-primary btn-sm">
                                Read More <i class="fa-solid fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
<?php
$content = (string)ob_get_clean();
require __DIR__ . '/../layout.php';
