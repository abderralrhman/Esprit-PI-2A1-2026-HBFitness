<?php
/**
 * Views/Frontoffice/articleDetails.php — Full Article View + Comments Thread
 */
$title            = $article->getTitle() . ' — HB FITNESS Blog';
$aRole            = $article->getAuthorRole();
$aBadgeClass      = $aRole === 'admin' ? 'hb-badge-admin' : ($aRole === 'coach' ? 'hb-badge-coach' : 'hb-badge-user');
$canManageArticle = ArticleController::canManageArticle($article, $currentUser);
ob_start();
?>
<section class="container py-5">
    <div class="mb-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <a href="index.php?action=articles" class="btn btn-hb-outline btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i>Back to Articles
        </a>

        <?php if ($canManageArticle): ?>
            <div class="d-flex gap-2">
                <a href="index.php?action=update_article&id=<?= $article->getId() ?>" class="btn btn-hb-outline btn-sm">
                    <i class="fa-solid fa-pen me-1"></i>Edit Article
                </a>
                <a href="index.php?action=delete_article&id=<?= $article->getId() ?>" class="btn btn-hb-danger btn-sm">
                    <i class="fa-solid fa-trash me-1"></i>Delete Article
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Article Body -->
    <article class="hb-article-reader mb-5 reveal">
        <div class="hb-article-meta mb-3">
            <span class="hb-badge <?= $aBadgeClass ?>">
                <?= htmlspecialchars($aRole, ENT_QUOTES, 'UTF-8') ?>
            </span>
            <span><i class="fa-regular fa-user me-1 text-coral"></i><?= htmlspecialchars($article->getAuthorName(), ENT_QUOTES, 'UTF-8') ?></span>
            <span><i class="fa-regular fa-calendar me-1 text-coral"></i><?= htmlspecialchars(date('F d, Y · H:i', strtotime($article->getPublishedAt() ?: 'now')), ENT_QUOTES, 'UTF-8') ?></span>
            <span><i class="fa-regular fa-comments me-1 text-coral"></i><?= count($comments) ?> comment<?= count($comments) === 1 ? '' : 's' ?></span>
            <?php if ($article->getRecipeId() !== null): ?>
                <span class="hb-badge hb-badge-recipe">
                    <i class="fa-solid fa-utensils"></i>Linked Recipe #<?= $article->getRecipeId() ?>
                </span>
            <?php endif; ?>
        </div>

        <h1 class="mb-4"><?= htmlspecialchars($article->getTitle(), ENT_QUOTES, 'UTF-8') ?></h1>

        <div class="hb-article-body">
            <?= nl2br(htmlspecialchars($article->getContent(), ENT_QUOTES, 'UTF-8')) ?>
        </div>
    </article>

    <!-- Comments Section -->
    <div id="comments-section" class="reveal">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
            <h2 class="h4 mb-0">
                <i class="fa-solid fa-comments text-coral me-2"></i>
                Community Discussion (<?= count($comments) ?>)
            </h2>
            <?php if ($currentUser): ?>
                <a href="index.php?action=create_comment&article_id=<?= $article->getId() ?>" class="btn btn-hb-outline btn-sm">
                    <i class="fa-solid fa-plus me-1"></i>Dedicated Comment Form
                </a>
            <?php endif; ?>
        </div>

        <!-- Inline Add Comment Form (Authenticated Users) OR Login CTA (Visitors) -->
        <?php if ($currentUser): ?>
            <div class="hb-card mb-4">
                <h3 class="h6 mb-3">Add Your Comment</h3>
                <form method="POST" action="index.php?action=create_comment" data-validate="comment" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string)$csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="article_id" value="<?= $article->getId() ?>">

                    <div class="mb-2">
                        <label for="inline-comment-content" class="form-label">Comment</label>
                        <textarea
                            id="inline-comment-content"
                            name="content"
                            rows="3"
                            class="form-control hb-textarea"
                            placeholder="Share your feedback, workout result, or question..."
                            data-max-chars="1000"
                            data-counter-target="inline-comment-counter"
                            required
                        ></textarea>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <span id="inline-comment-counter" class="hb-char-counter">0 / 1000 characters</span>
                        <button type="submit" class="btn btn-hb-primary btn-sm">
                            <i class="fa-solid fa-paper-plane me-1"></i>Post Comment
                        </button>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <div class="hb-card mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <h3 class="h6 mb-1">Join the Conversation</h3>
                    <p class="small text-silver mb-0">
                        Sign in through the shared HB FITNESS User Management module to post, edit, or delete your comments.
                    </p>
                </div>
                <span class="hb-badge hb-badge-user">
                    <i class="fa-solid fa-lock"></i>Auth Required
                </span>
            </div>
        <?php endif; ?>

        <!-- Comments List -->
        <?php if (empty($comments)): ?>
            <div class="hb-card hb-empty-state">
                <i class="fa-regular fa-comment-dots"></i>
                <h5>No Comments Yet</h5>
                <p class="mb-0">Be the first to start a discussion on this article.</p>
            </div>
        <?php else: ?>
            <div class="d-flex flex-column gap-3">
                <?php foreach ($comments as $comment): ?>
                    <?php
                    $cRole      = $comment->getAuthorRole();
                    $cBadge     = $cRole === 'admin' ? 'hb-badge-admin' : ($cRole === 'coach' ? 'hb-badge-coach' : 'hb-badge-user');
                    $itemClass  = $cRole === 'admin' ? 'is-admin' : ($cRole === 'coach' ? 'is-coach' : '');
                    $canManageC = CommentController::canManageComment($comment, $currentUser);
                    $initials   = strtoupper(mb_substr($comment->getAuthorName(), 0, 1));
                    ?>
                    <div class="hb-comment-item <?= $itemClass ?>">
                        <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
                            <div class="d-flex align-items-center gap-2">
                                <span class="hb-avatar"><?= htmlspecialchars($initials, ENT_QUOTES, 'UTF-8') ?></span>
                                <div>
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <strong class="text-white"><?= htmlspecialchars($comment->getAuthorName(), ENT_QUOTES, 'UTF-8') ?></strong>
                                        <span class="hb-badge <?= $cBadge ?>"><?= htmlspecialchars($cRole, ENT_QUOTES, 'UTF-8') ?></span>
                                    </div>
                                    <div class="small text-muted-hb">
                                        <?= htmlspecialchars(date('M d, Y · H:i', strtotime($comment->getCreatedAt() ?: 'now')), ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                </div>
                            </div>

                            <?php if ($canManageC): ?>
                                <div class="d-flex gap-2">
                                    <a href="index.php?action=update_comment&id=<?= $comment->getId() ?>" class="btn btn-hb-outline btn-sm" title="Edit Comment">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <a href="index.php?action=delete_comment&id=<?= $comment->getId() ?>" class="btn btn-hb-danger btn-sm" title="Delete Comment">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>

                        <p class="mb-0 text-silver" style="white-space:pre-line;">
                            <?= htmlspecialchars($comment->getContent(), ENT_QUOTES, 'UTF-8') ?>
                        </p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php
$content = (string)ob_get_clean();
require __DIR__ . '/../../layout.php';
