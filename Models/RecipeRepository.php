<?php
declare(strict_types=1);

/**
 * Models/RecipeRepository.php
 * Nutrition module data access (recipes, steps, categories).
 * Same pattern as SessionRepository: constructor-injected PDO,
 * prepared statements only, tables created if missing.
 */
final class RecipeRepository
{
    public function __construct(private PDO $pdo)
    {
        $this->ensureTables();
    }

    private function ensureTables(): void
    {
        $this->pdo->exec(
            'CREATE TABLE IF NOT EXISTS recipe_categories (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                name VARCHAR(60) NOT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY uk_recipe_categories_name (name)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
        $this->pdo->exec(
            'CREATE TABLE IF NOT EXISTS recipes (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                title VARCHAR(150) NOT NULL,
                description TEXT NOT NULL,
                category_id INT UNSIGNED NOT NULL,
                author_id INT UNSIGNED NOT NULL,
                prep_minutes SMALLINT UNSIGNED NOT NULL,
                servings TINYINT UNSIGNED NOT NULL DEFAULT 1,
                calories SMALLINT UNSIGNED NOT NULL,
                protein_g DECIMAL(5,1) NOT NULL DEFAULT 0,
                carbs_g DECIMAL(5,1) NOT NULL DEFAULT 0,
                fat_g DECIMAL(5,1) NOT NULL DEFAULT 0,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_recipes_category (category_id),
                KEY idx_recipes_author (author_id),
                CONSTRAINT fk_recipes_category FOREIGN KEY (category_id) REFERENCES recipe_categories(id)
                    ON DELETE RESTRICT ON UPDATE CASCADE,
                CONSTRAINT fk_recipes_author FOREIGN KEY (author_id) REFERENCES users(id)
                    ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
        $this->pdo->exec(
            'CREATE TABLE IF NOT EXISTS recipe_steps (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                recipe_id INT UNSIGNED NOT NULL,
                step_number TINYINT UNSIGNED NOT NULL,
                instruction VARCHAR(500) NOT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY uk_recipe_steps_order (recipe_id, step_number),
                CONSTRAINT fk_recipe_steps_recipe FOREIGN KEY (recipe_id) REFERENCES recipes(id)
                    ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );

        $seed = $this->pdo->prepare('INSERT IGNORE INTO recipe_categories (name) VALUES (?)');
        if ((int)$this->pdo->query('SELECT COUNT(*) FROM recipe_categories')->fetchColumn() === 0) {
            foreach (['Breakfast', 'Lunch', 'Dinner', 'Snacks', 'Smoothies', 'High Protein'] as $name) {
                $seed->execute([$name]);
            }
        }
    }

    // =========================================================================
    // RECIPES
    // =========================================================================

    /** Lists recipes with category and author (SQL JOIN). Optional filters. */
    public function recipes(?int $categoryId = null, ?string $search = null, ?int $authorId = null): array
    {
        $conditions = [];
        $parameters = [];

        if ($categoryId !== null && $categoryId > 0) {
            $conditions[] = 'r.category_id = ?';
            $parameters[] = $categoryId;
        }
        if ($search !== null && $search !== '') {
            $conditions[] = '(r.title LIKE ? OR r.description LIKE ? OR c.name LIKE ?)';
            $term = '%' . $search . '%';
            array_push($parameters, $term, $term, $term);
        }
        if ($authorId !== null) {
            $conditions[] = 'r.author_id = ?';
            $parameters[] = $authorId;
        }

        $sql = 'SELECT r.id, r.title, r.description, r.prep_minutes, r.servings, r.calories,
                       r.protein_g, r.carbs_g, r.fat_g, r.created_at, r.author_id,
                       c.id AS category_id, c.name AS category, u.full_name AS author
                FROM recipes r
                JOIN recipe_categories c ON c.id = r.category_id
                JOIN users u ON u.id = r.author_id';
        if ($conditions !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }
        $sql .= ' ORDER BY r.created_at DESC, r.id DESC';

        $statement = $this->pdo->prepare($sql);
        $statement->execute($parameters);

        return array_map([$this, 'formatRecipe'], $statement->fetchAll());
    }

    /** One recipe with its ordered steps, or null. */
    public function find(int $id): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT r.id, r.title, r.description, r.prep_minutes, r.servings, r.calories,
                    r.protein_g, r.carbs_g, r.fat_g, r.created_at, r.author_id,
                    c.id AS category_id, c.name AS category, u.full_name AS author
             FROM recipes r
             JOIN recipe_categories c ON c.id = r.category_id
             JOIN users u ON u.id = r.author_id
             WHERE r.id = ?'
        );
        $statement->execute([$id]);
        $row = $statement->fetch();
        if ($row === false) {
            return null;
        }

        $recipe = $this->formatRecipe($row);
        $recipe['steps'] = $this->steps($id);

        return $recipe;
    }

