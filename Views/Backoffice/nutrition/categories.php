<?php
/**
 * Views/Backoffice/nutrition/categories.php
 * Recipe category management (admin only).
 * Expects: $currentUser (User), $categories (array), $csrfToken (string)
 */
declare(strict_types=1);

$e = static fn (mixed $value): string => htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$currentPage = 'admin_recipe_categories';
$pageHeading = 'Recipe Categories';
$title       = 'Recipe Categories — HB FITNESS';

ob_start();
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <p class="text-silver mb-0">Organize recipes so members can filter them.</p>
    <a class="btn btn-hb-outline btn-sm" href="index.php?action=admin_recipes">
        <i class="fa-solid fa-arrow-left me-1"></i> Back to recipes
    </a>
</div>

<div class="hb-card mb-4">
    <h2 class="h5 mb-3"><i class="fa-solid fa-plus text-coral me-2"></i>New category</h2>
    <form method="post" action="index.php?action=admin_recipe_categories" class="d-flex flex-wrap gap-2">
        <input type="hidden" name="csrf_token" value="<?= $e($csrfToken) ?>">
        <input type="hidden" name="op" value="create">
        <input class="form-control" style="max-width: 320px;" name="name" maxlength="60"
               placeholder="e.g. Vegetarian" aria-label="Category name" required>
        <button type="submit" class="btn btn-hb-primary">Add category</button>
    </form>
</div>

<div class="hb-card">
    <h2 class="h5 mb-3"><i class="fa-solid fa-tags text-coral me-2"></i>All categories</h2>
    <?php if ($categories === []): ?>
        <p class="text-silver mb-0">No categories yet. Add the first one above.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-dark align-middle mb-0">
                <thead>
                    <tr><th>Name</th><th>Recipes</th><th class="text-end">Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $category): ?>
                        <tr>
                            <td>
                                <form method="post" action="index.php?action=admin_recipe_categories"
                                      class="d-flex gap-2">
                                    <input type="hidden" name="csrf_token" value="<?= $e($csrfToken) ?>">
                                    <input type="hidden" name="op" value="update">
                                    <input type="hidden" name="id" value="<?= (int)$category['id'] ?>">
                                    <input class="form-control form-control-sm" style="max-width: 260px;" name="name"
                                           maxlength="60" value="<?= $e($category['name']) ?>"
                                           aria-label="Category name">
                                    <button type="submit" class="btn btn-hb-outline btn-sm">Save</button>
                                </form>
                            </td>
                            <td><?= (int)$category['count'] ?></td>
                            <td class="text-end">
                                <form method="post" action="index.php?action=admin_recipe_categories"
                                      onsubmit="return confirm('Delete this category?');">
                                    <input type="hidden" name="csrf_token" value="<?= $e($csrfToken) ?>">
                                    <input type="hidden" name="op" value="delete">
                                    <input type="hidden" name="id" value="<?= (int)$category['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger"
                                        <?= $category['count'] > 0 ? 'disabled title="Remove or move its recipes first"' : '' ?>>
                                        Delete
                                    </button>
                                </form>
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
