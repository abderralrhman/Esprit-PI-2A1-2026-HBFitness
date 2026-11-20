<?php
require_once __DIR__ . '/../../controller/AuthController.php';
AuthController::redirectIfLoggedIn();

// ---- Contrôle de saisie PHP (in-page, signup) ----
$errors = [];
$old = ['full_name' => '', 'email' => '', 'phone' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';
    $terms    = isset($_POST['terms']);
    $old = ['full_name' => $fullName, 'email' => $email, 'phone' => $phone];

    if ($fullName === '' || mb_strlen($fullName) < 3 || mb_strlen($fullName) > 120 || !preg_match('/^[\p{L}\s\-\'\.]+$/u', $fullName)) {
        $errors['full_name'] = 'Full name must contain only letters and spaces (3–120 characters).';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    } elseif ((new UserController())->emailExists($email)) {
        $errors['email'] = 'An account with this email already exists.';
    }
    if ($phone !== '' && !preg_match('/^\+?[0-9\s\-]{8,20}$/', $phone)) {
        $errors['phone'] = 'Phone number must contain 8 to 20 digits.';
    }
    if (mb_strlen($password) < 8 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $errors['password'] = 'Password must be at least 8 characters and contain letters and digits.';
    }
    if ($password !== $confirm) {
        $errors['confirm_password'] = 'Passwords do not match.';
    }
    if (!$terms) {
        $errors['terms'] = 'You must accept the HB FITNESS community charter.';
    }

    if (!$errors) {
        $user = AuthController::register($fullName, $email, $password, $phone);
        if ($user) {
            AuthController::flash('Welcome to HB FITNESS, ' . $user['full_name'] . '! Your athlete account is ready.');
            AuthController::redirect(AuthController::homeFor($user));
        }
        $errors['email'] = 'Unable to create the account. Please try again.';
    }
}

[$flashMessage, $flashType] = AuthController::pullFlash();
$err = fn(string $k) => isset($errors[$k]) ? htmlspecialchars($errors[$k]) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign up — HB FITNESS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/fitness.css">
</head>
<body>
<canvas id="fitness-canvas"></canvas>

<div class="auth-wrapper">
    <aside class="auth-side">
        <a href="../../index.php?page=landing" class="brand-logo" style="color:#fff;position:relative;z-index:1;">
            <img src="../assets/img/hb-monogram.svg" alt="HB Fitness Logo" class="brand-logo-img">
            <span><span style="color:#B4B4B4;">HB</span> <span style="color:#E8405E;">FITNESS</span></span>
        </a>
        <div>
            <img src="../assets/img/hb-fitness-logo.svg" alt="HB Fitness" style="width:210px;max-width:100%;margin-bottom:1.2rem;display:block;">
            <h1>Join HB FITNESS in 30 seconds.</h1>
            <div class="perk"><i class="fa-solid fa-pen-nib"></i><div><strong>Share your expertise</strong><br><small>Publish training guides on strength, HIIT, nutrition, or mobility.</small></div></div>
            <div class="perk"><i class="fa-solid fa-comments"></i><div><strong>Get coach feedback</strong><br><small>Ask questions in article comments and spot Coach Pick replies.</small></div></div>
            <div class="perk"><i class="fa-solid fa-shield-halved"></i><div><strong>Your account is protected</strong><br><small>Hashed passwords and secure session management by default.</small></div></div>
        </div>
        <small style="color:#B4B4B4;position:relative;z-index:1;">HB FITNESS · Train smart. Eat well. Reuse more.</small>
    </aside>

    <main class="auth-form-panel">
        <div class="auth-card">
            <h2>Create your account</h2>
            <p class="sub">New accounts start as Athlete (user) accounts.</p>

            <?php if ($flashMessage): ?>
                <div class="alert-box <?= $flashType === 'error' ? 'alert-error' : 'alert-success' ?>">
                    <span><?= htmlspecialchars($flashMessage) ?></span>
                </div>
            <?php endif; ?>
            <?php if ($errors): ?>
                <div class="alert-box alert-error">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span>Please correct the highlighted fields.</span>
                </div>
            <?php endif; ?>

            <form id="signupForm" method="POST" novalidate>
                <div class="form-group">
                    <label for="full_name">Full name</label>
                    <div class="input-icon">
                        <i class="fa-solid fa-user"></i>
                        <input type="text" id="full_name" name="full_name" class="form-control <?= $err('full_name') ? 'is-invalid' : '' ?>" placeholder="e.g. Nour Ben Salah" value="<?= htmlspecialchars($old['full_name']) ?>">
                    </div>
                    <div class="field-error-msg" id="err_full_name"><?= $err('full_name') ?></div>
                </div>
                <div class="form-group">
                    <label for="email">Email address</label>
                    <div class="input-icon">
                        <i class="fa-solid fa-envelope"></i>
                        <input type="email" id="email" name="email" class="form-control <?= $err('email') ? 'is-invalid' : '' ?>" placeholder="you@fitpulse.tn" value="<?= htmlspecialchars($old['email']) ?>">
                    </div>
                    <div class="field-error-msg" id="err_email"><?= $err('email') ?></div>
                </div>
                <div class="form-group">
                    <label for="phone">Phone <small style="color:var(--slate-500);">(optional)</small></label>
                    <div class="input-icon">
                        <i class="fa-solid fa-phone"></i>
                        <input type="text" id="phone" name="phone" class="form-control <?= $err('phone') ? 'is-invalid' : '' ?>" placeholder="+216 55 000 000" value="<?= htmlspecialchars($old['phone']) ?>">
                    </div>
                    <div class="field-error-msg" id="err_phone"><?= $err('phone') ?></div>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-icon">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" id="password" name="password" class="form-control <?= $err('password') ? 'is-invalid' : '' ?>" placeholder="Min. 8 chars, letters + digits" autocomplete="new-password">
                        <button type="button" class="toggle-pass" data-target="password" aria-label="Show password"><i class="fa-solid fa-eye"></i></button>
                    </div>
                    <div class="strength-bar"><div class="strength-fill" id="strengthFill"></div></div>
                    <small id="strengthLabel" class="session-info"></small>
                    <div class="field-error-msg" id="err_password"><?= $err('password') ?></div>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm password</label>
                    <div class="input-icon">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control <?= $err('confirm_password') ? 'is-invalid' : '' ?>" placeholder="Repeat your password" autocomplete="new-password">
                    </div>
                    <div class="field-error-msg" id="err_confirm_password"><?= $err('confirm_password') ?></div>
                </div>
                <div class="form-group" style="display:flex;gap:0.5rem;align-items:flex-start;">
                    <input type="checkbox" id="terms" name="terms" value="1" style="margin-top:0.25rem;">
                    <label for="terms" style="margin:0;font-weight:500;">I accept the HB FITNESS community charter (respectful, constructive training discussions).</label>
                </div>
                <div class="field-error-msg" id="err_terms" style="margin-top:-0.6rem;margin-bottom:0.8rem;"><?= $err('terms') ?></div>

                <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:0.85rem;">
                    <i class="fa-solid fa-user-plus"></i> Create my account
                </button>
            </form>

            <p class="auth-switch">Already registered? <a href="login.php">Log in</a> · <a href="../../index.php?page=landing">Back to home</a></p>
        </div>
    </main>
