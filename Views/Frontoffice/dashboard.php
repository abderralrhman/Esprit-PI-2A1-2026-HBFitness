<?php
/**
 * Views/Frontoffice/dashboard.php
 * Protected Frontoffice Dashboard (User & Coach Space)
 * Expects: $currentUser (User), $stats (array)
 */
ob_start();
$roleName = $currentUser->getRoleName();
$isCoach  = $roleName === 'coach';
$isAdmin  = $roleName === 'admin';
?>
<div class="container py-4">
    <div class="fo-banner d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <div class="eyebrow mb-1"><?= $isCoach ? 'Authorized Coach Space' : 'Frontoffice Member Space' ?></div>
            <h1 class="h2 text-uppercase mb-1">Welcome, <?= htmlspecialchars($currentUser->getFullName(), ENT_QUOTES, 'UTF-8') ?></h1>
            <p class="text-silver mb-0">
                <?= $isCoach
                    ? 'You are signed in with Coach privileges. Manage your profile and monitor community growth.'
                    : 'Manage your personal HB FITNESS profile, update your credentials, and track your account security.' ?>
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="index.php?action=profile" class="btn btn-hb-primary">
                <i class="fa-solid fa-id-card me-1"></i> View Profile
            </a>
            <a href="index.php?action=edit_profile" class="btn btn-hb-outline">
                <i class="fa-solid fa-user-pen me-1"></i> Edit Profile
            </a>
            <a href="index.php?action=workouts" class="btn btn-hb-outline">
                <i class="fa-solid fa-dumbbell me-1"></i> Browse Workout Plans
            </a>
            <?php if ($isAdmin): ?>
                <a href="index.php?action=admin_dashboard" class="btn btn-hb-dark">
                    <i class="fa-solid fa-shield-halved me-1 text-coral"></i> Backoffice
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="hb-card h-100">
                <div class="eyebrow mb-2">Account Role</div>
                <div class="d-flex align-items-center justify-content-between">
                    <h3 class="h3 text-uppercase mb-0"><?= htmlspecialchars($roleName, ENT_QUOTES, 'UTF-8') ?></h3>
                    <span class="hb-badge hb-badge-<?= htmlspecialchars($roleName, ENT_QUOTES, 'UTF-8') ?>">
                        <i class="fa-solid <?= $isCoach ? 'fa-medal' : ($isAdmin ? 'fa-shield-halved' : 'fa-user') ?>"></i>
                        <?= htmlspecialchars($roleName, ENT_QUOTES, 'UTF-8') ?>
                    </span>
                </div>
                <p class="small text-silver mt-2 mb-0">Verified server-side from database via SQL JOIN on every request.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="hb-card h-100">
                <div class="eyebrow mb-2">Account Status</div>
                <div class="d-flex align-items-center justify-content-between">
                    <h3 class="h3 text-uppercase mb-0"><?= htmlspecialchars($currentUser->getStatusName(), ENT_QUOTES, 'UTF-8') ?></h3>
                    <span class="hb-badge hb-badge-<?= htmlspecialchars($currentUser->getStatusName(), ENT_QUOTES, 'UTF-8') ?>">
                        <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($currentUser->getStatusName(), ENT_QUOTES, 'UTF-8') ?>
                    </span>
                </div>
                <p class="small text-silver mt-2 mb-0">Suspended accounts are automatically blocked from protected routes.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="hb-card h-100">
                <div class="eyebrow mb-2">Community Size</div>
                <div class="d-flex align-items-center justify-content-between">
                    <h3 class="h3 mb-0"><?= (int)($stats['total_users'] ?? 0) ?> Members</h3>
                    <span class="hb-badge hb-badge-user"><?= (int)($stats['total_coaches'] ?? 0) ?> Coaches</span>
                </div>
                <p class="small text-silver mt-2 mb-0">HB FITNESS &middot; Train smart. Reuse more.</p>
            </div>
        </div>
    </div>

    <div class="hb-card">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <h2 class="h4 text-uppercase mb-0"><i class="fa-solid fa-user-shield text-coral me-2"></i>Account Summary</h2>
            <a href="index.php?action=edit_profile" class="btn btn-hb-outline btn-sm">Update Info</a>
        </div>
        <div class="table-responsive">
            <table class="hb-table">
                <tbody>
                    <tr>
                        <th style="width:220px;color:var(--hb-coral);">User ID</th>
                        <td>#<?= $currentUser->getId() ?></td>
                    </tr>
                    <tr>
                        <th style="color:var(--hb-coral);">Full Name</th>
                        <td><?= htmlspecialchars($currentUser->getFullName(), ENT_QUOTES, 'UTF-8') ?></td>
                    </tr>
                    <tr>
                        <th style="color:var(--hb-coral);">Email Address</th>
                        <td><?= htmlspecialchars($currentUser->getEmail(), ENT_QUOTES, 'UTF-8') ?></td>
                    </tr>
                    <tr>
                        <th style="color:var(--hb-coral);">Phone</th>
                        <td><?= htmlspecialchars($currentUser->getPhone() ?? 'Not provided', ENT_QUOTES, 'UTF-8') ?></td>
                    </tr>
                    <tr>
                        <th style="color:var(--hb-coral);">Member Since</th>
                        <td><?= htmlspecialchars($currentUser->getCreatedAt(), ENT_QUOTES, 'UTF-8') ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php
$content = (string)ob_get_clean();
$title   = ($isCoach ? 'Coach Space' : 'Frontoffice Dashboard') . ' — HB FITNESS';
require __DIR__ . '/../layout.php';
