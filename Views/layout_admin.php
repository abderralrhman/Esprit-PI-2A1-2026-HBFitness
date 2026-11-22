<?php
/**
 * Views/layout_admin.php — TailAdmin-Style Backoffice Layout Wrapper (Admin Only)
 * Variables expected: $title (string), $adminHeading (string), $content (string), $currentUser (array), $currentPage (string)
 */
require __DIR__ . '/includes/head.php';
?>
<body>
    <div class="hb-admin-shell">
        <?php require __DIR__ . '/includes/sidebar.php'; ?>

        <div class="hb-admin-content">
            <?php require __DIR__ . '/includes/header.php'; ?>

            <main class="hb-admin-body">
                <?php if (!empty($flashMessage)): ?>
                    <div class="hb-alert <?= ($flashType ?? 'success') === 'error' ? 'hb-alert-error' : 'hb-alert-success' ?> mb-4" data-auto-dismiss="true" role="alert">
                        <i class="fa-solid <?= ($flashType ?? 'success') === 'error' ? 'fa-circle-exclamation' : 'fa-circle-check' ?> mt-1"></i>
                        <span><?= htmlspecialchars((string)$flashMessage, ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                <?php endif; ?>

                <?= $content ?? '' ?>
            </main>
        </div>
    </div>

    <?php require __DIR__ . '/includes/scripts.php'; ?>
