<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HB FITNESS — <?= ($currentUser['role'] ?? '') === 'coach' ? 'Coach Studio' : 'Athlete Portal' ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="view/assets/css/fitness.css">
</head>
<body>
<div class="app-wrapper">
    <?php
    $basePrefix = '';
    $activeNav  = 'user';
    include __DIR__ . '/partials/navbar.php';
    ?>

    <div class="container">
        <!-- Hero Banner -->
        <section class="crud-page-header">
            <div>
                <div class="breadcrumb-trail">
                    <a href="index.php?page=landing">Home</a> <span>/</span> <span><?= $currentUser['role'] === 'coach' ? 'Coach Studio' : 'Athlete Community Hub' ?></span>
                </div>
                <h1><i class="fa-solid fa-fire-flame-curved"></i> Welcome, <?= htmlspecialchars($currentUser['full_name']) ?></h1>
                <p><?= $currentUser['role'] === 'coach'
                    ? 'Publish featured training guides, highlight Coach Pick comments, and explore sports disciplines.'
                    : 'Publish training articles, join discussions in the comments, and explore sports tailored to your fitness goals.' ?></p>
            </div>
            <div style="display:flex;gap:0.65rem;flex-wrap:wrap;z-index:1;">
                <a href="view/article/createArticle.php" class="btn btn-green">
                    <i class="fa-solid fa-pen-nib"></i> Write Article
                </a>
                <a href="view/article/createComment.php" class="btn btn-outline" >
                    <i class="fa-solid fa-reply"></i> Add Comment
                </a>
                <a href="view/sports/sports.php" class="btn btn-outline" >
                    <i class="fa-solid fa-person-running"></i> Sports View
                </a>
            </div>
        </section>

        <!-- Athlete KPI Overview -->
        <section class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-info">
                    <span>Published Articles</span>
                    <h3 class="counter-value" data-target="<?= count($articles) ?>">0</h3>
                </div>
                <div class="kpi-icon"><i class="fa-solid fa-newspaper"></i></div>
            </div>
            <div class="kpi-card">
                <div class="kpi-info">
                    <span>Community Comments</span>
                    <h3 class="counter-value" data-target="<?= count($comments) ?>">0</h3>
                </div>
                <div class="kpi-icon"><i class="fa-solid fa-comment-dots"></i></div>
            </div>
            <div class="kpi-card">
                <div class="kpi-info">
                    <span>My Articles</span>
                    <?php
                    $myArticlesCount = count(array_filter($articles, fn($a) => (int)$a['user_id'] === (int)$currentUser['id']));
                    ?>
                    <h3 class="counter-value" data-target="<?= $myArticlesCount ?>">0</h3>
                </div>
                <div class="kpi-icon"><i class="fa-solid fa-feather-pointed"></i></div>
            </div>
            <div class="kpi-card">
                <div class="kpi-info">
                    <span>Total Article Likes</span>
                    <?php
                    $totalLikesUser = array_reduce($articles, fn($c, $i) => $c + (int)$i['likes_count'], 0);
                    ?>
                    <h3 class="counter-value" data-target="<?= $totalLikesUser ?>">0</h3>
                </div>
                <div class="kpi-icon"><i class="fa-solid fa-thumbs-up"></i></div>
            </div>
        </section>

        <!-- Athlete Quick-Access Module Cards -->
        <section class="module-hub-grid">
            <div class="module-hub-card">
                <div>
                    <div class="feature-icon" style="width:48px;height:48px;font-size:1.2rem;"><i class="fa-solid fa-newspaper"></i></div>
                    <h3 style="color:var(--navy-900);margin-bottom:0.35rem;">Fitness Articles</h3>
                    <p style="font-size:0.86rem;color:var(--slate-700);">Browse training guides, publish your own workout plans, upvote favorites, or edit your posts.</p>
                </div>
                <div class="module-hub-actions">
                    <a href="view/article/listArticles.php" class="btn btn-navy btn-sm"><i class="fa-solid fa-list"></i> Browse Articles</a>
                    <a href="view/article/createArticle.php" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus"></i> New Article</a>
                </div>
            </div>

            <div class="module-hub-card">
                <div>
                    <div class="feature-icon" style="width:48px;height:48px;font-size:1.2rem;"><i class="fa-solid fa-comments"></i></div>
                    <h3 style="color:var(--navy-900);margin-bottom:0.35rem;">Athlete Comments</h3>
                    <p style="font-size:0.86rem;color:var(--slate-700);">Reply to articles, share progress updates, and spot Coach Pick tips from certified trainers.</p>
                </div>
                <div class="module-hub-actions">
                    <a href="view/article/listComments.php" class="btn btn-navy btn-sm"><i class="fa-solid fa-comment-dots"></i> All Comments</a>
                    <a href="view/article/createComment.php" class="btn btn-green btn-sm"><i class="fa-solid fa-reply"></i> Add Comment</a>
                </div>
            </div>

            <div class="module-hub-card">
                <div>
                    <div class="feature-icon" style="width:48px;height:48px;font-size:1.2rem;"><i class="fa-solid fa-person-running"></i></div>
                    <h3 style="color:var(--navy-900);margin-bottom:0.35rem;">Sports Explorer</h3>
                    <p style="font-size:0.86rem;color:var(--slate-700);">Discover sports by intensity, burn rate, and muscle focus, plus calculate your BMI & heart-rate zone.</p>
                </div>
                <div class="module-hub-actions">
                    <a href="view/sports/sports.php" class="btn btn-primary btn-sm"><i class="fa-solid fa-trophy"></i> Explore Sports</a>
                </div>
            </div>
        </section>

        <!-- Featured Articles Preview -->
        <div class="full-card">
            <div class="card-header">
                <div class="card-title"><i class="fa-solid fa-bolt"></i> Featured & Recent Fitness Articles</div>
                <a href="view/article/listArticles.php" class="btn btn-outline btn-sm">See All Articles &rarr;</a>
            </div>
            <div class="cards-grid">
                <?php foreach (array_slice($articles, 0, 3) as $art): ?>
                <div class="entity-card">
                    <div>
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.65rem;">
                            <span class="badge badge-orange"><?= htmlspecialchars($art['category']) ?></span>
                            <span class="badge badge-<?= htmlspecialchars($art['status']) ?>"><?= htmlspecialchars($art['status']) ?></span>
                        </div>
                        <h4 style="color:var(--navy-900);margin-bottom:0.4rem;"><?= htmlspecialchars($art['title']) ?></h4>
                        <p style="font-size:0.86rem;color:var(--slate-700);margin-bottom:0.9rem;"><?= htmlspecialchars(mb_strimwidth($art['content'], 0, 150, '…')) ?></p>
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center;padding-top:0.75rem;border-top:1px solid var(--slate-100);">
                        <span style="font-size:0.8rem;color:var(--slate-500);">
                            <i class="fa-solid fa-thumbs-up" style="color:var(--orange-600);"></i> <?= (int)$art['likes_count'] ?> ·
                            <i class="fa-solid fa-comment" style="color:var(--emerald-600);"></i> <?= (int)$art['comments_count'] ?>
                        </span>
                        <a href="view/article/createComment.php?article_id=<?= (int)$art['id'] ?>" class="btn btn-green btn-sm">
                            <i class="fa-solid fa-reply"></i> Comment
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<script src="view/assets/js/fitness.js"></script>
</body>
</html>
