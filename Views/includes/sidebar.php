<?php
/**
 * Views/includes/sidebar.php
 * Reusable Backoffice TailAdmin-Style Sidebar (User + Marketplace + Blog)
 * Expects: $currentPage (string), $currentUser (User|array)
 */
$currentPage = $currentPage ?? 'admin_dashboard';
$boActive    = static fn(string $key): string => $currentPage === $key ? 'active' : '';

$sideUserName     = 'Administrator';
$sideUserInitials = 'AD';
if (is_object($currentUser) && method_exists($currentUser, 'getFullName')) {
    $sideUserName     = (string)$currentUser->getFullName();
    $sideUserInitials = method_exists($currentUser, 'getInitials')
        ? (string)$currentUser->getInitials()
        : strtoupper(mb_substr($sideUserName, 0, 2));
} elseif (is_array($currentUser)) {
    $sideUserName = (string)($currentUser['full_name'] ?? 'Administrator');
    $parts        = preg_split('/\s+/', trim($sideUserName)) ?: [];
    $initials     = '';
    foreach (array_slice($parts, 0, 2) as $part) {
        $initials .= mb_strtoupper(mb_substr($part, 0, 1));
    }
    $sideUserInitials = $initials !== '' ? $initials : 'AD';
}
?>
<aside class="bo-sidebar" id="boSidebar">
    <div class="bo-sidebar-brand">
        <img src="Views/assets/images/hb-monogram.svg" alt="HB FITNESS" style="height:36px;width:auto;">
        <div>
            <div class="font-heading fw-bold" style="font-size:1.15rem;line-height:1;">
                <span class="text-silver">HB</span> <span class="text-coral">ADMIN</span>
            </div>
            <small class="eyebrow" style="font-size:0.6rem;">Backoffice Console</small>
        </div>
    </div>

    <nav class="bo-sidebar-nav">
        <div class="bo-nav-label">Overview</div>
        <a href="index.php?action=admin_dashboard" class="bo-nav-link <?= $boActive('admin_dashboard') ?>">
            <i class="fa-solid fa-chart-line"></i> Dashboard
        </a>

        <div class="bo-nav-label">User Management</div>
        <a href="index.php?action=admin_users" class="bo-nav-link <?= $boActive('admin_users') ?>">
            <i class="fa-solid fa-users"></i> All Users
        </a>
        <a href="index.php?action=admin_create_user" class="bo-nav-link <?= $boActive('admin_create_user') ?>">
            <i class="fa-solid fa-user-plus"></i> Create User
        </a>
        <a href="index.php?action=admin_roles" class="bo-nav-link <?= $boActive('admin_roles') ?>">
            <i class="fa-solid fa-user-shield"></i> Roles &amp; Statuses
        </a>

        <div class="bo-nav-label">Workout Programs</div>
        <a href="index.php?action=admin_workouts" class="bo-nav-link <?= $boActive('admin_workouts') ?>">
            <i class="fa-solid fa-dumbbell"></i> Manage Workouts
        </a>
        <a href="index.php?action=workouts" class="bo-nav-link">
            <i class="fa-solid fa-list"></i> Browse Plans
        </a>

        <div class="bo-nav-label">Marketplace</div>
        <a href="index.php?action=market_admin" class="bo-nav-link <?= $boActive('market_admin') ?>">
            <i class="fa-solid fa-shop"></i> Market Admin
        </a>
        <a href="index.php?action=store" class="bo-nav-link">
            <i class="fa-solid fa-bag-shopping"></i> View Storefront
        </a>

        <div class="bo-nav-label">Blog Management</div>
        <a href="index.php?action=admin_blog_dashboard" class="bo-nav-link <?= $boActive('admin_blog_dashboard') ?>">
            <i class="fa-solid fa-gauge-high"></i> Blog Overview
        </a>
        <a href="index.php?action=admin_articles" class="bo-nav-link <?= $boActive('admin_articles') ?>">
            <i class="fa-solid fa-newspaper"></i> All Articles
        </a>
        <a href="index.php?action=create_article" class="bo-nav-link <?= $boActive('admin_create_article') ?>">
            <i class="fa-solid fa-pen-nib"></i> New Article
        </a>
        <a href="index.php?action=admin_comments" class="bo-nav-link <?= $boActive('admin_comments') ?>">
            <i class="fa-solid fa-comments"></i> Moderate Comments
        </a>
        <a href="index.php?action=blog" class="bo-nav-link">
            <i class="fa-solid fa-globe"></i> View Public Blog
        </a>
        <?php if (is_object($currentUser) && method_exists($currentUser, 'getRoleName') && in_array($currentUser->getRoleName(), ['admin', 'coach'], true)): ?>
            <div class="bo-nav-label">Coaching Sessions</div>
            <a href="index.php?action=<?= $currentUser->getRoleName() === 'admin' ? 'admin_sessions' : 'coach_sessions' ?>" class="bo-nav-link <?= $boActive('admin_sessions') ?>">
                <i class="fa-solid fa-calendar-plus"></i> Manage Sessions
            </a>
        <?php endif; ?>

        <div class="bo-nav-label">Navigation</div>
        <a href="index.php?action=dashboard" class="bo-nav-link">
            <i class="fa-solid fa-arrow-up-right-from-square"></i> Frontoffice View
        </a>
        <a href="index.php?action=profile" class="bo-nav-link">
            <i class="fa-solid fa-id-card"></i> My Profile
        </a>
    </nav>

    <div class="bo-sidebar-footer">
        <div class="d-flex align-items-center gap-2 mb-2">
            <div class="hb-avatar" style="width:36px;height:36px;font-size:0.85rem;">
                <?= htmlspecialchars($sideUserInitials, ENT_QUOTES, 'UTF-8') ?>
            </div>
            <div class="overflow-hidden">
                <div class="fw-bold text-truncate" style="font-size:0.86rem;">
                    <?= htmlspecialchars($sideUserName, ENT_QUOTES, 'UTF-8') ?>
                </div>
                <div class="eyebrow" style="font-size:0.65rem;">Administrator</div>
            </div>
        </div>
        <a href="index.php?action=logout" class="btn btn-hb-outline btn-sm w-100">
            <i class="fa-solid fa-right-from-bracket me-1"></i> Sign Out
        </a>
    </div>
</aside>
