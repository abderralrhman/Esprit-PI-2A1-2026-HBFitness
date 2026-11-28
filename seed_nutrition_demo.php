<?php
declare(strict_types=1);

/**
 * seed_nutrition_demo.php
 * Adds demo recipes (with categories and steps) for the Nutrition module.
 *
 * - Local use only (localhost or command line).
 * - Safe to run several times: a recipe whose title already exists is skipped.
 * - Nutrition values are approximate and meant for demonstration only.
 *
 * Run from the browser:  http://localhost/<project-folder>/seed_nutrition_demo.php
 * or from a terminal:    C:\xampp\php\php.exe seed_nutrition_demo.php
 */

if (PHP_SAPI !== 'cli' && !in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true)) {
    http_response_code(403);
    exit('This seeder can only be run locally.');
}

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/Models/RecipeRepository.php';

if (PHP_SAPI !== 'cli') {
    header('Content-Type: text/plain; charset=utf-8');
}

$pdo        = Database::getConnexion();
$repository = new RecipeRepository($pdo);

// The recipes need an author: prefer an active admin, otherwise an active coach.
$authorId = $pdo->query(
    "SELECT u.id
     FROM users u
     JOIN roles r ON r.id = u.role_id
     WHERE r.name IN ('admin', 'coach') AND u.status_id = 1
     ORDER BY (r.name = 'admin') DESC, u.id
     LIMIT 1"
)->fetchColumn();

if ($authorId === false) {
    exit("No active admin or coach account found.\nCreate one first at index.php?action=setup, then run this script again.\n");
}

// name => id of the existing categories
$categoryIds = [];
foreach ($repository->categories() as $category) {
    $categoryIds[$category['name']] = $category['id'];
}

