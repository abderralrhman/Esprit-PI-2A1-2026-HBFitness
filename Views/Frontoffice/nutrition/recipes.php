<?php
/**
 * Views/Frontoffice/nutrition/recipes.php
 * Browse healthy recipes, filter by category, search.
 * Expects: $currentUser (?User), $recipes (array), $categories (array),
 *          $activeCategory (int), $search (string)
 */
declare(strict_types=1);

$e = static fn (mixed $value): string => htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$currentPage = 'recipes';
$title       = 'Healthy Recipes — HB FITNESS';
$role        = $currentUser ? $currentUser->getRoleName() : '';
$manageLink  = $role === 'admin' ? 'admin_recipes' : ($role === 'coach' ? 'coach_recipes' : '');

ob_start();
?>
<div class="container py-5">
    <div class="fo-banner d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h1 class="h2 mb-1"><i class="fa-solid fa-utensils text-coral me-2"></i>Healthy Recipes</h1>
            <p class="text-silver mb-0">Calories and macros for every meal, with step-by-step preparation.</p>
        </div>
        <?php if ($manageLink !== ''): ?>
            <a href="index.php?action=<?= $e($manageLink) ?>" class="btn btn-hb-primary">
                <i class="fa-solid fa-pen-to-square me-1"></i> Manage recipes
            </a>
        <?php endif; ?>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <nav class="d-flex flex-wrap gap-2" aria-label="Filter by category">
            <a class="btn btn-sm <?= $activeCategory === 0 ? 'btn-hb-primary' : 'btn-hb-outline' ?>"
               href="index.php?action=recipes<?= $search !== '' ? '&amp;q=' . rawurlencode($search) : '' ?>">All</a>
            <?php foreach ($categories as $category): ?>
                <a class="btn btn-sm <?= $activeCategory === $category['id'] ? 'btn-hb-primary' : 'btn-hb-outline' ?>"
                   href="index.php?action=recipes&amp;category=<?= (int)$category['id'] ?><?= $search !== '' ? '&amp;q=' . rawurlencode($search) : '' ?>">
                    <?= $e($category['name']) ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <form class="d-flex gap-2" method="get" action="index.php">
            <input type="hidden" name="action" value="recipes">
            <?php if ($activeCategory > 0): ?>
                <input type="hidden" name="category" value="<?= (int)$activeCategory ?>">
            <?php endif; ?>
            <input type="search" name="q" value="<?= $e($search) ?>" class="form-control"
                   placeholder="Search recipes" aria-label="Search recipes" maxlength="100">
            <button type="submit" class="btn btn-hb-primary">Search</button>
        </form>
    </div>

    <?php if ($recipes === []): ?>
        <div class="hb-card text-center py-5">
            <i class="fa-solid fa-magnifying-glass fa-2x mb-3 text-secondary"></i>
            <h2 class="h5">No recipes found</h2>
            <p class="text-silver mb-3">Try another search or browse all categories.</p>
            <a href="index.php?action=recipes" class="btn btn-hb-outline btn-sm">Reset filters</a>
        </div>
    <?php else: ?>
        <p class="text-silver small mb-3"><?= count($recipes) ?> recipe<?= count($recipes) === 1 ? '' : 's' ?></p>
        <div class="row g-4">
            <?php foreach ($recipes as $recipe): ?>
                <div class="col-md-6 col-lg-4">
                    <article class="hb-card hb-card-hover h-100 d-flex flex-column">
                        <span class="text-coral small fw-bold"><?= $e($recipe['category']) ?></span>
                        <h2 class="h5 mt-1 mb-2">
                            <a class="text-decoration-none text-white"
                               href="index.php?action=recipe_details&amp;id=<?= (int)$recipe['id'] ?>"><?= $e($recipe['title']) ?></a>
                        </h2>
                        <p class="text-silver small flex-grow-1">
                            <?= $e(mb_strimwidth($recipe['description'], 0, 120, '…')) ?>
                        </p>
                        <div class="d-flex flex-wrap gap-3 small mb-3">
                            <span><i class="fa-solid fa-fire text-coral me-1"></i><?= (int)$recipe['calories'] ?> kcal</span>
                            <span><i class="fa-solid fa-clock text-coral me-1"></i><?= (int)$recipe['prep_minutes'] ?> min</span>
                            <span>P <?= $e(rtrim(rtrim(number_format($recipe['protein_g'], 1), '0'), '.')) ?>g</span>
                            <span>C <?= $e(rtrim(rtrim(number_format($recipe['carbs_g'], 1), '0'), '.')) ?>g</span>
                            <span>F <?= $e(rtrim(rtrim(number_format($recipe['fat_g'], 1), '0'), '.')) ?>g</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-silver">By <?= $e($recipe['author']) ?></small>
                            <a class="btn btn-hb-outline btn-sm"
                               href="index.php?action=recipe_details&amp;id=<?= (int)$recipe['id'] ?>">View recipe</a>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<?php
$content = (string)ob_get_clean();
require __DIR__ . '/../../layout.php';
