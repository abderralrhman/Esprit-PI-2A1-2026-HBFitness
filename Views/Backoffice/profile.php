<?php
/**
 * Views/Backoffice/profile.php
 * Administrator Profile
 * Expects: $currentUser (User)
 */
ob_start();
?>
<div class="hb-card mx-auto" style="max-width:780px;">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 pb-4 mb-4" style="border-bottom:1px solid var(--hb-border);">
        <div class="d-flex align-items-center gap-3">
            <div class="hb-avatar hb-avatar-lg"><?= htmlspecialchars($currentUser->getInitials(), ENT_QUOTES, 'UTF-8') ?></div>
            <div>
                <div class="eyebrow mb-1">Administrator Profile</div>
                <h2 class="h3 text-uppercase mb-1"><?= htmlspecialchars($currentUser->getFullName(), ENT_QUOTES, 'UTF-8') ?></h2>
                <div class="d-flex gap-2 flex-wrap">
                    <span class="hb-badge hb-badge-admin">Role: Administrator</span>
                    <span class="hb-badge hb-badge-<?= htmlspecialchars($currentUser->getStatusName(), ENT_QUOTES, 'UTF-8') ?>">
                        Status: <?= htmlspecialchars($currentUser->getStatusName(), ENT_QUOTES, 'UTF-8') ?>
                    </span>
                </div>
            </div>
        </div>
        <a href="index.php?action=admin_edit_profile" class="btn btn-hb-primary">
            <i class="fa-solid fa-user-pen me-1"></i> Edit Profile
        </a>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="p-3 rounded-3" style="background:#0D0D10;border:1px solid var(--hb-border);">
                <div class="eyebrow mb-1">Email Address</div>
                <div class="fw-bold"><?= htmlspecialchars($currentUser->getEmail(), ENT_QUOTES, 'UTF-8') ?></div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="p-3 rounded-3" style="background:#0D0D10;border:1px solid var(--hb-border);">
                <div class="eyebrow mb-1">Phone Number</div>
                <div class="fw-bold"><?= htmlspecialchars($currentUser->getPhone() ?? 'Not provided', ENT_QUOTES, 'UTF-8') ?></div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="p-3 rounded-3" style="background:#0D0D10;border:1px solid var(--hb-border);">
                <div class="eyebrow mb-1">Created At</div>
                <div class="fw-bold"><?= htmlspecialchars($currentUser->getCreatedAt(), ENT_QUOTES, 'UTF-8') ?></div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="p-3 rounded-3" style="background:#0D0D10;border:1px solid var(--hb-border);">
                <div class="eyebrow mb-1">Last Updated</div>
                <div class="fw-bold"><?= htmlspecialchars($currentUser->getUpdatedAt(), ENT_QUOTES, 'UTF-8') ?></div>
            </div>
        </div>
    </div>
</div>
<?php
$content     = (string)ob_get_clean();
$title       = 'Administrator Profile - HB FITNESS';
$pageHeading = 'My Profile';
require __DIR__ . '/../layout_admin.php';
