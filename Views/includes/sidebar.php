<?php
/**
 * Views/includes/sidebar.php
 * Reusable Backoffice TailAdmin-Style Sidebar
 * Expects: $currentPage (string), $currentUser (User)
 */
$currentPage = $currentPage ?? 'admin_dashboard';
$boActive    = static fn(string $key): string => $currentPage === $key ? 'active' : '';
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
    </nav>

    <div class="bo-sidebar-footer">
        <a href="index.php?action=admin_profile" class="d-flex align-items-center gap-2 mb-2 text-decoration-none text-white">
            <div class="hb-avatar" style="width:36px;height:36px;font-size:0.85rem;">
                <?= htmlspecialchars($currentUser->getInitials(), ENT_QUOTES, 'UTF-8') ?>
            </div>
            <div class="overflow-hidden">
                <div class="fw-bold text-truncate" style="font-size:0.86rem;">
                    <?= htmlspecialchars($currentUser->getFullName(), ENT_QUOTES, 'UTF-8') ?>
                </div>
                <div class="eyebrow" style="font-size:0.65rem;">Administrator</div>
            </div>
        </a>
        <a href="index.php?action=logout" class="btn btn-hb-outline btn-sm w-100">
            <i class="fa-solid fa-right-from-bracket me-1"></i> Sign Out
        </a>
    </div>
</aside>
