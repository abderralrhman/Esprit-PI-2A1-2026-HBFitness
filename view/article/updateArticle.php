<?php
require_once __DIR__ . '/../../controller/AuthController.php';
require_once __DIR__ . '/../../controller/article/ArticleController.php';

$currentUser = AuthController::requireLogin();
$isAdmin     = AuthController::isAdmin();
$isStaff     = AuthController::isStaff();
$articleC    = new ArticleController();

$id = isset($_GET['id']) ? (int)$_GET['id'] : (int)($_POST['id'] ?? 0);
$existing = $articleC->getArticleById($id);

if (!$existing || (!$isStaff && (int)$existing['user_id'] !== (int)$currentUser['id'])) {
    AuthController::flash("Article not found or access denied.", 'error');
    header("Location: listArticles.php");
    exit;
}

// Contrôle de saisie PHP (Internal to updateArticle.php)
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title    = trim($_POST['title'] ?? '');
    $content  = trim($_POST['content'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $tags     = trim($_POST['tags'] ?? '');
    $readTime = (int)($_POST['read_time'] ?? $existing['read_time']);
    $status   = $isStaff ? ($_POST['status'] ?? $existing['status']) : $existing['status'];

    $errors = [];
    if ($title === '' || mb_strlen($title) < 5 || !preg_match('/[\p{L}]{2,}/u', $title)) {
        $errors[] = "Article Title must contain letters and be at least 5 characters.";
    }
    if ($category === '' || mb_strlen($category) < 3 || !preg_match('/^[\p{L}\s\-\'\&]+$/u', $category)) {
        $errors[] = "Category must contain letters only (min 3 characters).";
    }
    if ($readTime < 1 || $readTime > 60) {
        $errors[] = "Read Time must be between 1 and 60 minutes.";
    }
    if ($content === '' || mb_strlen($content) < 10 || !preg_match('/[\p{L}]{3,}/u', $content)) {
        $errors[] = "Article Content must be at least 10 characters with letters.";
    }

    if (empty($errors)) {
        $articleC->updateArticle(
            new Article($id, $title, $content, $category, $tags, $readTime, (int)$existing['likes_count'], (int)$existing['views_count'], $status, (int)$existing['user_id']),
            $id
        );
        AuthController::flash("Article #$id updated.");
        header("Location: listArticles.php");
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
    <title>Update Article #<?= $id ?> — HB FITNESS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/fitness.css">
</head>
<body>
<div class="app-wrapper">
    <?php
    $basePrefix = '../../';
    $activeNav  = 'articles';
    include __DIR__ . '/../partials/navbar.php';
    ?>

    <div class="container">
        <section class="crud-page-header">
            <div>
                <div class="breadcrumb-trail">
                    <a href="../../index.php?page=<?= $isAdmin ? 'admin' : 'user' ?>"><?= $isAdmin ? 'Admin Dashboard' : 'Athlete Hub' ?></a>
                    <span>/</span> <a href="listArticles.php">Articles</a> <span>/</span> <span>Edit #<?= $id ?></span>
                </div>
                <h1><i class="fa-solid fa-pen-to-square"></i> Edit Fitness Article #<?= $id ?></h1>
                <p>Update article title, sport category, read time, tags, or content.</p>
            </div>
            <a href="listArticles.php" class="btn btn-outline" style="z-index:1;">
                <i class="fa-solid fa-arrow-left"></i> Back to Articles
            </a>
        </section>

        <div class="glass-card form-card-centered">
            <div class="card-header">
                <div class="card-title"><i class="fa-solid fa-pen"></i> Article #<?= $id ?> Details</div>
            </div>
            <?php if ($error): ?>
                <div class="alert-box alert-error"><i class="fa-solid fa-triangle-exclamation"></i><span><?= htmlspecialchars($error) ?></span></div>
            <?php endif; ?>
            <form id="formUpdateArticle" method="POST" novalidate>
                <input type="hidden" name="id" value="<?= $id ?>">
                <div class="form-group">
                    <label>Title</label>
                    <input type="text" name="title" id="title" class="form-control" value="<?= htmlspecialchars($_POST['title'] ?? $existing['title']) ?>">
                    <div class="field-error-msg" id="err_title"></div>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Category (Letters only)</label>
                        <input type="text" name="category" id="category" class="form-control" value="<?= htmlspecialchars($_POST['category'] ?? $existing['category']) ?>">
                        <div class="field-error-msg" id="err_category"></div>
                    </div>
                    <div class="form-group">
                        <label>Read Time (minutes)</label>
                        <input type="number" name="read_time" id="read_time" class="form-control" min="1" max="60" value="<?= htmlspecialchars($_POST['read_time'] ?? $existing['read_time']) ?>">
                        <div class="field-error-msg" id="err_read_time"></div>
                    </div>
                    <?php if ($isStaff): ?>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <?php foreach (['Published', 'Featured', 'Archived'] as $s): ?>
                                <option value="<?= $s ?>" <?= ($_POST['status'] ?? $existing['status']) === $s ? 'selected' : '' ?>><?= $s ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label>Tags</label>
                    <input type="text" name="tags" class="form-control" value="<?= htmlspecialchars($_POST['tags'] ?? $existing['tags']) ?>">
                </div>
                <div class="form-group">
                    <label>Content</label>
                    <textarea name="content" id="content" class="form-control" rows="5"><?= htmlspecialchars($_POST['content'] ?? $existing['content']) ?></textarea>
                    <div class="field-error-msg" id="err_content"></div>
                </div>
                <div style="display:flex;gap:0.75rem;">
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Update Article</button>
                    <a href="listArticles.php" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="../assets/js/fitness.js"></script>
<script>
document.getElementById('formUpdateArticle').addEventListener('submit', function(e) {
    let valid = true;
    const hasLetters = /[\p{L}]{2,}/u;
    const alphaOnly = /^[\p{L}\s\-\'\&]+$/u;
    const title = document.getElementById('title');
    const category = document.getElementById('category');
    const readTime = document.getElementById('read_time');
    const content = document.getElementById('content');
    document.querySelectorAll('.field-error-msg').forEach(el => el.textContent = '');

    if (title.value.trim().length < 5 || !hasLetters.test(title.value.trim())) {
        document.getElementById('err_title').textContent = 'Article Title must contain letters and be at least 5 characters.';
        title.classList.add('is-invalid'); valid = false;
    } else { title.classList.remove('is-invalid'); }

    if (category.value.trim().length < 3 || !alphaOnly.test(category.value.trim())) {
        document.getElementById('err_category').textContent = 'Category must contain letters only (min 3 characters).';
        category.classList.add('is-invalid'); valid = false;
    } else { category.classList.remove('is-invalid'); }

    const rt = parseInt(readTime.value, 10);
    if (isNaN(rt) || rt < 1 || rt > 60) {
        document.getElementById('err_read_time').textContent = 'Read Time must be between 1 and 60 minutes.';
        readTime.classList.add('is-invalid'); valid = false;
    } else { readTime.classList.remove('is-invalid'); }

    if (content.value.trim().length < 10 || !hasLetters.test(content.value.trim())) {
        document.getElementById('err_content').textContent = 'Content must be at least 10 characters with letters.';
        content.classList.add('is-invalid'); valid = false;
    } else { content.classList.remove('is-invalid'); }

    if (!valid) e.preventDefault();
});
</script>
</body>
</html>
