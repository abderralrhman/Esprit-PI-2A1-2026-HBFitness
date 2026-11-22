SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS hb_fitness
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE hb_fitness;

CREATE TABLE IF NOT EXISTS role (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(30) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS `user` (
  id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(100) NOT NULL,
  email VARCHAR(120) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  phone VARCHAR(20),
  height_cm DECIMAL(5,1),
  weight_kg DECIMAL(5,1),
  goal VARCHAR(60),
  role_id INT NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (role_id) REFERENCES role(id) ON DELETE RESTRICT ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS equipment_category (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(60) NOT NULL
);

CREATE TABLE IF NOT EXISTS listing (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(120) NOT NULL,
  description TEXT,
  price DECIMAL(8,2) NOT NULL,
  item_condition VARCHAR(20),
  status VARCHAR(20) DEFAULT 'available',
  category_id INT,
  seller_id INT NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (category_id) REFERENCES equipment_category(id) ON DELETE SET NULL ON UPDATE CASCADE,
  FOREIGN KEY (seller_id) REFERENCES `user`(id) ON DELETE CASCADE ON UPDATE CASCADE
);

SET @listing_created_at_exists = (
  SELECT COUNT(*)
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'listing'
    AND COLUMN_NAME = 'created_at'
);
SET @add_listing_created_at = IF(
  @listing_created_at_exists = 0,
  'ALTER TABLE listing ADD COLUMN created_at DATETIME DEFAULT CURRENT_TIMESTAMP',
  'SELECT 1'
);
PREPARE listing_created_at_statement FROM @add_listing_created_at;
EXECUTE listing_created_at_statement;
DEALLOCATE PREPARE listing_created_at_statement;

CREATE TABLE IF NOT EXISTS market_order (
  id INT AUTO_INCREMENT PRIMARY KEY,
  listing_id INT NOT NULL,
  buyer_id INT NOT NULL,
  status VARCHAR(20) DEFAULT 'pending',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (listing_id) REFERENCES listing(id) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (buyer_id) REFERENCES `user`(id) ON DELETE CASCADE ON UPDATE CASCADE
);

INSERT INTO role (name)
VALUES ('admin'), ('coach'), ('member')
ON DUPLICATE KEY UPDATE name = VALUES(name);

UPDATE equipment_category
SET name = CASE name
  WHEN 'Musculation' THEN 'Strength Training'
  WHEN 'Yoga & bien-être' THEN 'Yoga & Wellness'
  WHEN 'Accessoires' THEN 'Accessories'
  WHEN 'Sports outdoor' THEN 'Outdoor Sports'
  ELSE name
END
WHERE name IN ('Musculation', 'Yoga & bien-être', 'Accessoires', 'Sports outdoor');

UPDATE listing
SET title = CASE title
  WHEN 'Haltères réglables 2 × 20 kg' THEN 'Adjustable dumbbells 2 × 20 kg'
  WHEN 'Tapis de yoga en liège' THEN 'Cork yoga mat'
  WHEN 'Vélo d’appartement FitPro' THEN 'FitPro exercise bike'
  WHEN 'Lot de bandes de résistance' THEN 'Set of resistance bands'
  WHEN 'Banc de musculation inclinable' THEN 'Adjustable weight bench'
  WHEN 'Corde à sauter lestée' THEN 'Weighted jump rope'
  WHEN 'Rouleau de massage premium' THEN 'Premium massage roller'
  ELSE title
END
WHERE title IN (
  'Haltères réglables 2 × 20 kg',
  'Tapis de yoga en liège',
  'Vélo d’appartement FitPro',
  'Lot de bandes de résistance',
  'Banc de musculation inclinable',
  'Corde à sauter lestée',
  'Rouleau de massage premium'
);

UPDATE listing
SET item_condition = CASE item_condition
  WHEN 'Comme neuf' THEN 'Like new'
  WHEN 'Très bon état' THEN 'Very good'
  WHEN 'Bon état' THEN 'Good'
  WHEN 'À réviser' THEN 'Needs repair'
  ELSE item_condition
END
WHERE item_condition IN ('Comme neuf', 'Très bon état', 'Bon état', 'À réviser');

INSERT IGNORE INTO `user` (full_name, email, password_hash, role_id)
SELECT 'Thomas Leroy', 'thomas.leroy@example.invalid', 'demo-account-no-login', id
FROM role WHERE name = 'member';
INSERT IGNORE INTO `user` (full_name, email, password_hash, role_id)
SELECT 'Camille Petit', 'camille.petit@example.invalid', 'demo-account-no-login', id
FROM role WHERE name = 'member';
INSERT IGNORE INTO `user` (full_name, email, password_hash, role_id)
SELECT 'Nicolas Bernard', 'nicolas.bernard@example.invalid', 'demo-account-no-login', id
FROM role WHERE name = 'member';
INSERT IGNORE INTO `user` (full_name, email, password_hash, role_id)
SELECT 'Léa Moreau', 'lea.moreau@example.invalid', 'demo-account-no-login', id
FROM role WHERE name = 'member';
INSERT IGNORE INTO `user` (full_name, email, password_hash, role_id)
SELECT 'Hugo Garnier', 'hugo.garnier@example.invalid', 'demo-account-no-login', id
FROM role WHERE name = 'member';
INSERT IGNORE INTO `user` (full_name, email, password_hash, role_id)
SELECT 'Sarah Blanc', 'sarah.blanc@example.invalid', 'demo-account-no-login', id
FROM role WHERE name = 'member';
INSERT IGNORE INTO `user` (full_name, email, password_hash, role_id)
SELECT 'Adam Robert', 'adam.robert@example.invalid', 'demo-account-no-login', id
FROM role WHERE name = 'member';
INSERT IGNORE INTO `user` (full_name, email, password_hash, role_id)
SELECT 'Emma Laurent', 'emma.laurent@example.invalid', 'demo-account-no-login', id
FROM role WHERE name = 'member';
INSERT IGNORE INTO `user` (full_name, email, password_hash, role_id)
SELECT 'Mehdi Fontaine', 'mehdi.fontaine@example.invalid', 'demo-account-no-login', id
FROM role WHERE name = 'member';
INSERT IGNORE INTO `user` (full_name, email, password_hash, role_id)
SELECT 'Julie André', 'julie.andre@example.invalid', 'demo-account-no-login', id
FROM role WHERE name = 'member';
INSERT IGNORE INTO `user` (full_name, email, password_hash, role_id)
SELECT 'Lucas Michel', 'lucas.michel@example.invalid', 'demo-account-no-login', id
FROM role WHERE name = 'member';
INSERT IGNORE INTO `user` (full_name, email, password_hash, role_id)
SELECT 'Inès Roux', 'ines.roux@example.invalid', 'demo-account-no-login', id
FROM role WHERE name = 'member';
INSERT IGNORE INTO `user` (full_name, email, password_hash, role_id)
SELECT 'Paul Chevalier', 'paul.chevalier@example.invalid', 'demo-account-no-login', id
FROM role WHERE name = 'member';
INSERT IGNORE INTO `user` (full_name, email, password_hash, role_id)
SELECT 'Chloé David', 'chloe.david@example.invalid', 'demo-account-no-login', id
FROM role WHERE name = 'member';

INSERT INTO equipment_category (name)
SELECT category_name
FROM (
  SELECT 'Strength Training' AS category_name
  UNION ALL SELECT 'Cardio'
  UNION ALL SELECT 'Yoga & Wellness'
  UNION ALL SELECT 'Accessories'
  UNION ALL SELECT 'Outdoor Sports'
) AS categories
WHERE NOT EXISTS (
  SELECT 1 FROM equipment_category existing
  WHERE existing.name = categories.category_name
);

INSERT INTO listing (title, price, item_condition, status, category_id, seller_id, created_at)
SELECT sample.title, sample.price, sample.item_condition, sample.status, c.id, u.id, sample.created_at
FROM (
  SELECT 'Adjustable dumbbells 2 × 20 kg' AS title, 'Strength Training' AS category_name, 85.00 AS price, 'Very good' AS item_condition, 'available' AS status, 'Thomas Leroy' AS seller, '2026-11-15 10:00:00' AS created_at
  UNION ALL SELECT 'Cork yoga mat', 'Yoga & Wellness', 32.00, 'Like new', 'available', 'Camille Petit', '2026-11-14 10:00:00'
  UNION ALL SELECT 'FitPro exercise bike', 'Cardio', 210.00, 'Good', 'pending', 'Nicolas Bernard', '2026-11-13 10:00:00'
  UNION ALL SELECT 'Set of resistance bands', 'Accessories', 18.00, 'Like new', 'available', 'Léa Moreau', '2026-11-12 10:00:00'
  UNION ALL SELECT 'Adjustable weight bench', 'Strength Training', 95.00, 'Very good', 'sold', 'Hugo Garnier', '2026-11-11 10:00:00'
  UNION ALL SELECT 'Weighted jump rope', 'Accessories', 14.00, 'Good', 'available', 'Sarah Blanc', '2026-11-10 10:00:00'
  UNION ALL SELECT 'Premium massage roller', 'Yoga & Wellness', 22.00, 'Like new', 'available', 'Adam Robert', '2026-11-09 10:00:00'
) AS sample
JOIN equipment_category c ON c.name = sample.category_name
JOIN `user` u ON u.full_name = sample.seller
WHERE NOT EXISTS (
  SELECT 1 FROM listing existing
  WHERE existing.title = sample.title AND existing.seller_id = u.id
);

INSERT INTO market_order (listing_id, buyer_id, status, created_at)
SELECT l.id, buyer.id, sample.status, sample.created_at
FROM (
  SELECT 'Adjustable weight bench' AS title, 'Emma Laurent' AS buyer, 'pending' AS status, '2026-11-16 09:00:00' AS created_at
  UNION ALL SELECT 'Cork yoga mat', 'Mehdi Fontaine', 'shipped', '2026-11-15 09:00:00'
  UNION ALL SELECT 'Set of resistance bands', 'Julie André', 'completed', '2026-11-14 09:00:00'
  UNION ALL SELECT 'Adjustable dumbbells 2 × 20 kg', 'Lucas Michel', 'pending', '2026-11-13 09:00:00'
  UNION ALL SELECT 'Weighted jump rope', 'Inès Roux', 'completed', '2026-11-12 09:00:00'
  UNION ALL SELECT 'FitPro exercise bike', 'Paul Chevalier', 'shipped', '2026-11-11 09:00:00'
  UNION ALL SELECT 'Premium massage roller', 'Chloé David', 'completed', '2026-11-10 09:00:00'
) AS sample
JOIN listing l ON l.title = sample.title
JOIN `user` buyer ON buyer.full_name = sample.buyer
WHERE NOT EXISTS (
  SELECT 1 FROM market_order existing
  WHERE existing.listing_id = l.id AND existing.buyer_id = buyer.id
);
