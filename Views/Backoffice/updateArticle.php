<?php
/**
 * Views/Backoffice/updateArticle.php — Edit Article (Admin: any | Coach: own only)
 * Uses layout_admin.php for Admin and Frontoffice layout.php for Coach.
 */
$isAdmin      = ($currentUser['role'] ?? '') === 'admin';
$title        = 'Edit Article #' . $article->getId() . ' — HB FITNESS Blog';
$adminHeading = 'Edit Article #' . $article->getId();
$cancelAction = $isAdmin ? 'admin_articles' : 'article_details&id=' . $article->getId();
ob_start();
?>
<div class="<?= $isAdmin ? '' : 'container py-5' ?>">
    <div class="mx-auto" style="max-width: 820px;">
        <div class="mb-3">
            <a href="index.php?action=<?= $cancelAction ?>" class="btn btn-hb-outline btn-sm">
                <i class="fa-solid fa-arrow-left me-1"></i>Back
            </a>
        </div>

        <div class="hb-card">
            <span class="text-coral small fw-bold text-uppercase" style="letter-spacing:0.1em;">Update Article #<?= $article->getId() ?></span>
            <h1 class="h3 mt-1 mb-3">Edit Fitness Article</h1>

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

            <form method="POST" action="index.php?action=update_article&id=<?= $article->getId() ?>" data-validate="article" novalidate>
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string)$csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="id" value="<?= $article->getId() ?>">

                <div class="mb-3">
                    <label for="update-article-title" class="form-label">Article Title <span class="text-coral">*</span></label>
                    <input
                        type="text"
                        id="update-article-title"
                        name="title"
                        class="form-control hb-input"
                        value="<?= htmlspecialchars((string)($old['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                        maxlength="180"
                        required
                    >
                </div>

                <div class="mb-3">
                    <label for="update-article-content" class="form-label">Article Content <span class="text-coral">*</span></label>
                    <textarea
                        id="update-article-content"
                        name="content"
                        rows="9"
                        class="form-control hb-textarea"
                        required
                    ><?= htmlspecialchars((string)($old['content'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>

                <div class="mb-4">
                    <label for="update-article-recipe" class="form-label">Optional Linked Recipe ID</label>
                    <input
                        type="number"
                        id="update-article-recipe"
                        name="recipe_id"
                        class="form-control hb-input"
                        min="1"
                        placeholder="Leave empty for none"
                        value="<?= htmlspecialchars((string)($old['recipe_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                    >
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-hb-primary">
                        <i class="fa-solid fa-floppy-disk me-1"></i>Save Changes
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
