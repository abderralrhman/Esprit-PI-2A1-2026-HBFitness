<?php
// Application base URL (e.g. "/fitness"), computed from the document root so redirects work from any folder depth
if (!defined('BASE_URL')) {
    $docRoot = isset($_SERVER['DOCUMENT_ROOT']) ? str_replace('\\', '/', (string)(realpath($_SERVER['DOCUMENT_ROOT']) ?: $_SERVER['DOCUMENT_ROOT'])) : '';
    $appDir  = str_replace('\\', '/', __DIR__);
    $base    = '/fitness';
    if ($docRoot !== '' && stripos($appDir, rtrim($docRoot, '/')) === 0) {
        $base = substr($appDir, strlen(rtrim($docRoot, '/')));
    }
    define('BASE_URL', rtrim($base, '/'));
}

class Config {
    private static $pdo = null;

    public static function getConnexion() {
        if (self::$pdo !== null) {
            return self::$pdo;
        }

        $host = 'localhost';
        $dbname = 'fitness_db';
        $username = 'root';
        $password = '';
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            $server = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password, $options);
            $server->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, $options);
            self::createTables($pdo);
            self::ensureRolesAndDemoUsers($pdo);
            self::$pdo = $pdo;
        } catch (PDOException $e) {
            self::$pdo = null;
            throw new RuntimeException(
                "Unable to connect to or initialize the MySQL database '$dbname': " . $e->getMessage(),
                0,
                $e
            );
        }

        return self::$pdo;
    }

    private static function createTables(PDO $pdo): void {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `users` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `full_name` VARCHAR(120) NOT NULL,
                `email` VARCHAR(150) NOT NULL UNIQUE,
                `password` VARCHAR(255) NOT NULL,
                `role` ENUM('admin', 'coach', 'user') NOT NULL DEFAULT 'user',
                `phone` VARCHAR(30) DEFAULT NULL,
                `avatar_color` VARCHAR(20) DEFAULT '#dc2626',
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        $pdo->exec("
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
                CONSTRAINT `fk_article_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
                    ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `comments` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `article_id` INT NOT NULL,
                `user_id` INT NOT NULL,
                `content` TEXT NOT NULL,
                `is_coach_pick` TINYINT(1) DEFAULT 0,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT `fk_comment_article` FOREIGN KEY (`article_id`) REFERENCES `articles`(`id`)
                    ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `fk_comment_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
                    ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `workout_plans` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `title` VARCHAR(150) NOT NULL,
                `level` ENUM('Beginner', 'Intermediate', 'Advanced') NOT NULL,
                `goal` VARCHAR(80) NOT NULL,
                `duration_weeks` TINYINT UNSIGNED NOT NULL,
                `created_by` INT NOT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT `fk_workout_plan_creator` FOREIGN KEY (`created_by`) REFERENCES `users`(`id`)
                    ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `exercises` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `name` VARCHAR(150) NOT NULL,
                `muscle_group` VARCHAR(80) NOT NULL,
                `sets` TINYINT UNSIGNED NOT NULL,
                `reps` VARCHAR(40) NOT NULL,
                `rest_seconds` SMALLINT UNSIGNED NOT NULL DEFAULT 60,
                `video_url` VARCHAR(500) DEFAULT NULL,
                `plan_id` INT NOT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT `fk_exercise_plan` FOREIGN KEY (`plan_id`) REFERENCES `workout_plans`(`id`)
                    ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    }

    /** Ensures the 3-role ENUM (admin, coach, user) and demo accounts exist. */
    private static function ensureRolesAndDemoUsers(PDO $pdo): void {
        $col = $pdo->query("SHOW COLUMNS FROM `users` LIKE 'role'")->fetch();
        if ($col && strpos((string)$col['Type'], "'coach'") === false) {
            $pdo->exec("ALTER TABLE `users` MODIFY `role` ENUM('admin', 'coach', 'user') NOT NULL DEFAULT 'user'");
        }
        $demoHash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
        $demos = [
            ['Yassine Ben Ali (Admin)', 'admin@fitpulse.tn', 'admin', '+216 22 100 200', '#910329'],
            ['Coach Amira Mansouri', 'coach@fitpulse.tn', 'coach', '+216 55 300 400', '#E8405E'],
            ['Karim Trabelsi', 'user@fitpulse.tn', 'user', '+216 98 765 432', '#B4B4B4'],
        ];
        $check = $pdo->prepare("SELECT id FROM `users` WHERE email = :email LIMIT 1");
        $insert = $pdo->prepare("
            INSERT INTO `users` (full_name, email, password, role, phone, avatar_color)
            VALUES (:full_name, :email, :password, :role, :phone, :avatar_color)
        ");
        foreach ($demos as [$name, $email, $role, $phone, $color]) {
            $check->execute(['email' => $email]);
            if (!$check->fetch()) {
                $insert->execute([
                    'full_name' => $name,
                    'email' => $email,
                    'password' => $demoHash,
                    'role' => $role,
                    'phone' => $phone,
                    'avatar_color' => $color,
                ]);
            }
        }
    }
}
?>
