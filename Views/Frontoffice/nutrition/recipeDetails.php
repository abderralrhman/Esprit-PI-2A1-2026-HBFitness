<?php
/**
 * Views/Frontoffice/nutrition/recipeDetails.php
 * Expects: $currentUser (?User), $recipe (array with 'steps'), $canManage (bool), $csrfToken (string)
 */
declare(strict_types=1);

$e = static fn (mixed $value): string => htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$fmt = static fn (float $value): string => rtrim(rtrim(number_format($value, 1, '.', ''), '0'), '.');
$currentPage = 'recipes';
$title       = $recipe['title'] . ' — HB FITNESS';
$prefix      = ($currentUser && $currentUser->getRoleName() === 'admin') ? 'admin' : 'coach';

ob_start();
?>
<div class="container py-5" style="max-width: 900px;">
    <a href="index.php?action=recipes" class="text-silver text-decoration-none small">
        <i class="fa-solid fa-arrow-left me-1"></i> Back to recipes
    </a>

    <div class="hb-card mt-3">
        <span class="text-coral small fw-bold"><?= $e($recipe['category']) ?></span>
        <h1 class="h2 mt-1"><?= $e($recipe['title']) ?></h1>
        <p class="text-silver small mb-3">
            By <?= $e($recipe['author']) ?> · <?= $e($recipe['date']) ?>
        </p>
        <p><?= nl2br($e($recipe['description'])) ?></p>

        <div class="row g-3 text-center my-3">
            <div class="col-6 col-md-3">
                <div class="hb-card py-3">
                    <div class="h4 mb-0 text-coral"><?= (int)$recipe['calories'] ?></div>
                    <small class="text-silver">kcal per serving</small>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="hb-card py-3">
                    <div class="h4 mb-0"><?= $e($fmt($recipe['protein_g'])) ?> g</div>
                    <small class="text-silver">Protein</small>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="hb-card py-3">
                    <div class="h4 mb-0"><?= $e($fmt($recipe['carbs_g'])) ?> g</div>
                    <small class="text-silver">Carbohydrates</small>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="hb-card py-3">
                    <div class="h4 mb-0"><?= $e($fmt($recipe['fat_g'])) ?> g</div>
                    <small class="text-silver">Fat</small>
                </div>
            </div>
        </div>

        <p class="text-silver small">
            <i class="fa-solid fa-clock text-coral me-1"></i><?= (int)$recipe['prep_minutes'] ?> minutes
            <span class="mx-2">|</span>
            <i class="fa-solid fa-users text-coral me-1"></i><?= (int)$recipe['servings'] ?>
            serving<?= $recipe['servings'] === 1 ? '' : 's' ?>
        </p>

        <h2 class="h5 mt-4">Preparation</h2>
        <ol class="ps-3">
            <?php foreach ($recipe['steps'] as $step): ?>
                <li class="mb-2"><?= $e($step) ?></li>
            <?php endforeach; ?>
        </ol>

        <?php if (!empty($canManage)): ?>
            <hr class="border-secondary">
            <div class="d-flex flex-wrap gap-2">
                <a class="btn btn-hb-outline btn-sm"
                   href="index.php?action=<?= $e($prefix) ?>_edit_recipe&amp;id=<?= (int)$recipe['id'] ?>">
                    <i class="fa-solid fa-pen me-1"></i> Edit
                </a>
                <form method="post" action="index.php?action=<?= $e($prefix) ?>_delete_recipe"
                      onsubmit="return confirm('Delete this recipe?');">
                    <input type="hidden" name="csrf_token" value="<?= $e($csrfToken) ?>">
                    <input type="hidden" name="id" value="<?= (int)$recipe['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-outline-danger">
                        <i class="fa-solid fa-trash me-1"></i> Delete
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php
$content = (string)ob_get_clean();
require __DIR__ . '/../../layout.php';
