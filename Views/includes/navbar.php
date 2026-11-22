<?php
/**
 * Views/includes/navbar.php
 * Reusable Frontoffice Navigation Bar (User + Marketplace + Blog)
 * Expects: $currentPage (string), $currentUser (?User|?array)
 */
$currentPage = $currentPage ?? 'home';
$currentUser = $currentUser ?? null;

$navUserRole = null;
$navUserName = null;
if (is_object($currentUser) && method_exists($currentUser, 'getRoleName')) {
    $navUserRole = (string)$currentUser->getRoleName();
    $navUserName = (string)$currentUser->getFullName();
} elseif (is_array($currentUser) && !empty($currentUser['role'])) {
    $navUserRole = (string)$currentUser['role'];
    $navUserName = (string)($currentUser['full_name'] ?? 'User');
}

$isActive     = static fn(string $page): string => $currentPage === $page ? 'active' : '';
$isBlogActive = in_array($currentPage, ['blog', 'articles', 'my_comments', 'create_article'], true) ? 'active' : '';
?>
<nav class="navbar navbar-expand-lg fo-navbar sticky-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2 text-white font-heading" href="index.php?action=home">
            <img src="Views/assets/images/hb-monogram.svg" alt="HB FITNESS" class="fo-brand-img">
            <div>
                <span class="text-silver">HB</span> <span class="text-coral">FITNESS</span>
                <small class="d-block eyebrow" style="font-size:0.62rem;">Train smart. Reuse more.</small>
            </div>
        </a>

        <button class="navbar-toggler border-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#foNavMenu">
            <i class="fa-solid fa-bars text-white"></i>
        </button>

        <div class="collapse navbar-collapse" id="foNavMenu">
            <ul class="navbar-nav mx-auto gap-1">
                <li class="nav-item">
                    <a class="nav-link <?= $isActive('home') ?>" href="index.php?action=home">
                        <i class="fa-solid fa-house me-1"></i> Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $isActive('store') ?>" href="index.php?action=store">
                        <i class="fa-solid fa-shop me-1"></i> Marketplace
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $isBlogActive ?>" href="index.php?action=blog">
                        <i class="fa-solid fa-newspaper me-1"></i> Blog
                    </a>
                </li>
                <?php if ($navUserRole !== null): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $isActive('dashboard') ?>" href="index.php?action=dashboard">
                            <i class="fa-solid fa-gauge-high me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $isActive('profile') ?>" href="index.php?action=profile">
                            <i class="fa-solid fa-id-card me-1"></i> Profile
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $isActive('edit_profile') ?>" href="index.php?action=edit_profile">
                            <i class="fa-solid fa-user-pen me-1"></i> Edit Profile
                        </a>
                    </li>
                    <?php if ($navUserRole === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?action=admin_dashboard">
                                <i class="fa-solid fa-shield-halved me-1 text-coral"></i> Backoffice
                            </a>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>
            </ul>

            <div class="d-flex align-items-center gap-2 mt-3 mt-lg-0">
                <?php if ($navUserRole !== null): ?>
                    <span class="hb-badge hb-badge-<?= htmlspecialchars($navUserRole, ENT_QUOTES, 'UTF-8') ?>">
                        <i class="fa-solid fa-user-check"></i>
                        <?= htmlspecialchars((string)$navUserName, ENT_QUOTES, 'UTF-8') ?>
                        (<?= htmlspecialchars($navUserRole, ENT_QUOTES, 'UTF-8') ?>)
                    </span>
                    <a href="index.php?action=logout" class="btn btn-hb-outline btn-sm">
                        <i class="fa-solid fa-right-from-bracket me-1"></i> Logout
                    </a>
                <?php else: ?>
                    <a href="index.php?action=login" class="btn btn-hb-outline btn-sm">
                        <i class="fa-solid fa-right-to-bracket me-1"></i> Login
                    </a>
                    <a href="index.php?action=register" class="btn btn-hb-primary btn-sm">
                        <i class="fa-solid fa-user-plus me-1"></i> Register
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