</div>

<script src="../assets/js/fitness.js"></script>
<script>
// Contrôle de saisie JS (in-page, signup)
(function () {
    const $ = id => document.getElementById(id);
    const nameRegex  = /^[\p{L}\s\-'\.]+$/u;
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const phoneRegex = /^\+?[0-9\s\-]{8,20}$/;

    function setErr(input, key, msg) {
        $('err_' + key).textContent = msg;
        if (input) {
            input.classList.toggle('is-invalid', !!msg);
            input.classList.toggle('is-valid', !msg && input.value.trim() !== '');
        }
        return !msg;
    }

    const rules = {
        full_name: () => {
            const v = $('full_name').value.trim();
            return setErr($('full_name'), 'full_name', (v.length >= 3 && v.length <= 120 && nameRegex.test(v)) ? '' : 'Full name must contain only letters and spaces (3–120 characters).');
        },
        email: () => setErr($('email'), 'email', emailRegex.test($('email').value.trim()) ? '' : 'Please enter a valid email address.'),
        phone: () => {
            const v = $('phone').value.trim();
            return setErr($('phone'), 'phone', (v === '' || phoneRegex.test(v)) ? '' : 'Phone number must contain 8 to 20 digits.');
        },
        password: () => {
            const v = $('password').value;
            return setErr($('password'), 'password', (v.length >= 8 && /[A-Za-z]/.test(v) && /[0-9]/.test(v)) ? '' : 'Password must be at least 8 characters and contain letters and digits.');
        },
        confirm_password: () => setErr($('confirm_password'), 'confirm_password', ($('confirm_password').value !== '' && $('confirm_password').value === $('password').value) ? '' : 'Passwords do not match.'),
        terms: () => setErr(null, 'terms', $('terms').checked ? '' : 'You must accept the HB FITNESS community charter.')
    };

    Object.keys(rules).forEach(k => {
        const el = $(k);
        el.addEventListener(el.type === 'checkbox' ? 'change' : 'input', rules[k]);
    });

    // Animated password strength meter
    $('password').addEventListener('input', () => {
        const v = $('password').value;
        let score = 0;
        if (v.length >= 8) score++;
        if (/[A-Z]/.test(v) && /[a-z]/.test(v)) score++;
        if (/[0-9]/.test(v)) score++;
        if (/[^A-Za-z0-9]/.test(v)) score++;
        const levels = [
            ['0%', '#e2e8f0', ''],
            ['25%', '#ef4444', 'Weak'],
            ['50%', '#f59e0b', 'Fair'],
            ['75%', '#f97316', 'Good'],
            ['100%', '#10b981', 'Strong']
        ];
        const [w, color, label] = levels[v ? Math.max(score, 1) : 0];
        $('strengthFill').style.width = w;
        $('strengthFill').style.background = color;
        $('strengthLabel').textContent = label ? 'Strength: ' + label : '';
        if ($('confirm_password').value) rules.confirm_password();
    });

    $('signupForm').addEventListener('submit', e => {
        const ok = Object.values(rules).map(fn => fn()).every(Boolean);
        if (!ok) {
            e.preventDefault();
            const first = document.querySelector('.is-invalid');
            if (first) first.focus();
        }
    });

    document.querySelectorAll('.toggle-pass').forEach(btn => btn.addEventListener('click', () => {
        const input = $(btn.dataset.target);
        const show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        $('confirm_password').type = input.type;
        btn.innerHTML = `<i class="fa-solid ${show ? 'fa-eye-slash' : 'fa-eye'}"></i>`;
    }));
})();
</script>
</body>
</html>
