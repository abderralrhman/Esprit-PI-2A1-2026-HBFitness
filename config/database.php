<?php
declare(strict_types=1);

/**
 * config/database.php
 * Centralized Singleton PDO Database Connection (ESPRIT MVC Workshop)
 */

if (!defined('BASE_URL')) {
    $docRoot = isset($_SERVER['DOCUMENT_ROOT'])
        ? str_replace('\\', '/', (string)(realpath($_SERVER['DOCUMENT_ROOT']) ?: $_SERVER['DOCUMENT_ROOT']))
        : '';
    $appDir = str_replace('\\', '/', dirname(__DIR__));
    $base   = '/HBfitess';
    if ($docRoot !== '' && stripos($appDir, rtrim($docRoot, '/')) === 0) {
        $base = substr($appDir, strlen(rtrim($docRoot, '/')));
    }
    define('BASE_URL', rtrim($base, '/'));
}

class Database
{
    private static ?PDO $pdo = null;

    private function __construct() {}
    private function __clone() {}

    /**
     * Returns the shared PDO connection (Singleton pattern).
     */
    public static function getConnexion(): PDO
    {
        if (self::$pdo === null) {
            $host     = getenv('HBFITESS_DB_HOST') ?: 'localhost';
            $dbname   = getenv('HBFITESS_DB_NAME') ?: 'hbfitess_db';
            $username = getenv('HBFITESS_DB_USER') ?: 'root';
            $password = getenv('HBFITESS_DB_PASS') !== false ? (string)getenv('HBFITESS_DB_PASS') : '';

            $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$pdo = new PDO($dsn, $username, $password, $options);
            } catch (PDOException $e) {
                // If database does not exist yet (MySQL 1049), auto-initialize from root hbfitess.sql
                $sqlFile = dirname(__DIR__) . '/hbfitess.sql';
                if (file_exists($sqlFile)) {
                    try {
                        $bootstrapPdo = new PDO(
                            "mysql:host={$host};charset=utf8mb4",
                            $username,
                            $password,
                            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                        );
                        $sql = (string)file_get_contents($sqlFile);
                        $bootstrapPdo->exec($sql);
                        self::$pdo = new PDO($dsn, $username, $password, $options);
                        $blogSqlFile = dirname(__DIR__) . '/blog.sql';
                        if (file_exists($blogSqlFile)) {
                            self::$pdo->exec((string)file_get_contents($blogSqlFile));
                        }
                        return self::$pdo;
                    } catch (PDOException $ex) {
                        error_log('[HBfitess DB Bootstrap Error] ' . $ex->getMessage());
                    }
                }
                error_log('[HBfitess DB Error] ' . $e->getMessage());
                http_response_code(500);
                exit(
                    'Database connection unavailable. Please verify that MySQL is running in XAMPP '
                    . 'and that hbfitess.sql has been imported into phpMyAdmin.'
                );
            }
        }

        return self::$pdo;
    }

    /** Alias matching English workshop naming */
    public static function getConnection(): PDO
    {
        return self::getConnexion();
    }

    public static function ensureWorkoutTables(PDO $pdo): void
    {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `workout_plans` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `title` VARCHAR(150) NOT NULL,
                `level` ENUM('Beginner', 'Intermediate', 'Advanced') NOT NULL,
                `goal` VARCHAR(80) NOT NULL,
                `duration_weeks` TINYINT UNSIGNED NOT NULL,
                `created_by` INT UNSIGNED NOT NULL,
                `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `idx_workout_plans_level_goal` (`level`, `goal`),
                CONSTRAINT `fk_workout_plan_creator`
                    FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
                    ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `exercises` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `name` VARCHAR(150) NOT NULL,
                `muscle_group` VARCHAR(80) NOT NULL,
                `sets` TINYINT UNSIGNED NOT NULL,
                `reps` VARCHAR(40) NOT NULL,
                `rest_seconds` SMALLINT UNSIGNED NOT NULL DEFAULT 60,
                `video_url` VARCHAR(500) DEFAULT NULL,
                `plan_id` INT UNSIGNED NOT NULL,
                `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `idx_exercises_plan_id` (`plan_id`),
                CONSTRAINT `fk_exercise_plan`
                    FOREIGN KEY (`plan_id`) REFERENCES `workout_plans` (`id`)
                    ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }
}
