<?php
/**
 * Views/Frontoffice/resetPassword.php
 * Local-only password reset form
 * Expects: $token (string), $errors (array), $csrfToken (string), $isValidToken (bool)
 */
ob_start();
$errors = $errors ?? [];
?>
<div class="container py-4">
    <div class="hb-card fo-auth-card">
        <div class="text-center mb-4">
            <img src="Views/assets/images/hb-monogram.svg" alt="HB FITNESS" style="height:48px;width:auto;" class="mb-2">
            <div class="eyebrow mb-1">Account Security</div>
            <h1 class="h2 text-uppercase mb-1">Set a New Password</h1>
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

        <?php if (!empty($isValidToken)): ?>
            <form method="POST" action="index.php?action=reset_password" data-validate="true" novalidate>
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">

                <div class="mb-3">
                    <label for="password" class="hb-label">New Password</label>
                    <input type="password" id="password" name="password" class="form-control hb-input"
                           autocomplete="new-password" required>
                    <div class="field-error"></div>
                    <small class="text-silver">At least 8 characters, including letters and digits.</small>
                </div>

                <div class="mb-4">
                    <label for="password_confirm" class="hb-label">Confirm New Password</label>
                    <input type="password" id="password_confirm" name="password_confirm" class="form-control hb-input"
                           autocomplete="new-password" required>
                    <div class="field-error"></div>
                </div>

                <button type="submit" class="btn btn-hb-primary w-100 py-2">
                    <i class="fa-solid fa-lock me-1"></i> Save New Password
                </button>
            </form>
        <?php else: ?>
            <div class="hb-alert hb-alert-error">
                <i class="fa-solid fa-triangle-exclamation mt-1"></i>
                <div>This reset link is invalid, expired, or already used. Request a new one.</div>
            </div>
            <a href="index.php?action=forgot_password" class="btn btn-hb-primary w-100">Request a New Link</a>
        <?php endif; ?>
    </div>
</div>
<?php
$content = (string)ob_get_clean();
$title   = 'Reset Password — HB FITNESS';
require __DIR__ . '/../layout.php';
