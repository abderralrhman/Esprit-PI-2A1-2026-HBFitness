<?php
declare(strict_types=1);

/** An event registration associated with a User Management account ID. */
class EventRegistration
{
    public function __construct(
        private int $id = 0,
        private int $eventId = 0,
        private int $userId = 0,
        private ?string $registeredAt = null
    ) {
    }

    public function getId(): int { return $this->id; }
    public function getEventId(): int { return $this->eventId; }
    public function getUserId(): int { return $this->userId; }
    public function getRegisteredAt(): ?string { return $this->registeredAt; }

    /** Hydrate a complete persisted registration from an associative PDO row. */
    public static function fromArray(array $row): self
    {
        if (!isset($row['id'], $row['event_id'], $row['user_id'], $row['registered_at'])) {
            throw new InvalidArgumentException('Incomplete registration row.');
        }

        return new self(
            id: (int) $row['id'],
            eventId: (int) $row['event_id'],
            userId: (int) $row['user_id'],
            registeredAt: (string) $row['registered_at']
        );
    }
}
