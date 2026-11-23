<?php
declare(strict_types=1);

/**
 * Models/Event.php
 * Pure OOP Entity Model for HB FITNESS events.
 */
class Event
{
    private int $id;
    private string $title;
    private string $description;
    private string $location;
    private string $eventDate;
    private int $capacity;
    private ?int $sponsorId;
    private ?string $sponsorName;
    private int $registrationsCount;
    private string $createdAt;
    private string $updatedAt;

    public function __construct(
        int $id = 0,
        string $title = '',
        string $description = '',
        string $location = '',
        string $eventDate = '',
        int $capacity = 0,
        ?int $sponsorId = null,
        ?string $sponsorName = null,
        int $registrationsCount = 0,
        string $createdAt = '',
        string $updatedAt = ''
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->location = $location;
        $this->eventDate = $eventDate;
        $this->capacity = $capacity;
        $this->sponsorId = $sponsorId;
        $this->sponsorName = $sponsorName;
        $this->registrationsCount = $registrationsCount;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getLocation(): string
    {
        return $this->location;
    }

    public function setLocation(string $location): void
    {
        $this->location = $location;
    }

    public function getEventDate(): string
    {
        return $this->eventDate;
    }

    public function setEventDate(string $eventDate): void
    {
        $this->eventDate = $eventDate;
    }

    public function getCapacity(): int
    {
        return $this->capacity;
    }

    public function setCapacity(int $capacity): void
    {
        $this->capacity = $capacity;
    }

    public function getSponsorId(): ?int
    {
        return $this->sponsorId;
    }

    public function setSponsorId(?int $sponsorId): void
    {
        $this->sponsorId = $sponsorId;
    }

    public function getSponsorName(): ?string
    {
        return $this->sponsorName;
    }

    public function setSponsorName(?string $sponsorName): void
    {
        $this->sponsorName = $sponsorName;
    }

    public function getRegistrationsCount(): int
    {
        return $this->registrationsCount;
    }

    public function setRegistrationsCount(int $count): void
    {
        $this->registrationsCount = $count;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): string
    {
        return $this->updatedAt;
    }

    public static function fromArray(array $row): self
    {
        return new self(
            (int)($row['id'] ?? 0),
            (string)($row['title'] ?? ''),
            (string)($row['description'] ?? ''),
            (string)($row['location'] ?? ''),
            (string)($row['event_date'] ?? ''),
            (int)($row['capacity'] ?? 0),
            isset($row['sponsor_id'])
                ? (int)$row['sponsor_id'] : null,
            isset($row['sponsor_name'])
                ? (string)$row['sponsor_name'] : null,
            (int)($row['registrations_count'] ?? 0),
            (string)($row['created_at'] ?? ''),
            (string)($row['updated_at'] ?? '')
        );
    }
}