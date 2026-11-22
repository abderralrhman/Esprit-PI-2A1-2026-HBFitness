<?php
/**
 * Views/layout.php
 * Global Frontoffice Layout (Template Integration Workshop)
 * Expects: $title (string), $content (string), $currentPage (string), $currentUser (?User)
 */
$isAdminLayout = false;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php require __DIR__ . '/includes/head.php'; ?>
</head>
<body class="d-flex flex-column min-vh-100">
    <canvas id="hb-canvas"></canvas>
    <div class="fo-app-layer d-flex flex-column min-vh-100">
    <?php require __DIR__ . '/includes/navbar.php'; ?>

    <main class="flex-grow-1 fo-page-enter">
        <?php if (!empty($flashMessage)): ?>
            <div class="container pt-3">
                <div class="hb-alert <?= ($flashType ?? 'success') === 'error' ? 'hb-alert-error' : 'hb-alert-success' ?> hb-flash-auto">
                    <i class="fa-solid <?= ($flashType ?? 'success') === 'error' ? 'fa-circle-exclamation' : 'fa-circle-check' ?> mt-1"></i>
                    <div><?= htmlspecialchars((string)$flashMessage, ENT_QUOTES, 'UTF-8') ?></div>
                </div>
            </div>
        <?php endif; ?>

        <?= $content ?>
    </main>

    <?php require __DIR__ . '/includes/footer.php'; ?>
    </div>
    <?php require __DIR__ . '/includes/scripts.php'; ?>
</body>
</html>
