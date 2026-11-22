<?php
/**
 * Views/Backoffice/manageRoles.php
 * Role & Account Status Management Console (SQL JOIN counts + CSRF-protected updates)
 * Expects: $currentUser (User), $roles (Role[]), $statuses (UserStatus[]), $users (User[]), $errors (array), $csrfToken (string)
 */
ob_start();
?>
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

<div class="row g-3 mb-4">
    <?php foreach ($roles as $r): /** @var Role $r */ ?>
        <div class="col-md-4">
            <div class="bo-stat-card">
                <div>
                    <div class="eyebrow mb-1">Role &middot; <?= htmlspecialchars($r->getName(), ENT_QUOTES, 'UTF-8') ?></div>
                    <div class="font-heading fs-2 fw-bold"><?= $r->getUsersCount() ?> User(s)</div>
                </div>
                <span class="hb-badge hb-badge-<?= htmlspecialchars($r->getName(), ENT_QUOTES, 'UTF-8') ?>">
                    <?= htmlspecialchars($r->getName(), ENT_QUOTES, 'UTF-8') ?>
                </span>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="hb-card">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <div class="eyebrow mb-1">Access Governance</div>
            <h2 class="h5 text-uppercase mb-0">Assign Roles &amp; Account Statuses</h2>
        </div>
        <a href="index.php?action=admin_users" class="btn btn-hb-dark btn-sm">Back to User Directory</a>
    </div>

    <div class="table-responsive">
        <table class="hb-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Assigned Role</th>
                    <th>Account Status</th>
                    <th>Apply</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): /** @var User $u */
                    $isSelf = $u->getId() === $currentUser->getId();
                ?>
                    <tr>
                        <td><strong>#<?= $u->getId() ?></strong></td>
                        <td>
                            <div class="fw-bold">
                                <?= htmlspecialchars($u->getFullName(), ENT_QUOTES, 'UTF-8') ?>
                                <?php if ($isSelf): ?>
                                    <span class="hb-badge hb-badge-admin ms-1" style="font-size:0.62rem;">YOU</span>
                                <?php endif; ?>
                            </div>
                            <small class="text-silver"><?= htmlspecialchars($u->getEmail(), ENT_QUOTES, 'UTF-8') ?></small>
                        </td>
                        <td colspan="3">
                            <form method="POST" action="index.php?action=admin_roles" class="row g-2 align-items-center">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="user_id" value="<?= $u->getId() ?>">

                                <div class="col-sm-4">
                                    <select name="role_id" class="form-select form-select-sm hb-input">
                                        <?php foreach ($roles as $r): /** @var Role $r */ ?>
                                            <option value="<?= $r->getId() ?>" <?= $u->getRoleId() === $r->getId() ? 'selected' : '' ?>>
                                                <?= htmlspecialchars(ucfirst($r->getName()), ENT_QUOTES, 'UTF-8') ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-sm-4">
                                    <select name="status_id" class="form-select form-select-sm hb-input">
                                        <?php foreach ($statuses as $s): /** @var UserStatus $s */ ?>
                                            <option value="<?= $s->getId() ?>" <?= $u->getStatusId() === $s->getId() ? 'selected' : '' ?>>
                                                <?= htmlspecialchars(ucfirst($s->getName()), ENT_QUOTES, 'UTF-8') ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-sm-4">
                                    <button type="submit" class="btn btn-hb-primary btn-sm w-100">
                                        <i class="fa-solid fa-floppy-disk me-1"></i> Save
                                    </button>
                                </div>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($users)): ?>
                    <tr><td colspan="5" class="text-center text-silver py-4">No users available.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php
$content     = (string)ob_get_clean();
$title       = 'Manage Roles & Statuses — HB FITNESS Backoffice';
$pageHeading = 'Roles & Account Statuses';
require __DIR__ . '/../layout_admin.php';
