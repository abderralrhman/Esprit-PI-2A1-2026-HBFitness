<?php
/**
 * Views/includes/header.php — Backoffice Topbar Header
 */
$headerTitle = $adminHeading ?? 'Blog Backoffice';
$user        = $currentUser ?? null;
?>
<header class="hb-admin-header">
    <div class="d-flex align-items-center gap-3">
        <button type="button" id="hb-sidebar-toggle" class="btn btn-hb-outline btn-sm d-lg-none" aria-label="Toggle sidebar">
            <i class="fa-solid fa-bars"></i>
        </button>
        <div>
            <h5 class="mb-0"><?= htmlspecialchars($headerTitle, ENT_QUOTES, 'UTF-8') ?></h5>
            <span class="small text-muted-hb">HB FITNESS · Blog Management Module</span>
        </div>
    </div>

    <div class="d-flex align-items-center gap-2">
        <a href="index.php?action=home" class="btn btn-hb-outline btn-sm d-none d-sm-inline-flex align-items-center gap-1">
            <i class="fa-solid fa-globe"></i>Frontoffice
        </a>
        <?php if ($user): ?>
            <div class="d-flex align-items-center gap-2 px-3 py-1 rounded-3" style="background:rgba(255,255,255,0.04);border:1px solid var(--hb-border);">
                <span class="small fw-semibold text-white"><?= htmlspecialchars($user['full_name'], ENT_QUOTES, 'UTF-8') ?></span>
                <span class="hb-badge hb-badge-admin"><i class="fa-solid fa-shield-halved"></i>Admin</span>
            </div>
        <?php endif; ?>
    </div>
</header>
