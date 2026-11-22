<?php
require_once __DIR__ . '/../../controller/AuthController.php';
require_once __DIR__ . '/../../controller/article/ArticleController.php';

$currentUser = AuthController::requireLogin();
$isStaff     = AuthController::isStaff();

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $articleC = new ArticleController();
    $existing = $articleC->getArticleById($id);
    if ($existing && ($isStaff || (int)$existing['user_id'] === (int)$currentUser['id'])) {
        $articleC->deleteArticle($id);
        AuthController::flash("Article #$id deleted.");
    } else {
        AuthController::flash("Access denied or article not found.", 'error');
    }
}
header("Location: listArticles.php");
exit;
?>
