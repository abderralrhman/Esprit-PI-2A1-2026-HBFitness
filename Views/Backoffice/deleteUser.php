<?php
/**
 * Views/Backoffice/deleteUser.php
 * CSRF-Protected User Deletion Confirmation Screen
 * Expects: $currentUser (User), $targetUser (User), $errors (array), $csrfToken (string)
 */
ob_start();
$isSelf = $targetUser->getId() === $currentUser->getId();
?>
<div class="hb-card mx-auto" style="max-width:600px;border-top:5px solid var(--hb-crimson);">
    <div class="eyebrow mb-1">User CRUD &middot; Delete Confirmation</div>
    <h2 class="h4 text-uppercase mb-3">
        <i class="fa-solid fa-triangle-exclamation text-coral me-2"></i>Delete User #<?= $targetUser->getId() ?>
    </h2>

    <?php if (!empty($errors)): ?>
        <div class="hb-alert hb-alert-error">
            <i class="fa-solid fa-circle-exclamation mt-1"></i>
            <div>
                <?php foreach ($errors as $err): ?>
                    <div><?= htmlspecialchars((string)$err, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="p-3 rounded-3 mb-4" style="background:#0D0D10;border:1px solid var(--hb-border);">
        <div class="d-flex align-items-center gap-3">
            <div class="hb-avatar"><?= htmlspecialchars($targetUser->getInitials(), ENT_QUOTES, 'UTF-8') ?></div>
            <div>
                <div class="fw-bold fs-5"><?= htmlspecialchars($targetUser->getFullName(), ENT_QUOTES, 'UTF-8') ?></div>
                <div class="small text-silver"><?= htmlspecialchars($targetUser->getEmail(), ENT_QUOTES, 'UTF-8') ?></div>
                <div class="mt-2 d-flex gap-2">
                    <span class="hb-badge hb-badge-<?= htmlspecialchars($targetUser->getRoleName(), ENT_QUOTES, 'UTF-8') ?>">
                        <?= htmlspecialchars($targetUser->getRoleName(), ENT_QUOTES, 'UTF-8') ?>
                    </span>
                    <span class="hb-badge hb-badge-<?= htmlspecialchars($targetUser->getStatusName(), ENT_QUOTES, 'UTF-8') ?>">
                        <?= htmlspecialchars($targetUser->getStatusName(), ENT_QUOTES, 'UTF-8') ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <?php if ($isSelf): ?>
        <div class="hb-alert hb-alert-error">
            <i class="fa-solid fa-shield-halved mt-1"></i>
            <div>You cannot delete your own administrator account while signed in.</div>
        </div>
        <a href="index.php?action=admin_users" class="btn btn-hb-dark">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to User List
        </a>
    <?php else: ?>
        <p class="text-silver mb-4">
            This action permanently removes the user from <code>hbfitess_db</code> and cannot be undone.
        </p>
        <form method="POST" action="index.php?action=admin_delete_user&id=<?= $targetUser->getId() ?>" class="d-flex justify-content-end gap-2">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="id" value="<?= $targetUser->getId() ?>">
            <a href="index.php?action=admin_users" class="btn btn-hb-dark">Cancel</a>
            <button type="submit" class="btn btn-hb-primary">
                <i class="fa-solid fa-trash me-1"></i> Confirm Permanent Delete
            </button>
        </form>
    <?php endif; ?>
</div>
<?php
$content     = (string)ob_get_clean();
$title       = 'Delete User #' . $targetUser->getId() . ' — HB FITNESS Backoffice';
$pageHeading = 'Delete User';
require __DIR__ . '/../layout_admin.php';
