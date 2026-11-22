<?php
/**
 * Views/Frontoffice/register.php
 * Public Registration Form (Creates normal 'user' role accounts only)
 * Expects: $errors (array), $old (array), $csrfToken (string)
 */
ob_start();
$old    = $old ?? [];
$errors = $errors ?? [];
?>
<div class="container py-4">
    <div class="hb-card fo-auth-card" style="max-width:520px;">
        <div class="text-center mb-4">
            <img src="Views/assets/images/hb-monogram.svg" alt="HB FITNESS" style="height:46px;width:auto;" class="mb-2">
            <div class="eyebrow mb-1">Join HB FITNESS</div>
            <h1 class="h2 text-uppercase mb-1">Create an Account</h1>
            <p class="text-silver small mb-0">Public registration creates a standard Athlete (user) account.</p>
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

        <form method="POST" action="index.php?action=register" data-validate="true" novalidate>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">

            <div class="mb-3">
                <label for="full_name" class="hb-label">Full Name</label>
                <input type="text" id="full_name" name="full_name" class="form-control hb-input"
                       placeholder="e.g. Nour Ben Salah"
                       value="<?= htmlspecialchars((string)($old['full_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
                <div class="field-error"></div>
            </div>

            <div class="mb-3">
                <label for="email" class="hb-label">Email Address</label>
                <input type="email" id="email" name="email" class="form-control hb-input"
                       placeholder="you@example.com" autocomplete="email"
                       value="<?= htmlspecialchars((string)($old['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
                <div class="field-error"></div>
            </div>

            <div class="mb-3">
                <label for="phone" class="hb-label">Phone Number <span class="text-silver fw-normal">(Optional)</span></label>
                <input type="text" id="phone" name="phone" class="form-control hb-input"
                       placeholder="+216 55 000 000"
                       value="<?= htmlspecialchars((string)($old['phone'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                <div class="field-error"></div>
            </div>

            <div class="mb-3">
                <label for="password" class="hb-label">Password (min 8 chars, letters + digits)</label>
                <div class="input-group">
                    <input type="password" id="password" name="password" class="form-control hb-input"
                           placeholder="Create a strong password" autocomplete="new-password"
                           data-strength-bar="regStrengthBar" data-strength-label="regStrengthLabel" required>
                    <button type="button" class="btn btn-hb-dark" data-toggle-password="password" aria-label="Toggle password">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                </div>
                <div class="strength-track"><div class="strength-bar" id="regStrengthBar"></div></div>
                <small id="regStrengthLabel" class="text-silver" style="font-size:0.75rem;"></small>
                <div class="field-error"></div>
            </div>

            <div class="mb-4">
                <label for="password_confirm" class="hb-label">Confirm Password</label>
                <input type="password" id="password_confirm" name="password_confirm" class="form-control hb-input"
                       placeholder="Repeat your password" autocomplete="new-password" required>
                <div class="field-error"></div>
            </div>

            <button type="submit" class="btn btn-hb-primary w-100 py-2">
                <i class="fa-solid fa-user-plus me-2"></i>Create My Account
            </button>
        </form>

        <div class="text-center mt-4 small text-silver">
            Already have an account? <a href="index.php?action=login" class="fw-bold">Log in</a>
        </div>
    </div>
</div>
<?php
$content = (string)ob_get_clean();
$title   = 'Register — HB FITNESS';
require __DIR__ . '/../layout.php';
