<?php
/**
 * Views/Backoffice/createUser.php
 * Admin Create User Form (CSRF + Server & Client Validation)
 * Expects: $roles (Role[]), $statuses (UserStatus[]), $errors (array), $old (array), $csrfToken (string)
 */
ob_start();
?>
<div class="hb-card mx-auto" style="max-width:740px;">
    <div class="d-flex justify-content-between align-items-center pb-3 mb-4" style="border-bottom:1px solid var(--hb-border);">
        <div>
            <div class="eyebrow mb-1">User CRUD &middot; Create</div>
            <h2 class="h4 text-uppercase mb-0">Create New User Account</h2>
        </div>
        <a href="index.php?action=admin_users" class="btn btn-hb-dark btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to List
        </a>
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

    <form method="POST" action="index.php?action=admin_create_user" data-validate="true" novalidate>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">

        <div class="mb-3">
            <label for="full_name" class="hb-label">Full Name</label>
            <input type="text" id="full_name" name="full_name" class="form-control hb-input"
                   placeholder="e.g. Sami Ben Youssef"
                   value="<?= htmlspecialchars((string)($old['full_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
            <div class="field-error"></div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label for="email" class="hb-label">Email Address</label>
                <input type="email" id="email" name="email" class="form-control hb-input"
                       placeholder="member@hbfitess.local"
                       value="<?= htmlspecialchars((string)($old['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
                <div class="field-error"></div>
            </div>
            <div class="col-md-6">
                <label for="phone" class="hb-label">Phone Number (Optional)</label>
                <input type="text" id="phone" name="phone" class="form-control hb-input"
                       placeholder="+216 55 000 000"
                       value="<?= htmlspecialchars((string)($old['phone'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                <div class="field-error"></div>
            </div>
        </div>

        <div class="mb-3">
            <label for="password" class="hb-label">Password (min 8 chars, letters + digits)</label>
            <div class="input-group">
                <input type="password" id="password" name="password" class="form-control hb-input"
                       autocomplete="new-password"
                       data-strength-bar="createStrengthBar" data-strength-label="createStrengthLabel" required>
                <button type="button" class="btn btn-hb-dark" data-toggle-password="password" aria-label="Toggle password">
                    <i class="fa-solid fa-eye"></i>
                </button>
            </div>
            <div class="strength-track"><div class="strength-bar" id="createStrengthBar"></div></div>
            <small id="createStrengthLabel" class="text-silver" style="font-size:0.75rem;"></small>
            <div class="field-error"></div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label for="role_id" class="hb-label">Role</label>
                <select id="role_id" name="role_id" class="form-select hb-input" required>
                    <?php foreach ($roles as $r): /** @var Role $r */ ?>
                        <option value="<?= $r->getId() ?>" <?= (int)($old['role_id'] ?? 2) === $r->getId() ? 'selected' : '' ?>>
                            <?= htmlspecialchars(ucfirst($r->getName()), ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label for="status_id" class="hb-label">Account Status</label>
                <select id="status_id" name="status_id" class="form-select hb-input" required>
                    <?php foreach ($statuses as $s): /** @var UserStatus $s */ ?>
                        <option value="<?= $s->getId() ?>" <?= (int)($old['status_id'] ?? 1) === $s->getId() ? 'selected' : '' ?>>
                            <?= htmlspecialchars(ucfirst($s->getName()), ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2">
            <a href="index.php?action=admin_users" class="btn btn-hb-dark">Cancel</a>
            <button type="submit" class="btn btn-hb-primary">
                <i class="fa-solid fa-floppy-disk me-1"></i> Create User
            </button>
        </div>
    </form>
</div>
<?php
$content     = (string)ob_get_clean();
$title       = 'Create User — HB FITNESS Backoffice';
$pageHeading = 'Create User';
require __DIR__ . '/../layout_admin.php';
