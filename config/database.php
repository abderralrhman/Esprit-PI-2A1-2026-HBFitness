<?php
declare(strict_types=1);

/**
 * config/database.php
 * Centralized Singleton PDO Database Connection for HB FITNESS Blog Model
 * Standalone DB: hbfitess_blog_db
 */

if (!defined('BASE_URL')) {
    $docRoot = isset($_SERVER['DOCUMENT_ROOT'])
        ? str_replace('\\', '/', (string)(realpath($_SERVER['DOCUMENT_ROOT']) ?: $_SERVER['DOCUMENT_ROOT']))
        : '';
    $appDir = str_replace('\\', '/', dirname(__DIR__));
    $base   = '/Blog-Model';
    if ($docRoot !== '' && stripos($appDir, rtrim($docRoot, '/')) === 0) {
        $rel  = substr($appDir, strlen(rtrim($docRoot, '/')));
        $base = str_replace(' ', '%20', $rel);
    }
    define('BASE_URL', rtrim($base, '/'));
}

class Database
{
    private static ?PDO $pdo = null;

    private function __construct() {}
    private function __clone() {}

    public static function getConnexion(): PDO
    {
        if (self::$pdo === null) {
            $host     = getenv('HBFITESS_BLOG_DB_HOST') ?: 'localhost';
            $dbname   = getenv('HBFITESS_BLOG_DB_NAME') ?: 'hbfitess_blog_db';
            $username = getenv('HBFITESS_BLOG_DB_USER') ?: 'root';
            $password = getenv('HBFITESS_BLOG_DB_PASS') !== false ? (string)getenv('HBFITESS_BLOG_DB_PASS') : '';

            $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$pdo = new PDO($dsn, $username, $password, $options);
            } catch (PDOException $e) {
                // Auto-import blog.sql if database does not exist yet on local XAMPP
                $sqlFile = dirname(__DIR__) . '/blog.sql';
                if (file_exists($sqlFile)) {
                    try {
                        $bootstrap = new PDO(
                            "mysql:host={$host};charset=utf8mb4",
                            $username,
                            $password,
                            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                        );
                        $bootstrap->exec((string)file_get_contents($sqlFile));
                        self::$pdo = new PDO($dsn, $username, $password, $options);
                        return self::$pdo;
                    } catch (PDOException $ex) {
                        error_log('[Blog Model DB Bootstrap Error] ' . $ex->getMessage());
                    }
                }
                error_log('[Blog Model DB Error] ' . $e->getMessage());
                http_response_code(500);
                exit(
                    'Database connection unavailable. Please make sure MySQL is running in XAMPP '
                    . 'and that blog.sql has been imported into phpMyAdmin.'
                );
            }
        }

        return self::$pdo;
    }

    public static function getConnection(): PDO
    {
        return self::getConnexion();
    }
}
