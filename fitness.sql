CREATE TABLE IF NOT EXISTS recipe_categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS recipes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id INT UNSIGNED NOT NULL,
    title VARCHAR(150) NOT NULL,
    description TEXT NULL,
    prep_time INT UNSIGNED NOT NULL DEFAULT 0,
    cook_time INT UNSIGNED NOT NULL DEFAULT 0,
    calories INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_recipes_category
        FOREIGN KEY (category_id) REFERENCES recipe_categories(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS recipe_steps (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    recipe_id INT UNSIGNED NOT NULL,
    step_number INT UNSIGNED NOT NULL,
    instruction TEXT NOT NULL,
    CONSTRAINT fk_recipe_steps_recipe
        FOREIGN KEY (recipe_id) REFERENCES recipes(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO recipe_categories (id, name) VALUES
    (1, 'High Protein'),
    (2, 'Balanced Bowls'),
    (3, 'Quick Snacks');