<?php
/**
 * Views/layout_admin.php
 * Dedicated Backoffice Admin Layout (TailAdmin-style sidebar + topbar)
 * Expects: $title (string), $pageHeading (string), $content (string), $currentPage (string), $currentUser (User)
 */
$isAdminLayout = true;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php require __DIR__ . '/includes/head.php'; ?>
</head>
<body>
    <div class="bo-wrapper">
        <?php require __DIR__ . '/includes/sidebar.php'; ?>

        <div class="bo-main">
            <?php require __DIR__ . '/includes/header.php'; ?>

            <main class="bo-content">
                <?php if (!empty($flashMessage)): ?>
                    <div class="hb-alert <?= ($flashType ?? 'success') === 'error' ? 'hb-alert-error' : 'hb-alert-success' ?> hb-flash-auto">
                        <i class="fa-solid <?= ($flashType ?? 'success') === 'error' ? 'fa-circle-exclamation' : 'fa-circle-check' ?> mt-1"></i>
                        <div><?= htmlspecialchars((string)$flashMessage, ENT_QUOTES, 'UTF-8') ?></div>
                    </div>
                <?php endif; ?>

                <?= $content ?>
            </main>

            <?php require __DIR__ . '/includes/footer.php'; ?>
        </div>
    </div>

    <?php require __DIR__ . '/includes/scripts.php'; ?>
</body>
</html>
