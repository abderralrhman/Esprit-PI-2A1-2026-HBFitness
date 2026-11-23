<?php
declare(strict_types=1);

/**
 * Models/Sponsor.php
 * OOP Entity Model for HB FITNESS sponsors.
 */

class Sponsor
{
    private int $id;
    private string $name;
    private ?string $logo;
    private ?string $website;
    private ?string $contactEmail;
    private int $eventsCount;

    public function __construct(
        int $id = 0,
        string $name = '',
        ?string $logo = null,
        ?string $website = null,
        ?string $contactEmail = null,
        int $eventsCount = 0
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->logo = $logo;
        $this->website = $website;
        $this->contactEmail = $contactEmail;
        $this->eventsCount = $eventsCount;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function getContactEmail(): ?string
    {
        return $this->contactEmail;
    }

    public function getEventsCount(): int
    {
        return $this->eventsCount;
    }

    public static function fromArray(array $row): self
    {
        return new self(
            (int)($row['id'] ?? 0),
            (string)($row['name'] ?? ''),
            isset($row['logo']) && $row['logo'] !== ''
                ? (string)$row['logo'] : null,
            isset($row['website']) && $row['website'] !== ''
                ? (string)$row['website'] : null,
            isset($row['contact_email']) && $row['contact_email'] !== ''
                ? (string)$row['contact_email'] : null,
            (int)($row['events_count'] ?? 0)
        );
    }
}