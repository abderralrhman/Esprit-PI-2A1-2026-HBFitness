SET NAMES utf8mb4;
USE `hbfitess_db`;

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
