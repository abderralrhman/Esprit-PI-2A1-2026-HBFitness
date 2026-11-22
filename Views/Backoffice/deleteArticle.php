<?php
/**
 * Views/Backoffice/deleteArticle.php — CSRF-Protected Article Delete Confirmation
 * Uses layout_admin.php for Admin and Frontoffice layout.php for Coach.
 */
$isAdmin      = ($currentUser['role'] ?? '') === 'admin';
$title        = 'Delete Article #' . $article->getId() . ' — HB FITNESS Blog';
$adminHeading = 'Delete Article #' . $article->getId();
$cancelAction = $isAdmin ? 'admin_articles' : 'article_details&id=' . $article->getId();
ob_start();
?>
<div class="<?= $isAdmin ? '' : 'container py-5' ?>">
    <div class="mx-auto" style="max-width: 640px;">
        <div class="hb-card">
            <div class="d-flex align-items-center gap-3 mb-3">
                <div class="hb-stat-icon" style="background:rgba(239,68,68,0.16);color:#f87171;">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <div>
                    <h1 class="h4 mb-0">Confirm Article Deletion</h1>
                    <span class="small text-muted-hb">Article #<?= $article->getId() ?></span>
                </div>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="hb-alert hb-alert-error mb-3" role="alert">
                    <i class="fa-solid fa-circle-exclamation mt-1"></i>
                    <span><?= htmlspecialchars((string)$errors[0], ENT_QUOTES, 'UTF-8') ?></span>
                </div>
            <?php endif; ?>

            <p class="text-silver mb-3">
                You are about to permanently delete the following article:
            </p>

            <div class="p-3 rounded-3 mb-3" style="background:rgba(255,255,255,0.03);border:1px solid var(--hb-border);">
                <h5 class="mb-1"><?= htmlspecialchars($article->getTitle(), ENT_QUOTES, 'UTF-8') ?></h5>
                <div class="small text-muted-hb">
                    Author: <?= htmlspecialchars($article->getAuthorName(), ENT_QUOTES, 'UTF-8') ?>
                    · <?= $article->getCommentsCount() ?> attached comment<?= $article->getCommentsCount() === 1 ? '' : 's' ?>
                </div>
            </div>

            <div class="hb-alert hb-alert-info mb-4">
                <i class="fa-solid fa-circle-info mt-1"></i>
                <span class="small">
                    Deleting this article will also remove its associated comments via foreign-key cascade.
                    User accounts are never affected or deleted.
                </span>
            </div>

            <form method="POST" action="index.php?action=delete_article&id=<?= $article->getId() ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string)$csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="id" value="<?= $article->getId() ?>">

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-hb-danger">
                        <i class="fa-solid fa-trash me-1"></i>Yes, Delete Article
                    </button>
                    <a href="index.php?action=<?= $cancelAction ?>" class="btn btn-hb-outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?php
$content = (string)ob_get_clean();
require __DIR__ . ($isAdmin ? '/../layout_admin.php' : '/../layout.php');