$recipes = [
    [
        'title' => 'Greek yogurt oat bowl',
        'category' => 'Breakfast',
        'description' => 'A quick, high-protein breakfast that keeps you full until lunch.',
        'prep_minutes' => 5, 'servings' => 1, 'calories' => 320,
        'protein_g' => 24, 'carbs_g' => 38, 'fat_g' => 7,
        'steps' => [
            'Pour the Greek yogurt into a bowl.',
            'Stir in the rolled oats and a teaspoon of honey.',
            'Top with sliced banana and a few berries.',
        ],
    ],
    [
        'title' => 'Veggie omelette',
        'category' => 'Breakfast',
        'description' => 'Fluffy eggs packed with vegetables, ready in ten minutes.',
        'prep_minutes' => 10, 'servings' => 1, 'calories' => 290,
        'protein_g' => 21, 'carbs_g' => 6, 'fat_g' => 20,
        'steps' => [
            'Whisk two eggs with a pinch of salt and pepper.',
            'Sauté diced pepper, spinach and tomato in a little olive oil for 3 minutes.',
            'Pour the eggs over the vegetables and cook on medium heat until set.',
            'Fold the omelette in half and serve hot.',
        ],
    ],
    [
        'title' => 'Grilled chicken quinoa bowl',
        'category' => 'Lunch',
        'description' => 'Lean chicken, quinoa and fresh vegetables in one balanced bowl.',
        'prep_minutes' => 25, 'servings' => 2, 'calories' => 520,
        'protein_g' => 42, 'carbs_g' => 48, 'fat_g' => 16,
        'steps' => [
            'Cook the quinoa in salted water for 15 minutes, then drain.',
            'Season the chicken breast with paprika, salt and pepper.',
            'Grill the chicken for 6 minutes per side, then slice it.',
            'Assemble the bowls with quinoa, chicken, cucumber and cherry tomatoes.',
            'Drizzle with olive oil and lemon juice before serving.',
        ],
    ],
    [
        'title' => 'Baked salmon with sweet potato',
        'category' => 'Dinner',
        'description' => 'Omega-3 rich salmon served with oven-roasted sweet potato.',
        'prep_minutes' => 35, 'servings' => 2, 'calories' => 560,
        'protein_g' => 38, 'carbs_g' => 42, 'fat_g' => 24,
        'steps' => [
            'Preheat the oven to 200°C.',
            'Cut the sweet potatoes into cubes and roast them with olive oil for 20 minutes.',
            'Place the salmon fillets on a tray with lemon slices, salt and pepper.',
            'Bake the salmon for 12 to 15 minutes.',
            'Serve the salmon on top of the roasted sweet potatoes.',
        ],
    ],
    [
        'title' => 'Lentil vegetable soup',
        'category' => 'Dinner',
        'description' => 'A warming, fibre-rich soup that is cheap and easy to batch cook.',
        'prep_minutes' => 40, 'servings' => 4, 'calories' => 310,
        'protein_g' => 18, 'carbs_g' => 48, 'fat_g' => 5,
        'steps' => [
            'Sauté chopped onion, carrot and celery in a pot for 5 minutes.',
            'Add the rinsed lentils, cumin and one litre of vegetable stock.',
            'Simmer for 25 minutes until the lentils are soft.',
            'Season with salt, pepper and lemon juice, then serve.',
        ],
    ],
    [
        'title' => 'Banana peanut butter smoothie',
        'category' => 'Smoothies',
        'description' => 'A creamy post-workout smoothie with protein and slow carbs.',
        'prep_minutes' => 5, 'servings' => 1, 'calories' => 350,
        'protein_g' => 20, 'carbs_g' => 46, 'fat_g' => 10,
        'steps' => [
            'Add a frozen banana, a spoon of peanut butter and 250 ml of milk to a blender.',
            'Add a scoop of whey or a few spoons of Greek yogurt.',
            'Blend until smooth and drink right away.',
        ],
    ],
    [
        'title' => 'Energy oat bites',
        'category' => 'Snacks',
        'description' => 'No-bake bites to keep in the fridge for a quick snack before training.',
        'prep_minutes' => 15, 'servings' => 8, 'calories' => 140,
        'protein_g' => 5, 'carbs_g' => 18, 'fat_g' => 6,
        'steps' => [
            'Mix oats, peanut butter, honey and chopped dates in a bowl.',
            'Roll the mixture into small balls.',
            'Chill in the fridge for 30 minutes before eating.',
        ],
    ],
    [
        'title' => 'Turkey meatballs with tomato sauce',
        'category' => 'High Protein',
        'description' => 'Tender baked meatballs with a simple tomato sauce and plenty of protein.',
        'prep_minutes' => 30, 'servings' => 4, 'calories' => 410,
        'protein_g' => 44, 'carbs_g' => 14, 'fat_g' => 20,
        'steps' => [
            'Mix minced turkey with an egg, breadcrumbs, garlic, salt and pepper.',
            'Shape the mixture into 16 small balls.',
            'Bake the meatballs at 200°C for 18 minutes.',
            'Simmer crushed tomatoes with garlic and basil for 10 minutes.',
            'Add the meatballs to the sauce and serve hot.',
        ],
    ],
];

$exists = $pdo->prepare('SELECT 1 FROM recipes WHERE title = ? LIMIT 1');
$added  = 0;
$skipped = 0;

foreach ($recipes as $recipe) {
    $exists->execute([$recipe['title']]);
    if ($exists->fetchColumn()) {
        echo "Skipped (already exists): {$recipe['title']}\n";
        $skipped++;
        continue;
    }

    if (!isset($categoryIds[$recipe['category']])) {
        $categoryIds[$recipe['category']] = $repository->createCategory($recipe['category']);
    }

    $repository->create(
        [
            'title' => $recipe['title'],
            'description' => $recipe['description'],
            'category_id' => $categoryIds[$recipe['category']],
            'prep_minutes' => $recipe['prep_minutes'],
            'servings' => $recipe['servings'],
            'calories' => $recipe['calories'],
            'protein_g' => (float)$recipe['protein_g'],
            'carbs_g' => (float)$recipe['carbs_g'],
            'fat_g' => (float)$recipe['fat_g'],
        ],
        $recipe['steps'],
        (int)$authorId
    );
    echo "Added: {$recipe['title']}\n";
    $added++;
}

echo "\nDone. {$added} added, {$skipped} skipped.\n";
