<?php
/**
 * Views/Frontoffice/forgotPassword.php
 * Local-only password reset request form
 * Expects: $errors (array), $old (array), $csrfToken (string), $submitted (bool)
 */
ob_start();
$errors = $errors ?? [];
$old    = $old ?? [];
?>
<div class="container py-4">
    <div class="hb-card fo-auth-card">
        <div class="text-center mb-4">
            <img src="Views/assets/images/hb-monogram.svg" alt="HB FITNESS" style="height:48px;width:auto;" class="mb-2">
            <div class="eyebrow mb-1">Local Demo</div>
            <h1 class="h2 text-uppercase mb-1">Forgot Password?</h1>
            <p class="text-silver small mb-0">Enter your account email. Reset messages go to the local MailCatcher inbox, not to real email.</p>
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

        <?php if (!empty($submitted)): ?>
            <div class="hb-alert hb-alert-success">
                <i class="fa-solid fa-circle-check mt-1"></i>
                <div>If an active account matches that email, a reset message was sent to MailCatcher.</div>
            </div>
            <a href="http://127.0.0.1:1080/" target="_blank" rel="noopener noreferrer" class="btn btn-hb-primary w-100">
                <i class="fa-solid fa-inbox me-1"></i> Open MailCatcher Inbox
            </a>
        <?php else: ?>
            <form method="POST" action="index.php?action=forgot_password" data-validate="true" novalidate>
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <div class="mb-4">
                    <label for="email" class="hb-label">Account Email</label>
                    <input type="email" id="email" name="email" class="form-control hb-input"
                           value="<?= htmlspecialchars((string)($old['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                           autocomplete="email" required>
                    <div class="field-error"></div>
                </div>
                <button type="submit" class="btn btn-hb-primary w-100 py-2">
                    <i class="fa-solid fa-paper-plane me-1"></i> Create Reset Link
                </button>
            </form>
        <?php endif; ?>

        <div class="text-center mt-4 small">
            <a href="index.php?action=login">Back to Log In</a>
        </div>
    </div>
</div>
<?php
$content = (string)ob_get_clean();
$title   = 'Forgot Password — HB FITNESS';
require __DIR__ . '/../layout.php';
