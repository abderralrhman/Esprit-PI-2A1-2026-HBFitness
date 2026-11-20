<?php
require_once __DIR__ . '/../../controller/AuthController.php';
require_once __DIR__ . '/../../controller/article/CommentController.php';

$currentUser = AuthController::requireLogin();
$isAdmin     = AuthController::isAdmin();

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $commentC = new CommentController();
    $existing = $commentC->getCommentById($id);
    if ($existing && ($isAdmin || (int)$existing['user_id'] === (int)$currentUser['id'])) {
        $commentC->deleteComment($id);
        AuthController::flash("Comment #$id deleted.");
    } else {
        AuthController::flash("Access denied or comment not found.", 'error');
    }
}
header("Location: listComments.php");
exit;
?>
