<?php
/**
 * Views/Backoffice/dashboard.php
 * Backoffice Admin Dashboard (TailAdmin-style layout_admin.php)
 * Expects: $currentUser (User), $stats (array), $recentUsers (User[]), $roles (Role[]), $statuses (UserStatus[])
 */
ob_start();
?>
<!-- KPI Statistics Grid -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="bo-stat-card">
            <div>
                <div class="eyebrow mb-1">Total Accounts</div>
                <div class="font-heading fs-2 fw-bold"><?= (int)$stats['total_users'] ?></div>
            </div>
            <div class="bo-stat-icon"><i class="fa-solid fa-users"></i></div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="bo-stat-card">
            <div>
                <div class="eyebrow mb-1">Active Accounts</div>
                <div class="font-heading fs-2 fw-bold" style="color:#6EE7B7;"><?= (int)$stats['total_active'] ?></div>
            </div>
            <div class="bo-stat-icon"><i class="fa-solid fa-user-check"></i></div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="bo-stat-card">
            <div>
                <div class="eyebrow mb-1">Coaches &amp; Admins</div>
                <div class="font-heading fs-2 fw-bold text-coral"><?= (int)$stats['total_coaches'] + (int)$stats['total_admins'] ?></div>
            </div>
            <div class="bo-stat-icon"><i class="fa-solid fa-user-shield"></i></div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="bo-stat-card">
            <div>
                <div class="eyebrow mb-1">Suspended</div>
                <div class="font-heading fs-2 fw-bold"><?= (int)$stats['total_suspended'] ?></div>
            </div>
            <div class="bo-stat-icon"><i class="fa-solid fa-user-slash"></i></div>
        </div>
    </div>
</div>

<div class="hb-card d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h2 class="h5 text-uppercase mb-1"><i class="fa-solid fa-dumbbell text-coral me-2"></i>Workout Programs</h2>
        <p class="text-silver mb-0">Create training plans and manage their exercises.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="index.php?action=workouts" class="btn btn-hb-outline">Browse plans</a>
        <a href="index.php?action=admin_workouts" class="btn btn-hb-primary">Manage workouts</a>
    </div>
</div>

<!-- Role & Status Distribution -->
<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="hb-card h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h5 text-uppercase mb-0"><i class="fa-solid fa-sitemap text-coral me-2"></i>Users by Role (SQL JOIN)</h2>
                <a href="index.php?action=admin_roles" class="btn btn-hb-outline btn-sm">Manage Roles</a>
            </div>
            <div class="row g-3">
                <?php foreach ($roles as $r): /** @var Role $r */ ?>
                    <div class="col-4">
                        <div class="p-3 rounded-3 text-center" style="background:#0D0D10;border:1px solid var(--hb-border);">
                            <div class="font-heading fs-2 fw-bold"><?= $r->getUsersCount() ?></div>
                            <span class="hb-badge hb-badge-<?= htmlspecialchars($r->getName(), ENT_QUOTES, 'UTF-8') ?>">
                                <?= htmlspecialchars($r->getName(), ENT_QUOTES, 'UTF-8') ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="hb-card h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h5 text-uppercase mb-0"><i class="fa-solid fa-toggle-on text-coral me-2"></i>Account Statuses</h2>
                <a href="index.php?action=admin_create_user" class="btn btn-hb-primary btn-sm">
                    <i class="fa-solid fa-user-plus me-1"></i> Create User
                </a>
            </div>
            <div class="row g-3">
                <?php foreach ($statuses as $s): /** @var UserStatus $s */ ?>
                    <div class="col-6">
                        <div class="p-3 rounded-3 text-center" style="background:#0D0D10;border:1px solid var(--hb-border);">
                            <div class="font-heading fs-2 fw-bold"><?= $s->getUsersCount() ?></div>
                            <span class="hb-badge hb-badge-<?= htmlspecialchars($s->getName(), ENT_QUOTES, 'UTF-8') ?>">
                                <?= htmlspecialchars($s->getName(), ENT_QUOTES, 'UTF-8') ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Recent Users Table -->
<div class="hb-card">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h2 class="h5 text-uppercase mb-0"><i class="fa-solid fa-clock-rotate-left text-coral me-2"></i>Recent Registered Users</h2>
        <a href="index.php?action=admin_users" class="btn btn-hb-outline btn-sm">View All Users &rarr;</a>
    </div>
    <div class="table-responsive">
        <table class="hb-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentUsers as $u): /** @var User $u */ ?>
                    <tr>
                        <td><strong>#<?= $u->getId() ?></strong></td>
                        <td>
                            <div class="fw-bold"><?= htmlspecialchars($u->getFullName(), ENT_QUOTES, 'UTF-8') ?></div>
                            <small class="text-silver"><?= htmlspecialchars($u->getEmail(), ENT_QUOTES, 'UTF-8') ?></small>
                        </td>
                        <td>
                            <span class="hb-badge hb-badge-<?= htmlspecialchars($u->getRoleName(), ENT_QUOTES, 'UTF-8') ?>">
                                <?= htmlspecialchars($u->getRoleName(), ENT_QUOTES, 'UTF-8') ?>
                            </span>
                        </td>
                        <td>
                            <span class="hb-badge hb-badge-<?= htmlspecialchars($u->getStatusName(), ENT_QUOTES, 'UTF-8') ?>">
                                <?= htmlspecialchars($u->getStatusName(), ENT_QUOTES, 'UTF-8') ?>
                            </span>
                        </td>
                        <td class="small text-silver"><?= htmlspecialchars($u->getCreatedAt(), ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <a href="index.php?action=admin_update_user&id=<?= $u->getId() ?>" class="btn btn-hb-outline btn-sm">
                                <i class="fa-solid fa-pen"></i>
                            </a>
                            <?php if ($u->getId() !== $currentUser->getId()): ?>
                                <a href="index.php?action=admin_delete_user&id=<?= $u->getId() ?>" class="btn btn-hb-danger btn-sm">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($recentUsers)): ?>
                    <tr><td colspan="6" class="text-center text-silver py-4">No users found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php
$content     = (string)ob_get_clean();
$title       = 'Admin Dashboard — HB FITNESS';
$pageHeading = 'Admin Dashboard';
require __DIR__ . '/../layout_admin.php';
