<?php
/**
 * Views/Frontoffice/home.php
 * Dynamic Animated Homepage (Bootstrap 5 + HB FITNESS Brand Identity)
 * Expects: $currentUser (?User), $stats (array), $needsSetup (bool)
 */
ob_start();
?>
<section class="fo-hero">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6 reveal">
                <span class="hb-badge hb-badge-admin mb-3">
                    <i class="fa-solid fa-fire-flame-curved"></i> HB FITNESS &middot; TRAIN SMART. REUSE MORE.
                </span>
                <h1 class="fo-hero-title mb-3">
                    TRAIN SMART AND GROW<br>
                    <span class="gradient-text typed-word" id="typedWord">STRONGER</span>
                </h1>
                <p class="lead text-silver mb-4" style="max-width:560px;">
                    HB FITNESS connects Athletes, Coaches, and Administrators in one sustainable fitness platform with secure role-based access and profile management.
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <?php if ($currentUser): ?>
                        <a href="index.php?action=<?= $currentUser->getRoleName() === 'admin' ? 'admin_dashboard' : 'dashboard' ?>" class="btn btn-hb-primary btn-lg">
                            <i class="fa-solid fa-rocket me-2"></i>Open My Space
                        </a>
                        <a href="index.php?action=profile" class="btn btn-hb-outline btn-lg">
                            <i class="fa-solid fa-id-card me-2"></i>My Profile
                        </a>
                    <?php else: ?>
                        <a href="index.php?action=register" class="btn btn-hb-primary btn-lg">
                            <i class="fa-solid fa-user-plus me-2"></i>Join for Free
                        </a>
                        <a href="index.php?action=login" class="btn btn-hb-outline btn-lg">
                            <i class="fa-solid fa-right-to-bracket me-2"></i>Log In
                        </a>
                    <?php endif; ?>
                    <a href="index.php?action=workouts" class="btn btn-hb-outline btn-lg">
                        <i class="fa-solid fa-dumbbell me-2"></i>Browse Workout Plans
                    </a>
                </div>

                <?php if (!empty($needsSetup)): ?>
                    <div class="hb-alert hb-alert-error mt-4 mb-0">
                        <i class="fa-solid fa-screwdriver-wrench mt-1"></i>
                        <div>
                            <strong>First-time local setup:</strong> No administrator account exists yet.
                            <a href="index.php?action=setup" class="text-white text-decoration-underline fw-bold ms-1">Initialize local demo accounts &rarr;</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="col-lg-6 reveal">
                <div class="fo-hero-visual">
                    <div class="pulse-ring r1"></div>
                    <div class="pulse-ring r2"></div>
                    <div class="pulse-ring r3"></div>

                    <div class="hero-center-logo">
                        <img src="Views/assets/images/hb-fitness-logo.svg" alt="HB FITNESS Monogram">
                    </div>

                    <div class="floating-chip" style="top:28px;left:24px;">
                        <i class="fa-solid fa-users"></i> <?= (int)($stats['total_users'] ?? 0) ?> registered members
                    </div>
                    <div class="floating-chip" style="top:135px;right:24px;animation-delay:1s;">
                        <i class="fa-solid fa-medal"></i> <?= (int)($stats['total_coaches'] ?? 0) ?> certified coaches
                    </div>
                    <div class="floating-chip" style="bottom:98px;left:28px;animation-delay:1.9s;">
                        <i class="fa-solid fa-shield-halved"></i> <?= (int)($stats['total_active'] ?? 0) ?> active accounts
                    </div>

                    <div class="equalizer-bars">
                        <div class="eq-bar" style="animation-delay:.05s;"></div>
                        <div class="eq-bar" style="animation-delay:.25s;"></div>
                        <div class="eq-bar" style="animation-delay:.12s;"></div>
                        <div class="eq-bar" style="animation-delay:.4s;"></div>
                        <div class="eq-bar" style="animation-delay:.18s;"></div>
                        <div class="eq-bar" style="animation-delay:.48s;"></div>
                        <div class="eq-bar" style="animation-delay:.22s;"></div>
                        <div class="eq-bar" style="animation-delay:.35s;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Animated Live Stats Band -->
