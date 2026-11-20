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
        if (!isset(self::$pdo)) {
            $host = 'localhost';
            $dbname = 'fitness_db';
            $username = 'root';
            $password = '';

            try {
                self::$pdo = new PDO(
                    "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
                    $username,
                    $password,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false
                    ]
                );
                self::ensureRolesAndDemoUsers(self::$pdo);
            } catch (PDOException $e) {
                // Attempt to auto-create database if it doesn't exist
                try {
                    $tempPdo = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password, [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                    ]);
                    $sqlFile = __DIR__ . '/fitness.sql';
                    if (file_exists($sqlFile)) {
                        $sql = file_get_contents($sqlFile);
                        $tempPdo->exec($sql);
                        self::$pdo = new PDO(
                            "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
                            $username,
                            $password,
                            [
                                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                            ]
                        );
                    } else {
                        die('Database connection error: ' . $e->getMessage());
                    }
                } catch (PDOException $ex) {
                    die('Database connection failed: ' . $ex->getMessage());
                }
            }
        }
        return self::$pdo;
    }

    /** Ensures the 3-role ENUM (admin, coach, user) and demo accounts exist even if fitness_db was created earlier */
    private static function ensureRolesAndDemoUsers(PDO $pdo): void {
        try {
            $col = $pdo->query("SHOW COLUMNS FROM `users` LIKE 'role'")->fetch();
            if ($col && strpos((string)$col['Type'], "'coach'") === false) {
                $pdo->exec("ALTER TABLE `users` MODIFY `role` ENUM('admin', 'coach', 'user') NOT NULL DEFAULT 'user'");
            }
            $demoHash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'; // "password"
            $demos = [
                ['Yassine Ben Ali (Admin)', 'admin@fitpulse.tn', 'admin', '+216 22 100 200', '#910329'],
                ['Coach Amira Mansouri', 'coach@fitpulse.tn', 'coach', '+216 55 300 400', '#E8405E'],
                ['Karim Trabelsi', 'user@fitpulse.tn', 'user', '+216 98 765 432', '#B4B4B4'],
            ];
            $check = $pdo->prepare("SELECT id FROM `users` WHERE email = :email LIMIT 1");
            $ins   = $pdo->prepare("INSERT INTO `users` (full_name, email, password, role, phone, avatar_color) VALUES (:full_name, :email, :password, :role, :phone, :avatar_color)");
            foreach ($demos as [$name, $email, $role, $phone, $color]) {
                $check->execute(['email' => $email]);
                if (!$check->fetch()) {
                    $ins->execute([
                        'full_name'    => $name,
                        'email'        => $email,
                        'password'     => $demoHash,
                        'role'         => $role,
                        'phone'        => $phone,
                        'avatar_color' => $color,
                    ]);
                }
            }
        } catch (PDOException $ignored) {
            // Do not block normal requests if table is not yet initialized
        }
    }
}
?>
