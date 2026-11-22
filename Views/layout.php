<?php
/**
 * Views/layout.php — Main Frontoffice Layout Wrapper
 * Variables expected: $title (string), $content (string), $currentUser (?array), $currentPage (string)
 */
require __DIR__ . '/includes/head.php';
?>
<body>
    <canvas id="hb-canvas" aria-hidden="true"></canvas>

    <div class="hb-front-shell">
        <?php require __DIR__ . '/includes/navbar.php'; ?>

        <main class="hb-front-main">
            <?php if (!empty($flashMessage)): ?>
                <div class="container pt-3">
                    <div class="hb-alert <?= ($flashType ?? 'success') === 'error' ? 'hb-alert-error' : 'hb-alert-success' ?>" data-auto-dismiss="true" role="alert">
                        <i class="fa-solid <?= ($flashType ?? 'success') === 'error' ? 'fa-circle-exclamation' : 'fa-circle-check' ?> mt-1"></i>
                        <span><?= htmlspecialchars((string)$flashMessage, ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <?= $content ?? '' ?>
        </main>

        <?php require __DIR__ . '/includes/footer.php'; ?>
    </div>

    <?php require __DIR__ . '/includes/scripts.php'; ?>