<section class="py-3">
    <div class="container">
        <div class="fo-stats-band reveal">
            <div class="row g-4 text-center">
                <div class="col-6 col-md-3">
                    <div class="font-heading display-5 fw-bold counter-value" data-target="<?= (int)($stats['total_users'] ?? 0) ?>">0</div>
                    <div class="eyebrow">Total Members</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="font-heading display-5 fw-bold counter-value" data-target="<?= (int)($stats['total_active'] ?? 0) ?>">0</div>
                    <div class="eyebrow">Active Accounts</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="font-heading display-5 fw-bold counter-value" data-target="<?= (int)($stats['total_coaches'] ?? 0) ?>">0</div>
                    <div class="eyebrow">Coaches</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="font-heading display-5 fw-bold counter-value" data-target="<?= (int)($stats['total_admins'] ?? 0) ?>">0</div>
                    <div class="eyebrow">Administrators</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Three Pillars -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5 reveal">
            <div class="eyebrow mb-2">02 &middot; Platform Pillars</div>
            <h2 class="display-6 text-uppercase">Everything Athletes Need in One Hub</h2>
            <p class="text-silver mx-auto" style="max-width:600px;">
                Built around a unified User, Role, and Account Status core with strict MVC architecture and session security.
            </p>
        </div>

        <div class="row g-4">
            <div class="col-md-4 reveal">
                <div class="hb-card hb-card-hover h-100">
                    <div class="fo-pillar-icon"><i class="fa-solid fa-dumbbell"></i></div>
                    <h3 class="h4 text-uppercase">Plans For You</h3>
                    <p class="text-silver mb-0">Dedicated role-aware spaces for Athletes, Coaches, and Administrators with instant access verification.</p>
                </div>
            </div>
            <div class="col-md-4 reveal">
                <div class="hb-card hb-card-hover h-100">
                    <div class="fo-pillar-icon"><i class="fa-solid fa-apple-whole"></i></div>
                    <h3 class="h4 text-uppercase">Healthy Food</h3>
                    <p class="text-silver mb-0">Self-service profile management so members can keep their personal details and security credentials updated.</p>
                </div>
            </div>
            <div class="col-md-4 reveal">
                <div class="hb-card hb-card-hover h-100">
                    <div class="fo-pillar-icon"><i class="fa-solid fa-recycle"></i></div>
                    <h3 class="h4 text-uppercase">Used-Gear Market</h3>
                    <p class="text-silver mb-0">Trusted community governance backed by admin moderation, active/suspended account controls, and CSRF protection.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- How It Works Steps -->
<section class="py-4">
    <div class="container">
        <div class="text-center mb-4 reveal">
            <div class="eyebrow mb-2">04 &middot; Workflow</div>
            <h2 class="display-6 text-uppercase">How HB FITNESS Works</h2>
        </div>
        <div class="row g-4">
            <div class="col-sm-6 col-lg-3 reveal">
                <div class="fo-step-card h-100">
                    <div class="fo-step-num">01</div>
                    <h4 class="h5 text-uppercase">Register</h4>
                    <p class="small text-silver mb-0">Create your free athlete account with strong hashed password security.</p>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3 reveal">
                <div class="fo-step-card h-100">
                    <div class="fo-step-num">02</div>
                    <h4 class="h5 text-uppercase">Log In</h4>
                    <p class="small text-silver mb-0">Authenticate securely with Email &amp; Password and automatic role redirection.</p>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3 reveal">
                <div class="fo-step-card h-100">
                    <div class="fo-step-num">03</div>
                    <h4 class="h5 text-uppercase">Personalize</h4>
                    <p class="small text-silver mb-0">Access your Frontoffice dashboard and update your profile anytime.</p>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3 reveal">
                <div class="fo-step-card h-100">
                    <div class="fo-step-num">04</div>
                    <h4 class="h5 text-uppercase">Govern</h4>
                    <p class="small text-silver mb-0">Admins manage users, roles, and statuses from the Backoffice console.</p>
                </div>
            </div>
        </div>
    </div>
</section>
<?php
$content = (string)ob_get_clean();
$title   = 'HB FITNESS — Train Smart. Reuse More.';
require __DIR__ . '/../layout.php';
