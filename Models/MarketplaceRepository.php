<?php
declare(strict_types=1);

final class MarketplaceRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function listings(bool $availableOnly = false, ?string $category = null, ?string $search = null): array
    {
        $conditions = [];
        $parameters = [];

        if ($availableOnly) {
            $conditions[] = 'l.status = ?';
            $parameters[] = 'available';
        }
        if ($category !== null && $category !== '') {
            $conditions[] = 'c.name = ?';
            $parameters[] = $category;
        }
        if ($search !== null && $search !== '') {
            $conditions[] = '(l.title LIKE ? OR l.description LIKE ? OR c.name LIKE ?)';
            $term = '%' . $search . '%';
            array_push($parameters, $term, $term, $term);
        }

        $sql = 'SELECT l.id, l.title, l.price, l.item_condition, l.status, l.created_at,
                       c.name AS category, u.full_name AS seller
                FROM listing l
                LEFT JOIN equipment_category c ON c.id = l.category_id
                JOIN `user` u ON u.id = l.seller_id';
        if ($conditions !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }
        $sql .= ' ORDER BY l.created_at DESC, l.id DESC';

        $statement = $this->pdo->prepare($sql);
        $statement->execute($parameters);

        return array_map([$this, 'formatListing'], $statement->fetchAll());
    }

    public function listing(int $id): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT l.id, l.title, l.price, l.item_condition, l.status, l.created_at,
                    l.description, c.name AS category, u.full_name AS seller
             FROM listing l
             LEFT JOIN equipment_category c ON c.id = l.category_id
             JOIN `user` u ON u.id = l.seller_id
             WHERE l.id = ?'
        );
        $statement->execute([$id]);
        $row = $statement->fetch();

        return $row === false ? null : $this->formatListing($row);
    }

    public function orders(): array
    {
        $rows = $this->pdo->query(
            'SELECT o.id, o.created_at, o.status,
                    l.id AS listing_id, l.title AS item, l.price AS amount, c.name AS category,
                    b.full_name AS buyer
             FROM market_order o
             JOIN listing l ON l.id = o.listing_id
             LEFT JOIN equipment_category c ON c.id = l.category_id
             JOIN `user` b ON b.id = o.buyer_id
             ORDER BY o.created_at DESC, o.id DESC'
        )->fetchAll();

        return array_map(static function (array $row): array {
            $timestamp = strtotime($row['created_at']);
            $presentation = self::categoryPresentation($row['category'] ?? '');

            return [
                'id' => (int) $row['id'],
                'reference' => sprintf('AM-%s-%03d', date('Y', $timestamp), (int) $row['id']),
                'listingId' => (int) $row['listing_id'],
                'item' => self::listingTitle($row['item']),
                'buyer' => $row['buyer'],
                'date' => date('m/d/Y', $timestamp),
                'dateIso' => date('Y-m-d', $timestamp),
                'amount' => (float) $row['amount'],
                'status' => self::orderStatusFromDatabase($row['status']),
                'icon' => $presentation['icon'],
            ];
        }, $rows);
    }

    public function categories(): array
    {
        $rows = $this->pdo->query(
            'SELECT c.id, c.name, COUNT(l.id) AS listing_count
             FROM equipment_category c
             LEFT JOIN listing l ON l.category_id = c.id
             GROUP BY c.id, c.name
             ORDER BY c.name'
        )->fetchAll();

        return array_map(static function (array $row): array {
            $presentation = self::categoryPresentation($row['name']);

            return [
                'id' => (int) $row['id'],
                'name' => $row['name'],
                'label' => $presentation['label'],
                'icon' => $presentation['icon'],
                'description' => $presentation['description'],
                'count' => (int) $row['listing_count'],
            ];
        }, $rows);
    }

    public function createListing(array $data): int
    {
        $category = $this->pdo->prepare('SELECT id FROM equipment_category WHERE name = ? LIMIT 1');
        $category->execute([$data['category']]);
        $categoryId = $category->fetchColumn();
        if ($categoryId === false) {
            throw new DomainException('The selected category no longer exists.');
        }

        $seller = $this->pdo->prepare('SELECT id FROM `user` WHERE full_name = ? ORDER BY id LIMIT 1');
        $seller->execute([$data['seller']]);
        $sellerId = $seller->fetchColumn();
        if ($sellerId === false) {
            $role = $this->pdo->prepare('SELECT id FROM role WHERE name = ? LIMIT 1');
            $role->execute(['member']);
            $roleId = $role->fetchColumn();
            if ($roleId === false) {
                throw new RuntimeException('The member role is missing. Import marketplace_setup.sql.');
            }

            $createSeller = $this->pdo->prepare(
                'INSERT INTO `user` (full_name, email, password_hash, role_id) VALUES (?, ?, ?, ?)'
            );
            $createSeller->execute([
                $data['seller'],
                'marketplace+' . bin2hex(random_bytes(12)) . '@example.invalid',
                password_hash(bin2hex(random_bytes(24)), PASSWORD_DEFAULT),
                $roleId,
            ]);
            $sellerId = (int) $this->pdo->lastInsertId();
        }

        $insert = $this->pdo->prepare(
            'INSERT INTO listing (title, price, item_condition, status, category_id, seller_id)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $insert->execute([
            $data['title'],
            $data['price'],
            $data['condition'],
            'available',
            $categoryId,
            $sellerId,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function updateListingStatus(int $id, string $status): bool
    {
        $statuses = [
            'Available' => 'available',
            'Pending' => 'pending',
            'Sold' => 'sold',
        ];
        if (!isset($statuses[$status])) {
            throw new DomainException('Invalid listing status.');
        }

        $update = $this->pdo->prepare('UPDATE listing SET status = ? WHERE id = ?');
        $update->execute([$statuses[$status], $id]);
        if ($update->rowCount() > 0) {
            return true;
        }

        $exists = $this->pdo->prepare('SELECT 1 FROM listing WHERE id = ?');
        $exists->execute([$id]);

        return (bool) $exists->fetchColumn();
    }

    public function updateListing(int $id, array $data): bool
    {
        $category = $this->pdo->prepare('SELECT id FROM equipment_category WHERE name = ? LIMIT 1');
        $category->execute([$data['category']]);
        $categoryId = $category->fetchColumn();
        if ($categoryId === false) {
            throw new DomainException('The selected category no longer exists.');
        }

        $seller = $this->pdo->prepare('SELECT id FROM `user` WHERE full_name = ? ORDER BY id LIMIT 1');
        $seller->execute([$data['seller']]);
        $sellerId = $seller->fetchColumn();
        if ($sellerId === false) {
            throw new DomainException('The selected seller no longer exists.');
        }

        $update = $this->pdo->prepare(
            'UPDATE listing SET title = ?, price = ?, item_condition = ?, category_id = ?, seller_id = ? WHERE id = ?'
        );
        $update->execute([
            $data['title'],
            $data['price'],
            $data['condition'],
            $categoryId,
            $sellerId,
            $id,
        ]);

        if ($update->rowCount() > 0) {
            return true;
        }

        $exists = $this->pdo->prepare('SELECT 1 FROM listing WHERE id = ?');
        $exists->execute([$id]);
        return (bool) $exists->fetchColumn();
    }

    public function deleteListing(int $id): bool
    {
        $delete = $this->pdo->prepare('DELETE FROM listing WHERE id = ?');
        $delete->execute([$id]);

        return $delete->rowCount() === 1;
    }

    public function createCategory(string $name): int
    {
        $existing = $this->pdo->prepare('SELECT 1 FROM equipment_category WHERE LOWER(name) = LOWER(?) LIMIT 1');
        $existing->execute([$name]);
        if ($existing->fetchColumn()) {
            throw new DomainException('This category already exists.');
        }

        $insert = $this->pdo->prepare('INSERT INTO equipment_category (name) VALUES (?)');
        $insert->execute([$name]);

        return (int) $this->pdo->lastInsertId();
    }

    public function updateCategory(int $id, string $name): bool
    {
        $existing = $this->pdo->prepare('SELECT 1 FROM equipment_category WHERE LOWER(name) = LOWER(?) AND id <> ? LIMIT 1');
        $existing->execute([$name, $id]);
        if ($existing->fetchColumn()) {
            throw new DomainException('This category already exists.');
        }

        $update = $this->pdo->prepare('UPDATE equipment_category SET name = ? WHERE id = ?');
        $update->execute([$name, $id]);
        if ($update->rowCount() > 0) {
            return true;
        }

        $exists = $this->pdo->prepare('SELECT 1 FROM equipment_category WHERE id = ?');
        $exists->execute([$id]);
        return (bool) $exists->fetchColumn();
    }

    public function deleteCategory(int $id): bool
    {
        $count = $this->pdo->prepare('SELECT COUNT(*) FROM listing WHERE category_id = ?');
        $count->execute([$id]);
        if ((int) $count->fetchColumn() > 0) {
            throw new DomainException('A category containing listings cannot be deleted.');
        }

        $delete = $this->pdo->prepare('DELETE FROM equipment_category WHERE id = ?');
        $delete->execute([$id]);
        return $delete->rowCount() === 1;
    }

    public function deleteOrder(int $id): bool
    {
        $this->pdo->beginTransaction();
        try {
            $order = $this->pdo->prepare('SELECT listing_id FROM market_order WHERE id = ?');
            $order->execute([$id]);
            $listingId = $order->fetchColumn();
            if ($listingId === false) {
                $this->pdo->rollBack();
                return false;
            }

            $delete = $this->pdo->prepare('DELETE FROM market_order WHERE id = ?');
            $delete->execute([$id]);
            $this->pdo->prepare("UPDATE listing SET status = 'available' WHERE id = ? AND status IN ('pending', 'shipped')")
                ->execute([$listingId]);
            $this->pdo->commit();
            return $delete->rowCount() === 1;
        } catch (Throwable $error) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $error;
        }
    }

    public function updateOrder(int $id, int $listingId, string $buyerName, string $status): bool
    {
        $statuses = [
            'To process' => 'pending',
            'Shipped' => 'shipped',
            'Completed' => 'completed',
            'Cancelled' => 'cancelled',
        ];
        if (!isset($statuses[$status])) {
            throw new DomainException('Invalid order status.');
        }

        $this->pdo->beginTransaction();
        try {
            $current = $this->pdo->prepare('SELECT listing_id, buyer_id FROM market_order WHERE id = ? FOR UPDATE');
            $current->execute([$id]);
            $order = $current->fetch();
            if ($order === false) {
                $this->pdo->rollBack();
                return false;
            }

            $listing = $this->pdo->prepare('SELECT status FROM listing WHERE id = ?');
            $listing->execute([$listingId]);
            $listingStatus = $listing->fetchColumn();
            if ($listingStatus === false) {
                throw new DomainException('The selected listing no longer exists.');
            }

            $oldListingId = (int) $order['listing_id'];
            if ($listingId !== $oldListingId && $listingStatus !== 'available') {
                throw new DomainException('The selected listing is not available.');
            }

            $buyer = $this->pdo->prepare('UPDATE `user` SET full_name = ? WHERE id = ?');
            $buyer->execute([$buyerName, (int) $order['buyer_id']]);

            $update = $this->pdo->prepare('UPDATE market_order SET listing_id = ?, status = ? WHERE id = ?');
            $update->execute([$listingId, $statuses[$status], $id]);

            if ($listingId !== $oldListingId) {
                $this->pdo->prepare("UPDATE listing SET status = 'available' WHERE id = ?")->execute([$oldListingId]);
            }

            $newListingStatus = [
                'pending' => 'pending',
                'shipped' => 'pending',
                'completed' => 'sold',
                'cancelled' => 'available',
            ][$statuses[$status]];
            $this->pdo->prepare('UPDATE listing SET status = ? WHERE id = ?')->execute([$newListingStatus, $listingId]);

            $this->pdo->commit();
            return true;
        } catch (Throwable $error) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $error;
        }
    }

    public function advanceOrder(int $id, string $nextStatus): bool
    {
        $this->pdo->beginTransaction();
        try {
            $current = $this->pdo->prepare('SELECT status, listing_id FROM market_order WHERE id = ? FOR UPDATE');
            $current->execute([$id]);
            $currentOrder = $current->fetch();
            if ($currentOrder === false) {
                $this->pdo->rollBack();
                return false;
            }

            $transitions = [
                'pending' => ['Shipped' => 'shipped', 'Cancelled' => 'cancelled'],
                'shipped' => ['Completed' => 'completed', 'Cancelled' => 'cancelled'],
            ];
            $status = $transitions[$currentOrder['status']][$nextStatus] ?? null;
            if ($status === null) {
                $this->pdo->rollBack();
                throw new DomainException('This order status transition is not allowed.');
            }

            $update = $this->pdo->prepare('UPDATE market_order SET status = ? WHERE id = ?');
            $update->execute([$status, $id]);
            if ($status === 'completed') {
                $this->pdo->prepare('UPDATE listing SET status = ? WHERE id = ?')
                    ->execute(['sold', $currentOrder['listing_id']]);
            }
            if ($status === 'cancelled') {
                $this->pdo->prepare('UPDATE listing SET status = ? WHERE id = ?')
                    ->execute(['available', $currentOrder['listing_id']]);
            }
            $this->pdo->commit();

            return true;
        } catch (Throwable $error) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $error;
        }
    }

    public function createOrder(int $listingId, string $buyerName, string $buyerEmail): int
    {
        $this->pdo->beginTransaction();
        try {
            $listing = $this->pdo->prepare('SELECT status FROM listing WHERE id = ? FOR UPDATE');
            $listing->execute([$listingId]);
            $listingStatus = $listing->fetchColumn();
            if ($listingStatus === false) {
                throw new DomainException('This listing no longer exists.');
            }
            if ($listingStatus !== 'available') {
                throw new DomainException('This listing is no longer available.');
            }

            $role = $this->pdo->prepare('SELECT id FROM role WHERE name = ? LIMIT 1');
            $role->execute(['member']);
            $roleId = $role->fetchColumn();
            if ($roleId === false) {
                throw new RuntimeException('The member role is missing. Import marketplace_setup.sql.');
            }

            $buyer = $this->pdo->prepare('SELECT id FROM `user` WHERE email = ? LIMIT 1');
            $buyer->execute([$buyerEmail]);
            $buyerId = $buyer->fetchColumn();
            if ($buyerId === false) {
                $createBuyer = $this->pdo->prepare(
                    'INSERT INTO `user` (full_name, email, password_hash, role_id) VALUES (?, ?, ?, ?)'
                );
                $createBuyer->execute([
                    $buyerName,
                    $buyerEmail,
                    password_hash(bin2hex(random_bytes(24)), PASSWORD_DEFAULT),
                    $roleId,
                ]);
                $buyerId = (int) $this->pdo->lastInsertId();
            }

            $order = $this->pdo->prepare(
                'INSERT INTO market_order (listing_id, buyer_id, status) VALUES (?, ?, ?)'
            );
            $order->execute([$listingId, $buyerId, 'pending']);
            $this->pdo->prepare('UPDATE listing SET status = ? WHERE id = ?')->execute(['pending', $listingId]);
            $orderId = (int) $this->pdo->lastInsertId();
            $this->pdo->commit();

            return $orderId;
        } catch (Throwable $error) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $error;
        }
    }

    private function formatListing(array $row): array
    {
        $category = $row['category'] ?? 'Uncategorized';
        $presentation = self::categoryPresentation($category);

        return [
            'id' => (int) $row['id'],
            'title' => self::listingTitle($row['title']),
            'description' => $row['description'] ?? '',
            'category' => $category,
            'categoryLabel' => $presentation['label'],
            'price' => (float) $row['price'],
            'seller' => $row['seller'],
            'condition' => self::conditionLabel($row['item_condition'] ?? 'Good'),
            'status' => self::listingStatusFromDatabase($row['status']),
            'icon' => $presentation['icon'],
            'date' => date('Y-m-d', strtotime($row['created_at'])),
        ];
    }

    private static function listingStatusFromDatabase(string $status): string
    {
        return [
            'available' => 'Available',
            'pending' => 'Pending',
            'sold' => 'Sold',
        ][$status] ?? 'Pending';
    }

    private static function orderStatusFromDatabase(string $status): string
    {
        return [
            'pending' => 'To process',
            'shipped' => 'Shipped',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
        ][$status] ?? 'To process';
    }

    private static function categoryPresentation(string $name): array
    {
        $presentation = [
            'Strength Training' => ['Strength Training', '🏋️', 'Dumbbells, benches, weights, and machines'],
            'Musculation' => ['Strength Training', '🏋️', 'Dumbbells, benches, weights, and machines'],
            'Cardio' => ['Cardio', '🚴', 'Exercise bikes, treadmills, and rowers'],
            'Yoga & Wellness' => ['Yoga & Wellness', '🧘', 'Mats, blocks, and mindfulness accessories'],
            'Yoga & bien-être' => ['Yoga & Wellness', '🧘', 'Mats, blocks, and mindfulness accessories'],
            'Accessories' => ['Accessories', '🎽', 'Small equipment and sportswear'],
            'Accessoires' => ['Accessories', '🎽', 'Small equipment and sportswear'],
            'Outdoor Sports' => ['Outdoor Sports', '⛰️', 'Equipment for outdoor training'],
            'Sports outdoor' => ['Outdoor Sports', '⛰️', 'Equipment for outdoor training'],
        ];
        $details = $presentation[$name] ?? [$name, '📦', 'Sports equipment'];
        [$label, $icon, $description] = $details;

        return ['label' => $label, 'icon' => $icon, 'description' => $description];
    }

    private static function conditionLabel(string $condition): string
    {
        return [
            'Comme neuf' => 'Like new',
            'Très bon état' => 'Very good',
            'Bon état' => 'Good',
            'À réviser' => 'Needs repair',
        ][$condition] ?? $condition;
    }

    private static function listingTitle(string $title): string
    {
        return [
            'Haltères réglables 2 × 20 kg' => 'Adjustable dumbbells 2 × 20 kg',
            'Tapis de yoga en liège' => 'Cork yoga mat',
            'Vélo d’appartement FitPro' => 'FitPro exercise bike',
            'Lot de bandes de résistance' => 'Set of resistance bands',
            'Banc de musculation inclinable' => 'Adjustable weight bench',
            'Corde à sauter lestée' => 'Weighted jump rope',
            'Rouleau de massage premium' => 'Premium massage roller',
        ][$title] ?? $title;
    }
}
