<?php
declare(strict_types=1);

/**
 * Models/EventRegistration.php
 * Pure OOP Entity Model for HB FITNESS event registrations.
 */

class EventRegistration
{
    private int $id;
    private int $eventId;
    private int $userId;
    private string $registeredAt;

    public function __construct(
        int $id = 0,
        int $eventId = 0,
        int $userId = 0,
        string $registeredAt = ''
    ) {
        $this->id = $id;
        $this->eventId = $eventId;
        $this->userId = $userId;
        $this->registeredAt = $registeredAt;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getEventId(): int
    {
        return $this->eventId;
    }

    public function setEventId(int $eventId): void
    {
        $this->eventId = $eventId;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }

    public function getRegisteredAt(): string
    {
        return $this->registeredAt;
    }

    public function setRegisteredAt(string $registeredAt): void
    {
        $this->registeredAt = $registeredAt;
    }

    public static function fromArray(array $row): self
    {
        return new self(
            (int)($row['id'] ?? 0),
            (int)($row['event_id'] ?? 0),
            (int)($row['user_id'] ?? 0),
            (string)($row['registered_at'] ?? '')
        );
    }
}