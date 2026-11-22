<?php
declare(strict_types=1);

require_once __DIR__ . '/TrainingSession.php';
require_once __DIR__ . '/Booking.php';

final class SessionRepository
{
    public function __construct(private PDO $pdo)
    {
        $this->pdo->exec(
            'CREATE TABLE IF NOT EXISTS training_session (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                title VARCHAR(120) NOT NULL,
                mode ENUM(\'in-person\', \'online\') NOT NULL DEFAULT \'in-person\',
                start_at DATETIME NOT NULL,
                duration_min INT UNSIGNED NOT NULL,
                max_participants INT UNSIGNED NOT NULL,
                location VARCHAR(150) NOT NULL,
                coach_id INT UNSIGNED NOT NULL,
                PRIMARY KEY (id),
                KEY idx_training_session_start (start_at),
                KEY idx_training_session_coach (coach_id),
                CONSTRAINT fk_training_session_coach FOREIGN KEY (coach_id) REFERENCES users(id)
                    ON DELETE RESTRICT ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
        $this->pdo->exec(
            'CREATE TABLE IF NOT EXISTS booking (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                session_id INT UNSIGNED NOT NULL,
                user_id INT UNSIGNED NOT NULL,
                status ENUM(\'confirmed\', \'cancelled\', \'completed\') NOT NULL DEFAULT \'confirmed\',
                booked_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uk_booking_session_user (session_id, user_id),
                KEY idx_booking_user (user_id),
                KEY idx_booking_status (status),
                CONSTRAINT fk_booking_session FOREIGN KEY (session_id) REFERENCES training_session(id)
                    ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT fk_booking_user FOREIGN KEY (user_id) REFERENCES users(id)
                    ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    public function upcoming(): array
    {
        $rows = $this->pdo->query(
            'SELECT ts.*, u.full_name AS coach_name,
                    (SELECT COUNT(*) FROM booking b WHERE b.session_id = ts.id AND b.status = \'confirmed\') AS confirmed_count
             FROM training_session ts
             JOIN users u ON u.id = ts.coach_id
             WHERE ts.start_at >= NOW()
             ORDER BY ts.start_at ASC'
        )->fetchAll();

        return array_map(static function (array $row): array {
            $session = TrainingSession::fromArray($row);
            return ['session' => $session, 'confirmed_count' => (int)($row['confirmed_count'] ?? 0)];
        }, $rows);
    }

    public function allSessions(?int $coachId = null): array
    {
        $sql = 'SELECT ts.*, u.full_name AS coach_name
                FROM training_session ts
                JOIN users u ON u.id = ts.coach_id';
        $params = [];
        if ($coachId !== null) {
            $sql .= ' WHERE ts.coach_id = ?';
            $params[] = $coachId;
        }
        $sql .= ' ORDER BY ts.start_at DESC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return array_map([TrainingSession::class, 'fromArray'], $stmt->fetchAll());
    }

    public function find(int $id): ?TrainingSession
    {
        $stmt = $this->pdo->prepare(
            'SELECT ts.*, u.full_name AS coach_name
             FROM training_session ts JOIN users u ON u.id = ts.coach_id
             WHERE ts.id = ?'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ? TrainingSession::fromArray($row) : null;
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO training_session (title, mode, start_at, duration_min, max_participants, location, coach_id)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['title'], $data['mode'], $data['start_at'], $data['duration_min'],
            $data['max_participants'], $data['location'], $data['coach_id']
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE training_session
             SET title=?, mode=?, start_at=?, duration_min=?, max_participants=?, location=?, coach_id=?
             WHERE id=?'
        );
        $stmt->execute([
            $data['title'], $data['mode'], $data['start_at'], $data['duration_min'],
            $data['max_participants'], $data['location'], $data['coach_id'], $id
        ]);
        return $stmt->rowCount() >= 0;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM training_session WHERE id=?');
        $stmt->execute([$id]);
        return $stmt->rowCount() === 1;
    }

    public function coaches(): array
    {
        return $this->pdo->query(
            'SELECT u.id, u.full_name
             FROM users u JOIN roles r ON r.id=u.role_id
             WHERE r.name IN (\'coach\', \'admin\') AND u.status_id=1
             ORDER BY u.full_name'
        )->fetchAll();
    }

    public function book(int $sessionId, int $userId): void
    {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare('SELECT max_participants, start_at FROM training_session WHERE id=? FOR UPDATE');
            $stmt->execute([$sessionId]);
            $session = $stmt->fetch();
            if (!$session) throw new DomainException('This session no longer exists.');
            if (strtotime((string)$session['start_at']) <= time()) throw new DomainException('This session has already started.');

            $exists = $this->pdo->prepare(
                'SELECT id, status FROM booking WHERE session_id=? AND user_id=?'
            );
            $exists->execute([$sessionId, $userId]);
            $booking = $exists->fetch();
            if ($booking && $booking['status'] === 'confirmed') {
                throw new DomainException('You already have a place in this session.');
            }

            $count = $this->pdo->prepare(
                'SELECT COUNT(*) FROM booking WHERE session_id=? AND status=\'confirmed\''
            );
            $count->execute([$sessionId]);
            if ((int)$count->fetchColumn() >= (int)$session['max_participants']) {
                throw new DomainException('This session is full.');
            }

            if ($booking) {
                $update = $this->pdo->prepare(
                    'UPDATE booking SET status=\'confirmed\', booked_at=CURRENT_TIMESTAMP WHERE id=?'
                );
                $update->execute([(int)$booking['id']]);
            } else {
                $insert = $this->pdo->prepare(
                    'INSERT INTO booking (session_id, user_id, status, booked_at) VALUES (?, ?, \'confirmed\', CURRENT_TIMESTAMP)'
                );
                $insert->execute([$sessionId, $userId]);
            }
            $this->pdo->commit();
        } catch (Throwable $error) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            throw $error;
        }
    }

    public function cancel(int $sessionId, int $userId): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE booking SET status=\'cancelled\' WHERE session_id=? AND user_id=? AND status=\'confirmed\''
        );
        $stmt->execute([$sessionId, $userId]);
        return $stmt->rowCount() === 1;
    }

    public function bookingsForUser(int $userId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT b.*, ts.title AS session_title, ts.start_at, ts.location,
                    u.full_name AS coach_name
             FROM booking b
             JOIN training_session ts ON ts.id=b.session_id
             JOIN users u ON u.id=ts.coach_id
             WHERE b.user_id=? AND b.status=\'confirmed\'
             ORDER BY ts.start_at ASC'
        );
        $stmt->execute([$userId]);
        return array_map([Booking::class, 'fromArray'], $stmt->fetchAll());
    }

    public function bookingsForSession(int $sessionId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT b.*, ts.title AS session_title, ts.start_at, ts.location,
                    coach.full_name AS coach_name, member.full_name AS user_name
             FROM booking b
             JOIN training_session ts ON ts.id=b.session_id
             JOIN users coach ON coach.id=ts.coach_id
             JOIN users member ON member.id=b.user_id
             WHERE b.session_id=?
             ORDER BY b.booked_at DESC'
        );
        $stmt->execute([$sessionId]);
        return array_map([Booking::class, 'fromArray'], $stmt->fetchAll());
    }

    public function stats(?int $coachId = null): array
    {
        $where = $coachId === null ? '' : ' WHERE ts.coach_id=?';
        $params = $coachId === null ? [] : [$coachId];
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(DISTINCT ts.id) AS session_count,
                    COALESCE(SUM(b.status=\'confirmed\'), 0) AS booking_count
             FROM training_session ts
             LEFT JOIN booking b ON b.session_id=ts.id
             ' . $where
        );
        $stmt->execute($params);
        return $stmt->fetch() ?: ['session_count' => 0, 'booking_count' => 0];
    }
}
