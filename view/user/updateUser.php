<?php
require_once __DIR__ . '/../../controller/AuthController.php';
$currentUser = AuthController::requireAdmin();

$userC = new UserController();
$id = isset($_GET['id']) ? (int)$_GET['id'] : (int)($_POST['id'] ?? 0);
$existing = $userC->getUserById($id);

if (!$existing) {
    AuthController::flash("User #$id not found.", 'error');
    header("Location: listUsers.php");
    exit;
}

// Contrôle de saisie PHP (Internal to updateUser.php)
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role     = $_POST['role'] ?? 'user';
    $phone    = trim($_POST['phone'] ?? '');

    $errors = [];
    if ($fullName === '' || mb_strlen($fullName) < 3 || !preg_match('/^[\p{L}\s\-\'\.]+$/u', $fullName)) {
        $errors[] = "Full Name must contain only letters and spaces (min 3 characters, not empty).";
    }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid Email address.";
    } elseif ($userC->emailExists($email, $id)) {
        $errors[] = "Another account already uses this email.";
    }
    if ($password !== '' && mb_strlen($password) < 6) {
        $errors[] = "New Password must be at least 6 characters long.";
    }
    if (!in_array($role, ['admin', 'coach', 'user'], true)) {
        $errors[] = "Role must be admin, coach, or user.";
    }
    if ($id === (int)$currentUser['id'] && $role !== 'admin') {
        $errors[] = "You cannot remove your own administrator role.";
    }
    if ($phone !== '' && !preg_match('/^\+?[0-9\s\-]{8,20}$/', $phone)) {
        $errors[] = "Phone number must contain 8 to 20 digits.";
    }

    if (empty($errors)) {
        $user = new User($id, $fullName, $email, $password, $role, $phone !== '' ? $phone : null);
        $userC->updateUser($user, $id);
        AuthController::flash("User #$id updated.");
        header("Location: listUsers.php");
        exit;
    } else {
        $error = implode(' | ', $errors);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update User #<?= $id ?> — HB FITNESS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/fitness.css">
</head>
<body>
<div class="app-wrapper">
    <?php
    $basePrefix = '../../';
    $activeNav  = 'users';
    include __DIR__ . '/../partials/navbar.php';
    ?>

    <div class="container">
        <section class="crud-page-header">
            <div>
                <div class="breadcrumb-trail">
                    <a href="../../index.php?page=admin">Admin Dashboard</a> <span>/</span>
                    <a href="listUsers.php">Users</a> <span>/</span> <span>Edit #<?= $id ?></span>
                </div>
                <h1><i class="fa-solid fa-user-pen"></i> Edit User #<?= $id ?></h1>
                <p>Update account details, role, or set a new password.</p>
            </div>
            <a href="listUsers.php" class="btn btn-outline" style="z-index:1;">
                <i class="fa-solid fa-arrow-left"></i> Back to Users
            </a>
        </section>

        <div class="glass-card form-card-centered">
            <div class="card-header">
                <div class="card-title"><i class="fa-solid fa-user-pen"></i> Update Account Details</div>
            </div>
            <?php if ($error): ?>
                <div class="alert-box alert-error"><i class="fa-solid fa-triangle-exclamation"></i><span><?= htmlspecialchars($error) ?></span></div>
            <?php endif; ?>
            <form id="formUpdateUser" method="POST" novalidate>
                <input type="hidden" name="id" value="<?= $id ?>">
                <div class="form-group">
                    <label>Full Name (Characters only)</label>
                    <input type="text" name="full_name" id="full_name" class="form-control" value="<?= htmlspecialchars($_POST['full_name'] ?? $existing['full_name']) ?>">
                    <div class="field-error-msg" id="err_full_name"></div>
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" id="email" class="form-control" value="<?= htmlspecialchars($_POST['email'] ?? $existing['email']) ?>">
                    <div class="field-error-msg" id="err_email"></div>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label>New Password (leave blank to keep current password)</label>
                        <input type="password" name="password" id="password" class="form-control" placeholder="Optional new password...">
                        <div class="field-error-msg" id="err_password"></div>
                    </div>
                    <div class="form-group">
                        <label>Role</label>
                        <select name="role" class="form-control">
                            <option value="user" <?= ($_POST['role'] ?? $existing['role']) === 'user' ? 'selected' : '' ?>>User (Athlete)</option>
                            <option value="coach" <?= ($_POST['role'] ?? $existing['role']) === 'coach' ? 'selected' : '' ?>>Coach</option>
                            <option value="admin" <?= ($_POST['role'] ?? $existing['role']) === 'admin' ? 'selected' : '' ?>>Admin</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="text" name="phone" id="phone" class="form-control" value="<?= htmlspecialchars($_POST['phone'] ?? ($existing['phone'] ?? '')) ?>">
                    <div class="field-error-msg" id="err_phone"></div>
                </div>
                <div style="display:flex;gap:0.75rem;">
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Update User</button>
                    <a href="listUsers.php" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="../assets/js/fitness.js"></script>
<script>
document.getElementById('formUpdateUser').addEventListener('submit', function(e) {
    let valid = true;
    const nameRegex = /^[\p{L}\s\-\'\.]+$/u;
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const phoneRegex = /^\+?[0-9\s\-]{8,20}$/;

    const fullName = document.getElementById('full_name');
    const email = document.getElementById('email');
    const password = document.getElementById('password');
    const phone = document.getElementById('phone');
    document.querySelectorAll('.field-error-msg').forEach(el => el.textContent = '');

    if (fullName.value.trim().length < 3 || !nameRegex.test(fullName.value.trim())) {
        document.getElementById('err_full_name').textContent = 'Full Name must contain only letters and spaces (min 3 chars).';
        fullName.classList.add('is-invalid'); valid = false;
    } else { fullName.classList.remove('is-invalid'); }

    if (!emailRegex.test(email.value.trim())) {
        document.getElementById('err_email').textContent = 'Please enter a valid email address.';
        email.classList.add('is-invalid'); valid = false;
    } else { email.classList.remove('is-invalid'); }

    if (password.value !== '' && password.value.length < 6) {
        document.getElementById('err_password').textContent = 'New password must be at least 6 characters.';
        password.classList.add('is-invalid'); valid = false;
    } else { password.classList.remove('is-invalid'); }

    if (phone.value.trim() !== '' && !phoneRegex.test(phone.value.trim())) {
        document.getElementById('err_phone').textContent = 'Phone must contain 8 to 20 digits.';
        phone.classList.add('is-invalid'); valid = false;
    } else { phone.classList.remove('is-invalid'); }

    if (!valid) e.preventDefault();
});
</script>
</body>
</html>
