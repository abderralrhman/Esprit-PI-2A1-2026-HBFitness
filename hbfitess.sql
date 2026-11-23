-- ============================================================================
-- HB FITNESS — User Management Database Schema (Esprit 2026-2027)
-- Importable directly via phpMyAdmin
-- Engine: InnoDB | Charset: utf8mb4
-- ============================================================================

CREATE DATABASE IF NOT EXISTS `hbfitess_db`
    DEFAULT CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `hbfitess_db`;

-- ----------------------------------------------------------------------------
-- Table: roles (1 Role -> Many Users)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `roles` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(50) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_roles_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: user_statuses (1 UserStatus -> Many Users)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `user_statuses` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(50) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_user_statuses_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: users (FK -> roles.id, FK -> user_statuses.id)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `full_name` VARCHAR(120) NOT NULL,
    `email` VARCHAR(150) NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(30) DEFAULT NULL,
    `role_id` INT UNSIGNED NOT NULL,
    `status_id` INT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_users_email` (`email`),
    KEY `idx_users_role_id` (`role_id`),
    KEY `idx_users_status_id` (`status_id`),
    CONSTRAINT `fk_users_role`
        FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_users_status`
        FOREIGN KEY (`status_id`) REFERENCES `user_statuses` (`id`)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: password_reset_tokens (one-time, expiring password reset links)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `token_hash` CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    `expires_at` DATETIME NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_password_reset_token_hash` (`token_hash`),
    KEY `idx_password_reset_user_id` (`user_id`),
    KEY `idx_password_reset_expires_at` (`expires_at`),
    CONSTRAINT `fk_password_reset_user`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Reference Data: Required Roles & Account Statuses
-- ----------------------------------------------------------------------------
INSERT INTO `roles` (`id`, `name`) VALUES
    (1, 'admin'),
    (2, 'user'),
    (3, 'coach')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

INSERT INTO `user_statuses` (`id`, `name`) VALUES
    (1, 'active'),
    (2, 'suspended')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- ----------------------------------------------------------------------------
-- Table: training_session (FK -> users.id)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `training_session` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(120) NOT NULL,
    `mode` ENUM('in-person', 'online') NOT NULL DEFAULT 'in-person',
    `start_at` DATETIME NOT NULL,
    `duration_min` INT UNSIGNED NOT NULL,
    `max_participants` INT UNSIGNED NOT NULL,
    `location` VARCHAR(150) NOT NULL,
    `coach_id` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_training_session_start` (`start_at`),
    KEY `idx_training_session_coach` (`coach_id`),
    CONSTRAINT `fk_training_session_coach`
        FOREIGN KEY (`coach_id`) REFERENCES `users` (`id`)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: booking (FK -> training_session.id, FK -> users.id)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `booking` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `session_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `status` ENUM('confirmed', 'cancelled', 'completed') NOT NULL DEFAULT 'confirmed',
    `booked_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_booking_session_user` (`session_id`, `user_id`),
    KEY `idx_booking_user` (`user_id`),
    KEY `idx_booking_status` (`status`),
    CONSTRAINT `fk_booking_session`
        FOREIGN KEY (`session_id`) REFERENCES `training_session` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_booking_user`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Note on Demo Accounts:
-- To avoid committing plaintext credentials, create your local Admin / Coach /
-- User demo accounts via the built-in local setup form at:
--   http://localhost/HBfitess/index.php?action=setup
-- (Available only on localhost when no administrator account exists yet.)
-- ----------------------------------------------------------------------------
