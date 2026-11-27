<?php
/**
 * Views/Backoffice/nutrition/recipes.php
 * Recipe management (create / edit form + table).
 * Expects: $currentUser (User), $recipes, $categories, $stats, $editing (?array),
 *          $errors (array), $old (array), $csrfToken (string)
 */
declare(strict_types=1);

$e = static fn (mixed $value): string => htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$isAdmin     = $currentUser->getRoleName() === 'admin';
$prefix      = $isAdmin ? 'admin' : 'coach';
$currentPage = 'admin_recipes';
$pageHeading = 'Nutrition & Recipes';
$title       = 'Recipes — HB FITNESS';

$formAction = $editing
    ? 'index.php?action=' . $prefix . '_edit_recipe&id=' . (int)$editing['id']
    : 'index.php?action=' . $prefix . '_create_recipe';

$err = static fn (array $errors, string $field): string => isset($errors[$field])
    ? '<div class="text-danger small mt-1">' . htmlspecialchars($errors[$field], ENT_QUOTES, 'UTF-8') . '</div>'
    : '';

ob_start();
?>
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="hb-card"><small class="text-silver">Recipes</small>
            <div class="h3 mb-0 text-coral"><?= (int)$stats['recipes'] ?></div></div>
    </div>
    <div class="col-md-4">
        <div class="hb-card"><small class="text-silver">Average calories</small>
            <div class="h3 mb-0"><?= (int)$stats['avg_calories'] ?> kcal</div></div>
    </div>
    <div class="col-md-4">
        <div class="hb-card"><small class="text-silver">Categories</small>
            <div class="h3 mb-0"><?= (int)$stats['categories'] ?></div>
            <?php if ($isAdmin): ?>
                <a class="small text-coral" href="index.php?action=admin_recipe_categories">Manage categories</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="hb-card mb-4" id="recipe-form">
    <h2 class="h5 mb-3">
        <i class="fa-solid fa-<?= $editing ? 'pen' : 'plus' ?> text-coral me-2"></i>
        <?= $editing ? 'Edit recipe' : 'New recipe' ?>
    </h2>

    <?php if (!empty($errors['csrf'])): ?>
        <div class="hb-alert hb-alert-error mb-3"><?= $e($errors['csrf']) ?></div>
    <?php endif; ?>

    <form method="post" action="<?= $e($formAction) ?>" novalidate>
        <input type="hidden" name="csrf_token" value="<?= $e($csrfToken) ?>">
        <?php if ($editing): ?>
            <input type="hidden" name="id" value="<?= (int)$editing['id'] ?>">
        <?php endif; ?>

        <div class="row g-3">
            <div class="col-md-8">
                <label class="form-label" for="title">Title</label>
                <input class="form-control" id="title" name="title" maxlength="150"
                       value="<?= $e($old['title']) ?>" placeholder="e.g. Greek yogurt oat bowl">
                <?= $err($errors, 'title') ?>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="category_id">Category</label>
                <select class="form-select" id="category_id" name="category_id">
                    <option value="0">Choose a category</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= (int)$category['id'] ?>"
                            <?= (int)$old['category_id'] === $category['id'] ? 'selected' : '' ?>>
                            <?= $e($category['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?= $err($errors, 'category_id') ?>
            </div>

            <div class="col-12">
                <label class="form-label" for="description">Description</label>
                <textarea class="form-control" id="description" name="description" rows="3"
                          maxlength="2000"><?= $e($old['description']) ?></textarea>
                <?= $err($errors, 'description') ?>
            </div>

            <div class="col-6 col-md-2">
                <label class="form-label" for="prep_minutes">Prep (min)</label>
                <input class="form-control" id="prep_minutes" name="prep_minutes" inputmode="numeric"
                       value="<?= $e($old['prep_minutes']) ?>">
                <?= $err($errors, 'prep_minutes') ?>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label" for="servings">Servings</label>
                <input class="form-control" id="servings" name="servings" inputmode="numeric"
                       value="<?= $e($old['servings']) ?>">
                <?= $err($errors, 'servings') ?>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label" for="calories">Calories</label>
                <input class="form-control" id="calories" name="calories" inputmode="numeric"
                       value="<?= $e($old['calories']) ?>" placeholder="per serving">
                <?= $err($errors, 'calories') ?>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label" for="protein_g">Protein (g)</label>
                <input class="form-control" id="protein_g" name="protein_g" inputmode="decimal"
                       value="<?= $e($old['protein_g']) ?>">
                <?= $err($errors, 'protein_g') ?>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label" for="carbs_g">Carbs (g)</label>
                <input class="form-control" id="carbs_g" name="carbs_g" inputmode="decimal"
                       value="<?= $e($old['carbs_g']) ?>">
                <?= $err($errors, 'carbs_g') ?>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label" for="fat_g">Fat (g)</label>
                <input class="form-control" id="fat_g" name="fat_g" inputmode="decimal"
                       value="<?= $e($old['fat_g']) ?>">
                <?= $err($errors, 'fat_g') ?>
            </div>

            <div class="col-12">
                <label class="form-label" for="steps">Preparation steps</label>
                <textarea class="form-control" id="steps" name="steps" rows="6"
                          placeholder="One step per line"><?= $e($old['steps']) ?></textarea>
                <?= $err($errors, 'steps') ?>
            </div>
        </div>

        <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-hb-primary">
                <?= $editing ? 'Save changes' : 'Create recipe' ?>
            </button>
            <?php if ($editing): ?>
                <a class="btn btn-hb-outline" href="index.php?action=<?= $e($prefix) ?>_recipes">Cancel</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="hb-card">
    <h2 class="h5 mb-3"><i class="fa-solid fa-list text-coral me-2"></i><?= $isAdmin ? 'All recipes' : 'My recipes' ?></h2>

    <?php if ($recipes === []): ?>
        <p class="text-silver mb-0">No recipes yet. Use the form above to add the first one.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-dark align-middle mb-0">
                <thead>
                    <tr>
                        <th>Recipe</th>
                        <th>Category</th>
                        <th>Author</th>
                        <th>kcal</th>
                        <th>P / C / F (g)</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recipes as $recipe): ?>
                        <tr>
                            <td>
                                <a class="text-white text-decoration-none"
                                   href="index.php?action=recipe_details&amp;id=<?= (int)$recipe['id'] ?>"><?= $e($recipe['title']) ?></a>
                            </td>
                            <td><?= $e($recipe['category']) ?></td>
                            <td><?= $e($recipe['author']) ?></td>
                            <td><?= (int)$recipe['calories'] ?></td>
                            <td><?= $e($recipe['protein_g']) ?> / <?= $e($recipe['carbs_g']) ?> / <?= $e($recipe['fat_g']) ?></td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-2">
                                    <a class="btn btn-hb-outline btn-sm"
                                       href="index.php?action=<?= $e($prefix) ?>_edit_recipe&amp;id=<?= (int)$recipe['id'] ?>#recipe-form">Edit</a>
                                    <form method="post" action="index.php?action=<?= $e($prefix) ?>_delete_recipe"
                                          onsubmit="return confirm('Delete this recipe?');">
                                        <input type="hidden" name="csrf_token" value="<?= $e($csrfToken) ?>">
                                        <input type="hidden" name="id" value="<?= (int)$recipe['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<?php
$content = (string)ob_get_clean();
require __DIR__ . '/../../layout_admin.php';
