<?php
require_once __DIR__ . '/../../Controllers/RecipeCategoryController.php';
require_once __DIR__ . '/../../Controllers/RecipeController.php';
require_once __DIR__ . '/../../Controllers/StepController.php';

$categoryController = new RecipeCategoryController();
$recipeController   = new RecipeController();
$stepController     = new StepController();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'add_category') {
        $name = trim((string) ($_POST['category_name'] ?? ''));
        if ($name !== '') {
            $categoryController->addCategory(new RecipeCategory($name));
            $message = 'Category added.';
        }
    } elseif ($action === 'delete_category') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            $categoryController->deleteCategory($id);
            $message = 'Category deleted.';
        }
    } elseif ($action === 'add_recipe') {
        $categoryId  = (int) ($_POST['category_id'] ?? 0);
        $title       = trim((string) ($_POST['title'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $prepTime    = max(0, (int) ($_POST['prep_time'] ?? 0));
        $cookTime    = max(0, (int) ($_POST['cook_time'] ?? 0));
        $calories    = max(0, (int) ($_POST['calories'] ?? 0));

        if ($categoryId > 0 && $title !== '') {
            $recipeController->addRecipe(new Recipe($categoryId, $title, $description, $prepTime, $cookTime, $calories));
            $message = 'Recipe added.';
        }
    } elseif ($action === 'delete_recipe') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            $recipeController->deleteRecipe($id);
            $message = 'Recipe deleted.';
        }
    } elseif ($action === 'add_step') {
        $recipeId    = (int) ($_POST['recipe_id'] ?? 0);
        $stepNumber  = max(1, (int) ($_POST['step_number'] ?? 1));
        $instruction = trim((string) ($_POST['instruction'] ?? ''));

        if ($recipeId > 0 && $instruction !== '') {
            $stepController->addStep(new Step($recipeId, $stepNumber, $instruction));
            $message = 'Step added.';
        }
    } elseif ($action === 'delete_step') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            $stepController->deleteStep($id);
            $message = 'Step deleted.';
        }
    }
}

$categories = $categoryController->listCategories();
$recipes    = $recipeController->listRecipes();
$steps      = $stepController->listAllSteps();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Backoffice - Nutrition</title>
</head>
<body>
    <h1>Nutrition Backoffice</h1>

    <?php if ($message !== ''): ?>
        <p><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <h2>Categories</h2>
    <form method="post" action="">
        <input type="hidden" name="action" value="add_category">
        <input type="text" name="category_name" placeholder="New category name" required>
        <button type="submit">Add Category</button>
    </form>
    <ul>
        <?php foreach ($categories as $cat): ?>
            <li>
                <?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?>
                <form method="post" action="" style="display:inline;">
                    <input type="hidden" name="action" value="delete_category">
                    <input type="hidden" name="id" value="<?= (int) $cat['id'] ?>">
                    <button type="submit">Delete</button>
                </form>
            </li>
        <?php endforeach; ?>
    </ul>

    <h2>Recipes</h2>
    <form method="post" action="">
        <input type="hidden" name="action" value="add_recipe">
        <select name="category_id" required>
            <option value="">Select category</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= (int) $cat['id'] ?>"><?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
        </select>
        <input type="text" name="title" placeholder="Recipe title" required>
        <input type="number" name="prep_time" min="0" value="10" placeholder="Prep min">
        <input type="number" name="cook_time" min="0" value="15" placeholder="Cook min">
        <input type="number" name="calories" min="0" value="400" placeholder="Calories">
        <input type="text" name="description" placeholder="Description">
        <button type="submit">Add Recipe</button>
    </form>
    <ul>
        <?php foreach ($recipes as $recipe): ?>
            <li>
                <?= htmlspecialchars($recipe['title'], ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($recipe['category_name'], ENT_QUOTES, 'UTF-8') ?>)
                <form method="post" action="" style="display:inline;">
                    <input type="hidden" name="action" value="delete_recipe">
                    <input type="hidden" name="id" value="<?= (int) $recipe['id'] ?>">
                    <button type="submit">Delete</button>
                </form>
            </li>
        <?php endforeach; ?>
    </ul>

    <h2>Steps</h2>
    <form method="post" action="">
        <input type="hidden" name="action" value="add_step">
        <select name="recipe_id" required>
            <option value="">Select recipe</option>
            <?php foreach ($recipes as $recipe): ?>
                <option value="<?= (int) $recipe['id'] ?>"><?= htmlspecialchars($recipe['title'], ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
        </select>
        <input type="number" name="step_number" min="1" value="1" required>
        <input type="text" name="instruction" placeholder="Step instruction" required>
        <button type="submit">Add Step</button>
    </form>
    <ul>
        <?php foreach ($steps as $step): ?>
            <li>
                <?= htmlspecialchars($step['recipe_title'], ENT_QUOTES, 'UTF-8') ?> — Step <?= (int) $step['step_number'] ?>:
                <?= htmlspecialchars($step['instruction'], ENT_QUOTES, 'UTF-8') ?>
                <form method="post" action="" style="display:inline;">
                    <input type="hidden" name="action" value="delete_step">
                    <input type="hidden" name="id" value="<?= (int) $step['id'] ?>">
                    <button type="submit">Delete</button>
                </form>
            </li>
        <?php endforeach; ?>
    </ul>
</body>
</html>