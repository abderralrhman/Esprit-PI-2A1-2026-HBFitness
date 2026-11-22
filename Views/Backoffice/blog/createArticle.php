<?php
/**
 * Views/Backoffice/createArticle.php — Publish New Article (Admin & Coach)
 * Uses layout_admin.php for Admin and Frontoffice layout.php for Coach.
 */
$isAdmin      = ($currentUser['role'] ?? '') === 'admin';
$title        = 'Publish Article — HB FITNESS Blog';
$adminHeading = 'Publish New Article';
$cancelAction = $isAdmin ? 'admin_articles' : 'articles';
ob_start();
?>
<div class="<?= $isAdmin ? '' : 'container py-5' ?>">
    <div class="mx-auto" style="max-width: 820px;">
        <div class="mb-3">
            <a href="index.php?action=<?= $cancelAction ?>" class="btn btn-hb-outline btn-sm">
                <i class="fa-solid fa-arrow-left me-1"></i>Back to Articles
            </a>
        </div>

        <div class="hb-card">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                <div>
                    <span class="text-coral small fw-bold text-uppercase" style="letter-spacing:0.1em;">Article Authoring</span>
                    <h1 class="h3 mb-0 mt-1">Publish New Fitness Article</h1>
                </div>
                <span class="hb-badge <?= $isAdmin ? 'hb-badge-admin' : 'hb-badge-coach' ?>">
                    <i class="fa-solid <?= $isAdmin ? 'fa-shield-halved' : 'fa-medal' ?>"></i>
                    Author: <?= htmlspecialchars($currentUser['full_name'], ENT_QUOTES, 'UTF-8') ?>
                </span>
            </div>

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

            <form method="POST" action="index.php?action=create_article" data-validate="article" novalidate>
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string)$csrfToken, ENT_QUOTES, 'UTF-8') ?>">

                <div class="mb-3">
                    <label for="article-title" class="form-label">Article Title <span class="text-coral">*</span></label>
                    <input
                        type="text"
                        id="article-title"
                        name="title"
                        class="form-control hb-input"
                        placeholder="e.g. Progressive Overload: Building Lean Muscle Safely"
                        value="<?= htmlspecialchars((string)($old['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                        maxlength="180"
                        required
                    >
                </div>

                <div class="mb-3">
                    <label for="article-content" class="form-label">Article Content <span class="text-coral">*</span></label>
                    <textarea
                        id="article-content"
                        name="content"
                        rows="9"
                        class="form-control hb-textarea"
                        placeholder="Write the full training, nutrition, or recovery article..."
                        required
                    ><?= htmlspecialchars((string)($old['content'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>

                <div class="mb-4">
                    <label for="article-recipe" class="form-label">Optional Linked Recipe ID</label>
                    <input
                        type="number"
                        id="article-recipe"
                        name="recipe_id"
                        class="form-control hb-input"
                        placeholder="Leave empty if not linked to a nutrition recipe"
                        min="1"
                        value="<?= htmlspecialchars((string)($old['recipe_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                    >
                    <div class="form-text text-muted-hb">
                        Nullable reference field prepared for future integration with the team's Nutrition / Recipe module.
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-hb-primary">
                        <i class="fa-solid fa-paper-plane me-1"></i>Publish Article
                    </button>
                    <a href="index.php?action=<?= $cancelAction ?>" class="btn btn-hb-outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?php
$content = (string)ob_get_clean();
require __DIR__ . ($isAdmin ? '/../../layout_admin.php' : '/../../layout.php');
