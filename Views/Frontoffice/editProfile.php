<?php
/**
 * Views/Frontoffice/editProfile.php
 * Authenticated User Self-Service Profile Editor (CSRF + Server/Client Validation)
 * Expects: $currentUser (User), $errors (array), $old (array), $csrfToken (string)
 */
ob_start();
$errors = $errors ?? [];
$old    = $old ?? [];
?>
<div class="container py-4">
    <div class="hb-card mx-auto" style="max-width:680px;">
        <div class="d-flex justify-content-between align-items-center pb-3 mb-4" style="border-bottom:1px solid var(--hb-border);">
            <div>
                <div class="eyebrow mb-1">Account Settings</div>
                <h1 class="h3 text-uppercase mb-0">Edit My Profile</h1>
            </div>
            <a href="index.php?action=profile" class="btn btn-hb-dark btn-sm">
                <i class="fa-solid fa-arrow-left me-1"></i> Back
            </a>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="hb-alert hb-alert-error">
                <i class="fa-solid fa-triangle-exclamation mt-1"></i>
                <div>
                    <?php foreach ($errors as $err): ?>
                        <div><?= htmlspecialchars((string)$err, ENT_QUOTES, 'UTF-8') ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <form method="POST" action="index.php?action=edit_profile" data-validate="true" novalidate>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">

            <div class="mb-3">
                <label for="full_name" class="hb-label">Full Name</label>
                <input type="text" id="full_name" name="full_name" class="form-control hb-input"
                       value="<?= htmlspecialchars((string)($old['full_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
                <div class="field-error"></div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label for="email" class="hb-label">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control hb-input"
                           value="<?= htmlspecialchars((string)($old['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
                    <div class="field-error"></div>
                </div>
                <div class="col-md-6">
                    <label for="phone" class="hb-label">Phone Number</label>
                    <input type="text" id="phone" name="phone" class="form-control hb-input"
                           placeholder="+216 55 000 000"
                           value="<?= htmlspecialchars((string)($old['phone'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    <div class="field-error"></div>
                </div>
            </div>

            <hr class="my-4" style="border-color:var(--hb-border);">
            <div class="eyebrow mb-3">Change Password (Optional)</div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label for="new_password" class="hb-label">New Password</label>
                    <input type="password" id="new_password" name="new_password" class="form-control hb-input"
                           placeholder="Leave blank to keep current" autocomplete="new-password">
                    <div class="field-error"></div>
                </div>
                <div class="col-md-6">
                    <label for="new_password_confirm" class="hb-label">Confirm New Password</label>
                    <input type="password" id="new_password_confirm" name="new_password_confirm" class="form-control hb-input"
                           placeholder="Repeat new password" autocomplete="new-password">
                    <div class="field-error"></div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="index.php?action=profile" class="btn btn-hb-dark">Cancel</a>
                <button type="submit" class="btn btn-hb-primary">
                    <i class="fa-solid fa-floppy-disk me-1"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
<?php
$content = (string)ob_get_clean();
$title   = 'Edit Profile — HB FITNESS';
require __DIR__ . '/../layout.php';
