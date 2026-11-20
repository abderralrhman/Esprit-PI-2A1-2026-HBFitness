<?php
require_once __DIR__ . '/../../controller/AuthController.php';
require_once __DIR__ . '/../../controller/article/CommentController.php';
require_once __DIR__ . '/../../controller/article/ArticleController.php';

$currentUser   = AuthController::requireLogin();
$isAdmin       = AuthController::isAdmin();
$isStaff       = AuthController::isStaff();
$articleC      = new ArticleController();
$articles      = $articleC->listArticles();
$preselectedId = (int)($_GET['article_id'] ?? $_POST['article_id'] ?? 0);

// Contrôle de saisie PHP (Internal to createComment.php)
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $articleId   = (int)($_POST['article_id'] ?? 0);
    $content     = trim($_POST['content'] ?? '');
    $isCoachPick = $isStaff && isset($_POST['is_coach_pick']) ? 1 : 0;

    $errors = [];
    if (!$articleC->getArticleById($articleId)) {
        $errors[] = "Please select a valid Parent Article.";
    }
    if ($content === '' || mb_strlen($content) < 3 || !preg_match('/[\p{L}]{2,}/u', $content)) {
        $errors[] = "Comment Content cannot be empty and must contain letters (min 3 chars).";
    }

    if (empty($errors)) {
        $commentC = new CommentController();
        $commentC->addComment(new Comment(null, $articleId, (int)$currentUser['id'], $content, $isCoachPick));
        AuthController::flash("Comment posted.");
        header("Location: listComments.php");
        exit;
    } else {
        $error = implode(' | ', $errors);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Comment — HB FITNESS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/fitness.css">
</head>
<body>
<div class="app-wrapper">
    <?php
    $basePrefix = '../../';
    $activeNav  = 'comments';
    include __DIR__ . '/../partials/navbar.php';
    ?>

    <div class="container">
        <section class="crud-page-header">
            <div>
                <div class="breadcrumb-trail">
                    <a href="../../index.php?page=<?= $isAdmin ? 'admin' : 'user' ?>"><?= $isAdmin ? 'Admin Dashboard' : 'Athlete Hub' ?></a>
                    <span>/</span> <a href="listComments.php">Comments</a> <span>/</span> <span>Add</span>
                </div>
                <h1><i class="fa-solid fa-reply"></i> Add Article Comment</h1>
                <p>Share your training feedback or question on a fitness article.</p>
            </div>
            <a href="listComments.php" class="btn btn-outline" style="z-index:1;">
                <i class="fa-solid fa-arrow-left"></i> Back to Comments
            </a>
        </section>

        <div class="crud-subnav">
            <a href="listArticles.php" class="subnav-pill"><i class="fa-solid fa-newspaper"></i> Articles List</a>
            <a href="createArticle.php" class="subnav-pill"><i class="fa-solid fa-pen-nib"></i> Create Article</a>
            <a href="listComments.php" class="subnav-pill"><i class="fa-solid fa-comments"></i> Comments List</a>
            <a href="createComment.php" class="subnav-pill active"><i class="fa-solid fa-reply"></i> Add Comment</a>
        </div>

        <div class="glass-card form-card-centered">
            <div class="card-header">
                <div class="card-title"><i class="fa-solid fa-comment-medical"></i> New Comment</div>
            </div>
            <?php if ($error): ?>
                <div class="alert-box alert-error"><i class="fa-solid fa-triangle-exclamation"></i><span><?= htmlspecialchars($error) ?></span></div>
            <?php endif; ?>
            <form id="formCreateComment" method="POST" novalidate>
                <div class="form-group">
                    <label>Fitness Article</label>
                    <select name="article_id" class="form-control">
                        <?php foreach ($articles as $a): ?>
                            <option value="<?= (int)$a['id'] ?>" <?= $preselectedId === (int)$a['id'] ? 'selected' : '' ?>>
                                #<?= (int)$a['id'] ?> — <?= htmlspecialchars($a['title']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Comment Content (min 3 chars with letters)</label>
                    <textarea name="content" id="content" class="form-control" rows="4" placeholder="Share your experience, question, or training tip..."><?= htmlspecialchars($_POST['content'] ?? '') ?></textarea>
                    <div class="field-error-msg" id="err_content"></div>
                </div>
                <?php if ($isStaff): ?>
                <div class="form-group" style="display:flex;align-items:center;gap:0.5rem;">
                    <input type="checkbox" name="is_coach_pick" value="1" id="chkCoach">
                    <label for="chkCoach" style="margin:0;">Mark as Verified Coach Pick</label>
                </div>
                <?php endif; ?>
                <div style="display:flex;gap:0.75rem;">
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-paper-plane"></i> Post Comment</button>
                    <a href="listComments.php" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="../assets/js/fitness.js"></script>
<script>
document.getElementById('formCreateComment').addEventListener('submit', function(e) {
    let valid = true;
    const hasLetters = /[\p{L}]{2,}/u;
    const content = document.getElementById('content');
    document.getElementById('err_content').textContent = '';

    if (content.value.trim().length < 3 || !hasLetters.test(content.value.trim())) {
        document.getElementById('err_content').textContent = 'Comment cannot be empty and must contain letters (min 3 chars).';
        content.classList.add('is-invalid'); valid = false;
    } else { content.classList.remove('is-invalid'); }

    if (!valid) e.preventDefault();
});
</script>
</body>
</html>