    /** @return string[] instructions ordered by step number */
    public function steps(int $recipeId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT instruction FROM recipe_steps WHERE recipe_id = ? ORDER BY step_number'
        );
        $statement->execute([$recipeId]);

        return array_column($statement->fetchAll(), 'instruction');
    }

    /** @param string[] $steps */
    public function create(array $data, array $steps, int $authorId): int
    {
        $this->pdo->beginTransaction();
        try {
            $insert = $this->pdo->prepare(
                'INSERT INTO recipes (title, description, category_id, author_id, prep_minutes,
                                      servings, calories, protein_g, carbs_g, fat_g)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $insert->execute([
                $data['title'], $data['description'], $data['category_id'], $authorId,
                $data['prep_minutes'], $data['servings'], $data['calories'],
                $data['protein_g'], $data['carbs_g'], $data['fat_g'],
            ]);
            $recipeId = (int)$this->pdo->lastInsertId();
            $this->replaceSteps($recipeId, $steps);
            $this->pdo->commit();

            return $recipeId;
        } catch (Throwable $error) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $error;
        }
    }

    /** @param string[] $steps */
    public function update(int $id, array $data, array $steps): bool
    {
        $this->pdo->beginTransaction();
        try {
            $exists = $this->pdo->prepare('SELECT 1 FROM recipes WHERE id = ? FOR UPDATE');
            $exists->execute([$id]);
            if (!$exists->fetchColumn()) {
                $this->pdo->rollBack();
                return false;
            }

            $update = $this->pdo->prepare(
                'UPDATE recipes
                 SET title = ?, description = ?, category_id = ?, prep_minutes = ?, servings = ?,
                     calories = ?, protein_g = ?, carbs_g = ?, fat_g = ?
                 WHERE id = ?'
            );
            $update->execute([
                $data['title'], $data['description'], $data['category_id'], $data['prep_minutes'],
                $data['servings'], $data['calories'], $data['protein_g'], $data['carbs_g'],
                $data['fat_g'], $id,
            ]);
            $this->replaceSteps($id, $steps);
            $this->pdo->commit();

            return true;
        } catch (Throwable $error) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $error;
        }
    }

    public function delete(int $id): bool
    {
        $delete = $this->pdo->prepare('DELETE FROM recipes WHERE id = ?');
        $delete->execute([$id]);

        return $delete->rowCount() === 1;
    }

    /** @param string[] $steps */
    private function replaceSteps(int $recipeId, array $steps): void
    {
        $this->pdo->prepare('DELETE FROM recipe_steps WHERE recipe_id = ?')->execute([$recipeId]);
        $insert = $this->pdo->prepare(
            'INSERT INTO recipe_steps (recipe_id, step_number, instruction) VALUES (?, ?, ?)'
        );
        foreach (array_values($steps) as $index => $instruction) {
            $insert->execute([$recipeId, $index + 1, $instruction]);
        }
    }

    // =========================================================================
    // CATEGORIES
    // =========================================================================

    public function categories(): array
    {
        $rows = $this->pdo->query(
            'SELECT c.id, c.name, COUNT(r.id) AS recipe_count
             FROM recipe_categories c
             LEFT JOIN recipes r ON r.category_id = c.id
             GROUP BY c.id, c.name
             ORDER BY c.name'
        )->fetchAll();

        return array_map(static fn (array $row): array => [
            'id' => (int)$row['id'],
            'name' => $row['name'],
            'count' => (int)$row['recipe_count'],
        ], $rows);
    }

    public function categoryExists(int $id): bool
    {
        $statement = $this->pdo->prepare('SELECT 1 FROM recipe_categories WHERE id = ?');
        $statement->execute([$id]);

        return (bool)$statement->fetchColumn();
    }

    public function createCategory(string $name): int
    {
        $existing = $this->pdo->prepare('SELECT 1 FROM recipe_categories WHERE LOWER(name) = LOWER(?) LIMIT 1');
        $existing->execute([$name]);
        if ($existing->fetchColumn()) {
            throw new DomainException('This category already exists.');
        }

        $insert = $this->pdo->prepare('INSERT INTO recipe_categories (name) VALUES (?)');
        $insert->execute([$name]);

        return (int)$this->pdo->lastInsertId();
    }

    public function updateCategory(int $id, string $name): bool
    {
        $existing = $this->pdo->prepare(
            'SELECT 1 FROM recipe_categories WHERE LOWER(name) = LOWER(?) AND id <> ? LIMIT 1'
        );
        $existing->execute([$name, $id]);
        if ($existing->fetchColumn()) {
            throw new DomainException('This category already exists.');
        }

        $update = $this->pdo->prepare('UPDATE recipe_categories SET name = ? WHERE id = ?');
        $update->execute([$name, $id]);

        return $update->rowCount() > 0 || $this->categoryExists($id);
    }

    public function deleteCategory(int $id): bool
    {
        $count = $this->pdo->prepare('SELECT COUNT(*) FROM recipes WHERE category_id = ?');
        $count->execute([$id]);
        if ((int)$count->fetchColumn() > 0) {
            throw new DomainException('A category that contains recipes cannot be deleted.');
        }

        $delete = $this->pdo->prepare('DELETE FROM recipe_categories WHERE id = ?');
        $delete->execute([$id]);

        return $delete->rowCount() === 1;
    }

    // =========================================================================
    // STATS & FORMATTING
    // =========================================================================

    public function stats(?int $authorId = null): array
    {
        $where = $authorId === null ? '' : ' WHERE author_id = ?';
        $statement = $this->pdo->prepare(
            'SELECT COUNT(*) AS recipe_count, COALESCE(AVG(calories), 0) AS avg_calories
             FROM recipes' . $where
        );
        $statement->execute($authorId === null ? [] : [$authorId]);
        $row = $statement->fetch() ?: ['recipe_count' => 0, 'avg_calories' => 0];

        return [
            'recipes' => (int)$row['recipe_count'],
            'avg_calories' => (int)round((float)$row['avg_calories']),
            'categories' => (int)$this->pdo->query('SELECT COUNT(*) FROM recipe_categories')->fetchColumn(),
        ];
    }

    private function formatRecipe(array $row): array
    {
        return [
            'id' => (int)$row['id'],
            'title' => $row['title'],
            'description' => $row['description'],
            'category_id' => (int)$row['category_id'],
            'category' => $row['category'],
            'author_id' => (int)$row['author_id'],
            'author' => $row['author'],
            'prep_minutes' => (int)$row['prep_minutes'],
            'servings' => (int)$row['servings'],
            'calories' => (int)$row['calories'],
            'protein_g' => (float)$row['protein_g'],
            'carbs_g' => (float)$row['carbs_g'],
            'fat_g' => (float)$row['fat_g'],
            'date' => date('Y-m-d', strtotime((string)$row['created_at'])),
        ];
    }
}
