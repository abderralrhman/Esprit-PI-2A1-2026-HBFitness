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

            $pdo = new PDO(
                "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
                $username,
                $password,
                $options
            );
            self::createTables($pdo);
            self::ensureRolesAndDemoUsers($pdo);
            self::seedDemoArticlesAndComments($pdo);
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

    private static function seedDemoArticlesAndComments(PDO $pdo): void {
        $articles = [
            [
                'Progressive Overload: The Science Behind Building Lean Muscle',
                'To stimulate continuous strength and hypertrophy gains, gradually increase training volume, load, or time under tension every 2 to 3 weeks while prioritizing clean compound form on squats, deadlifts, and presses.',
                'Strength Training', 'Hypertrophy,Strength,CompoundLifts', 6, 48, 320, 'Featured', 'coach@fitpulse.tn',
            ],
            [
                '20-Minute HIIT Protocol for Maximum VO2 Max & Fat Burn',
                'Alternate 40 seconds of all-out sprint or kettlebell swing intervals with 20 seconds of active recovery across 4 rounds. Keep heart rate above 85% max during work intervals and cool down with diaphragmatic breathing.',
                'Cardio & HIIT', 'HIIT,VO2Max,FatLoss', 4, 35, 245, 'Published', 'coach@fitpulse.tn',
            ],
            [
                'Pre- & Post-Workout Nutrition: Timing Protein and Carbs Right',
                'Fueling with 30-40g of complex carbohydrates 90 minutes before training stabilizes energy output, while 25-35g of high-leucine protein within two hours post-session accelerates muscle protein synthesis.',
                'Nutrition & Recovery', 'Protein,Macros,Recovery', 5, 62, 410, 'Featured', 'admin@fitpulse.tn',
            ],
            [
                '10-Minute Morning Mobility Flow for Runners & Lifters',
                'Unlock tight hip flexors, thoracic spine stiffness, and ankle dorsiflexion before every workout with world-greatest stretches, cat-cow waves, and deep squat holds to prevent injury.',
                'Yoga & Mobility', 'Mobility,Flexibility,Warmup', 4, 29, 188, 'Published', 'user@fitpulse.tn',
            ],
            [
                'Half-Marathon Pacing Strategy: Negative Splits Made Simple',
                'Start the first 5km 10 seconds per kilometer slower than your goal pace to conserve glycogen stores, settle into race rhythm through 15km, and unleash a controlled kick over the final 6km.',
                'Running & Endurance', 'Running,Marathon,Pacing', 7, 41, 275, 'Published', 'user@fitpulse.tn',
            ],
        ];
        $findUser = $pdo->prepare("SELECT id FROM `users` WHERE email = :email LIMIT 1");
        $findArticle = $pdo->prepare("SELECT id FROM `articles` WHERE title = :title LIMIT 1");
        $insertArticle = $pdo->prepare("
            INSERT INTO `articles`
                (title, content, category, tags, read_time, likes_count, views_count, status, user_id)
            VALUES
                (:title, :content, :category, :tags, :read_time, :likes_count, :views_count, :status, :user_id)
        ");
        foreach ($articles as [$title, $content, $category, $tags, $readTime, $likes, $views, $status, $email]) {
            $findArticle->execute(['title' => $title]);
            if (!$findArticle->fetch()) {
                $findUser->execute(['email' => $email]);
                $userId = $findUser->fetchColumn();
                if ($userId === false) {
                    throw new RuntimeException("Unable to seed article '$title': demo author '$email' was not found.");
                }
                $insertArticle->execute([
                    'title' => $title,
                    'content' => $content,
                    'category' => $category,
                    'tags' => $tags,
                    'read_time' => $readTime,
                    'likes_count' => $likes,
                    'views_count' => $views,
                    'status' => $status,
                    'user_id' => $userId,
                ]);
            }
        }

        $comments = [
            [1, 'karim', 'Adding 1.25kg micro-plates each week helped me break through my bench press plateau without compromising form!', 1],
            [1, 'coach', 'Great tip Karim! I also recommend taking one deload week every 6 weeks during a strength block.', 1],
            [2, 'coach', 'Beginners can start with 30s work / 30s rest and build up to 40/20 over four weeks.', 1],
            [3, 'karim', 'Swapping my pre-workout snack to oats + banana completely eliminated my mid-session energy crash.', 0],
            [5, 'admin', 'Solid negative split plan — tested this with our weekend running club and everyone set a new PB!', 1],
        ];
        $articleTitles = array_column($articles, 0);
        $findComment = $pdo->prepare("
            SELECT id FROM `comments`
            WHERE article_id = :article_id AND user_id = :user_id AND content = :content
            LIMIT 1
        ");
        $insertComment = $pdo->prepare("
            INSERT INTO `comments` (article_id, user_id, content, is_coach_pick)
            VALUES (:article_id, :user_id, :content, :is_coach_pick)
        ");
        $demoEmails = [
            'admin' => 'admin@fitpulse.tn',
            'coach' => 'coach@fitpulse.tn',
            'karim' => 'user@fitpulse.tn',
        ];
        foreach ($comments as [$articleIndex, $author, $content, $coachPick]) {
            $findArticle->execute(['title' => $articleTitles[$articleIndex - 1]]);
            $articleId = $findArticle->fetchColumn();
            $findUser->execute(['email' => $demoEmails[$author]]);
            $userId = $findUser->fetchColumn();
            if ($articleId === false || $userId === false) {
                throw new RuntimeException('Unable to seed demo comments because a referenced article or user was not found.');
            }
            $params = ['article_id' => $articleId, 'user_id' => $userId, 'content' => $content];
            $findComment->execute($params);
            if (!$findComment->fetch()) {
                $insertComment->execute($params + ['is_coach_pick' => $coachPick]);
            }
        }
    }
}
?>
