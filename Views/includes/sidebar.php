<?php
/**
 * Views/includes/sidebar.php — Admin Backoffice TailAdmin-Style Sidebar
 */
$activePage = $currentPage ?? 'admin_dashboard';
?>
<div id="hb-sidebar-backdrop" class="hb-sidebar-backdrop"></div>
<aside id="hb-sidebar" class="hb-sidebar">
    <div class="hb-sidebar-brand">
        <a href="index.php?action=admin_dashboard" class="d-flex align-items-center gap-2 text-decoration-none">
            <img src="Views/assets/images/hb-monogram.svg" alt="HB FITNESS" width="38" height="38">
            <div>
                <span class="font-display fs-5 text-white d-block lh-1">HB <span class="text-coral">FITNESS</span></span>
                <span class="hb-brand-tagline">Blog Backoffice</span>
            </div>
        </a>
    </div>

    <div class="hb-sidebar-section">Blog Management</div>
    <ul class="hb-sidebar-nav">
        <li>
            <a href="index.php?action=admin_dashboard" class="hb-sidebar-link <?= $activePage === 'admin_dashboard' ? 'active' : '' ?>">
                <i class="fa-solid fa-chart-line"></i><span>Dashboard</span>
            </a>
        </li>
        <li>
            <a href="index.php?action=admin_articles" class="hb-sidebar-link <?= $activePage === 'admin_articles' ? 'active' : '' ?>">
                <i class="fa-solid fa-newspaper"></i><span>All Articles</span>
            </a>
        </li>
        <li>
            <a href="index.php?action=create_article" class="hb-sidebar-link <?= $activePage === 'admin_create_article' ? 'active' : '' ?>">
                <i class="fa-solid fa-circle-plus"></i><span>Publish Article</span>
            </a>
        </li>
        <li>
            <a href="index.php?action=admin_comments" class="hb-sidebar-link <?= $activePage === 'admin_comments' ? 'active' : '' ?>">
                <i class="fa-solid fa-comments"></i><span>Manage Comments</span>
            </a>
        </li>
    </ul>

    <div class="hb-sidebar-section">Navigation</div>
    <ul class="hb-sidebar-nav">
        <li>
            <a href="index.php?action=home" class="hb-sidebar-link">
                <i class="fa-solid fa-arrow-up-right-from-square"></i><span>View Frontoffice</span>
            </a>
        </li>
    </ul>

    <div class="hb-sidebar-footer">
        <a href="index.php?action=logout" class="btn btn-hb-danger w-100 btn-sm">
            <i class="fa-solid fa-right-from-bracket me-2"></i>Logout
        </a>
    </div>
</aside>
