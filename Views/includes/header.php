<?php
/**
 * Views/includes/header.php
 * Reusable Backoffice Top Header Bar
 * Expects: $pageHeading (string), $currentUser (User)
 */
$pageHeading = $pageHeading ?? 'Admin Dashboard';
?>
<header class="bo-topbar">
    <div class="d-flex align-items-center gap-3">
        <button type="button" class="btn btn-hb-dark btn-sm d-lg-none" id="boSidebarToggle" aria-label="Toggle Menu">
            <i class="fa-solid fa-bars"></i>
        </button>
        <div>
            <h1 class="h4 mb-0 text-uppercase"><?= htmlspecialchars($pageHeading, ENT_QUOTES, 'UTF-8') ?></h1>
        </div>
    </div>
    <div class="d-flex align-items-center gap-3">
        <span class="bo-env-pill"><i class="fa-solid fa-lock me-1"></i> Backoffice</span>
        <a href="index.php?action=admin_create_user" class="btn btn-hb-primary btn-sm d-none d-sm-inline-flex align-items-center gap-1">
            <i class="fa-solid fa-user-plus"></i> New User
        </a>
    </div>
</header>
