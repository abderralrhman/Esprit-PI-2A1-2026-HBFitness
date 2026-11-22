<?php
/**
 * Shared Coherent Layout Navigation Bar for all Admin / Member / Sports Pages
 * Expects: $activeNav ('admin'|'user'|'articles'|'comments'|'sports'|'users'), $basePrefix ('../../' or '')
 */
$basePrefix = $basePrefix ?? '../../';
$activeNav  = $activeNav ?? '';
$cu         = $currentUser ?? AuthController::currentUser();
$isAdm      = $cu && ($cu['role'] === 'admin');
$isCoach    = $cu && ($cu['role'] === 'coach');
[$_flMsg, $_flType] = AuthController::pullFlash();
?>
<canvas id="fitness-canvas"></canvas>

<?php if (!empty($_flMsg)): ?>
<div class="toast-alert <?= $_flType === 'error' ? 'toast-error' : '' ?>">
    <i class="fa-solid <?= $_flType === 'error' ? 'fa-circle-exclamation' : 'fa-circle-check' ?>"></i>
    <span><?= htmlspecialchars($_flMsg) ?></span>
</div>
<?php endif; ?>

<header class="top-navbar">
    <a href="<?= $basePrefix ?>index.php?page=landing" class="brand-logo">
        <img src="<?= $basePrefix ?>view/assets/img/hb-monogram.svg" alt="HB Fitness Logo" class="brand-logo-img">
        <div>
            <span style="color:#B4B4B4;">HB</span> <span style="color:#E8405E;">FITNESS</span>
            <span class="brand-subtitle"><?= $isAdm ? 'Admin Command Center' : ($isCoach ? 'Coach Studio' : ($cu ? 'Train Smart · Eat Well' : 'Train Smart · Eat Well')) ?></span>
        </div>
    </a>

    <nav class="nav-links">
        <?php if ($isAdm): ?>
            <a href="<?= $basePrefix ?>index.php?page=admin" class="nav-btn <?= $activeNav === 'admin' ? 'active' : '' ?>">
                <i class="fa-solid fa-gauge-high"></i> Dashboard
            </a>
            <a href="<?= $basePrefix ?>view/article/listArticles.php" class="nav-btn <?= $activeNav === 'articles' ? 'active' : '' ?>">
                <i class="fa-solid fa-newspaper"></i> Articles
            </a>
            <a href="<?= $basePrefix ?>view/article/listComments.php" class="nav-btn <?= $activeNav === 'comments' ? 'active' : '' ?>">
                <i class="fa-solid fa-comments"></i> Comments
            </a>
            <a href="<?= $basePrefix ?>view/sports/sports.php" class="nav-btn <?= $activeNav === 'sports' ? 'active' : '' ?>">
                <i class="fa-solid fa-person-running"></i> Sports
            </a>
            <a href="<?= $basePrefix ?>view/user/manageUsers.php" class="nav-btn <?= $activeNav === 'users' ? 'active' : '' ?>">
                <i class="fa-solid fa-users-gear"></i> Users
            </a>
        <?php elseif ($isCoach): ?>
            <a href="<?= $basePrefix ?>index.php?page=coach" class="nav-btn <?= $activeNav === 'coach' ? 'active' : '' ?>">
                <i class="fa-solid fa-dumbbell"></i> Coach Studio
            </a>
            <a href="<?= $basePrefix ?>view/article/listArticles.php" class="nav-btn <?= $activeNav === 'articles' ? 'active' : '' ?>">
                <i class="fa-solid fa-newspaper"></i> Articles
            </a>
            <a href="<?= $basePrefix ?>view/article/listComments.php" class="nav-btn <?= $activeNav === 'comments' ? 'active' : '' ?>">
                <i class="fa-solid fa-comments"></i> Comments
            </a>
            <a href="<?= $basePrefix ?>view/sports/sports.php" class="nav-btn <?= $activeNav === 'sports' ? 'active' : '' ?>">
                <i class="fa-solid fa-person-running"></i> Sports
            </a>
        <?php elseif ($cu): ?>
            <a href="<?= $basePrefix ?>index.php?page=user" class="nav-btn <?= $activeNav === 'user' ? 'active' : '' ?>">
                <i class="fa-solid fa-house-user"></i> Athlete Hub
            </a>
            <a href="<?= $basePrefix ?>view/article/listArticles.php" class="nav-btn <?= $activeNav === 'articles' ? 'active' : '' ?>">
                <i class="fa-solid fa-newspaper"></i> Articles
            </a>
            <a href="<?= $basePrefix ?>view/article/listComments.php" class="nav-btn <?= $activeNav === 'comments' ? 'active' : '' ?>">
                <i class="fa-solid fa-comments"></i> Comments
            </a>
            <a href="<?= $basePrefix ?>view/sports/sports.php" class="nav-btn <?= $activeNav === 'sports' ? 'active' : '' ?>">
                <i class="fa-solid fa-person-running"></i> Sports
            </a>
        <?php else: ?>
            <a href="<?= $basePrefix ?>index.php?page=landing" class="nav-btn">
                <i class="fa-solid fa-house"></i> Home
            </a>
            <a href="<?= $basePrefix ?>view/sports/sports.php" class="nav-btn <?= $activeNav === 'sports' ? 'active' : '' ?>">
                <i class="fa-solid fa-person-running"></i> Sports
            </a>
        <?php endif; ?>
    </nav>

    <div class="role-switcher">
        <?php if ($cu): ?>
            <span class="role-pill <?= $isAdm ? 'admin' : ($isCoach ? 'coach' : '') ?>">
                <i class="fa-solid <?= $isAdm ? 'fa-shield-halved' : ($isCoach ? 'fa-medal' : 'fa-user-check') ?>"></i>
                <?= htmlspecialchars($cu['full_name']) ?> (<?= strtoupper(htmlspecialchars($cu['role'])) ?>)
            </span>
            <?php if ($isAdm): ?>
                <a href="<?= $basePrefix ?>index.php?page=user" class="btn btn-green btn-sm" title="Preview Athlete Portal">
                    <i class="fa-solid fa-eye"></i> Athlete View
                </a>
            <?php endif; ?>
            <a href="<?= $basePrefix ?>index.php?action=logout" class="btn btn-outline btn-sm" title="Log Out">
                <i class="fa-solid fa-right-from-bracket"></i>
            </a>
        <?php else: ?>
            <a href="<?= $basePrefix ?>view/auth/login.php" class="btn btn-outline btn-sm">
                <i class="fa-solid fa-right-to-bracket"></i> Log in
            </a>
            <a href="<?= $basePrefix ?>view/auth/signup.php" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-user-plus"></i> Sign up
            </a>
        <?php endif; ?>
    </div>
</header>
