<?php
/**
 * Views/Frontoffice/createComment.php — Dedicated Comment Creation Form
 */
$title = 'Add Comment — HB FITNESS Blog';
ob_start();
?>
<section class="container py-5">
    <div class="mx-auto" style="max-width: 720px;">
        <div class="mb-3">
            <a href="index.php?action=article_details&id=<?= $article->getId() ?>" class="btn btn-hb-outline btn-sm">
                <i class="fa-solid fa-arrow-left me-1"></i>Back to Article
            </a>
        </div>

        <div class="hb-card reveal">
            <span class="text-coral small fw-bold text-uppercase" style="letter-spacing:0.1em;">New Community Comment</span>
            <h1 class="h3 mt-1 mb-2">Comment on Article</h1>
            <p class="text-silver small mb-4">
                Article: <strong class="text-white"><?= htmlspecialchars($article->getTitle(), ENT_QUOTES, 'UTF-8') ?></strong>
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

            <form method="POST" action="index.php?action=create_comment" data-validate="comment" novalidate>
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string)$csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="article_id" value="<?= $article->getId() ?>">

                <div class="mb-3">
                    <label for="comment-content" class="form-label">Your Comment</label>
                    <textarea
                        id="comment-content"
                        name="content"
                        rows="5"
                        class="form-control hb-textarea"
                        placeholder="Write a constructive comment (3 to 1000 characters)..."
                        data-max-chars="1000"
                        data-counter-target="create-comment-counter"
                        required
                    ><?= htmlspecialchars((string)($old['content'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                    <div id="create-comment-counter" class="hb-char-counter mt-1">0 / 1000 characters</div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-hb-primary">
                        <i class="fa-solid fa-paper-plane me-1"></i>Publish Comment
                    </button>
                    <a href="index.php?action=article_details&id=<?= $article->getId() ?>" class="btn btn-hb-outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</section>
<?php
$content = (string)ob_get_clean();
require __DIR__ . '/../layout.php';
