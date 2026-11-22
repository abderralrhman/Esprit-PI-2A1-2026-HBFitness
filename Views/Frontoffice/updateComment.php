<?php
/**
 * Views/Frontoffice/updateComment.php — Edit Own Comment (or Admin Moderation)
 */
$title     = 'Edit Comment — HB FITNESS Blog';
$backRoute = ($currentUser['role'] === 'admin' && !empty($fromAdmin))
    ? 'admin_comments'
    : 'article_details&id=' . $comment->getArticleId();
ob_start();
?>
<section class="container py-5">
    <div class="mx-auto" style="max-width: 720px;">
        <div class="mb-3">
            <a href="index.php?action=<?= htmlspecialchars($backRoute, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-hb-outline btn-sm">
                <i class="fa-solid fa-arrow-left me-1"></i>Back
            </a>
        </div>

        <div class="hb-card reveal">
            <span class="text-coral small fw-bold text-uppercase" style="letter-spacing:0.1em;">Update Comment #<?= $comment->getId() ?></span>
            <h1 class="h3 mt-1 mb-2">Edit Comment</h1>
            <p class="text-silver small mb-4">
                Related Article: <strong class="text-white"><?= htmlspecialchars($comment->getArticleTitle(), ENT_QUOTES, 'UTF-8') ?></strong>
            </p>

            <?php if (!empty($errors)): ?>
                <div class="hb-alert hb-alert-error mb-4" role="alert">
                    <i class="fa-solid fa-triangle-exclamation mt-1"></i>
                    <ul class="mb-0 ps-3">
                        <?php foreach ($errors as $err): ?>
                            <li><?= htmlspecialchars((string)$err, ENT_QUOTES, 'UTF-8') ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="index.php?action=update_comment&id=<?= $comment->getId() ?>" data-validate="comment" novalidate>
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string)$csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="id" value="<?= $comment->getId() ?>">
                <?php if (!empty($fromAdmin)): ?>
                    <input type="hidden" name="from_admin" value="1">
                <?php endif; ?>

                <div class="mb-3">
                    <label for="update-comment-content" class="form-label">Comment Content</label>
                    <textarea
                        id="update-comment-content"
                        name="content"
                        rows="5"
                        class="form-control hb-textarea"
                        data-max-chars="1000"
                        data-counter-target="update-comment-counter"
                        required
                    ><?= htmlspecialchars((string)($old['content'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                    <div id="update-comment-counter" class="hb-char-counter mt-1">0 / 1000 characters</div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-hb-primary">
                        <i class="fa-solid fa-floppy-disk me-1"></i>Save Changes
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
