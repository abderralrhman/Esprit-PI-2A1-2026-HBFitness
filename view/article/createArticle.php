<?php
require_once __DIR__ . '/../../controller/AuthController.php';
require_once __DIR__ . '/../../controller/article/ArticleController.php';

$currentUser = AuthController::requireLogin();
$isAdmin     = AuthController::isAdmin();
$isStaff     = AuthController::isStaff();

$categoriesList = [
    'Strength Training',
    'Cardio & HIIT',
    'Nutrition & Recovery',
    'Yoga & Mobility',
    'Running & Endurance',
    'Combat Sports'
];

// Contrôle de saisie PHP (Internal to createArticle.php)
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title    = trim($_POST['title'] ?? '');
    $content  = trim($_POST['content'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $tags     = trim($_POST['tags'] ?? '');
    $readTime = (int)($_POST['read_time'] ?? 5);
    $status   = $isStaff ? ($_POST['status'] ?? 'Published') : 'Published';

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
        $articleC = new ArticleController();
        $articleC->addArticle(new Article(null, $title, $content, $category, $tags, $readTime, 0, 0, $status, (int)$currentUser['id']));
        AuthController::flash("Fitness article published.");
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
    <title>Create Fitness Article — HB FITNESS</title>
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
                    <span>/</span> <a href="listArticles.php">Articles</a> <span>/</span> <span>Create</span>
                </div>
                <h1><i class="fa-solid fa-pen-nib"></i> Publish New Fitness Article</h1>
                <p>Share a workout protocol, nutrition guide, or sport performance breakdown.</p>
            </div>
            <a href="listArticles.php" class="btn btn-outline" style="z-index:1;">
                <i class="fa-solid fa-arrow-left"></i> Back to Articles
            </a>
        </section>

        <div class="crud-subnav">
            <a href="listArticles.php" class="subnav-pill"><i class="fa-solid fa-newspaper"></i> Articles List</a>
            <a href="createArticle.php" class="subnav-pill active"><i class="fa-solid fa-pen-nib"></i> Create Article</a>
            <a href="listComments.php" class="subnav-pill"><i class="fa-solid fa-comments"></i> Comments List</a>
            <a href="createComment.php" class="subnav-pill"><i class="fa-solid fa-reply"></i> Add Comment</a>
        </div>

        <div class="glass-card form-card-centered">
            <div class="card-header">
                <div class="card-title"><i class="fa-solid fa-newspaper"></i> Article Details</div>
            </div>
            <?php if ($error): ?>
                <div class="alert-box alert-error"><i class="fa-solid fa-triangle-exclamation"></i><span><?= htmlspecialchars($error) ?></span></div>
            <?php endif; ?>
            <form id="formCreateArticle" method="POST" novalidate>
                <div class="form-group">
                    <label>Title (min 5 chars with letters)</label>
                    <input type="text" name="title" id="title" class="form-control" placeholder="e.g. 5 Compound Lifts for Total-Body Strength" value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">
                    <div class="field-error-msg" id="err_title"></div>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Sport / Fitness Category</label>
                        <select name="category" class="form-control">
                            <?php foreach ($categoriesList as $cat): ?>
                                <option value="<?= $cat ?>" <?= ($_POST['category'] ?? '') === $cat ? 'selected' : '' ?>><?= $cat ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Read Time (minutes, 1–60)</label>
                        <input type="number" name="read_time" id="read_time" class="form-control" min="1" max="60" value="<?= htmlspecialchars($_POST['read_time'] ?? '5') ?>">
                        <div class="field-error-msg" id="err_read_time"></div>
                    </div>
                    <?php if ($isStaff): ?>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="Published">Published</option>
                            <option value="Featured">Featured</option>
                            <option value="Archived">Archived</option>
                        </select>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label>Tags (comma-separated)</label>
                    <input type="text" name="tags" class="form-control" value="<?= htmlspecialchars($_POST['tags'] ?? 'Fitness,Workout,Performance') ?>">
                </div>
                <div class="form-group">
                    <label>Content (min 10 chars)</label>
                    <textarea name="content" id="content" class="form-control" rows="5" placeholder="Write your training insights, reps, sets, or nutrition guidance..."><?= htmlspecialchars($_POST['content'] ?? '') ?></textarea>
                    <div class="field-error-msg" id="err_content"></div>
                </div>
                <div style="display:flex;gap:0.75rem;">
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-paper-plane"></i> Publish Article</button>
                    <a href="listArticles.php" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="../assets/js/fitness.js"></script>
<script>
document.getElementById('formCreateArticle').addEventListener('submit', function(e) {
    let valid = true;
    const hasLetters = /[\p{L}]{2,}/u;
    const title = document.getElementById('title');
    const readTime = document.getElementById('read_time');
    const content = document.getElementById('content');
    document.querySelectorAll('.field-error-msg').forEach(el => el.textContent = '');

    if (title.value.trim().length < 5 || !hasLetters.test(title.value.trim())) {
        document.getElementById('err_title').textContent = 'Article Title must contain letters and be at least 5 characters.';
        title.classList.add('is-invalid'); valid = false;
    } else { title.classList.remove('is-invalid'); }

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
