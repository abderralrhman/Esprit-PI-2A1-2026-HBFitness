<?php
/**
 * Views/auth/login.php — HB FITNESS Blog Login
 * Displays Local Dev 3-Role One-Click Login Buttons (when enabled on localhost)
 * above the standard Email + Password login form.
 */
$title             = 'Log In — HB FITNESS Blog';
$showQuickLogin    = !empty($quickLoginEnabled);
ob_start();
?>
<section class="container py-5">
    <div class="row justify-content-center align-items-center" style="min-height:70vh;">
        <div class="<?= $showQuickLogin ? 'col-lg-9 col-xl-8' : 'col-md-7 col-lg-5' ?>">
            <div class="hb-card p-4 p-md-5 reveal">
                <div class="text-center mb-4">
                    <img src="Views/assets/images/hb-monogram.svg" alt="HB FITNESS" width="58" height="58" class="mb-2">
                    <h1 class="h2 mb-1">HB <span class="text-coral">FITNESS</span> Login</h1>
                    <p class="small text-silver mb-0">Sign in to comment on articles, publish coach guides, or moderate the blog.</p>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="hb-alert hb-alert-error mb-4" role="alert">
                        <i class="fa-solid fa-triangle-exclamation mt-1"></i>
                        <ul class="mb-0 ps-3">
                            <?php foreach ($errors as $err): ?>
                                <li><?= htmlspecialchars((string)$err, ENT_QUOTES, 'UTF-8') ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if ($showQuickLogin): ?>
                    <!-- LOCAL DEVELOPMENT ONLY: One-Click Role Validation Buttons (CSRF-protected POST) -->
                    <div class="mb-4">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                            <span class="font-display small text-uppercase text-silver" style="letter-spacing:0.12em;">
                                <i class="fa-solid fa-bolt text-coral me-1"></i>Quick Local Role Access
                            </span>
                            <span class="hb-badge hb-badge-admin">Localhost Validation Only</span>
                        </div>

                        <div class="row g-3">
                            <!-- 1. ENTER AS ADMIN -->
                            <div class="col-md-4">
                                <form method="POST" action="index.php?action=quick_login" class="h-100">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string)$csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="role_slot" value="admin">
                                    <button type="submit" class="hb-role-btn hb-role-btn-admin w-100 h-100">
                                        <span class="hb-role-icon"><i class="fa-solid fa-shield-halved"></i></span>
                                        <span class="hb-role-title">ENTER AS ADMIN</span>
                                        <span class="hb-role-desc">Back Office · Full Article CRUD &amp; Comment Moderation</span>
                                    </button>
                                </form>
                            </div>

                            <!-- 2. ENTER AS USER -->
                            <div class="col-md-4">
                                <form method="POST" action="index.php?action=quick_login" class="h-100">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string)$csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="role_slot" value="user">
                                    <button type="submit" class="hb-role-btn hb-role-btn-user w-100 h-100">
                                        <span class="hb-role-icon"><i class="fa-solid fa-user"></i></span>
                                        <span class="hb-role-title">ENTER AS USER</span>
                                        <span class="hb-role-desc">Front Office · Read Articles &amp; Manage Own Comments</span>
                                    </button>
                                </form>
                            </div>

                            <!-- 3. ENTER AS COACH -->
                            <div class="col-md-4">
                                <form method="POST" action="index.php?action=quick_login" class="h-100">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string)$csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="role_slot" value="coach">
                                    <button type="submit" class="hb-role-btn hb-role-btn-coach w-100 h-100">
                                        <span class="hb-role-icon"><i class="fa-solid fa-medal"></i></span>
                                        <span class="hb-role-title">ENTER AS COACH</span>
                                        <span class="hb-role-desc">Front Office · Publish Own Articles &amp; Own Comments</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="hb-divider my-4">
                        <span>OR SIGN IN WITH EMAIL &amp; PASSWORD</span>
                    </div>
                <?php endif; ?>

                <!-- Standard Email + Password Login Form (Preserved) -->
                <div class="mx-auto" style="max-width: 440px;">
                    <form method="POST" action="index.php?action=login" data-validate="login" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string)$csrfToken, ENT_QUOTES, 'UTF-8') ?>">

                        <div class="mb-3">
                            <label for="login-email" class="form-label">Email Address</label>
                            <input
                                type="email"
                                id="login-email"
                                name="email"
                                class="form-control hb-input"
                                placeholder="you@hbfitness.tn"
                                value="<?= htmlspecialchars((string)($old['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                autocomplete="email"
                                required
                            >
                        </div>

                        <div class="mb-4">
                            <label for="login-password" class="form-label">Password</label>
                            <div class="input-group">
                                <input
                                    type="password"
                                    id="login-password"
                                    name="password"
                                    class="form-control hb-input"
                                    placeholder="Enter your password"
                                    autocomplete="current-password"
                                    required
                                >
                                <button type="button" class="btn btn-hb-outline" data-toggle-password="login-password" aria-label="Toggle password visibility">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-hb-primary w-100 py-2">
                            <i class="fa-solid fa-right-to-bracket me-2"></i>Sign In
                        </button>
                    </form>
                </div>

                <div class="mt-4 pt-3 border-top border-secondary border-opacity-25 text-center">
                    <a href="index.php?action=articles" class="small text-silver">
                        <i class="fa-solid fa-arrow-left me-1"></i>Continue browsing articles as Visitor
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
<?php
$content = (string)ob_get_clean();
require __DIR__ . '/../layout.php';
