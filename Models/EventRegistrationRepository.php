<?php
declare(strict_types=1);

require_once __DIR__ . '/EventRegistration.php';
require_once __DIR__ . '/EventRepository.php';

/** Manages registrations using the same PDO transaction as the event row lock. */
final class EventRegistrationRepository
{
    public function __construct(private PDO $db, private EventRepository $events)
    {
    }

    public function hasRegistration(int $eventId, int $userId): bool
    {
        $stmt = $this->db->prepare('
            SELECT 1 FROM event_registrations WHERE event_id = :event_id AND user_id = :user_id
        ');
        $stmt->execute(['event_id' => $eventId, 'user_id' => $userId]);
        return $stmt->fetchColumn() !== false;
    }

    public function register(int $eventId, int $userId): void
    {
        $this->events->withLockedEvent($eventId, function (Event $event) use ($eventId, $userId): void {
            $user = $this->db->prepare('SELECT id FROM users WHERE id = :id FOR UPDATE');
            $user->execute(['id' => $userId]);
            if ($user->fetchColumn() === false) {
                throw new DomainException('Your account is no longer available.');
            }
            $this->requireOpenEvent($event);
            if ($this->hasRegistration($eventId, $userId)) {
                throw new DomainException('You are already registered for this event.');
            }
            if ($this->events->countRegistrations($eventId) >= $event->getCapacity()) {
                throw new DomainException('This event is full.');
            }

            $stmt = $this->db->prepare('
                INSERT INTO event_registrations (event_id, user_id) VALUES (:event_id, :user_id)
            ');
            try {
                $stmt->execute(['event_id' => $eventId, 'user_id' => $userId]);
            } catch (PDOException $error) {
                if ((int) ($error->errorInfo[1] ?? 0) === 1062
                    && str_contains((string) ($error->errorInfo[2] ?? ''), 'uk_event_user')) {
                    throw new DomainException('You are already registered for this event.', 0, $error);
                }
                throw $error;
            }
        });
    }

    public function cancel(int $eventId, int $userId): void
    {
        $this->events->withLockedEvent($eventId, function (Event $event) use ($eventId, $userId): void {
            $this->requireOpenEvent($event);
            $stmt = $this->db->prepare('
                DELETE FROM event_registrations WHERE event_id = :event_id AND user_id = :user_id
            ');
            $stmt->execute(['event_id' => $eventId, 'user_id' => $userId]);
            if ($stmt->rowCount() === 0) {
                throw new DomainException('You do not have a registration for this event.');
            }
        });
    }

    /** @return list<array{registration: EventRegistration, userName: ?string}> */
    public function participants(int $eventId): array
    {
        $stmt = $this->db->prepare('
            SELECT r.id, r.event_id, r.user_id, r.registered_at, u.full_name AS user_name
            FROM event_registrations r
            LEFT JOIN users u ON u.id = r.user_id
            WHERE r.event_id = :event_id
            ORDER BY r.registered_at, r.id
        ');
        $stmt->execute(['event_id' => $eventId]);

        return array_map(static fn(array $row): array => [
            'registration' => EventRegistration::fromArray($row),
            'userName' => $row['user_name'],
        ], $stmt->fetchAll());
    }

    private function requireOpenEvent(Event $event): void
    {
        if ($event->hasStarted()) {
            throw new DomainException('Registration and cancellation close when the event starts.');
        }
    }
}
