<?php
/**
 * Views/Backoffice/listUsers.php
 * User Directory with Search, Role Filter, Status Filter & SQL JOIN display
 * Expects: $currentUser (User), $users (User[]), $roles (Role[]), $statuses (UserStatus[]), $search (string), $filterRole (?int), $filterStat (?int)
 */
ob_start();
?>
<!-- Search & Filter Bar -->
<div class="hb-card mb-4">
    <form method="GET" action="index.php" class="row g-3 align-items-end">
        <input type="hidden" name="action" value="admin_users">

        <div class="col-md-4">
            <label for="search" class="hb-label">Search Name or Email</label>
            <input type="text" id="search" name="search" class="form-control hb-input"
                   placeholder="e.g. Amira or @hbfitess.local"
                   value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <div class="col-md-3">
            <label for="role_id" class="hb-label">Filter by Role</label>
            <select id="role_id" name="role_id" class="form-select hb-input">
                <option value="">All Roles</option>
                <?php foreach ($roles as $r): /** @var Role $r */ ?>
                    <option value="<?= $r->getId() ?>" <?= $filterRole === $r->getId() ? 'selected' : '' ?>>
                        <?= htmlspecialchars(ucfirst($r->getName()), ENT_QUOTES, 'UTF-8') ?> (<?= $r->getUsersCount() ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-3">
            <label for="status_id" class="hb-label">Filter by Status</label>
            <select id="status_id" name="status_id" class="form-select hb-input">
                <option value="">All Statuses</option>
                <?php foreach ($statuses as $s): /** @var UserStatus $s */ ?>
                    <option value="<?= $s->getId() ?>" <?= $filterStat === $s->getId() ? 'selected' : '' ?>>
                        <?= htmlspecialchars(ucfirst($s->getName()), ENT_QUOTES, 'UTF-8') ?> (<?= $s->getUsersCount() ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-hb-primary w-100">
                <i class="fa-solid fa-magnifying-glass"></i> Filter
            </button>
            <a href="index.php?action=admin_users" class="btn btn-hb-dark" title="Reset Filters">
                <i class="fa-solid fa-rotate-left"></i>
            </a>
        </div>
    </form>
</div>

<!-- Users Table -->
<div class="hb-card">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h2 class="h5 text-uppercase mb-0">
            <i class="fa-solid fa-users text-coral me-2"></i>User Accounts (<?= count($users) ?>)
        </h2>
        <a href="index.php?action=admin_create_user" class="btn btn-hb-primary btn-sm">
            <i class="fa-solid fa-user-plus me-1"></i> Create User
        </a>
    </div>

    <div class="table-responsive">
        <table class="hb-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Phone</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): /** @var User $u */
                    $isSelf = $u->getId() === $currentUser->getId();
                ?>
                    <tr>
                        <td><strong>#<?= $u->getId() ?></strong></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="hb-avatar" style="width:36px;height:36px;font-size:0.82rem;">
                                    <?= htmlspecialchars($u->getInitials(), ENT_QUOTES, 'UTF-8') ?>
                                </div>
                                <div>
                                    <div class="fw-bold">
                                        <?= htmlspecialchars($u->getFullName(), ENT_QUOTES, 'UTF-8') ?>
                                        <?php if ($isSelf): ?>
                                            <span class="hb-badge hb-badge-admin ms-1" style="font-size:0.62rem;">YOU</span>
                                        <?php endif; ?>
                                    </div>
                                    <small class="text-silver"><?= htmlspecialchars($u->getEmail(), ENT_QUOTES, 'UTF-8') ?></small>
                                </div>
                            </div>
                        </td>
                        <td class="small text-silver"><?= htmlspecialchars($u->getPhone() ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
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
                            <div class="d-flex gap-2">
                                <a href="index.php?action=admin_update_user&id=<?= $u->getId() ?>" class="btn btn-hb-outline btn-sm" title="Edit User">
                                    <i class="fa-solid fa-pen me-1"></i> Edit
                                </a>
                                <?php if (!$isSelf): ?>
                                    <a href="index.php?action=admin_delete_user&id=<?= $u->getId() ?>" class="btn btn-hb-danger btn-sm" title="Delete User">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-silver py-4">
                            No users match your current search or filters.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php
$content     = (string)ob_get_clean();
$title       = 'Manage Users — HB FITNESS Backoffice';
$pageHeading = 'User Directory';
require __DIR__ . '/../layout_admin.php';
