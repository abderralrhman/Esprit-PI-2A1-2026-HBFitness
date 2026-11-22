<?php
require_once __DIR__ . '/../../controller/AuthController.php';
require_once __DIR__ . '/../../controller/article/CommentController.php';
require_once __DIR__ . '/../../controller/article/ArticleController.php';

$currentUser = AuthController::requireLogin();
$isAdmin     = AuthController::isAdmin();
$isStaff     = AuthController::isStaff();
$commentC    = new CommentController();
$articleC    = new ArticleController();
$articles    = $articleC->listArticles();

$id = isset($_GET['id']) ? (int)$_GET['id'] : (int)($_POST['id'] ?? 0);
$existing = $commentC->getCommentById($id);

if (!$existing || (!$isStaff && (int)$existing['user_id'] !== (int)$currentUser['id'])) {
    AuthController::flash("Comment not found or access denied.", 'error');
    header("Location: listComments.php");
    exit;
}

// Contrôle de saisie PHP (Internal to updateComment.php)
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $articleId   = $isStaff ? (int)($_POST['article_id'] ?? $existing['article_id']) : (int)$existing['article_id'];
    $content     = trim($_POST['content'] ?? '');
    $isCoachPick = $isStaff ? (isset($_POST['is_coach_pick']) ? 1 : 0) : (int)$existing['is_coach_pick'];

    $errors = [];
    if (!$articleC->getArticleById($articleId)) {
        $errors[] = "Please select a valid Parent Article.";
    }
    if ($content === '' || mb_strlen($content) < 3 || !preg_match('/[\p{L}]{2,}/u', $content)) {
        $errors[] = "Comment Content cannot be empty and must contain letters (min 3 chars).";
    }

    if (empty($errors)) {
        $commentC->updateComment(new Comment($id, $articleId, (int)$existing['user_id'], $content, $isCoachPick), $id);
        AuthController::flash("Comment #$id updated.");
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
    <title>Update Comment #<?= $id ?> — HB FITNESS</title>
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
                    <span>/</span> <a href="listComments.php">Comments</a> <span>/</span> <span>Edit #<?= $id ?></span>
                </div>
                <h1><i class="fa-solid fa-pen-to-square"></i> Edit Comment #<?= $id ?></h1>
                <p>Update your reply content or Coach Pick badge.</p>
            </div>
            <a href="listComments.php" class="btn btn-outline" style="z-index:1;">
                <i class="fa-solid fa-arrow-left"></i> Back to Comments
            </a>
        </section>

        <div class="glass-card form-card-centered">
            <div class="card-header">
                <div class="card-title"><i class="fa-solid fa-pen"></i> Comment #<?= $id ?> Details</div>
            </div>
            <?php if ($error): ?>
                <div class="alert-box alert-error"><i class="fa-solid fa-triangle-exclamation"></i><span><?= htmlspecialchars($error) ?></span></div>
            <?php endif; ?>
            <form id="formUpdateComment" method="POST" novalidate>
                <input type="hidden" name="id" value="<?= $id ?>">
                <?php if ($isStaff): ?>
                <div class="form-group">
                    <label>Parent Article</label>
                    <select name="article_id" class="form-control">
                        <?php foreach ($articles as $a): ?>
                            <option value="<?= (int)$a['id'] ?>" <?= (int)($_POST['article_id'] ?? $existing['article_id']) === (int)$a['id'] ? 'selected' : '' ?>><?= htmlspecialchars($a['title']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                <div class="form-group">
                    <label>Comment Content</label>
                    <textarea name="content" id="content" class="form-control" rows="4"><?= htmlspecialchars($_POST['content'] ?? $existing['content']) ?></textarea>
                    <div class="field-error-msg" id="err_content"></div>
                </div>
                <?php if ($isStaff): ?>
                <div class="form-group" style="display:flex;align-items:center;gap:0.5rem;">
                    <input type="checkbox" name="is_coach_pick" value="1" id="chkCoachUpd" <?= (int)$existing['is_coach_pick'] === 1 ? 'checked' : '' ?>>
                    <label for="chkCoachUpd" style="margin:0;">Mark as Verified Coach Pick</label>
                </div>
                <?php endif; ?>
                <div style="display:flex;gap:0.75rem;">
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Update Comment</button>
                    <a href="listComments.php" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="../assets/js/fitness.js"></script>
<script>
document.getElementById('formUpdateComment').addEventListener('submit', function(e) {
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
