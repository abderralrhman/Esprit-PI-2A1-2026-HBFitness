<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HB FITNESS — Admin Command Center</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="view/assets/css/fitness.css">
</head>
<body>
<div class="app-wrapper">
    <?php
    $basePrefix = '';
    $activeNav  = 'admin';
    include __DIR__ . '/partials/navbar.php';
    ?>

    <div class="container">
        <!-- Hero Banner -->
        <section class="crud-page-header">
            <div>
                <div class="breadcrumb-trail">
                    <a href="index.php?page=landing">Home</a> <span>/</span> <span>Coach Admin Center</span>
                </div>
                <h1><i class="fa-solid fa-gauge-high"></i> HB FITNESS Coaching & Editorial Command Center</h1>
                <p>Manage fitness articles, highlight Coach Pick comments, explore sports disciplines, and oversee athlete accounts.</p>
            </div>
            <div style="display:flex;gap:0.65rem;flex-wrap:wrap;z-index:1;">
                <a href="index.php?page=coach" class="btn btn-outline">
                    <i class="fa-solid fa-medal"></i> Preview Coach Studio
                </a>
                <a href="index.php?page=user" class="btn btn-outline">
                    <i class="fa-solid fa-eye"></i> Preview Athlete Portal
                </a>
                <a href="view/workout/manage.php" class="btn btn-primary">
                    <i class="fa-solid fa-dumbbell"></i> Manage Workouts
                </a>
                <a href="view/user/manageUsers.php" class="btn btn-green">
                    <i class="fa-solid fa-users-gear"></i> Manage Users
                </a>
            </div>
        </section>

        <!-- Animated KPI Row -->
        <section class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-info">
                    <span>Fitness Articles</span>
                    <h3 class="counter-value" data-target="<?= count($articles) ?>">0</h3>
                </div>
                <div class="kpi-icon"><i class="fa-solid fa-newspaper"></i></div>
            </div>
            <div class="kpi-card">
                <div class="kpi-info">
                    <span>Community Comments</span>
                    <h3 class="counter-value" data-target="<?= count($comments) ?>">0</h3>
                </div>
                <div class="kpi-icon"><i class="fa-solid fa-comments"></i></div>
            </div>
            <div class="kpi-card">
                <div class="kpi-info">
                    <span>Total Upvotes</span>
                    <?php
                    $totalLikes = array_reduce($articles, fn($carry, $item) => $carry + (int)$item['likes_count'], 0);
                    ?>
                    <h3 class="counter-value" data-target="<?= $totalLikes ?>">0</h3>
                </div>
                <div class="kpi-icon"><i class="fa-solid fa-fire"></i></div>
            </div>
            <div class="kpi-card">
                <div class="kpi-info">
                    <span>Registered Athletes</span>
                    <h3 class="counter-value" data-target="<?= count($users) ?>">0</h3>
                </div>
                <div class="kpi-icon"><i class="fa-solid fa-user-shield"></i></div>
            </div>
        </section>

        <!-- Dedicated CRUD Modules Navigation Grid -->
        <section class="module-hub-grid">
            <!-- Module 1: Articles & Comments -->
            <div class="module-hub-card">
                <div>
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.85rem;">
                        <div class="feature-icon" style="width:48px;height:48px;font-size:1.2rem;margin-bottom:0;">
                            <i class="fa-solid fa-newspaper"></i>
                        </div>
                        <span class="badge badge-orange">Editorial Hub</span>
                    </div>
                    <h3 style="color:var(--navy-900);margin-bottom:0.35rem;">Fitness Articles</h3>
                    <p style="font-size:0.86rem;color:var(--slate-700);">
                        Publish workout guides, feature top nutrition articles, and manage community posts.
                    </p>
                </div>
                <div class="module-hub-actions">
                    <a href="view/article/listArticles.php" class="btn btn-navy btn-sm"><i class="fa-solid fa-list"></i> Articles (<?= count($articles) ?>)</a>
                    <a href="view/article/createArticle.php" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus"></i> New Article</a>
                </div>
            </div>

            <!-- Module 2: Comments -->
            <div class="module-hub-card">
                <div>
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.85rem;">
                        <div class="feature-icon" style="width:48px;height:48px;font-size:1.2rem;margin-bottom:0;">
                            <i class="fa-solid fa-comments"></i>
                        </div>
                        <span class="badge badge-green">Community</span>
                    </div>
                    <h3 style="color:var(--navy-900);margin-bottom:0.35rem;">Article Comments</h3>
                    <p style="font-size:0.86rem;color:var(--slate-700);">
                        Moderate athlete replies and highlight verified Coach Pick tips across discussions.
                    </p>
                </div>
                <div class="module-hub-actions">
                    <a href="view/article/listComments.php" class="btn btn-navy btn-sm"><i class="fa-solid fa-comment-dots"></i> Comments (<?= count($comments) ?>)</a>
                    <a href="view/article/createComment.php" class="btn btn-green btn-sm"><i class="fa-solid fa-plus"></i> Add Comment</a>
                </div>
            </div>

            <!-- Module 3: Sports View -->
            <div class="module-hub-card">
                <div>
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.85rem;">
                        <div class="feature-icon" style="width:48px;height:48px;font-size:1.2rem;margin-bottom:0;">
                            <i class="fa-solid fa-person-running"></i>
                        </div>
                        <span class="badge badge-orange">Disciplines</span>
                    </div>
                    <h3 style="color:var(--navy-900);margin-bottom:0.35rem;">Sports Explorer</h3>
                    <p style="font-size:0.86rem;color:var(--slate-700);">
                        Browse athletic sports, intensity metrics, BMI & training zones, and linked articles.
                    </p>
                </div>
                <div class="module-hub-actions">
                    <a href="view/sports/sports.php" class="btn btn-primary btn-sm"><i class="fa-solid fa-trophy"></i> Open Sports View</a>
                </div>
            </div>

            <!-- Shared Module: Users -->
            <div class="module-hub-card">
                <div>
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.85rem;">
                        <div class="feature-icon" style="width:48px;height:48px;font-size:1.2rem;margin-bottom:0;">
                            <i class="fa-solid fa-users-gear"></i>
                        </div>
                        <span class="badge badge-green">Access Control</span>
                    </div>
                    <h3 style="color:var(--navy-900);margin-bottom:0.35rem;">Users & Accounts</h3>
                    <p style="font-size:0.86rem;color:var(--slate-700);">
                        Manage coach administrators and registered athlete accounts safely.
                    </p>
                </div>
                <div class="module-hub-actions">
                    <a href="view/user/manageUsers.php" class="btn btn-primary btn-sm"><i class="fa-solid fa-users-gear"></i> User Manager (<?= count($users) ?>)</a>
                    <a href="view/user/listUsers.php" class="btn btn-navy btn-sm"><i class="fa-solid fa-table"></i> Classic List</a>
                </div>
            </div>
        </section>

        <!-- Recent Activity Overview -->
        <div class="dual-grid">
            <div class="glass-card">
                <div class="card-header">
                    <div class="card-title"><i class="fa-solid fa-newspaper"></i> Latest Fitness Articles</div>
                    <a href="view/article/listArticles.php" class="btn btn-outline btn-sm">View All &rarr;</a>
                </div>
                <div class="table-responsive">
                    <table class="smart-table">
                        <thead>
                            <tr>
                                <th>Article</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($articles, 0, 4) as $art): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($art['title']) ?></strong></td>
                                <td><span class="badge badge-orange"><?= htmlspecialchars($art['category']) ?></span></td>
                                <td><span class="badge badge-<?= htmlspecialchars($art['status']) ?>"><?= htmlspecialchars($art['status']) ?></span></td>
                                <td>
                                    <a href="view/article/updateArticle.php?id=<?= (int)$art['id'] ?>" class="btn btn-outline btn-sm"><i class="fa-solid fa-pen"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="glass-card">
                <div class="card-header">
                    <div class="card-title"><i class="fa-solid fa-id-card-clip"></i> Session & Security Status</div>
                </div>
                <?php
                $adminCount   = count(array_filter($users, fn($u) => $u['role'] === 'admin'));
                $coachCount   = count(array_filter($users, fn($u) => $u['role'] === 'coach'));
                $athleteCount = count(array_filter($users, fn($u) => $u['role'] === 'user'));
                ?>
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:0.8rem;margin-bottom:1rem;">
                    <div style="padding:1rem;border-radius:14px;background:rgba(145,3,41,0.28);border:1px solid rgba(232,64,94,0.4);text-align:center;">
                        <div style="font-size:1.8rem;font-weight:900;color:#fff;"><?= $adminCount ?></div>
                        <div style="font-size:0.78rem;font-weight:700;color:#E8405E;">ADMINS</div>
                    </div>
                    <div style="padding:1rem;border-radius:14px;background:#0D0D10;border:1px solid rgba(232,64,94,0.35);text-align:center;">
                        <div style="font-size:1.8rem;font-weight:900;color:#fff;"><?= $coachCount ?></div>
                        <div style="font-size:0.78rem;font-weight:700;color:#E8405E;">COACHES</div>
                    </div>
                    <div style="padding:1rem;border-radius:14px;background:#0D0D10;border:1px solid rgba(180,180,180,0.2);text-align:center;">
                        <div style="font-size:1.8rem;font-weight:900;color:#fff;"><?= $athleteCount ?></div>
                        <div style="font-size:0.78rem;font-weight:700;color:#B4B4B4;">USERS</div>
                    </div>
                </div>
                <div style="padding:1rem;border-radius:12px;background:#0D0D10;border:1px solid rgba(180,180,180,0.14);font-size:0.86rem;color:#D4D4D8;line-height:1.85;">
                    <div><i class="fa-solid fa-user-shield" style="color:var(--orange-600);"></i> Logged in as <strong><?= htmlspecialchars($currentUser['full_name']) ?></strong> (<?= htmlspecialchars($currentUser['email']) ?>)</div>
                    <div><i class="fa-solid fa-clock" style="color:var(--emerald-600);"></i> Session opened: <strong><?= htmlspecialchars($_SESSION['login_time'] ?? '—') ?></strong></div>
                    <div><i class="fa-solid fa-lock" style="color:var(--orange-600);"></i> Your administrative session is protected with automatic 30-minute inactivity timeout.</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="view/assets/js/fitness.js"></script>
</body>
</html>
