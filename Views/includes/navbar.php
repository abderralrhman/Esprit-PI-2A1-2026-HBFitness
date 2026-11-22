<?php
/**
 * Views/includes/navbar.php — Frontoffice Responsive Navigation Bar
 */
$activePage = $currentPage ?? 'home';
$user       = $currentUser ?? null;
$role       = $user['role'] ?? null;
?>
<nav class="navbar navbar-expand-lg navbar-dark hb-navbar">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="index.php?action=home">
            <img src="Views/assets/images/hb-monogram.svg" alt="HB FITNESS" width="40" height="40">
            <div>
                <span class="font-display fs-5 d-block lh-1">HB <span class="text-coral">FITNESS</span></span>
                <span class="hb-brand-tagline">Train smart. Reuse more.</span>
            </div>
        </a>

        <button class="navbar-toggler border-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#hbMainNav" aria-controls="hbMainNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="hbMainNav">
            <ul class="navbar-nav mx-auto gap-lg-1 my-3 my-lg-0">
                <li class="nav-item">
                    <a class="nav-link hb-nav-link <?= $activePage === 'home' ? 'active' : '' ?>" href="index.php?action=home">
                        <i class="fa-solid fa-house me-1"></i>Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link hb-nav-link <?= $activePage === 'articles' ? 'active' : '' ?>" href="index.php?action=articles">
                        <i class="fa-solid fa-newspaper me-1"></i>Articles
                    </a>
                </li>
                <?php if ($user): ?>
                    <li class="nav-item">
                        <a class="nav-link hb-nav-link <?= $activePage === 'my_comments' ? 'active' : '' ?>" href="index.php?action=my_comments">
                            <i class="fa-solid fa-comments me-1"></i>My Comments
                        </a>
                    </li>
                <?php endif; ?>
                <?php if ($role === 'coach'): ?>
                    <li class="nav-item">
                        <a class="nav-link hb-nav-link <?= $activePage === 'create_article' ? 'active' : '' ?>" href="index.php?action=create_article">
                            <i class="fa-solid fa-pen-nib me-1"></i>Publish Article
                        </a>
                    </li>
                <?php endif; ?>
                <?php if ($role === 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link hb-nav-link" href="index.php?action=admin_dashboard">
                            <i class="fa-solid fa-gauge-high me-1"></i>Backoffice
                        </a>
                    </li>
                <?php endif; ?>
            </ul>

            <div class="d-flex align-items-center gap-2 flex-wrap">
                <?php if ($user): ?>
                    <?php
                    $badgeClass = $role === 'admin' ? 'hb-badge-admin' : ($role === 'coach' ? 'hb-badge-coach' : 'hb-badge-user');
                    $badgeIcon  = $role === 'admin' ? 'fa-shield-halved' : ($role === 'coach' ? 'fa-medal' : 'fa-user');
                    ?>
                    <div class="d-flex align-items-center gap-2 px-2 py-1 rounded-3" style="background:rgba(255,255,255,0.04);border:1px solid var(--hb-border);">
                        <span class="small fw-semibold text-white"><?= htmlspecialchars($user['full_name'], ENT_QUOTES, 'UTF-8') ?></span>
                        <span class="hb-badge <?= $badgeClass ?>">
                            <i class="fa-solid <?= $badgeIcon ?>"></i><?= htmlspecialchars((string)$role, ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </div>
                    <a href="index.php?action=logout" class="btn btn-hb-outline btn-sm">
                        <i class="fa-solid fa-right-from-bracket me-1"></i>Logout
                    </a>
                <?php else: ?>
                    <a href="index.php?action=login" class="btn btn-hb-primary btn-sm">
                        <i class="fa-solid fa-right-to-bracket me-1"></i>Log In
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
