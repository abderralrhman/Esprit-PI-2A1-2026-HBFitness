<?php
/**
 * Views/Frontoffice/login.php
 * Login Form (STRICTLY Email + Password + hidden CSRF token)
 * Also renders local-only initial setup when ?action=setup on localhost with 0 admins.
 * Expects: $errors (array), $old (array), $csrfToken (string), $currentPage (string)
 */
ob_start();
$old        = $old ?? [];
$errors     = $errors ?? [];
$isSetup    = ($currentPage ?? 'login') === 'setup';
$needsSetup = $needsSetup ?? false;
?>
<div class="container py-4">
    <div class="hb-card fo-auth-card">
        <div class="text-center mb-4">
            <img src="Views/assets/images/hb-monogram.svg" alt="HB FITNESS" style="height:48px;width:auto;" class="mb-2">
            <?php if ($isSetup): ?>
                <div class="eyebrow mb-1">Localhost First-Run Setup</div>
                <h1 class="h2 text-uppercase mb-1">Initialize Demo Accounts</h1>
                <p class="text-silver small mb-0">Create your local Administrator (and optional Coach &amp; User accounts) with a securely hashed password.</p>
            <?php else: ?>
                <div class="eyebrow mb-1">Member &amp; Staff Access</div>
                <h1 class="h2 text-uppercase mb-1">Log In</h1>
                <p class="text-silver small mb-0">Sign in with your email address and password.</p>
            <?php endif; ?>
        </div>

        <?php if (!empty($_GET['registered'])): ?>
            <div class="hb-alert hb-alert-success">
                <i class="fa-solid fa-circle-check mt-1"></i>
                <div>Account created successfully! You can now log in below.</div>
            </div>
        <?php endif; ?>

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

        <?php if ($isSetup): ?>
            <form method="POST" action="index.php?action=setup" novalidate>
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">

                <div class="mb-3">
                    <label for="admin_name" class="hb-label">Administrator Full Name</label>
                    <input type="text" id="admin_name" name="admin_name" class="form-control hb-input"
                           value="<?= htmlspecialchars((string)($old['admin_name'] ?? 'HB Fitness Admin'), ENT_QUOTES, 'UTF-8') ?>" required>
                </div>

                <div class="mb-3">
                    <label for="admin_email" class="hb-label">Administrator Email</label>
                    <input type="email" id="admin_email" name="admin_email" class="form-control hb-input"
                           value="<?= htmlspecialchars((string)($old['admin_email'] ?? 'admin@hbfitess.local'), ENT_QUOTES, 'UTF-8') ?>" required>
                </div>

                <div class="mb-3">
                    <label for="setup_password" class="hb-label">Password (min 8 chars, letters + digits)</label>
                    <input type="password" id="setup_password" name="setup_password" class="form-control hb-input"
                           autocomplete="new-password" required>
                </div>

                <div class="mb-3">
                    <label for="setup_password_confirm" class="hb-label">Confirm Password</label>
                    <input type="password" id="setup_password_confirm" name="setup_password_confirm" class="form-control hb-input"
                           autocomplete="new-password" required>
                </div>

                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" id="seed_sample_users" name="seed_sample_users" value="1" checked>
                    <label class="form-check-label small text-silver" for="seed_sample_users">
                        Also create sample <strong>Coach</strong> (<code>coach@hbfitess.local</code>) and <strong>User</strong> (<code>user@hbfitess.local</code>) accounts with the same password
                    </label>
                </div>

                <button type="submit" class="btn btn-hb-primary w-100 py-2">
                    <i class="fa-solid fa-shield-halved me-2"></i>Create Hashed Local Accounts
                </button>
            </form>
        <?php else: ?>
            <!-- LOGIN FORM: STRICTLY EMAIL AND PASSWORD ONLY -->
            <form method="POST" action="index.php?action=login" data-validate="true" data-form-type="login" novalidate>
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">

                <div class="mb-3">
                    <label for="email" class="hb-label">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control hb-input"
                           placeholder="you@hbfitess.local" autocomplete="email"
                           value="<?= htmlspecialchars((string)($old['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
                    <div class="field-error"></div>
                </div>

                <div class="mb-4">
                    <label for="password" class="hb-label">Password</label>
                    <div class="input-group">
                        <input type="password" id="password" name="password" class="form-control hb-input"
                               placeholder="Enter your password" autocomplete="current-password" required>
                        <button type="button" class="btn btn-hb-dark" data-toggle-password="password" aria-label="Toggle password">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                    <div class="field-error"></div>
                </div>

                <button type="submit" class="btn btn-hb-primary w-100 py-2">
                    <i class="fa-solid fa-right-to-bracket me-2"></i>Log In
                </button>
            </form>

            <div class="text-center mt-3 small">
                <a href="index.php?action=forgot_password">Forgot password?</a>
            </div>

            <?php if ($needsSetup): ?>
                <div class="mt-3 p-3 rounded-3 small" style="background:#0D0D10;border:1px solid var(--hb-border-accent);">
                    <strong class="text-coral"><i class="fa-solid fa-screwdriver-wrench me-1"></i>No admin account yet?</strong>
                    <a href="index.php?action=setup" class="text-white text-decoration-underline ms-1">Run local setup</a>
                </div>
            <?php endif; ?>

            <div class="text-center mt-4 small text-silver">
                Don't have an account yet? <a href="index.php?action=register" class="fw-bold">Register here</a>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php
$content = (string)ob_get_clean();
$title   = ($isSetup ? 'Local Setup' : 'Log In') . ' — HB FITNESS';
require __DIR__ . '/../layout.php';
