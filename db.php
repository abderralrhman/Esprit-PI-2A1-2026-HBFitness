<?php
declare(strict_types=1);

function database(): PDO
{
    static $connection = null;
    if ($connection instanceof PDO) {
        return $connection;
    }

    $host = getenv('DB_HOST') ?: '127.0.0.1';
    $port = getenv('DB_PORT') ?: '3306';
    $name = getenv('DB_NAME') ?: 'hb_fitness';
    $user = getenv('DB_USER') ?: 'root';
    $password = getenv('DB_PASSWORD') ?: '';

    $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    try {
        $pdo = new PDO($dsn, $user, $password, $options);
    } catch (PDOException $error) {
        if ((int) ($error->errorInfo[1] ?? 0) !== 1049) {
            throw $error;
        }

        $server = new PDO(
            "mysql:host={$host};port={$port};charset=utf8mb4",
            $user,
            $password,
            $options
        );
        $quotedName = '`' . str_replace('`', '``', $name) . '`';
        $server->exec(
            "CREATE DATABASE IF NOT EXISTS {$quotedName} "
            . 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'
        );
        $pdo = new PDO($dsn, $user, $password, $options);
    }

    ensureMarketplaceTables($pdo);
    $connection = $pdo;
    return $connection;
}

function ensureMarketplaceTables(PDO $connection): void
{
    $connection->exec("
        CREATE TABLE IF NOT EXISTS `role` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(30) NOT NULL UNIQUE,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    $connection->exec("
        CREATE TABLE IF NOT EXISTS `user` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `full_name` VARCHAR(100) NOT NULL,
            `email` VARCHAR(120) NOT NULL UNIQUE,
            `password_hash` VARCHAR(255) NOT NULL,
            `phone` VARCHAR(20) DEFAULT NULL,
            `height_cm` DECIMAL(5,1) DEFAULT NULL,
            `weight_kg` DECIMAL(5,1) DEFAULT NULL,
            `goal` VARCHAR(60) DEFAULT NULL,
            `role_id` INT NOT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            CONSTRAINT `fk_market_user_role`
                FOREIGN KEY (`role_id`) REFERENCES `role` (`id`)
                ON DELETE RESTRICT ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    $connection->exec("
        CREATE TABLE IF NOT EXISTS `equipment_category` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(60) NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    $connection->exec("
        CREATE TABLE IF NOT EXISTS `listing` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `title` VARCHAR(120) NOT NULL,
            `description` TEXT DEFAULT NULL,
            `price` DECIMAL(8,2) NOT NULL,
            `item_condition` VARCHAR(20) DEFAULT NULL,
            `status` VARCHAR(20) DEFAULT 'available',
            `category_id` INT DEFAULT NULL,
            `seller_id` INT NOT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            CONSTRAINT `fk_market_listing_category`
                FOREIGN KEY (`category_id`) REFERENCES `equipment_category` (`id`)
                ON DELETE SET NULL ON UPDATE CASCADE,
            CONSTRAINT `fk_market_listing_seller`
                FOREIGN KEY (`seller_id`) REFERENCES `user` (`id`)
                ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    $createdAt = $connection->query("SHOW COLUMNS FROM `listing` LIKE 'created_at'")->fetch();
    if ($createdAt === false) {
        $connection->exec(
            'ALTER TABLE `listing` ADD COLUMN `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP'
        );
    }
    $connection->exec("
        CREATE TABLE IF NOT EXISTS `market_order` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `listing_id` INT NOT NULL,
            `buyer_id` INT NOT NULL,
            `status` VARCHAR(20) DEFAULT 'pending',
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            CONSTRAINT `fk_market_order_listing`
                FOREIGN KEY (`listing_id`) REFERENCES `listing` (`id`)
                ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `fk_market_order_buyer`
                FOREIGN KEY (`buyer_id`) REFERENCES `user` (`id`)
                ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $roles = $connection->prepare('INSERT IGNORE INTO `role` (`name`) VALUES (?)');
    foreach (['admin', 'coach', 'member'] as $role) {
        $roles->execute([$role]);
    }

    $categories = $connection->prepare('
        INSERT INTO `equipment_category` (`name`)
        SELECT ? WHERE NOT EXISTS (
            SELECT 1 FROM `equipment_category` WHERE LOWER(`name`) = LOWER(?)
        )
    ');
    foreach (['Strength Training', 'Cardio', 'Yoga & Wellness', 'Accessories', 'Outdoor Sports'] as $category) {
        $categories->execute([$category, $category]);
    }
}
