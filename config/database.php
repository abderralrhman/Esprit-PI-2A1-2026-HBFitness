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
}
