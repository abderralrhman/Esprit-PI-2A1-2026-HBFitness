<?php
require_once __DIR__ . '/../../controller/AuthController.php';
AuthController::redirectIfLoggedIn();

// ---- Contrôle de saisie PHP + brute-force protection (session based) ----
$errors = [];
$email = '';
const MAX_ATTEMPTS = 5;
const LOCK_SECONDS = 60;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $lockUntil = $_SESSION['login_lock_until'] ?? 0;

    if (time() < $lockUntil) {
        $errors[] = 'Too many failed attempts. Please wait ' . ($lockUntil - time()) . ' seconds.';
    } else {
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        }
        if ($password === '') {
            $errors[] = 'Password cannot be empty.';
        }

        if (!$errors) {
            $user = AuthController::login($email, $password);
            if ($user) {
                AuthController::flash('Welcome back, ' . $user['full_name'] . '!');
                AuthController::redirect(AuthController::homeFor($user));
            }
            $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
            $left = MAX_ATTEMPTS - $_SESSION['login_attempts'];
            if ($left <= 0) {
                $_SESSION['login_lock_until'] = time() + LOCK_SECONDS;
                $_SESSION['login_attempts'] = 0;
                $errors[] = 'Too many failed attempts. Login locked for ' . LOCK_SECONDS . ' seconds.';
            } else {
                $errors[] = "Incorrect email or password. $left attempt(s) left.";
            }
        }
    }
}

[$flashMessage, $flashType] = AuthController::pullFlash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log in — HB FITNESS</title>
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
            <h1>Train smart. Eat well. Reuse more.</h1>
            <div class="perk"><i class="fa-solid fa-newspaper"></i><div><strong>Publish training guides</strong><br><small>Share workout plans, nutrition strategies, and recovery tips.</small></div></div>
            <div class="perk"><i class="fa-solid fa-comments"></i><div><strong>Discuss with athletes</strong><br><small>Comment on articles and learn from Coach Pick answers.</small></div></div>
            <div class="perk"><i class="fa-solid fa-person-running"></i><div><strong>Explore top sports</strong><br><small>Compare sports disciplines, burn rates, and training zones.</small></div></div>
        </div>
        <small style="color:#B4B4B4;position:relative;z-index:1;">HB FITNESS · Protected &amp; private athlete access</small>
    </aside>

    <main class="auth-form-panel">
        <div class="auth-card">
            <h2>Log in</h2>
            <p class="sub">Access your athlete or coach administrator space.</p>

            <?php if ($flashMessage): ?>
                <div class="alert-box <?= $flashType === 'error' ? 'alert-error' : 'alert-success' ?>">
                    <i class="fa-solid <?= $flashType === 'error' ? 'fa-circle-exclamation' : 'fa-circle-check' ?>"></i>
                    <span><?= htmlspecialchars($flashMessage) ?></span>
                </div>
            <?php endif; ?>
            <?php if ($errors): ?>
                <div class="alert-box alert-error">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span><?= implode('<br>', array_map('htmlspecialchars', $errors)) ?></span>
                </div>
            <?php endif; ?>

            <form id="loginForm" method="POST" novalidate>
                <div class="form-group">
                    <label for="email">Email address</label>
                    <div class="input-icon">
                        <i class="fa-solid fa-envelope"></i>
                        <input type="email" id="email" name="email" class="form-control" placeholder="you@fitpulse.tn" value="<?= htmlspecialchars($email) ?>" autocomplete="email">
                    </div>
                    <div class="field-error-msg" id="err_email"></div>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-icon">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" autocomplete="current-password">
                        <button type="button" class="toggle-pass" data-target="password" aria-label="Show password"><i class="fa-solid fa-eye"></i></button>
                    </div>
                    <div class="field-error-msg" id="err_password"></div>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:0.85rem;">
                    <i class="fa-solid fa-right-to-bracket"></i> Log in
                </button>
            </form>

            <div class="demo-creds">
                <strong><i class="fa-solid fa-flask"></i> Demo accounts</strong> (password: <code>password</code>)<br>
                <button type="button" data-email="admin@fitpulse.tn">Admin</button>
                <button type="button" data-email="coach@fitpulse.tn">Coach</button>
                <button type="button" data-email="user@fitpulse.tn">User (Athlete)</button>
            </div>

            <p class="auth-switch">No account yet? <a href="signup.php">Create one</a> · <a href="../../index.php?page=landing">Back to home</a></p>
        </div>
    </main>
</div>

<script src="../assets/js/fitness.js"></script>
<script>
// Contrôle de saisie JS (in-page, login)
(function () {
    const form = document.getElementById('loginForm');
    const email = document.getElementById('email');
    const password = document.getElementById('password');
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    function setErr(input, id, msg) {
        document.getElementById(id).textContent = msg;
        input.classList.toggle('is-invalid', !!msg);
        input.classList.toggle('is-valid', !msg && input.value.trim() !== '');
        return !msg;
    }
    const checkEmail = () => setErr(email, 'err_email', emailRegex.test(email.value.trim()) ? '' : 'Please enter a valid email address.');
    const checkPass  = () => setErr(password, 'err_password', password.value !== '' ? '' : 'Password cannot be empty.');

    email.addEventListener('input', checkEmail);
    password.addEventListener('input', checkPass);
    form.addEventListener('submit', e => {
        const ok = [checkEmail(), checkPass()].every(Boolean);
        if (!ok) e.preventDefault();
    });

    document.querySelectorAll('.toggle-pass').forEach(btn => btn.addEventListener('click', () => {
        const input = document.getElementById(btn.dataset.target);
        const show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        btn.innerHTML = `<i class="fa-solid ${show ? 'fa-eye-slash' : 'fa-eye'}"></i>`;
    }));

    document.querySelectorAll('.demo-creds button').forEach(btn => btn.addEventListener('click', () => {
        email.value = btn.dataset.email;
        password.value = 'password';
        checkEmail(); checkPass();
    }));
})();
</script>
</body>
</html>
