<?php
/**
 * Views/Frontoffice/deleteComment.php — CSRF-Protected Comment Delete Confirmation
 */
$title     = 'Delete Comment — HB FITNESS Blog';
$backRoute = ($currentUser['role'] === 'admin' && !empty($fromAdmin))
    ? 'admin_comments'
    : 'article_details&id=' . $comment->getArticleId();
ob_start();
?>
<section class="container py-5">
    <div class="mx-auto" style="max-width: 620px;">
        <div class="hb-card reveal">
            <div class="d-flex align-items-center gap-3 mb-3">
                <div class="hb-stat-icon" style="background:rgba(239,68,68,0.16);color:#f87171;">
                    <i class="fa-solid fa-trash-can"></i>
                </div>
                <div>
                    <h1 class="h4 mb-0">Confirm Comment Deletion</h1>
                    <span class="small text-muted-hb">Comment #<?= $comment->getId() ?></span>
                </div>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="hb-alert hb-alert-error mb-3" role="alert">
                    <i class="fa-solid fa-triangle-exclamation mt-1"></i>
                    <span><?= htmlspecialchars((string)$errors[0], ENT_QUOTES, 'UTF-8') ?></span>
                </div>
            <?php endif; ?>

            <p class="text-silver mb-3">
                Are you sure you want to permanently delete this comment on
                <strong class="text-white"><?= htmlspecialchars($comment->getArticleTitle(), ENT_QUOTES, 'UTF-8') ?></strong>?
            </p>

            <blockquote class="p-3 rounded-3 mb-4" style="background:rgba(255,255,255,0.03);border-left:3px solid var(--hb-coral);color:var(--hb-silver);">
                <?= htmlspecialchars($comment->getContent(), ENT_QUOTES, 'UTF-8') ?>
            </blockquote>

            <form method="POST" action="index.php?action=delete_comment&id=<?= $comment->getId() ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string)$csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="id" value="<?= $comment->getId() ?>">
                <?php if (!empty($fromAdmin)): ?>
                    <input type="hidden" name="from_admin" value="1">
                <?php endif; ?>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-hb-danger">
                        <i class="fa-solid fa-trash me-1"></i>Yes, Delete Comment
                    </button>
                    <a href="index.php?action=<?= htmlspecialchars($backRoute, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-hb-outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</section>
<?php
$content = (string)ob_get_clean();
require __DIR__ . '/../layout.php';
