<?php
require_once __DIR__ . '/../../controller/AuthController.php';
$currentUser = AuthController::requireAdmin();

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($id === (int)$currentUser['id']) {
        AuthController::flash("You cannot delete your own account while logged in.", 'error');
    } else {
        $userC = new UserController();
        $userC->deleteUser($id);
        AuthController::flash("User #$id deleted.");
    }
}
header("Location: listUsers.php");
exit;
?>
