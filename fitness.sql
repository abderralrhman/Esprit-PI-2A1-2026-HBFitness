-- FitPulse Fitness Database Schema (MySQL / phpMyAdmin)
CREATE DATABASE IF NOT EXISTS `fitness_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `fitness_db`;

-- --------------------------------------------------------
-- Shared Module: User Entity
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `full_name` VARCHAR(120) NOT NULL,
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('admin', 'coach', 'user') NOT NULL DEFAULT 'user',
    `phone` VARCHAR(30) DEFAULT NULL,
    `avatar_color` VARCHAR(20) DEFAULT '#dc2626',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Module: Articles & Comments (Article + Comment)
-- Join: Comment.article_id = Article.id
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `articles` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(200) NOT NULL,
    `content` TEXT NOT NULL,
    `category` VARCHAR(80) NOT NULL DEFAULT 'Strength Training',
    `tags` VARCHAR(200) DEFAULT 'Fitness,Workout,Health',
    `read_time` INT DEFAULT 5,
    `likes_count` INT DEFAULT 0,
    `views_count` INT DEFAULT 0,
    `status` ENUM('Published', 'Featured', 'Archived') DEFAULT 'Published',
    `user_id` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_article_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `comments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `article_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `content` TEXT NOT NULL,
    `is_coach_pick` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_comment_article` FOREIGN KEY (`article_id`) REFERENCES `articles`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_comment_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Seed Data (demo password for all accounts: "password")
-- --------------------------------------------------------
INSERT INTO `users` (`id`, `full_name`, `email`, `password`, `role`, `phone`, `avatar_color`) VALUES
(1, 'Yassine Ben Ali (Admin)', 'admin@fitpulse.tn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '+216 22 100 200', '#dc2626'),
(2, 'Coach Amira Mansouri', 'coach@fitpulse.tn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'coach', '+216 55 300 400', '#991b1b'),
(3, 'Karim Trabelsi', 'user@fitpulse.tn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', '+216 98 765 432', '#171717')
ON DUPLICATE KEY UPDATE `full_name` = VALUES(`full_name`), `email` = VALUES(`email`), `role` = VALUES(`role`);

INSERT INTO `articles` (`id`, `title`, `content`, `category`, `tags`, `read_time`, `likes_count`, `views_count`, `status`, `user_id`) VALUES
(1, 'Progressive Overload: The Science Behind Building Lean Muscle', 'To stimulate continuous strength and hypertrophy gains, gradually increase training volume, load, or time under tension every 2 to 3 weeks while prioritizing clean compound form on squats, deadlifts, and presses.', 'Strength Training', 'Hypertrophy,Strength,CompoundLifts', 6, 48, 320, 'Featured', 2),
(2, '20-Minute HIIT Protocol for Maximum VO2 Max & Fat Burn', 'Alternate 40 seconds of all-out sprint or kettlebell swing intervals with 20 seconds of active recovery across 4 rounds. Keep heart rate above 85% max during work intervals and cool down with diaphragmatic breathing.', 'Cardio & HIIT', 'HIIT,VO2Max,FatLoss', 4, 35, 245, 'Published', 2),
(3, 'Pre- & Post-Workout Nutrition: Timing Protein and Carbs Right', 'Fueling with 30-40g of complex carbohydrates 90 minutes before training stabilizes energy output, while 25-35g of high-leucine protein within two hours post-session accelerates muscle protein synthesis.', 'Nutrition & Recovery', 'Protein,Macros,Recovery', 5, 62, 410, 'Featured', 1),
(4, '10-Minute Morning Mobility Flow for Runners & Lifters', 'Unlock tight hip flexors, thoracic spine stiffness, and ankle dorsiflexion before every workout with world-greatest stretches, cat-cow waves, and deep squat holds to prevent injury.', 'Yoga & Mobility', 'Mobility,Flexibility,Warmup', 4, 29, 188, 'Published', 3),
(5, 'Half-Marathon Pacing Strategy: Negative Splits Made Simple', 'Start the first 5km 10 seconds per kilometer slower than your goal pace to conserve glycogen stores, settle into race rhythm through 15km, and unleash a controlled kick over the final 6km.', 'Running & Endurance', 'Running,Marathon,Pacing', 7, 41, 275, 'Published', 3)
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`);

INSERT INTO `comments` (`id`, `article_id`, `user_id`, `content`, `is_coach_pick`) VALUES
(1, 1, 3, 'Adding 1.25kg micro-plates each week helped me break through my bench press plateau without compromising form!', 1),
(2, 1, 2, 'Great tip Karim! I also recommend taking one deload week every 6 weeks during a strength block.', 1),
(3, 2, 2, 'Beginners can start with 30s work / 30s rest and build up to 40/20 over four weeks.', 1),
(4, 3, 3, 'Swapping my pre-workout snack to oats + banana completely eliminated my mid-session energy crash.', 0),
(5, 5, 1, 'Solid negative split plan — tested this with our weekend running club and everyone set a new PB!', 1)
ON DUPLICATE KEY UPDATE `content` = VALUES(`content`);
