<?php
declare(strict_types=1);

/** An event with a start time stored as a Tunis local DATETIME. */
class Event
{
    public const TIMEZONE = 'Africa/Tunis';

    public function __construct(
        private int $id = 0,
        private string $title = '',
        private string $description = '',
        private string $location = '',
        private string $eventDate = '',
        private int $capacity = 0,
        private ?int $sponsorId = null,
        private ?string $createdAt = null,
        private ?string $updatedAt = null
    ) {
    }

    public function getId(): int { return $this->id; }
    public function getTitle(): string { return $this->title; }
    public function getDescription(): string { return $this->description; }
    public function getLocation(): string { return $this->location; }
    public function getEventDate(): string { return $this->eventDate; }
    public function getCapacity(): int { return $this->capacity; }
    public function getSponsorId(): ?int { return $this->sponsorId; }
    public function getCreatedAt(): ?string { return $this->createdAt; }
    public function getUpdatedAt(): ?string { return $this->updatedAt; }

    public function hasStarted(?DateTimeImmutable $now = null): bool
    {
        $tunis = new DateTimeZone(self::TIMEZONE);
        $localTime = ($now ?? new DateTimeImmutable())->setTimezone($tunis);
        return strcmp($this->eventDate, $localTime->format('Y-m-d H:i:s')) <= 0;
    }

    /** Hydrate the persisted event fields from an associative PDO row. */
    public static function fromArray(array $row): self
    {
        if (!isset(
            $row['id'], $row['title'], $row['description'], $row['location'],
            $row['event_date'], $row['capacity'], $row['created_at'], $row['updated_at']
        )) {
            throw new InvalidArgumentException('Incomplete event row.');
        }

        return new self(
            id: (int) $row['id'],
            title: (string) $row['title'],
            description: (string) $row['description'],
            location: (string) $row['location'],
            eventDate: (string) $row['event_date'],
            capacity: (int) $row['capacity'],
            sponsorId: isset($row['sponsor_id']) ? (int) $row['sponsor_id'] : null,
            createdAt: (string) $row['created_at'],
            updatedAt: (string) $row['updated_at']
        );
    }
}
