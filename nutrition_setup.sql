-- ============================================================================
-- HB FITNESS - Nutrition & Healthy Recipes schema
-- Import AFTER hbfitess.sql (it references users.id).
-- Engine: InnoDB | Charset: utf8mb4
-- ============================================================================

USE `hbfitess_db`;

-- ----------------------------------------------------------------------------
-- Table: recipe_categories (1 Category -> Many Recipes)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `recipe_categories` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(60) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_recipe_categories_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: recipes (FK -> recipe_categories.id, FK -> users.id)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `recipes` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(150) NOT NULL,
    `description` TEXT NOT NULL,
    `category_id` INT UNSIGNED NOT NULL,
    `author_id` INT UNSIGNED NOT NULL,
    `prep_minutes` SMALLINT UNSIGNED NOT NULL,
    `servings` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `calories` SMALLINT UNSIGNED NOT NULL,
    `protein_g` DECIMAL(5,1) NOT NULL DEFAULT 0,
    `carbs_g` DECIMAL(5,1) NOT NULL DEFAULT 0,
    `fat_g` DECIMAL(5,1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_recipes_category` (`category_id`),
    KEY `idx_recipes_author` (`author_id`),
    CONSTRAINT `fk_recipes_category`
        FOREIGN KEY (`category_id`) REFERENCES `recipe_categories` (`id`)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_recipes_author`
        FOREIGN KEY (`author_id`) REFERENCES `users` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: recipe_steps (FK -> recipes.id)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `recipe_steps` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `recipe_id` INT UNSIGNED NOT NULL,
    `step_number` TINYINT UNSIGNED NOT NULL,
    `instruction` VARCHAR(500) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_recipe_steps_order` (`recipe_id`, `step_number`),
    CONSTRAINT `fk_recipe_steps_recipe`
        FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Reference data: starter categories
-- ----------------------------------------------------------------------------
INSERT IGNORE INTO `recipe_categories` (`name`) VALUES
    ('Breakfast'),
    ('Lunch'),
    ('Dinner'),
    ('Snacks'),
    ('Smoothies'),
    ('High Protein');
