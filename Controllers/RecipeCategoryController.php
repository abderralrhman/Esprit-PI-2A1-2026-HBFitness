<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../Models/RecipeCategory.php';

class RecipeCategoryController {
    public function listCategories(): array {
        $db = Config::getConnexion();
        $sql = "
            SELECT rc.id, rc.name, COUNT(r.id) AS recipes_count
            FROM recipe_categories rc
            LEFT JOIN recipes r ON r.category_id = rc.id
            GROUP BY rc.id, rc.name
            ORDER BY rc.name ASC
        ";
        return $db->query($sql)->fetchAll();
    }

    public function getCategoryById(int $id): ?array {
        $db = Config::getConnexion();
        $stmt = $db->prepare("SELECT id, name FROM recipe_categories WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function addCategory(RecipeCategory $category): bool {
        $db = Config::getConnexion();
        $stmt = $db->prepare("INSERT INTO recipe_categories (name) VALUES (:name)");
        return $stmt->execute(['name' => $category->getName()]);
    }

    public function updateCategory(RecipeCategory $category, int $id): bool {
        $db = Config::getConnexion();
        $stmt = $db->prepare("UPDATE recipe_categories SET name = :name WHERE id = :id");
        return $stmt->execute([
            'name' => $category->getName(),
            'id'   => $id
        ]);
    }

    public function deleteCategory(int $id): bool {
        $db = Config::getConnexion();
        $stmt = $db->prepare("DELETE FROM recipe_categories WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}