<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HB FITNESS — Coach Studio</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="view/assets/css/fitness.css?v=3">
</head>
<body>
<div class="app-wrapper">
    <?php
    $basePrefix = '';
    $activeNav  = 'coach';
    include __DIR__ . '/partials/navbar.php';
    ?>

    <div class="container">
        <?php
        $featuredCount  = count(array_filter($articles, fn($a) => $a['status'] === 'Featured'));
        $coachPickCount = count(array_filter($comments, fn($c) => (int)$c['is_coach_pick'] === 1));
        $myArticles     = array_filter($articles, fn($a) => (int)$a['user_id'] === (int)$currentUser['id']);
        $totalLikes     = array_reduce($articles, fn($carry, $a) => $carry + (int)$a['likes_count'], 0);
        ?>

        <!-- Coach Studio Hero Banner -->
        <section class="crud-page-header">
            <div>
                <div class="breadcrumb-trail">
                    <a href="index.php?page=landing">Home</a> <span>/</span> <span>Coach Studio</span>
                </div>
                <h1><i class="fa-solid fa-medal"></i> Coach Studio — <?= htmlspecialchars($currentUser['full_name']) ?></h1>
                <p>Publish featured training guides, highlight Coach Pick answers for athletes, and curate sports performance content.</p>
            </div>
            <div style="display:flex;gap:0.65rem;flex-wrap:wrap;z-index:1;">
                <a href="view/article/createArticle.php" class="btn btn-primary">
                    <i class="fa-solid fa-pen-nib"></i> Publish Article
                </a>
                <a href="view/article/listComments.php" class="btn btn-green">
                    <i class="fa-solid fa-award"></i> Review Coach Picks
                </a>
                <a href="view/sports/sports.php" class="btn btn-outline">
                    <i class="fa-solid fa-person-running"></i> Sports Explorer
                </a>
            </div>
        </section>

        <!-- Coach KPI Stats -->
        <section class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-info">
                    <span>Total Articles (<?= $featuredCount ?> Featured)</span>
                    <h3 class="counter-value" data-target="<?= count($articles) ?>">0</h3>
                </div>
                <div class="kpi-icon"><i class="fa-solid fa-newspaper"></i></div>
            </div>
            <div class="kpi-card">
                <div class="kpi-info">
                    <span>Coach Pick Tips</span>
                    <h3 class="counter-value" data-target="<?= $coachPickCount ?>">0</h3>
                </div>
                <div class="kpi-icon"><i class="fa-solid fa-medal"></i></div>
            </div>
            <div class="kpi-card">
                <div class="kpi-info">
                    <span>My Published Guides</span>
                    <h3 class="counter-value" data-target="<?= count($myArticles) ?>">0</h3>
                </div>
                <div class="kpi-icon"><i class="fa-solid fa-feather-pointed"></i></div>
            </div>
            <div class="kpi-card">
                <div class="kpi-info">
                    <span>Community Engagement (Likes)</span>
                    <h3 class="counter-value" data-target="<?= $totalLikes ?>">0</h3>
                </div>
                <div class="kpi-icon"><i class="fa-solid fa-fire"></i></div>
            </div>
        </section>

        <!-- Coach Modules Grid -->
        <section class="module-hub-grid">
            <div class="module-hub-card">
                <div>
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.85rem;">
                        <div class="feature-icon" style="width:48px;height:48px;font-size:1.2rem;margin-bottom:0;">
                            <i class="fa-solid fa-newspaper"></i>
                        </div>
                        <span class="badge badge-orange">Coach Editorial</span>
                    </div>
                    <h3 style="color:#fff;margin-bottom:0.35rem;">Training Articles</h3>
                    <p style="font-size:0.86rem;color:var(--hb-silver);">
                        Write science-backed workout &amp; nutrition guides, feature top community articles, or archive outdated posts.
                    </p>
                </div>
                <div class="module-hub-actions">
                    <a href="view/article/listArticles.php" class="btn btn-navy btn-sm"><i class="fa-solid fa-list"></i> Manage Articles (<?= count($articles) ?>)</a>
                    <a href="view/article/createArticle.php" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus"></i> New Guide</a>
                </div>
            </div>

            <div class="module-hub-card">
                <div>
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.85rem;">
                        <div class="feature-icon" style="width:48px;height:48px;font-size:1.2rem;margin-bottom:0;">
                            <i class="fa-solid fa-comments"></i>
                        </div>
                        <span class="badge badge-navy">Coach Picks</span>
                    </div>
                    <h3 style="color:#fff;margin-bottom:0.35rem;">Athlete Q&amp;A &amp; Comments</h3>
                    <p style="font-size:0.86rem;color:var(--hb-silver);">
                        Answer athlete questions on articles and award the verified Coach Pick badge to the best tips.
                    </p>
                </div>
                <div class="module-hub-actions">
                    <a href="view/article/listComments.php" class="btn btn-navy btn-sm"><i class="fa-solid fa-comment-dots"></i> All Comments (<?= count($comments) ?>)</a>
                    <a href="view/article/createComment.php" class="btn btn-green btn-sm"><i class="fa-solid fa-reply"></i> Post Coach Tip</a>
                </div>
            </div>

            <div class="module-hub-card">
                <div>
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.85rem;">
                        <div class="feature-icon" style="width:48px;height:48px;font-size:1.2rem;margin-bottom:0;">
                            <i class="fa-solid fa-person-running"></i>
                        </div>
                        <span class="badge badge-orange">Performance Lab</span>
                    </div>
                    <h3 style="color:#fff;margin-bottom:0.35rem;">Sports &amp; Zones Explorer</h3>
                    <p style="font-size:0.86rem;color:var(--hb-silver);">
                        Review 8 athletic disciplines, burn rates, and use the interactive BMI &amp; heart-rate zone calculator.
                    </p>
                </div>
                <div class="module-hub-actions">
                    <a href="view/sports/sports.php" class="btn btn-primary btn-sm"><i class="fa-solid fa-trophy"></i> Open Sports Lab</a>
                </div>
            </div>
        </section>

        <!-- Dual Coaching Workspace: Editorial Queue + Recent Athlete Comments -->
        <div class="dual-grid">
            <div class="glass-card">
                <div class="card-header">
                    <div class="card-title"><i class="fa-solid fa-star"></i> Articles Editorial Queue</div>
                    <a href="view/article/listArticles.php" class="btn btn-outline btn-sm">All Articles &rarr;</a>
                </div>
                <div class="table-responsive">
                    <table class="smart-table">
                        <thead>
                            <tr>
                                <th>Article</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Moderate</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($articles, 0, 5) as $art): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($art['title']) ?></strong>
                                    <div style="font-size:0.76rem;color:var(--hb-silver);">by <?= htmlspecialchars($art['author_name'] ?? 'Athlete') ?></div>
                                </td>
                                <td><span class="badge badge-orange"><?= htmlspecialchars($art['category']) ?></span></td>
                                <td><span class="badge badge-<?= htmlspecialchars($art['status']) ?>"><?= htmlspecialchars($art['status']) ?></span></td>
                                <td>
                                    <a href="view/article/updateArticle.php?id=<?= (int)$art['id'] ?>" class="btn btn-outline btn-sm" title="Edit / Feature">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="glass-card">
                <div class="card-header">
                    <div class="card-title"><i class="fa-solid fa-medal"></i> Athlete Comments &amp; Coach Picks</div>
                    <a href="view/article/listComments.php" class="btn btn-outline btn-sm">All Comments &rarr;</a>
                </div>
                <div class="table-responsive">
                    <table class="smart-table">
                        <thead>
                            <tr>
                                <th>Comment</th>
                                <th>Badge</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($comments, 0, 5) as $cm): ?>
                            <tr>
                                <td>
                                    <div style="font-size:0.85rem;color:#fff;"><?= htmlspecialchars(mb_strimwidth($cm['content'], 0, 85, '…')) ?></div>
                                    <div style="font-size:0.75rem;color:var(--hb-silver);">
                                        <?= htmlspecialchars($cm['author_name'] ?? 'Athlete') ?> on <em><?= htmlspecialchars(mb_strimwidth($cm['article_title'], 0, 35, '…')) ?></em>
                                    </div>
                                </td>
                                <td>
                                    <?php if ((int)$cm['is_coach_pick'] === 1): ?>
                                        <span class="badge badge-orange"><i class="fa-solid fa-medal"></i> Coach Pick</span>
                                    <?php else: ?>
                                        <span class="badge badge-amber">Standard</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="view/article/updateComment.php?id=<?= (int)$cm['id'] ?>" class="btn btn-outline btn-sm" title="Edit / Toggle Coach Pick">
                                        <i class="fa-solid fa-award"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="view/assets/js/fitness.js?v=3"></script>
</body>
</html>
