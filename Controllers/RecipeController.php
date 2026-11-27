<?php
declare(strict_types=1);

require_once __DIR__ . '/../Models/RecipeRepository.php';

/**
 * Controllers/RecipeController.php
 * Validation, normalization and permissions for the Nutrition module.
 * Mirrors SessionController: the router (index.php) handles requests and views.
 */
final class RecipeController
{
    public function __construct(private RecipeRepository $recipes) {}

    /** Empty form values (create form). */
    public function defaults(): array
    {
        return [
            'title' => '', 'description' => '', 'category_id' => 0,
            'prep_minutes' => 15, 'servings' => 1, 'calories' => '',
            'protein_g' => '', 'carbs_g' => '', 'fat_g' => '', 'steps' => '',
        ];
    }

    /** Raw POST values kept as typed so the form can be redisplayed on error. */
    public function fromPost(array $post): array
    {
        return [
            'title' => trim((string)($post['title'] ?? '')),
            'description' => trim((string)($post['description'] ?? '')),
            'category_id' => (int)($post['category_id'] ?? 0),
            'prep_minutes' => trim((string)($post['prep_minutes'] ?? '')),
            'servings' => trim((string)($post['servings'] ?? '')),
            'calories' => trim((string)($post['calories'] ?? '')),
            'protein_g' => trim((string)($post['protein_g'] ?? '')),
            'carbs_g' => trim((string)($post['carbs_g'] ?? '')),
            'fat_g' => trim((string)($post['fat_g'] ?? '')),
            'steps' => trim((string)($post['steps'] ?? '')),
        ];
    }

    /** Form values for editing an existing recipe (from RecipeRepository::find). */
    public function fromRecipe(array $recipe): array
    {
        return [
            'title' => $recipe['title'],
            'description' => $recipe['description'],
            'category_id' => $recipe['category_id'],
            'prep_minutes' => $recipe['prep_minutes'],
            'servings' => $recipe['servings'],
            'calories' => $recipe['calories'],
            'protein_g' => $recipe['protein_g'],
            'carbs_g' => $recipe['carbs_g'],
            'fat_g' => $recipe['fat_g'],
            'steps' => implode("\n", $recipe['steps'] ?? []),
        ];
    }

    /** @return array<string,string> field => error message */
    public function validate(array $data): array
    {
        $errors = [];

        $title = (string)($data['title'] ?? '');
        if ($title === '' || mb_strlen($title) > 150) {
            $errors['title'] = 'Enter a recipe title (1-150 characters).';
        }

        $description = (string)($data['description'] ?? '');
        if ($description === '' || mb_strlen($description) > 2000) {
            $errors['description'] = 'Enter a description (1-2000 characters).';
        }

        $categoryId = (int)($data['category_id'] ?? 0);
        if ($categoryId < 1 || !$this->recipes->categoryExists($categoryId)) {
            $errors['category_id'] = 'Choose an existing category.';
        }

        if (!$this->isIntBetween($data['prep_minutes'] ?? null, 1, 600)) {
            $errors['prep_minutes'] = 'Preparation time must be between 1 and 600 minutes.';
        }
        if (!$this->isIntBetween($data['servings'] ?? null, 1, 50)) {
            $errors['servings'] = 'Servings must be between 1 and 50.';
        }
        if (!$this->isIntBetween($data['calories'] ?? null, 0, 5000)) {
            $errors['calories'] = 'Calories per serving must be between 0 and 5000.';
        }
        foreach (['protein_g' => 'Protein', 'carbs_g' => 'Carbohydrates', 'fat_g' => 'Fat'] as $field => $label) {
            $value = $this->toFloat($data[$field] ?? null);
            if ($value === null || $value < 0 || $value > 500) {
                $errors[$field] = $label . ' must be between 0 and 500 g.';
            }
        }

        $steps = $this->parseSteps((string)($data['steps'] ?? ''));
        if ($steps === []) {
            $errors['steps'] = 'Add at least one preparation step (one per line).';
        } elseif (count($steps) > 30) {
            $errors['steps'] = 'A recipe can have at most 30 steps.';
        } else {
            foreach ($steps as $step) {
                if (mb_strlen($step) > 500) {
                    $errors['steps'] = 'Each step must be 500 characters or fewer.';
                    break;
                }
            }
        }

        return $errors;
    }

    /** Typed values ready for RecipeRepository::create/update. */
    public function normalized(array $data): array
    {
        return [
            'title' => (string)$data['title'],
            'description' => (string)$data['description'],
            'category_id' => (int)$data['category_id'],
            'prep_minutes' => (int)$data['prep_minutes'],
            'servings' => (int)$data['servings'],
            'calories' => (int)$data['calories'],
            'protein_g' => round((float)$this->toFloat($data['protein_g']), 1),
            'carbs_g' => round((float)$this->toFloat($data['carbs_g']), 1),
            'fat_g' => round((float)$this->toFloat($data['fat_g']), 1),
        ];
    }

    /** @return string[] one non-empty instruction per line */
    public function parseSteps(string $text): array
    {
        $lines = preg_split('/\R/u', $text) ?: [];
        $steps = [];
        foreach ($lines as $line) {
            $line = trim((string)preg_replace('/^\s*\d+[\.\)]\s*/u', '', $line));
            if ($line !== '') {
                $steps[] = $line;
            }
        }

        return $steps;
    }

    public function validateCategoryName(string $name): ?string
    {
        if ($name === '' || mb_strlen($name) > 60 || preg_match('/[<>]/', $name)) {
            return 'Category name is required (1-60 characters, no < or >).';
        }

        return null;
    }

    /** Admin manages everything; a coach manages only the recipes they authored. */
    public function canManage(User $user, array $recipe): bool
    {
        return $user->getRoleName() === 'admin'
            || ($user->getRoleName() === 'coach' && (int)$recipe['author_id'] === $user->getId());
    }

    private function isIntBetween(mixed $value, int $min, int $max): bool
    {
        $int = filter_var($value, FILTER_VALIDATE_INT);

        return $int !== false && $int >= $min && $int <= $max;
    }

    private function toFloat(mixed $value): ?float
    {
        $normalized = str_replace(',', '.', trim((string)$value));
        $float = filter_var($normalized, FILTER_VALIDATE_FLOAT);

        return $float === false ? null : (float)$float;
    }
}
