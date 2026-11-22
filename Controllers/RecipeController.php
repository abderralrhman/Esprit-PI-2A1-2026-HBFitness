<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../Models/Recipe.php';

class RecipeController {
    public function listRecipes(?int $categoryId = null, string $search = ''): array {
        $db = Database::getConnexion();
        $sql = "
            SELECT r.id, r.category_id, r.title, r.description, r.prep_time, r.cook_time, r.calories, r.created_at,
                   rc.name AS category_name
            FROM recipes r
            INNER JOIN recipe_categories rc ON rc.id = r.category_id
            WHERE 1=1
        ";
        $params = [];

        if ($categoryId !== null && $categoryId > 0) {
            $sql .= " AND r.category_id = :category_id";
            $params['category_id'] = $categoryId;
        }

        if (trim($search) !== '') {
            $sql .= " AND (r.title LIKE :search OR r.description LIKE :search)";
            $params['search'] = '%' . trim($search) . '%';
        }

        $sql .= " ORDER BY r.created_at DESC, r.id DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getRecipeById(int $id): ?array {
        $db = Database::getConnexion();
        $stmt = $db->prepare("
            SELECT r.id, r.category_id, r.title, r.description, r.prep_time, r.cook_time, r.calories, r.created_at,
                   rc.name AS category_name
            FROM recipes r
            INNER JOIN recipe_categories rc ON rc.id = r.category_id
            WHERE r.id = :id
        ");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function addRecipe(Recipe $recipe): bool {
        $db = Database::getConnexion();
        $stmt = $db->prepare("
            INSERT INTO recipes (category_id, title, description, prep_time, cook_time, calories)
            VALUES (:category_id, :title, :description, :prep_time, :cook_time, :calories)
        ");
        return $stmt->execute([
            'category_id' => $recipe->getCategoryId(),
            'title'       => $recipe->getTitle(),
            'description' => $recipe->getDescription(),
            'prep_time'   => $recipe->getPrepTime(),
            'cook_time'   => $recipe->getCookTime(),
            'calories'    => $recipe->getCalories(),
        ]);
    }

    public function updateRecipe(Recipe $recipe, int $id): bool {
        $db = Database::getConnexion();
        $stmt = $db->prepare("
            UPDATE recipes
            SET category_id = :category_id,
                title = :title,
                description = :description,
                prep_time = :prep_time,
                cook_time = :cook_time,
                calories = :calories
            WHERE id = :id
        ");
        return $stmt->execute([
            'id'          => $id,
            'category_id' => $recipe->getCategoryId(),
            'title'       => $recipe->getTitle(),
            'description' => $recipe->getDescription(),
            'prep_time'   => $recipe->getPrepTime(),
            'cook_time'   => $recipe->getCookTime(),
            'calories'    => $recipe->getCalories(),
        ]);
    }

    public function deleteRecipe(int $id): bool {
        $db = Database::getConnexion();
        $stmt = $db->prepare("DELETE FROM recipes WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}