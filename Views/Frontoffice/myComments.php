<?php
/**
 * Views/Frontoffice/myComments.php — Logged-In Member's Own Comments
 */
$title = 'My Comments — HB FITNESS Blog';
ob_start();
?>
<section class="container py-5">
    <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4 reveal">
        <div>
            <span class="text-coral small fw-bold text-uppercase" style="letter-spacing:0.1em;">Your Activity</span>
            <h1 class="h2 mb-1">My Comments</h1>
            <p class="text-silver mb-0">
                You have published <?= count($comments) ?> comment<?= count($comments) === 1 ? '' : 's' ?> across HB FITNESS articles.
            </p>
        </div>
        <a href="index.php?action=articles" class="btn btn-hb-outline btn-sm">
            <i class="fa-solid fa-newspaper me-1"></i>Browse Articles
        </a>
    </div>

    <?php if (empty($comments)): ?>
        <div class="hb-card hb-empty-state reveal">
            <i class="fa-regular fa-comments"></i>
            <h4>No Comments Yet</h4>
            <p class="mb-3">Read an article and share your thoughts with the HB FITNESS community.</p>
            <a href="index.php?action=articles" class="btn btn-hb-primary btn-sm">Explore Articles</a>
        </div>
    <?php else: ?>
        <div class="d-flex flex-column gap-3 reveal">
            <?php foreach ($comments as $comment): ?>
                <div class="hb-card">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-2">
                        <div>
                            <span class="small text-muted-hb">On Article:</span>
                            <a href="index.php?action=article_details&id=<?= $comment->getArticleId() ?>" class="fw-semibold ms-1">
                                <?= htmlspecialchars($comment->getArticleTitle(), ENT_QUOTES, 'UTF-8') ?>
                            </a>
                        </div>
                        <span class="small text-muted-hb">
                            <i class="fa-regular fa-clock me-1"></i>
                            <?= htmlspecialchars(date('M d, Y · H:i', strtotime($comment->getCreatedAt() ?: 'now')), ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </div>

                    <p class="text-silver mb-3" style="white-space:pre-line;">
                        <?= htmlspecialchars($comment->getContent(), ENT_QUOTES, 'UTF-8') ?>
                    </p>

                    <div class="d-flex gap-2">
                        <a href="index.php?action=article_details&id=<?= $comment->getArticleId() ?>" class="btn btn-hb-outline btn-sm">
                            <i class="fa-solid fa-eye me-1"></i>View Article
                        </a>
                        <a href="index.php?action=update_comment&id=<?= $comment->getId() ?>" class="btn btn-hb-outline btn-sm">
                            <i class="fa-solid fa-pen me-1"></i>Edit
                        </a>
                        <a href="index.php?action=delete_comment&id=<?= $comment->getId() ?>" class="btn btn-hb-danger btn-sm">
                            <i class="fa-solid fa-trash me-1"></i>Delete
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
<?php
$content = (string)ob_get_clean();
require __DIR__ . '/../layout.php';
