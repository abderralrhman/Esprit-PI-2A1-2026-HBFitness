<?php
require_once __DIR__ . '/../../Controllers/RecipeCategoryController.php';
require_once __DIR__ . '/../../Controllers/RecipeController.php';
require_once __DIR__ . '/../../Controllers/StepController.php';

$categoryController = new RecipeCategoryController();
$recipeController   = new RecipeController();
$stepController     = new StepController();

$selectedCategory = isset($_GET['category_id']) && $_GET['category_id'] !== '' ? (int) $_GET['category_id'] : null;
$searchQuery      = trim((string) ($_GET['q'] ?? ''));
$selectedRecipeId = isset($_GET['recipe_id']) && $_GET['recipe_id'] !== '' ? (int) $_GET['recipe_id'] : null;

$categories = $categoryController->listCategories();
$recipes    = $recipeController->listRecipes($selectedCategory, $searchQuery);
$steps      = $selectedRecipeId ? $stepController->listStepsByRecipe($selectedRecipeId) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Nutrition &amp; Recipes</title>
</head>
<body>
    <h1>Nutrition &amp; Healthy Recipes</h1>

    <form method="get" action="">
        <input type="text" name="q" value="<?= htmlspecialchars($searchQuery, ENT_QUOTES, 'UTF-8') ?>" placeholder="Search recipes...">
        <select name="category_id">
            <option value="">All Categories</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= (int) $cat['id'] ?>" <?= ($selectedCategory === (int) $cat['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?> (<?= (int) $cat['recipes_count'] ?>)
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Filter</button>
    </form>

    <h2>Recipes</h2>
    <?php if (empty($recipes)): ?>
        <p>No recipes found.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($recipes as $recipe): ?>
                <li>
                    <strong><?= htmlspecialchars($recipe['title'], ENT_QUOTES, 'UTF-8') ?></strong>
                    — <?= htmlspecialchars($recipe['category_name'], ENT_QUOTES, 'UTF-8') ?>
                    — <?= (int) $recipe['calories'] ?> kcal
                    (Prep: <?= (int) $recipe['prep_time'] ?> min, Cook: <?= (int) $recipe['cook_time'] ?> min)
                    <?php if (!empty($recipe['description'])): ?>
                        <p><?= htmlspecialchars($recipe['description'], ENT_QUOTES, 'UTF-8') ?></p>
                    <?php endif; ?>
                    <a href="?<?= http_build_query(array_filter(['q' => $searchQuery, 'category_id' => $selectedCategory, 'recipe_id' => (int) $recipe['id']])) ?>">View Steps</a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <?php if ($selectedRecipeId): ?>
        <h2>Preparation Steps</h2>
        <?php if (empty($steps)): ?>
            <p>No steps added for this recipe yet.</p>
        <?php else: ?>
            <ol>
                <?php foreach ($steps as $step): ?>
                    <li><?= htmlspecialchars($step['instruction'], ENT_QUOTES, 'UTF-8') ?></li>
                <?php endforeach; ?>
            </ol>
        <?php endif; ?>
    <?php endif; ?>
</body>
</html>