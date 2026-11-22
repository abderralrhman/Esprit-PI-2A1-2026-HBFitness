-- ============================================================================
-- HB FITNESS — Blog Module Database Schema (Integration-Ready)
-- Assigned Module: Blog Management (Article 1 — N Comment)
-- Repository Target: Esprit-PI-2A1-2026-HBFitness
-- Engine: InnoDB | Charset: utf8mb4
--
-- NON-DESTRUCTIVE MIGRATION INSTRUCTIONS:
-- 1. Select the shared HB FITNESS database in phpMyAdmin (containing the
--    team's existing `users` table with `id INT UNSIGNED PRIMARY KEY`).
-- 2. Import this file. It ONLY creates `articles` and `comments` if they do
--    not already exist; it NEVER drops tables or overwrites `users` data.
-- 3. `recipe_id` is intentionally nullable for future Nutrition-module linkage.
-- ============================================================================

-- ----------------------------------------------------------------------------
-- Entity 1: Article (HB Fitness Validation Deck, Page 11)
-- Foreign Keys:
--   articles.author_id -> users.id (ON DELETE RESTRICT ON UPDATE CASCADE)
--   articles.recipe_id -> nullable (prepared for Nutrition / Recipe module)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `articles` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(200) NOT NULL,
    `content` TEXT NOT NULL,
    `published_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `author_id` INT UNSIGNED NOT NULL,
    `recipe_id` INT UNSIGNED DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_articles_author_id` (`author_id`),
    KEY `idx_articles_published_at` (`published_at`),
    KEY `idx_articles_recipe_id` (`recipe_id`),
    CONSTRAINT `fk_articles_author`
        FOREIGN KEY (`author_id`) REFERENCES `users` (`id`)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Entity 2: Comment (HB Fitness Validation Deck, Page 11)
-- Foreign Keys:
--   comments.article_id -> articles.id (ON DELETE CASCADE ON UPDATE CASCADE)
--   comments.user_id    -> users.id    (ON DELETE CASCADE ON UPDATE CASCADE)
-- Deleting an Article cascades safely to its Comments without touching Users.
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `comments` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `content` TEXT NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `article_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_comments_article_id` (`article_id`),
    KEY `idx_comments_user_id` (`user_id`),
    KEY `idx_comments_created_at` (`created_at`),
    CONSTRAINT `fk_comments_article`
        FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_comments_user`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
