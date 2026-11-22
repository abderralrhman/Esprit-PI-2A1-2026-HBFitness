-- ============================================================================
-- HB FITNESS — Blog Model Standalone Database Schema (Esprit 2026-2027)
-- Module 1: Blog Management (Article 1 — N Comment)
-- Importable directly via phpMyAdmin
-- Engine: InnoDB | Charset: utf8mb4
-- ============================================================================

CREATE DATABASE IF NOT EXISTS `hbfitess_blog_db`
    DEFAULT CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `hbfitess_blog_db`;

-- ----------------------------------------------------------------------------
-- Minimal Standalone Users Table (Supporting infrastructure ONLY)
-- IMPORTANT FOR FUTURE INTEGRATION:
-- Do NOT import this table over the team's shared User Management `users` table.
-- Supports the 3 HB Fitness roles: admin, user, coach.
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `full_name` VARCHAR(120) NOT NULL,
    `email` VARCHAR(150) NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `role` ENUM('admin', 'user', 'coach') NOT NULL DEFAULT 'user',
    `status` ENUM('active', 'suspended') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_users_email` (`email`),
    KEY `idx_users_role` (`role`),
    KEY `idx_users_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Entity 1: Article (HB Fitness Validation Deck, Page 11)
-- Relationships:
--   User 1 — N Article (articles.author_id -> users.id)
--   Article 1 — N Comment (comments.article_id -> articles.id)
--   recipe_id is nullable (Recipe module belongs to another team member)
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
-- Relationships:
--   Article 1 — N Comment (comments.article_id -> articles.id ON DELETE CASCADE)
--   User 1 — N Comment (comments.user_id -> users.id ON DELETE CASCADE)
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

-- ----------------------------------------------------------------------------
-- NOTE ON INITIAL LOCAL ACCOUNTS:
-- No plaintext passwords are stored in this SQL file.
-- See README.md ("Authentication Setup") for the exact PHP CLI password_hash()
-- and phpMyAdmin INSERT commands to create your local admin, coach, and user accounts.
-- ----------------------------------------------------------------------------
